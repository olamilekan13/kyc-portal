<?php

namespace App\Events;

use App\Models\KycSubmission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a KYC submission verification fails
 *
 * This event is dispatched when verification through YouVerify API fails due to
 * API errors, validation failures, or unverified identity data.
 * Listeners can use this to send failure notifications, log errors, or trigger
 * alternative verification workflows.
 */
class SubmissionVerificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The KYC submission that failed verification
     *
     * @var KycSubmission
     */
    public KycSubmission $submission;

    /**
     * The error message or reason for failure
     *
     * @var string
     */
    public string $errorMessage;

    /**
     * The full error/response data from YouVerify
     *
     * @var array
     */
    public array $errorData;

    /**
     * Create a new event instance
     *
     * @param KycSubmission $submission The submission that failed verification
     * @param string $errorMessage The error message describing the failure
     * @param array $errorData The complete error/response data from the verification service
     */
    public function __construct(KycSubmission $submission, string $errorMessage, array $errorData = [])
    {
        $this->submission = $submission;
        $this->errorMessage = $errorMessage;
        $this->errorData = $errorData;
    }

    /**
     * Get the submission that failed verification
     *
     * @return KycSubmission
     */
    public function getSubmission(): KycSubmission
    {
        return $this->submission;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get the complete error data
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }
}
