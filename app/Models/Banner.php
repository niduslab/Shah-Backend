<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
        'button_text',
        'position',
        'sort_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Check if banner is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope for specific position.
     */
    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope for ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope for homepage hero banners.
     */
    public function scopeHomepageHero($query)
    {
        return $query->position('homepage_hero');
    }

    /**
     * Scope for homepage secondary banners.
     */
    public function scopeHomepageSecondary($query)
    {
        return $query->position('homepage_secondary');
    }

    /**
     * Scope for category page banners.
     */
    public function scopeCategoryPage($query)
    {
        return $query->position('category_page');
    }

    /**
     * Scope for sidebar banners.
     */
    public function scopeSidebar($query)
    {
        return $query->position('sidebar');
    }
}
