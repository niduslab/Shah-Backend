<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\Contracts\InventoryServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService implements InventoryServiceInterface
{
    /**
     * Check if product/variation has sufficient stock.
     * 
     * @param Product $product
     * @param int $quantity
     * @param ProductVariation|null $variation
     * @return bool
     */
    public function checkAvailability(Product $product, int $quantity, ?ProductVariation $variation = null): bool
    {
        if ($variation) {
            return $variation->quantity >= $quantity;
        }

        return $product->quantity >= $quantity;
    }

    /**
     * Reserve stock for an order item (decrement inventory).
     * 
     * @param OrderItem $item
     * @return void
     */
    public function reserveStock(OrderItem $item): void
    {
        DB::transaction(function () use ($item) {
            $product = $item->product;
            $variation = $item->productVariation;
            $quantity = $item->quantity;

            if ($variation) {
                $this->decrementVariationStock($variation, $quantity, 'sale', $item);
            } else {
                $this->decrementProductStock($product, $quantity, 'sale', $item);
            }
        });
    }

    /**
     * Release reserved stock (e.g., order cancelled).
     * 
     * @param OrderItem $item
     * @return void
     */
    public function releaseStock(OrderItem $item): void
    {
        DB::transaction(function () use ($item) {
            $product = $item->product;
            $variation = $item->productVariation;
            $quantity = $item->quantity;

            if ($variation) {
                $this->incrementVariationStock($variation, $quantity, 'return', $item);
            } else {
                $this->incrementProductStock($product, $quantity, 'return', $item);
            }
        });
    }

    /**
     * Adjust stock with logging.
     * 
     * @param Product $product
     * @param int $adjustment Positive or negative
     * @param string $reason
     * @param ProductVariation|null $variation
     * @param string|null $notes
     * @return void
     */
    public function adjustStock(
        Product $product,
        int $adjustment,
        string $reason,
        ?ProductVariation $variation = null,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($product, $adjustment, $reason, $variation, $notes) {
            if ($variation) {
                $quantityBefore = $variation->quantity;
                $newQuantity = max(0, $quantityBefore + $adjustment);
                
                $variation->update(['quantity' => $newQuantity]);

                $this->logInventoryChange(
                    $product,
                    $variation,
                    $quantityBefore,
                    $adjustment,
                    $reason,
                    $notes
                );
            } else {
                $quantityBefore = $product->quantity;
                $newQuantity = max(0, $quantityBefore + $adjustment);
                
                $product->update(['quantity' => $newQuantity]);

                $this->logInventoryChange(
                    $product,
                    null,
                    $quantityBefore,
                    $adjustment,
                    $reason,
                    $notes
                );
            }
        });
    }

    /**
     * Get products with low stock.
     * 
     * @param int|null $threshold Override default threshold
     * @return Collection
     */
    public function getLowStockProducts(?int $threshold = null): Collection
    {
        $query = Product::active()
            ->with(['category', 'brand', 'variations']);

        if ($threshold !== null) {
            $query->where('quantity', '<=', $threshold);
        } else {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        $lowStockProducts = $query->get();

        // Also check variations
        $variationsLowStock = ProductVariation::with(['product.category', 'product.brand'])
            ->whereHas('product', fn($q) => $q->active())
            ->when($threshold !== null, function ($q) use ($threshold) {
                $q->where('quantity', '<=', $threshold);
            }, function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->whereColumn('product_variations.quantity', '<=', 'products.low_stock_threshold');
                });
            })
            ->get();

        return collect([
            'products' => $lowStockProducts,
            'variations' => $variationsLowStock,
        ]);
    }

    /**
     * Get inventory logs for a product.
     * 
     * @param Product $product
     * @param ProductVariation|null $variation
     * @return Collection
     */
    public function getInventoryLogs(Product $product, ?ProductVariation $variation = null): Collection
    {
        $query = InventoryLog::where('product_id', $product->id)
            ->with('createdBy')
            ->orderBy('created_at', 'desc');

        if ($variation) {
            $query->where('product_variation_id', $variation->id);
        }

        return $query->get();
    }

    /**
     * Decrement product stock.
     * 
     * @param Product $product
     * @param int $quantity
     * @param string $reason
     * @param OrderItem|null $orderItem
     * @return void
     */
    protected function decrementProductStock(Product $product, int $quantity, string $reason, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $product->quantity;
        $newQuantity = max(0, $quantityBefore - $quantity);

        $product->update(['quantity' => $newQuantity]);

        $this->logInventoryChange(
            $product,
            null,
            $quantityBefore,
            -$quantity,
            $reason,
            null,
            $orderItem ? 'order_item' : null,
            $orderItem?->id
        );
    }

    /**
     * Increment product stock.
     * 
     * @param Product $product
     * @param int $quantity
     * @param string $reason
     * @param OrderItem|null $orderItem
     * @return void
     */
    protected function incrementProductStock(Product $product, int $quantity, string $reason, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $product->quantity;
        $newQuantity = $quantityBefore + $quantity;

        $product->update(['quantity' => $newQuantity]);

        $this->logInventoryChange(
            $product,
            null,
            $quantityBefore,
            $quantity,
            $reason,
            null,
            $orderItem ? 'order_item' : null,
            $orderItem?->id
        );
    }

    /**
     * Decrement variation stock.
     * 
     * @param ProductVariation $variation
     * @param int $quantity
     * @param string $reason
     * @param OrderItem|null $orderItem
     * @return void
     */
    protected function decrementVariationStock(ProductVariation $variation, int $quantity, string $reason, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $variation->quantity;
        $newQuantity = max(0, $quantityBefore - $quantity);

        $variation->update(['quantity' => $newQuantity]);

        $this->logInventoryChange(
            $variation->product,
            $variation,
            $quantityBefore,
            -$quantity,
            $reason,
            null,
            $orderItem ? 'order_item' : null,
            $orderItem?->id
        );
    }

    /**
     * Increment variation stock.
     * 
     * @param ProductVariation $variation
     * @param int $quantity
     * @param string $reason
     * @param OrderItem|null $orderItem
     * @return void
     */
    protected function incrementVariationStock(ProductVariation $variation, int $quantity, string $reason, ?OrderItem $orderItem = null): void
    {
        $quantityBefore = $variation->quantity;
        $newQuantity = $quantityBefore + $quantity;

        $variation->update(['quantity' => $newQuantity]);

        $this->logInventoryChange(
            $variation->product,
            $variation,
            $quantityBefore,
            $quantity,
            $reason,
            null,
            $orderItem ? 'order_item' : null,
            $orderItem?->id
        );
    }

    /**
     * Log inventory change.
     * 
     * @param Product $product
     * @param ProductVariation|null $variation
     * @param int $quantityBefore
     * @param int $quantityChange
     * @param string $reason
     * @param string|null $notes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     */
    protected function logInventoryChange(
        Product $product,
        ?ProductVariation $variation,
        int $quantityBefore,
        int $quantityChange,
        string $reason,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        InventoryLog::create([
            'product_id' => $product->id,
            'product_variation_id' => $variation?->id,
            'quantity_before' => $quantityBefore,
            'quantity_change' => $quantityChange,
            'quantity_after' => $quantityBefore + $quantityChange,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }
}
