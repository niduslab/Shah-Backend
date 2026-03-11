<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_order',
            'title' => 'New Order Received',
            'message' => "New order #{$this->order->order_number} has been placed.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'customer_name' => $this->order->customer_display_name,
            'action_url' => "/admin/orders/{$this->order->id}",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'new_order',
            'title' => 'New Order Received',
            'message' => "New order #{$this->order->order_number} has been placed.",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'customer_name' => $this->order->customer_display_name,
            'action_url' => "/admin/orders/{$this->order->id}",
        ]);
    }
}
