<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'quantity_limit',
        'quantity_sold',
        'per_user_limit',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'quantity_limit' => 'integer',
        'quantity_sold' => 'integer',
        'per_user_limit' => 'integer',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'flash_deal_products')
            ->withPivot('flash_price', 'quantity_limit', 'quantity_sold')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        return $now->gte($this->starts_at) && $now->lte($this->ends_at);
    }

    public function hasStock(): bool
    {
        if (!$this->quantity_limit) {
            return true;
        }

        return $this->quantity_sold < $this->quantity_limit;
    }

    public function getRemainingQuantityAttribute(): ?int
    {
        if (!$this->quantity_limit) {
            return null;
        }

        return max(0, $this->quantity_limit - $this->quantity_sold);
    }

    public function getTimeRemainingAttribute(): array
    {
        $now = now();
        
        if ($now->lt($this->starts_at)) {
            return [
                'status' => 'upcoming',
                'seconds' => $now->diffInSeconds($this->starts_at),
            ];
        }

        if ($now->gt($this->ends_at)) {
            return [
                'status' => 'ended',
                'seconds' => 0,
            ];
        }

        return [
            'status' => 'active',
            'seconds' => $now->diffInSeconds($this->ends_at),
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '>', now());
    }
}
