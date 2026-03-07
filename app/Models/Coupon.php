<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'applies_to',
        'usage_limit',
        'usage_count',
        'once_per_customer',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_count' => 'integer',
        'usage_limit' => 'integer',
        'once_per_customer' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get products this coupon applies to.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    /**
     * Get brands this coupon applies to.
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'coupon_brands');
    }

    /**
     * Get categories this coupon applies to.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    /**
     * Get coupon usages.
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Get orders using this coupon.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if coupon is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon can be used by email.
     */
    public function canBeUsedByEmail(string $email): bool
    {
        if (!$this->once_per_customer) {
            return true;
        }

        return !$this->usages()->where('customer_email', $email)->exists();
    }

    /**
     * Calculate discount for a given amount.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_order_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->discount_type) {
            case 'percentage':
                $discount = $amount * ($this->discount_value / 100);
                break;
            case 'fixed_amount':
                $discount = $this->discount_value;
                break;
            case 'free_shipping':
                // Handled separately
                return 0;
        }

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return min($discount, $amount);
    }

    /**
     * Check if coupon is free shipping type.
     */
    public function isFreeShipping(): bool
    {
        return $this->discount_type === 'free_shipping';
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Check if coupon applies to products.
     */
    public function appliesToProducts(): bool
    {
        return $this->applies_to === 'all_products' || $this->applies_to === 'specific_products';
    }

    /**
     * Check if coupon applies to brands.
     */
    public function appliesToBrands(): bool
    {
        return $this->applies_to === 'all_products' || $this->applies_to === 'specific_brands';
    }

    /**
     * Check if coupon applies to categories.
     */
    public function appliesToCategories(): bool
    {
        return $this->applies_to === 'all_products' || $this->applies_to === 'specific_categories';
    }

    /**
     * Check if coupon applies to all products.
     */
    public function appliesToAll(): bool
    {
        return $this->applies_to === 'all_products';
    }

    /**
     * Get applies_to as array.
     */
    public function getAppliesToTypesAttribute(): array
    {
        return [$this->applies_to];
    }

    /**
     * Scope for active coupons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope for valid coupons (not exceeded usage limit).
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('usage_count', '<', 'usage_limit');
            });
    }
}
