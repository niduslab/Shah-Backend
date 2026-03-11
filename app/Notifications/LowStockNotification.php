<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $product;
    public $currentStock;

    public function __construct(Product $product, $currentStock)
    {
        $this->product = $product;
        $this->currentStock = $currentStock;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'low_stock',
            'title' => 'Low Stock Alert',
            'message' => "{$this->product->name} is running low on stock. Only {$this->currentStock} units remaining.",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->currentStock,
            'action_url' => "/admin/products/{$this->product->id}",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
