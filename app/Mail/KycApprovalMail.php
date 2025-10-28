<?php

namespace App\Mail;

use App\Models\KycSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for KYC Approval Email
 *
 * Sends a professional, branded email to notify applicants
 * that their KYC submission has been approved.
 */
class KycApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The KYC submission instance
     *
     * @var KycSubmission
     */
    public KycSubmission $submission;

    /**
     * The applicant's email address
     *
     * @var string
     */
    public string $applicantEmail;

    /**
     * The reviewer's name
     *
     * @var string|null
     */
    public ?string $reviewerName;

    /**
     * Create a new message instance.
     *
     * @param KycSubmission $submission
     * @param string $applicantEmail
     * @param string|null $reviewerName
     */
    public function __construct(
        KycSubmission $submission,
        string $applicantEmail,
        ?string $reviewerName = null
    ) {
        $this->submission = $submission;
        $this->applicantEmail = $applicantEmail;
        $this->reviewerName = $reviewerName ?? 'KYC Review Team';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'KYC Submission Approved - Reference #' . $this->submission->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.kyc.approval',
            with: [
                'submission' => $this->submission,
                'applicantEmail' => $this->applicantEmail,
                'reviewerName' => $this->reviewerName,
                'referenceNumber' => $this->submission->id,
                'reviewDate' => $this->submission->reviewed_at?->format('F j, Y'),
                'formName' => $this->submission->kycForm->name ?? 'KYC Application',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
