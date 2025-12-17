<?php

namespace App\Mail;

use App\Models\PartnerUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $partnerUser;
    public $plainPassword;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(PartnerUser $partnerUser, string $plainPassword)
    {
        $this->partnerUser = $partnerUser;
        $this->plainPassword = $plainPassword;
        $this->loginUrl = route('partner.login');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Your Partner Dashboard - Login Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-welcome',
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
