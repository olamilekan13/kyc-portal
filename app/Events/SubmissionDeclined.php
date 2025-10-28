<?php

namespace App\Events;

use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a KYC submission is declined
 *
 * This event is dispatched after a KYC submission has been reviewed and declined
 * by an authorized user. Listeners can use this to send decline notifications,
 * log the decision, or trigger remediation workflows.
 */
class SubmissionDeclined
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The declined KYC submission
     *
     * @var KycSubmission
     */
    public KycSubmission $submission;

    /**
     * The user who declined the submission
     *
     * @var User
     */
    public User $decliner;

    /**
     * The reason for declining the submission
     *
     * @var string
     */
    public string $reason;

    /**
     * Create a new event instance
     *
     * @param KycSubmission $submission The submission that was declined
     * @param User $decliner The user who declined the submission
     * @param string $reason The reason for declining
     */
    public function __construct(KycSubmission $submission, User $decliner, string $reason)
    {
        $this->submission = $submission;
        $this->decliner = $decliner;
        $this->reason = $reason;
    }

    /**
     * Get the declined submission
     *
     * @return KycSubmission
     */
    public function getSubmission(): KycSubmission
    {
        return $this->submission;
    }

    /**
     * Get the user who declined the submission
     *
     * @return User
     */
    public function getDecliner(): User
    {
        return $this->decliner;
    }

    /**
     * Get the decline reason
     *
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
