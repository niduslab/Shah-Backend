<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutFunnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_session_id',
        'user_id',
        'order_id',
        'status',
        'cart_items',
        'cart_total',
        'items_count',
        'abandonment_reason',
        'cart_viewed_at',
        'checkout_initiated_at',
        'shipping_entered_at',
        'payment_entered_at',
        'completed_at',
        'abandoned_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'cart_total' => 'decimal:2',
        'items_count' => 'integer',
        'cart_viewed_at' => 'datetime',
        'checkout_initiated_at' => 'datetime',
        'shipping_entered_at' => 'datetime',
        'payment_entered_at' => 'datetime',
        'completed_at' => 'datetime',
        'abandoned_at' => 'datetime',
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
     * Get the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for abandoned checkouts.
     */
    public function scopeAbandoned($query)
    {
        return $query->where('status', 'abandoned');
    }

    /**
     * Scope for completed checkouts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'order_completed');
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Calculate conversion rate.
     */
    public static function conversionRate($from = null, $to = null)
    {
        $query = static::query();
        
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $total = $query->count();
        $completed = $query->where('status', 'order_completed')->count();

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    /**
     * Get abandonment rate.
     */
    public static function abandonmentRate($from = null, $to = null)
    {
        $query = static::query();
        
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $total = $query->count();
        $abandoned = $query->where('status', 'abandoned')->count();

        return $total > 0 ? ($abandoned / $total) * 100 : 0;
    }
}
