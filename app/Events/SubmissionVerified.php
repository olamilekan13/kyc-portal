<?php

namespace App\Events;

use App\Models\KycSubmission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a KYC submission is successfully verified
 *
 * This event is dispatched after a successful verification through YouVerify API.
 * Listeners can use this to send notifications, update related records, or trigger
 * additional business logic.
 */
class SubmissionVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The verified KYC submission
     *
     * @var KycSubmission
     */
    public KycSubmission $submission;

    /**
     * The verification response data from YouVerify
     *
     * @var array
     */
    public array $verificationData;

    /**
     * Create a new event instance
     *
     * @param KycSubmission $submission The submission that was verified
     * @param array $verificationData The response data from the verification service
     */
    public function __construct(KycSubmission $submission, array $verificationData)
    {
        $this->submission = $submission;
        $this->verificationData = $verificationData;
    }

    /**
     * Get the submission that was verified
     *
     * @return KycSubmission
     */
    public function getSubmission(): KycSubmission
    {
        return $this->submission;
    }

    /**
     * Get the verification response data
     *
     * @return array
     */
    public function getVerificationData(): array
    {
        return $this->verificationData;
    }
}
