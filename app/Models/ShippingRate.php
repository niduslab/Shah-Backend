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
        'is_active',
    ];

    protected $casts = [
        'free_shipping_min_order' => 'decimal:2',
        'base_cost' => 'decimal:2',
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
     * Calculate shipping cost for given weight and location.
     */
    public function calculateCost(float $totalWeight, float $orderAmount, ?string $state = null, ?string $city = null): float
    {
        // Check for free shipping
        if ($this->free_shipping_min_order > 0 && $orderAmount >= $this->free_shipping_min_order) {
            return 0;
        }

        // Find applicable weight cost rule
        $rule = $this->weightCostRules()
            ->when($state, fn($q) => $q->where('state', $state)->orWhereNull('state'))
            ->when($city, fn($q) => $q->where('city', $city)->orWhereNull('city'))
            ->orderByRaw('CASE WHEN city IS NOT NULL THEN 1 WHEN state IS NOT NULL THEN 2 ELSE 3 END')
            ->first();

        if (!$rule) {
            return $this->base_cost;
        }

        if ($rule->shipping_calculation_method === 'per_unit') {
            return $this->base_cost + ($totalWeight * ($rule->per_unit_cost ?? 0));
        }

        // Rules-based calculation
        $ruleItem = $rule->items()
            ->where('weight', '>=', $totalWeight)
            ->orderBy('weight')
            ->first();

        if ($ruleItem) {
            return $this->base_cost + $ruleItem->cost;
        }

        return $this->base_cost + ($rule->default_rule_cost ?? 0);
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
