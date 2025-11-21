<?php

namespace App\Mail;

use App\Models\FinalOnboarding;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnershipRenewalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public FinalOnboarding $finalOnboarding;
    public string $partnerName;
    public string $partnershipModel;
    public string $expiryDate;
    public int $daysUntilExpiry;
    public string $renewalUrl;
    public string $partnershipPrice;
    public string $bankName;
    public string $bankAccountNumber;
    public string $bankAccountName;

    /**
     * Create a new message instance.
     */
    public function __construct(FinalOnboarding $finalOnboarding)
    {
        $this->finalOnboarding = $finalOnboarding;
        $this->partnerName = $finalOnboarding->partner_name ?? 'Partner';
        $this->partnershipModel = $finalOnboarding->partnership_model_name ?? 'Partnership';
        $this->expiryDate = $finalOnboarding->formatted_end_date ?? 'N/A';
        $this->daysUntilExpiry = $finalOnboarding->days_until_expiry ?? 0;
        $this->renewalUrl = url('/renew/' . $finalOnboarding->renewal_token);
        $this->partnershipPrice = '₦' . number_format($finalOnboarding->partnership_model_price, 2);
        $this->bankName = SystemSetting::get('bank_name', '');
        $this->bankAccountNumber = SystemSetting::get('bank_account_number', '');
        $this->bankAccountName = SystemSetting::get('bank_account_name', '');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->daysUntilExpiry <= 1
            ? 'URGENT: Your Partnership Expires Tomorrow!'
            : "Your Partnership Expires in {$this->daysUntilExpiry} Days - Renew Now";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.renewal.reminder',
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
