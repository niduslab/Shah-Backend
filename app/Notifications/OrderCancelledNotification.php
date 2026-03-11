<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $cancelledBy;

    public function __construct(Order $order, $cancelledBy = 'customer')
    {
        $this->order = $order;
        $this->cancelledBy = $cancelledBy;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $isAdmin = $notifiable->isAdmin();
        
        if ($isAdmin) {
            return [
                'type' => 'order_cancelled',
                'title' => 'Order Cancelled',
                'message' => "Order #{$this->order->order_number} has been cancelled by {$this->cancelledBy}.",
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'cancelled_by' => $this->cancelledBy,
                'action_url' => "/admin/orders/{$this->order->id}",
            ];
        }

        return [
            'type' => 'order_cancelled',
            'title' => 'Order Cancelled',
            'message' => "Your order #{$this->order->order_number} has been cancelled.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'action_url' => "/orders/{$this->order->order_number}",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
