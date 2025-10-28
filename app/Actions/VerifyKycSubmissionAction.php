<?php

namespace App\Actions;

use App\Events\SubmissionVerified;
use App\Events\SubmissionVerificationFailed;
use App\Models\KycSubmission;
use App\Services\YouVerifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Action class for verifying KYC submissions through YouVerify
 *
 * This action encapsulates the business logic for verifying a KYC submission,
 * including status updates, API interaction, event firing, and activity logging.
 *
 * @see App\Services\YouVerifyService
 * @see App\Events\SubmissionVerified
 * @see App\Events\SubmissionVerificationFailed
 */
class VerifyKycSubmissionAction
{
    /**
     * YouVerify service instance
     *
     * @var YouVerifyService
     */
    protected YouVerifyService $youVerifyService;

    /**
     * Create a new action instance with dependency injection
     *
     * @param YouVerifyService $youVerifyService The YouVerify service for API integration
     */
    public function __construct(YouVerifyService $youVerifyService)
    {
        $this->youVerifyService = $youVerifyService;
    }

    /**
     * Execute the verification action for a KYC submission
     *
     * This method performs the following steps:
     * 1. Validates that submission is not already verified
     * 2. Updates submission status to 'under_review'
     * 3. Calls YouVerify API to verify identity
     * 4. Fires appropriate success or failure events
     * 5. Logs activity using Spatie activity log
     * 6. Returns standardized result array
     *
     * @param KycSubmission $submission The submission to verify
     * @return array{success: bool, verified: bool, message: string, data?: array, error?: string}
     *
     * @throws Exception If an unexpected error occurs during verification
     */
    public function execute(KycSubmission $submission): array
    {
        try {
            // Check if submission is already verified
            if ($this->isAlreadyVerified($submission)) {
                return $this->handleAlreadyVerified($submission);
            }

            // Log the verification attempt
            Log::info('KYC verification action initiated', [
                'submission_id' => $submission->id,
                'form_id' => $submission->kyc_form_id,
                'current_status' => $submission->status,
                'verification_status' => $submission->verification_status,
            ]);

            // Use database transaction to ensure atomicity
            DB::beginTransaction();

            try {
                // Update submission status to 'under_review'
                $submission->update([
                    'status' => KycSubmission::STATUS_UNDER_REVIEW,
                ]);

                // Log activity for status change
                activity()
                    ->performedOn($submission)
                    ->withProperties([
                        'old_status' => $submission->getOriginal('status'),
                        'new_status' => KycSubmission::STATUS_UNDER_REVIEW,
                        'action' => 'verification_initiated',
                    ])
                    ->log('KYC submission verification initiated');

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Call YouVerify service to verify identity
            $verificationResponse = $this->youVerifyService->verifyIdentity($submission);

            // Refresh the submission to get latest data
            $submission->refresh();

            // Handle the verification response
            if ($verificationResponse['success'] && $verificationResponse['verified']) {
                return $this->handleSuccessfulVerification($submission, $verificationResponse);
            } else {
                return $this->handleFailedVerification($submission, $verificationResponse);
            }
        } catch (Exception $e) {
            return $this->handleException($submission, $e);
        }
    }

    /**
     * Check if submission is already verified
     *
     * @param KycSubmission $submission
     * @return bool
     */
    protected function isAlreadyVerified(KycSubmission $submission): bool
    {
        return $submission->verification_status === KycSubmission::VERIFICATION_VERIFIED;
    }

    /**
     * Handle case where submission is already verified
     *
     * @param KycSubmission $submission
     * @return array
     */
    protected function handleAlreadyVerified(KycSubmission $submission): array
    {
        Log::info('KYC verification skipped: Already verified', [
            'submission_id' => $submission->id,
        ]);

        return [
            'success' => true,
            'verified' => true,
            'message' => 'Submission has already been verified',
            'data' => [
                'submission_id' => $submission->id,
                'verification_status' => $submission->verification_status,
                'verified_at' => $submission->verificationLogs()->latest()->first()?->created_at,
            ],
        ];
    }

    /**
     * Handle successful verification response
     *
     * @param KycSubmission $submission
     * @param array $verificationResponse
     * @return array
     */
    protected function handleSuccessfulVerification(
        KycSubmission $submission,
        array $verificationResponse
    ): array {
        // Log activity for successful verification
        activity()
            ->performedOn($submission)
            ->withProperties([
                'verification_provider' => 'YouVerify',
                'verification_status' => KycSubmission::VERIFICATION_VERIFIED,
                'submission_status' => KycSubmission::STATUS_VERIFIED,
                'verification_data' => $verificationResponse['data'] ?? [],
            ])
            ->log('KYC submission verified successfully through YouVerify');

        // Fire successful verification event
        event(new SubmissionVerified($submission, $verificationResponse['data'] ?? []));

        Log::info('KYC verification action completed successfully', [
            'submission_id' => $submission->id,
            'verification_status' => $submission->verification_status,
            'submission_status' => $submission->status,
        ]);

        return [
            'success' => true,
            'verified' => true,
            'message' => 'Identity verified successfully. The submission has been marked as verified and is ready for final review.',
            'data' => [
                'submission_id' => $submission->id,
                'verification_status' => $submission->verification_status,
                'submission_status' => $submission->status,
                'verification_response' => $verificationResponse['data'] ?? [],
            ],
        ];
    }

    /**
     * Handle failed verification response
     *
     * @param KycSubmission $submission
     * @param array $verificationResponse
     * @return array
     */
    protected function handleFailedVerification(
        KycSubmission $submission,
        array $verificationResponse
    ): array {
        $errorMessage = $verificationResponse['error'] ?? $verificationResponse['message'] ?? 'Verification failed';

        // Update submission with error information
        DB::transaction(function () use ($submission, $errorMessage, $verificationResponse) {
            $submission->update([
                'verification_status' => KycSubmission::VERIFICATION_FAILED,
            ]);

            // Log activity for failed verification
            activity()
                ->performedOn($submission)
                ->withProperties([
                    'verification_provider' => 'YouVerify',
                    'verification_status' => KycSubmission::VERIFICATION_FAILED,
                    'error_message' => $errorMessage,
                    'error_data' => $verificationResponse['data'] ?? [],
                ])
                ->log('KYC submission verification failed');
        });

        // Fire failed verification event
        event(new SubmissionVerificationFailed(
            $submission,
            $errorMessage,
            $verificationResponse['data'] ?? []
        ));

        Log::warning('KYC verification action failed', [
            'submission_id' => $submission->id,
            'error_message' => $errorMessage,
            'verification_status' => $submission->verification_status,
        ]);

        return [
            'success' => false,
            'verified' => false,
            'message' => 'Verification failed: ' . $errorMessage,
            'error' => $errorMessage,
            'data' => [
                'submission_id' => $submission->id,
                'verification_status' => $submission->verification_status,
                'error_details' => $verificationResponse['data'] ?? [],
            ],
        ];
    }

    /**
     * Handle exceptions during verification
     *
     * @param KycSubmission $submission
     * @param Exception $e
     * @return array
     */
    protected function handleException(KycSubmission $submission, Exception $e): array
    {
        // Rollback any pending transactions
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        $errorMessage = $e->getMessage();

        // Update submission status to failed
        try {
            DB::transaction(function () use ($submission, $errorMessage, $e) {
                $submission->update([
                    'verification_status' => KycSubmission::VERIFICATION_FAILED,
                ]);

                // Log activity for exception
                activity()
                    ->performedOn($submission)
                    ->withProperties([
                        'verification_provider' => 'YouVerify',
                        'verification_status' => KycSubmission::VERIFICATION_FAILED,
                        'exception' => get_class($e),
                        'error_message' => $errorMessage,
                    ])
                    ->log('KYC submission verification encountered an error');
            });
        } catch (Exception $updateException) {
            Log::error('Failed to update submission after verification exception', [
                'submission_id' => $submission->id,
                'original_exception' => $e->getMessage(),
                'update_exception' => $updateException->getMessage(),
            ]);
        }

        // Fire failed verification event
        event(new SubmissionVerificationFailed($submission, $errorMessage, [
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString(),
        ]));

        Log::error('KYC verification action encountered an exception', [
            'submission_id' => $submission->id,
            'exception' => get_class($e),
            'error_message' => $errorMessage,
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'verified' => false,
            'message' => 'An unexpected error occurred during verification: ' . $errorMessage,
            'error' => $errorMessage,
            'data' => [
                'submission_id' => $submission->id,
                'exception_type' => get_class($e),
            ],
        ];
    }
}
