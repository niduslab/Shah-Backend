<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Variation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variation) {
            if (empty($variation->slug)) {
                $variation->slug = Str::slug($variation->name);
            }
        });
    }

    /**
     * Get variation options.
     */
    public function options()
    {
        return $this->hasMany(VariationOption::class);
    }

    /**
     * Scope for active variations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
