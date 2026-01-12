<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
    ];

    /**
     * Get the brand that owns this model.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get products with this model.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'model_id');
    }
}
