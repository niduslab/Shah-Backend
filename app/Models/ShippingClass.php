<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShippingClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shippingClass) {
            if (empty($shippingClass->slug)) {
                $shippingClass->slug = Str::slug($shippingClass->name);
            }
        });
    }

    /**
     * Get products in this shipping class.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get shipping rates for this class.
     */
    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}
