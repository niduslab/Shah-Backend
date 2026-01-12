<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'quantity',
        'is_default',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'is_default' => 'boolean',
    ];

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get variation values.
     */
    public function variationValues()
    {
        return $this->hasMany(VariationValue::class);
    }

    /**
     * Get order items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get inventory logs.
     */
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Get effective price (variation price or product price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price ?? $this->product->price;
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute(): string
    {
        $threshold = $this->product->low_stock_threshold;

        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->quantity <= $threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Check if in stock.
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Decrement stock.
     */
    public function decrementStock(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Increment stock.
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Get variation name (combination of option values).
     */
    public function getNameAttribute(): string
    {
        return $this->variationValues()
            ->with('variationOption')
            ->get()
            ->pluck('variationOption.value')
            ->implode(' / ');
    }
}
