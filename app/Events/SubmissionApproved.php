<?php

namespace App\Events;

use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a KYC submission is approved
 *
 * This event is dispatched after a KYC submission has been reviewed and approved
 * by an authorized user. Listeners can use this to send approval notifications,
 * update related systems, or trigger post-approval workflows.
 */
class SubmissionApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The approved KYC submission
     *
     * @var KycSubmission
     */
    public KycSubmission $submission;

    /**
     * The user who approved the submission
     *
     * @var User
     */
    public User $approver;

    /**
     * Create a new event instance
     *
     * @param KycSubmission $submission The submission that was approved
     * @param User $approver The user who approved the submission
     */
    public function __construct(KycSubmission $submission, User $approver)
    {
        $this->submission = $submission;
        $this->approver = $approver;
    }

    /**
     * Get the approved submission
     *
     * @return KycSubmission
     */
    public function getSubmission(): KycSubmission
    {
        return $this->submission;
    }

    /**
     * Get the user who approved the submission
     *
     * @return User
     */
    public function getApprover(): User
    {
        return $this->approver;
    }
}
