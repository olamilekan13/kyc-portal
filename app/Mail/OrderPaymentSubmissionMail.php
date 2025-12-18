<?php

namespace App\Mail;

use App\Models\PartnerOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaymentSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(PartnerOrder $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Payment Submitted - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-payment-submission',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
