<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * Get products for this brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get models for this brand.
     */
    public function models()
    {
        return $this->hasMany(ProductModel::class);
    }

    /**
     * Get promotions for this brand.
     */
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_brands');
    }

    /**
     * Get coupons for this brand.
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_brands');
    }

    /**
     * Scope for active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
