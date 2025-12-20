<?php

namespace App\Mail;

use App\Models\PartnerOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $viewOrderUrl;
    public $dashboardUrl;

    public function __construct(PartnerOrder $order)
    {
        $this->order = $order;
        $this->viewOrderUrl = route('partner.orders.show', ['order' => $order->id]);
        $this->dashboardUrl = route('partner.dashboard');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Payment Rejected - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
