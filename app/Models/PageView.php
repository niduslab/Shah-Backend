<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_session_id',
        'user_id',
        'page_type',
        'page_url',
        'page_title',
        'product_id',
        'category_id',
        'time_spent_seconds',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    /**
     * Get the visitor session.
     */
    public function visitorSession()
    {
        return $this->belongsTo(VisitorSession::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope for specific page type.
     */
    public function scopePageType($query, $type)
    {
        return $query->where('page_type', $type);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('viewed_at', [$from, $to]);
    }
}
