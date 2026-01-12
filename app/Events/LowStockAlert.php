<?php

namespace App\Events;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public ?ProductVariation $variation = null,
        public int $currentStock = 0
    ) {}
}
