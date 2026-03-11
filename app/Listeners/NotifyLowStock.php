<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyLowStock implements ShouldQueue
{
    public function handle(LowStockAlert $event): void
    {
        $product = $event->product;
        $variation = $event->variation;
        $currentStock = $event->currentStock;

        $message = $variation
            ? "Low stock alert: {$product->name} (Variation: {$variation->sku}) - Current stock: {$currentStock}"
            : "Low stock alert: {$product->name} - Current stock: {$currentStock}";

        // Log the alert
        Log::warning($message);

        // Send notification to admin users
        app(\App\Services\NotificationService::class)->notifyLowStock($product, $currentStock);
    }
}
