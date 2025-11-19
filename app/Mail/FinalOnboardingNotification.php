<?php

namespace App\Mail;

use App\Models\KycSubmission;
use App\Models\FinalOnboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FinalOnboardingNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $kycSubmission;
    public $finalOnboarding;

    /**
     * Create a new message instance.
     */
    public function __construct(KycSubmission $kycSubmission, FinalOnboarding $finalOnboarding)
    {
        $this->kycSubmission = $kycSubmission;
        $this->finalOnboarding = $finalOnboarding;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Final Onboarding Submission - ' . $this->kycSubmission->onboarding_token,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.onboarding.notification',
            with: [
                'kycSubmission' => $this->kycSubmission,
                'finalOnboarding' => $this->finalOnboarding,
                'applicantName' => $this->kycSubmission->submission_data['full_name'] ?? 'N/A',
                'applicantEmail' => $this->kycSubmission->submission_data['email'] ?? 'N/A',
                'partnershipModel' => $this->finalOnboarding->partnership_model_name,
                'totalAmount' => $this->finalOnboarding->total_amount,
                'paymentMethod' => ucfirst(str_replace('_', ' ', $this->finalOnboarding->payment_method)),
                'onboardingToken' => $this->kycSubmission->onboarding_token,
                'submittedAt' => $this->finalOnboarding->created_at->format('F d, Y h:i A'),
            ]
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
