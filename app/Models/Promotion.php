<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'promotion_type',
        'discount_value',
        'applies_to',
        'apply_level',
        'min_purchase_amount',
        'max_discount_amount',
        'starts_at',
        'ends_at',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get products in this promotion.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_products');
    }

    /**
     * Get brands in this promotion.
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'promotion_brands');
    }

    /**
     * Get categories in this promotion.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'promotion_categories');
    }

    /**
     * Check if promotion is currently valid.
     */
    public function isValidNow(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        return $now->gte($this->starts_at) && $now->lte($this->ends_at);
    }

    /**
     * Calculate discount for a given amount.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_purchase_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->promotion_type) {
            case 'percentage':
                $discount = $amount * ($this->discount_value / 100);
                break;
            case 'fixed_amount':
            case 'flash_sale':
                $discount = $this->discount_value;
                break;
            case 'free_delivery':
                // Handled separately in shipping calculation
                return 0;
            case 'combo_offer':
                $discount = $this->discount_value;
                break;
        }

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return min($discount, $amount);
    }

    /**
     * Check if promotion applies to a product.
     */
    public function appliesToProduct(Product $product): bool
    {
        switch ($this->applies_to) {
            case 'all_products':
                return true;
            case 'specific_products':
                return $this->products()->where('products.id', $product->id)->exists();
            case 'specific_brands':
                return $product->brand_id && $this->brands()->where('brands.id', $product->brand_id)->exists();
            case 'specific_categories':
                return $this->categories()->where('categories.id', $product->category_id)->exists();
            default:
                return false;
        }
    }

    /**
     * Scope for active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    /**
     * Scope for ordering by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
