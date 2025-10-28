<?php

namespace App\Actions;

use App\Events\SubmissionApproved;
use App\Mail\KycApprovalMail;
use App\Models\KycNotification;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

/**
 * Action class for approving KYC submissions
 *
 * This action encapsulates the business logic for approving a KYC submission,
 * including status updates, notification creation and sending, activity logging,
 * and event firing.
 */
class ApproveKycSubmissionAction
{
    /**
     * Execute the approval action for a KYC submission
     *
     * This method performs the following steps:
     * 1. Validates that submission can be approved
     * 2. Updates submission status to 'approved'
     * 3. Sets reviewed_by and reviewed_at timestamps
     * 4. Creates notification record in database
     * 5. Sends approval notification email
     * 6. Logs activity using Spatie activity log
     * 7. Fires SubmissionApproved event
     *
     * @param KycSubmission $submission The submission to approve
     * @param int $reviewerId The ID of the user approving the submission
     * @return void
     *
     * @throws Exception If approval process encounters an error
     */
    public function execute(KycSubmission $submission, int $reviewerId): void
    {
        try {
            // Validate that submission can be approved
            $this->validateCanApprove($submission);

            // Get reviewer user
            $reviewer = User::findOrFail($reviewerId);

            Log::info('KYC submission approval initiated', [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewerId,
                'reviewer_name' => $reviewer->name,
                'current_status' => $submission->status,
            ]);

            // Use database transaction to ensure atomicity
            DB::beginTransaction();

            try {
                // Update submission status
                $submission->update([
                    'status' => KycSubmission::STATUS_APPROVED,
                    'reviewed_by' => $reviewerId,
                    'reviewed_at' => now(),
                ]);

                // Create notification record
                $notification = $this->createNotificationRecord($submission, $reviewer);

                // Log activity
                $this->logActivity($submission, $reviewer);

                DB::commit();

                // Send notification email (outside transaction to avoid rollback on email failure)
                $this->sendNotificationEmail($submission, $notification);

                // Fire event
                event(new SubmissionApproved($submission, $reviewer));

                Log::info('KYC submission approved successfully', [
                    'submission_id' => $submission->id,
                    'reviewer_id' => $reviewerId,
                    'status' => $submission->status,
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Failed to approve KYC submission', [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to approve submission: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate that the submission can be approved
     *
     * @param KycSubmission $submission
     * @return void
     *
     * @throws Exception If submission cannot be approved
     */
    protected function validateCanApprove(KycSubmission $submission): void
    {
        // Check if submission is already approved
        if ($submission->status === KycSubmission::STATUS_APPROVED) {
            throw new Exception('Submission has already been approved');
        }

        // Check if submission is already declined
        if ($submission->status === KycSubmission::STATUS_DECLINED) {
            throw new Exception('Cannot approve a declined submission');
        }

        // Check if submission is verified
        if ($submission->status !== KycSubmission::STATUS_VERIFIED) {
            throw new Exception('Submission must be verified before it can be approved. Current status: ' . $submission->status);
        }
    }

    /**
     * Create notification record in database
     *
     * @param KycSubmission $submission
     * @param User $reviewer
     * @return KycNotification
     */
    protected function createNotificationRecord(KycSubmission $submission, User $reviewer): KycNotification
    {
        $recipient = $this->getRecipientEmail($submission);
        $subject = 'KYC Submission Approved - Reference #' . $submission->id;
        $message = $this->buildApprovalMessage($submission, $reviewer);

        return $submission->notifications()->create([
            'type' => KycNotification::TYPE_EMAIL,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'sent_at' => null, // Will be set after email is sent
        ]);
    }

    /**
     * Get recipient email from submission data
     *
     * @param KycSubmission $submission
     * @return string
     */
    protected function getRecipientEmail(KycSubmission $submission): string
    {
        $submissionData = $submission->submission_data;

        return $submissionData['email']
            ?? $submissionData['email_address']
            ?? $submissionData['contact_email']
            ?? 'noreply@example.com'; // Fallback (should log warning if this happens)
    }

    /**
     * Build approval notification message
     *
     * @param KycSubmission $submission
     * @param User $reviewer
     * @return string
     */
    protected function buildApprovalMessage(KycSubmission $submission, User $reviewer): string
    {
        $applicantName = $this->getApplicantName($submission);

        return <<<MESSAGE
Dear {$applicantName},

We are pleased to inform you that your KYC (Know Your Customer) submission has been reviewed and approved.

Submission Reference: #{$submission->id}
Reviewed By: {$reviewer->name}
Approved On: {$submission->reviewed_at->format('F d, Y \a\t H:i A')}

Your account is now fully verified and you can proceed with using our services.

If you have any questions or concerns, please don't hesitate to contact our support team.

Best regards,
KYC Verification Team
MESSAGE;
    }

    /**
     * Get applicant name from submission data
     *
     * @param KycSubmission $submission
     * @return string
     */
    protected function getApplicantName(KycSubmission $submission): string
    {
        $data = $submission->submission_data;

        $firstName = $data['first_name'] ?? $data['firstName'] ?? '';
        $lastName = $data['last_name'] ?? $data['lastName'] ?? '';

        $fullName = trim($firstName . ' ' . $lastName);

        return !empty($fullName) ? $fullName : 'Valued Customer';
    }

    /**
     * Send notification email
     *
     * @param KycSubmission $submission
     * @param KycNotification $notification
     * @return void
     */
    protected function sendNotificationEmail(KycSubmission $submission, KycNotification $notification): void
    {
        try {
            // Get reviewer name
            $reviewer = $submission->reviewer;
            $reviewerName = $reviewer?->name ?? 'KYC Review Team';

            // Send email using the KycApprovalMail Mailable
            Mail::to($notification->recipient)
                ->send(new KycApprovalMail(
                    $submission,
                    $notification->recipient,
                    $reviewerName
                ));

            // Mark notification as sent
            $notification->markAsSent();

            Log::info('Approval notification email sent', [
                'submission_id' => $submission->id,
                'notification_id' => $notification->id,
                'recipient' => $notification->recipient,
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the approval process
            Log::error('Failed to send approval notification email', [
                'submission_id' => $submission->id,
                'notification_id' => $notification->id,
                'recipient' => $notification->recipient,
                'error' => $e->getMessage(),
            ]);

            // Note: We don't rethrow here because the approval was successful,
            // we just failed to send the notification email
        }
    }

    /**
     * Log activity using Spatie activity log
     *
     * @param KycSubmission $submission
     * @param User $reviewer
     * @return void
     */
    protected function logActivity(KycSubmission $submission, User $reviewer): void
    {
        activity()
            ->performedOn($submission)
            ->causedBy($reviewer)
            ->withProperties([
                'submission_id' => $submission->id,
                'old_status' => $submission->getOriginal('status'),
                'new_status' => KycSubmission::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewer_name' => $reviewer->name,
                'reviewed_at' => $submission->reviewed_at->toIso8601String(),
            ])
            ->log('KYC submission approved');
    }
}
