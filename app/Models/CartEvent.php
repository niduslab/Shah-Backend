<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_session_id',
        'user_id',
        'product_id',
        'product_variation_id',
        'event_type',
        'quantity',
        'price',
        'event_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'event_at' => 'datetime',
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
     * Get the product variation.
     */
    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Scope for event type.
     */
    public function scopeEventType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('event_at', [$from, $to]);
    }

    /**
     * Scope for added events.
     */
    public function scopeAdded($query)
    {
        return $query->where('event_type', 'added');
    }
}
