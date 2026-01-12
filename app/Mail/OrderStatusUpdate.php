<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus
    ) {}

    public function envelope(): Envelope
    {
        $statusMessages = [
            'confirmed' => 'Your Order Has Been Confirmed',
            'processing' => 'Your Order Is Being Processed',
            'shipped' => 'Your Order Has Been Shipped',
            'delivered' => 'Your Order Has Been Delivered',
            'cancelled' => 'Your Order Has Been Cancelled',
        ];

        $subject = $statusMessages[$this->order->status] ?? 'Order Status Update';

        return new Envelope(
            subject: $subject . ' - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-update',
            with: [
                'order' => $this->order,
                'previousStatus' => $this->previousStatus,
            ],
        );
    }
}
