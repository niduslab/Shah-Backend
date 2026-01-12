<?php

namespace App\Services\Contracts;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Collection;

interface InventoryServiceInterface
{
    /**
     * Check if product/variation has sufficient stock.
     */
    public function checkAvailability(Product $product, int $quantity, ?ProductVariation $variation = null): bool;

    /**
     * Reserve stock for an order item.
     */
    public function reserveStock(OrderItem $item): void;

    /**
     * Release reserved stock (e.g., order cancelled).
     */
    public function releaseStock(OrderItem $item): void;

    /**
     * Adjust stock with logging.
     */
    public function adjustStock(
        Product $product,
        int $adjustment,
        string $reason,
        ?ProductVariation $variation = null,
        ?string $notes = null
    ): void;

    /**
     * Get products with low stock.
     */
    public function getLowStockProducts(?int $threshold = null): Collection;

    /**
     * Get inventory logs for a product.
     */
    public function getInventoryLogs(Product $product, ?ProductVariation $variation = null): Collection;
}
