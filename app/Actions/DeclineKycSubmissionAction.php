<?php

namespace App\Actions;

use App\Events\SubmissionDeclined;
use App\Mail\KycDeclineMail;
use App\Models\KycNotification;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

/**
 * Action class for declining KYC submissions
 *
 * This action encapsulates the business logic for declining a KYC submission,
 * including status updates, reason recording, notification creation and sending,
 * activity logging, and event firing.
 */
class DeclineKycSubmissionAction
{
    /**
     * Execute the decline action for a KYC submission
     *
     * This method performs the following steps:
     * 1. Validates that submission can be declined
     * 2. Updates submission status to 'declined'
     * 3. Sets reviewed_by, reviewed_at, and decline_reason
     * 4. Creates notification record in database
     * 5. Sends decline notification email
     * 6. Logs activity using Spatie activity log
     * 7. Fires SubmissionDeclined event
     *
     * @param KycSubmission $submission The submission to decline
     * @param int $reviewerId The ID of the user declining the submission
     * @param string $reason The reason for declining the submission
     * @return void
     *
     * @throws Exception If decline process encounters an error
     */
    public function execute(KycSubmission $submission, int $reviewerId, string $reason): void
    {
        try {
            // Validate inputs
            $this->validateInputs($submission, $reason);

            // Get reviewer user
            $reviewer = User::findOrFail($reviewerId);

            Log::info('KYC submission decline initiated', [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewerId,
                'reviewer_name' => $reviewer->name,
                'current_status' => $submission->status,
                'reason_length' => strlen($reason),
            ]);

            // Use database transaction to ensure atomicity
            DB::beginTransaction();

            try {
                // Update submission status
                $submission->update([
                    'status' => KycSubmission::STATUS_DECLINED,
                    'reviewed_by' => $reviewerId,
                    'reviewed_at' => now(),
                    'decline_reason' => $reason,
                ]);

                // Create notification record
                $notification = $this->createNotificationRecord($submission, $reviewer, $reason);

                // Log activity
                $this->logActivity($submission, $reviewer, $reason);

                DB::commit();

                // Send notification email (outside transaction to avoid rollback on email failure)
                $this->sendNotificationEmail($submission, $notification);

                // Fire event
                event(new SubmissionDeclined($submission, $reviewer, $reason));

                Log::info('KYC submission declined successfully', [
                    'submission_id' => $submission->id,
                    'reviewer_id' => $reviewerId,
                    'status' => $submission->status,
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Failed to decline KYC submission', [
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to decline submission: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate inputs for declining submission
     *
     * @param KycSubmission $submission
     * @param string $reason
     * @return void
     *
     * @throws Exception If validation fails
     */
    protected function validateInputs(KycSubmission $submission, string $reason): void
    {
        // Check if submission is already declined
        if ($submission->status === KycSubmission::STATUS_DECLINED) {
            throw new Exception('Submission has already been declined');
        }

        // Check if submission is already approved
        if ($submission->status === KycSubmission::STATUS_APPROVED) {
            throw new Exception('Cannot decline an approved submission');
        }

        // Check if submission is verified (can only decline verified submissions)
        if ($submission->status !== KycSubmission::STATUS_VERIFIED) {
            throw new Exception('Submission must be verified before it can be declined. Current status: ' . $submission->status);
        }

        // Validate decline reason
        if (empty(trim($reason))) {
            throw new Exception('Decline reason is required and cannot be empty');
        }

        // Ensure reason has minimum length
        if (strlen(trim($reason)) < 10) {
            throw new Exception('Decline reason must be at least 10 characters long');
        }
    }

    /**
     * Create notification record in database
     *
     * @param KycSubmission $submission
     * @param User $reviewer
     * @param string $reason
     * @return KycNotification
     */
    protected function createNotificationRecord(
        KycSubmission $submission,
        User $reviewer,
        string $reason
    ): KycNotification {
        $recipient = $this->getRecipientEmail($submission);
        $subject = 'KYC Submission Declined - Reference #' . $submission->id;
        $message = $this->buildDeclineMessage($submission, $reviewer, $reason);

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

        $email = $submissionData['email']
            ?? $submissionData['email_address']
            ?? $submissionData['contact_email']
            ?? null;

        if (empty($email)) {
            Log::warning('No email found in submission data, using fallback', [
                'submission_id' => $submission->id,
            ]);
            return 'noreply@example.com'; // Fallback
        }

        return $email;
    }

    /**
     * Build decline notification message
     *
     * @param KycSubmission $submission
     * @param User $reviewer
     * @param string $reason
     * @return string
     */
    protected function buildDeclineMessage(
        KycSubmission $submission,
        User $reviewer,
        string $reason
    ): string {
        $applicantName = $this->getApplicantName($submission);

        return <<<MESSAGE
Dear {$applicantName},

Thank you for submitting your KYC (Know Your Customer) information. After careful review, we regret to inform you that your submission has been declined.

Submission Reference: #{$submission->id}
Reviewed By: {$reviewer->name}
Declined On: {$submission->reviewed_at->format('F d, Y \a\t H:i A')}

Reason for Decline:
{$reason}

What's Next?
You may resubmit your KYC application with the necessary corrections or additional documentation. Please ensure all information is accurate and complete before resubmitting.

If you believe this decision was made in error or if you have any questions, please contact our support team with your reference number.

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

            // Send email using the KycDeclineMail Mailable
            Mail::to($notification->recipient)
                ->send(new KycDeclineMail(
                    $submission,
                    $notification->recipient,
                    $submission->decline_reason ?? 'No reason provided',
                    $reviewerName
                ));

            // Mark notification as sent
            $notification->markAsSent();

            Log::info('Decline notification email sent', [
                'submission_id' => $submission->id,
                'notification_id' => $notification->id,
                'recipient' => $notification->recipient,
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the decline process
            Log::error('Failed to send decline notification email', [
                'submission_id' => $submission->id,
                'notification_id' => $notification->id,
                'recipient' => $notification->recipient,
                'error' => $e->getMessage(),
            ]);

            // Note: We don't rethrow here because the decline was successful,
            // we just failed to send the notification email
        }
    }

    /**
     * Log activity using Spatie activity log
     *
     * @param KycSubmission $submission
     * @param User $reviewer
     * @param string $reason
     * @return void
     */
    protected function logActivity(KycSubmission $submission, User $reviewer, string $reason): void
    {
        activity()
            ->performedOn($submission)
            ->causedBy($reviewer)
            ->withProperties([
                'submission_id' => $submission->id,
                'old_status' => $submission->getOriginal('status'),
                'new_status' => KycSubmission::STATUS_DECLINED,
                'reviewed_by' => $reviewer->id,
                'reviewer_name' => $reviewer->name,
                'reviewed_at' => $submission->reviewed_at->toIso8601String(),
                'decline_reason' => $reason,
            ])
            ->log('KYC submission declined');
    }
}
