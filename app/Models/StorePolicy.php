<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StorePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_type',
        'title',
        'slug',
        'content',
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

        static::creating(function ($policy) {
            if (empty($policy->slug)) {
                $policy->slug = Str::slug($policy->title);
            }
        });
    }

    /**
     * Scope for active policies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific policy type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('policy_type', $type);
    }
}
