<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variation_id',
        'variation_option_id',
    ];

    /**
     * Get the product variation.
     */
    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Get the variation option.
     */
    public function variationOption()
    {
        return $this->belongsTo(VariationOption::class);
    }
}
