<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'visitor_session_id',
        'user_id',
        'view_count',
        'time_spent_seconds',
        'added_to_cart',
        'added_to_wishlist',
        'purchased',
        'first_viewed_at',
        'last_viewed_at',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'time_spent_seconds' => 'integer',
        'added_to_cart' => 'boolean',
        'added_to_wishlist' => 'boolean',
        'purchased' => 'boolean',
        'first_viewed_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

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
     * Scope for date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('first_viewed_at', [$from, $to]);
    }

    /**
     * Scope for converted views (added to cart).
     */
    public function scopeConverted($query)
    {
        return $query->where('added_to_cart', true);
    }

    /**
     * Scope for purchased views.
     */
    public function scopePurchased($query)
    {
        return $query->where('purchased', true);
    }
}
