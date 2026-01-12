<?php

namespace App\Listeners;

use App\Events\ReturnCompleted;
use App\Services\Contracts\InventoryServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class RestoreInventoryOnReturn implements ShouldQueue
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService
    ) {}

    public function handle(ReturnCompleted $event): void
    {
        $return = $event->return;
        $orderItem = $return->orderItem;

        // Restore inventory
        $this->inventoryService->adjustStock(
            $orderItem->product_id,
            $orderItem->product_variation_id,
            $return->quantity,
            'return',
            "Return #{$return->id} completed"
        );
    }
}
