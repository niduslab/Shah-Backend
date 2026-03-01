<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($gallery) {
            if (empty($gallery->slug)) {
                $gallery->slug = Str::slug($gallery->title);
            }
        });
    }

    public function images()
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort_order');
    }

    public function featuredImage()
    {
        return $this->hasOne(GalleryImage::class)->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
