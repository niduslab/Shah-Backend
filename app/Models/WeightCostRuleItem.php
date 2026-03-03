<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightCostRuleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'weight_cost_rule_id',
        'weight',
        'cost',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the weight cost rule.
     */
    public function weightCostRule()
    {
        return $this->belongsTo(WeightCostRule::class);
    }
}
