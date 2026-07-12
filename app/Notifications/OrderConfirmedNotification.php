<?php

namespace App\Notifications;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new OrderConfirmation($this->order))
            ->to($this->order->customer_email ?? $notifiable->email);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'order_confirmed',
            'title' => 'Order Confirmed',
            'message' => "Your order #{$this->order->order_number} has been confirmed and will be processed soon.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'action_url' => "/orders/{$this->order->order_number}",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
