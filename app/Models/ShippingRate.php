<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shipping_class_id',
        'method',
        'country',
        'delivery_time',
        'free_shipping_min_order',
        'base_cost',
        'weight_pricing_enabled',
        'is_active',
    ];

    protected $casts = [
        'free_shipping_min_order' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'weight_pricing_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the shipping class.
     */
    public function shippingClass()
    {
        return $this->belongsTo(ShippingClass::class);
    }

    /**
     * Get weight cost rules.
     */
    public function weightCostRules()
    {
        return $this->hasMany(WeightCostRule::class);
    }

    /**
     * Check if this is Shah Sports Team delivery.
     */
    public function isShahSportsTeam(): bool
    {
        return $this->method === 'shah_sports_team';
    }

    /**
     * Check if this is Pathao Courier.
     */
    public function isPathaoCourier(): bool
    {
        return $this->method === 'pathao_courier';
    }

    /**
     * Scope for active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', $method);
    }
}
