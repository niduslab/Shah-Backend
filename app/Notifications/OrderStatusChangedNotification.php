<?php

namespace App\Notifications;

use App\Mail\OrderStatusUpdate;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $oldStatus;
    public $newStatus;

    public function __construct(Order $order, $oldStatus, $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new OrderStatusUpdate($this->order, $this->oldStatus))
            ->to($this->order->customer_email ?? $notifiable->email);
    }

    public function toArray($notifiable)
    {
        $statusMessages = [
            'pending' => 'is pending',
            'confirmed' => 'has been confirmed',
            'processing' => 'is being processed',
            'shipped' => 'has been shipped',
            'delivered' => 'has been delivered',
            'cancelled' => 'has been cancelled',
        ];

        $message = $statusMessages[$this->newStatus] ?? 'status has been updated';

        return [
            'type' => 'order_status_changed',
            'title' => 'Order Status Updated',
            'message' => "Your order #{$this->order->order_number} {$message}.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'tracking_number' => $this->order->tracking_number,
            'action_url' => "/orders/{$this->order->order_number}",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
