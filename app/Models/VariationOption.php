<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'variation_id',
        'value',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the variation.
     */
    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }

    /**
     * Get variation values using this option.
     */
    public function variationValues()
    {
        return $this->hasMany(VariationValue::class);
    }

    /**
     * Get display label.
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?? $this->value;
    }

    /**
     * Scope for active options.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
