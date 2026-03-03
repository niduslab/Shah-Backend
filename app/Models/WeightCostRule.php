<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightCostRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_rate_id',
        'state',
        'city',
        'shipping_calculation_method',
        'per_unit_cost',
        'default_rule_cost',
    ];

    protected $casts = [
        'per_unit_cost' => 'decimal:2',
        'default_rule_cost' => 'decimal:2',
    ];

    /**
     * Get the shipping rate.
     */
    public function shippingRate()
    {
        return $this->belongsTo(ShippingRate::class);
    }

    /**
     * Get weight cost rule items.
     */
    public function items()
    {
        return $this->hasMany(WeightCostRuleItem::class)->orderBy('weight');
    }

    /**
     * Calculate cost for given weight.
     */
    public function calculateCost(float $weight): float
    {
        if ($this->shipping_calculation_method === 'per_unit') {
            return $weight * ($this->per_unit_cost ?? 0);
        }

        // Rules-based calculation
        $item = $this->items()
            ->where('weight', '>=', $weight)
            ->orderBy('weight')
            ->first();

        if ($item) {
            return $item->cost;
        }

        return $this->default_rule_cost ?? 0;
    }

    /**
     * Scope for specific location.
     */
    public function scopeForLocation($query, ?string $state = null, ?string $city = null)
    {
        return $query->where(function ($q) use ($state, $city) {
            $q->where(function ($subQ) use ($state, $city) {
                if ($city) {
                    $subQ->where('city', $city);
                }
                if ($state) {
                    $subQ->orWhere('state', $state);
                }
            })->orWhere(function ($subQ) {
                $subQ->whereNull('city')->whereNull('state');
            });
        });
    }
}
