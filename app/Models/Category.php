<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants (recursive children).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get promotions for this category.
     */
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_categories');
    }

    /**
     * Get coupons for this category.
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_categories');
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for ordering by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get all ancestor IDs.
     */
    public function getAncestorIds(): array
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent) {
            $ancestors[] = $parent->id;
            $parent = $parent->parent;
        }

        return $ancestors;
    }
}
