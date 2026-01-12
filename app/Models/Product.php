<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'model_id',
        'shipping_class_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'quantity',
        'low_stock_threshold',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'is_featured',
        'is_trending',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_featured' => 'boolean',
        'is_trending' => 'boolean',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get stock status attribute.
     */
    public function getStockStatusAttribute(): string
    {
        $qty = $this->getTotalQuantity();

        if ($qty <= 0) {
            return 'out_of_stock';
        }

        if ($qty <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Get total quantity (product + variations).
     */
    public function getTotalQuantity(): int
    {
        if ($this->variations()->exists()) {
            return $this->variations()->sum('quantity');
        }

        return $this->quantity;
    }

    /**
     * Get the category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the product model.
     */
    public function productModel()
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    /**
     * Get the shipping class.
     */
    public function shippingClass()
    {
        return $this->belongsTo(ShippingClass::class);
    }

    /**
     * Get product variations.
     */
    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Get product images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get primary image.
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews.
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    /**
     * Get order items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get promotions.
     */
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products');
    }

    /**
     * Get coupons.
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_products');
    }

    /**
     * Get inventory logs.
     */
    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Get average rating.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get review count.
     */
    public function getReviewCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Scope for active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for trending products.
     */
    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    /**
     * Scope for in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getTotalQuantity() > 0;
    }

    /**
     * Check if product has variations.
     */
    public function hasVariations(): bool
    {
        return $this->variations()->exists();
    }
}
