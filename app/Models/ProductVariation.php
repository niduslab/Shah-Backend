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
        'shipping_type',
        'shipping_cost',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'is_default' => 'boolean',
        'shipping_cost' => 'decimal:2',
    ];

    protected $appends = [
        'attributes',
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

    /**
     * Get attributes as key-value pairs (e.g., ["color" => "Red", "size" => "XL"]).
     */
    public function getAttributesAttribute(): array
    {
        $attributes = [];

        foreach ($this->variationValues as $variationValue) {
            if ($variationValue->variationOption && $variationValue->variationOption->variation) {
                $attributeName = strtolower($variationValue->variationOption->variation->name);
                $attributeValue = $variationValue->variationOption->value;
                $attributes[$attributeName] = $attributeValue;
            }
        }

        return $attributes;
    }

    /**
     * Get shipping cost for this variation.
     *
     * @param int $quantity
     * @return float|null Returns null if inheriting from product
     */
    public function getCustomShippingCost(int $quantity = 1): ?float
    {
        // If variation has its own shipping type, use it
        if ($this->shipping_type && $this->shipping_type !== 'inherit') {
            return match ($this->shipping_type) {
                'free' => 0,
                'fixed' => $this->shipping_cost ?? 0,
                'per_item' => ($this->shipping_cost ?? 0) * $quantity,
                default => null,
            };
        }

        // Otherwise inherit from product
        return $this->product->getCustomShippingCost($quantity);
    }

    /**
     * Check if variation has free shipping.
     */
    public function hasFreeShipping(): bool
    {
        if ($this->shipping_type === 'free') {
            return true;
        }

        if ($this->shipping_type === 'inherit') {
            return $this->product->hasFreeShipping();
        }

        return false;
    }

    /**
     * Check if variation requires shipping.
     */
    public function requiresShipping(): bool
    {
        // Variations inherit requires_shipping from product
        return $this->product->requiresShipping();
    }

}
