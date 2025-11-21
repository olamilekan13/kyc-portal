<?php

namespace App\Mail;

use App\Models\FinalOnboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnershipExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public FinalOnboarding $finalOnboarding;
    public string $partnerName;
    public string $partnershipModel;
    public string $expiryDate;
    public string $renewalUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(FinalOnboarding $finalOnboarding)
    {
        $this->finalOnboarding = $finalOnboarding;
        $this->partnerName = $finalOnboarding->partner_name ?? 'Partner';
        $this->partnershipModel = $finalOnboarding->partnership_model_name ?? 'Partnership';
        $this->expiryDate = $finalOnboarding->formatted_end_date ?? 'N/A';
        $this->renewalUrl = url('/renew/' . $finalOnboarding->renewal_token);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Partnership Has Expired - Renew to Continue',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.renewal.expired',
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
