<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'referrer',
        'landing_page',
        'first_visit_at',
        'last_activity_at',
        'page_views',
        'duration_seconds',
    ];

    protected $casts = [
        'first_visit_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'page_views' => 'integer',
        'duration_seconds' => 'integer',
    ];

    /**
     * Get the user associated with this session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get page views for this session.
     */
    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    /**
     * Get product views for this session.
     */
    public function productViews()
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * Get cart events for this session.
     */
    public function cartEvents()
    {
        return $this->hasMany(CartEvent::class);
    }

    /**
     * Get checkout funnel for this session.
     */
    public function checkoutFunnel()
    {
        return $this->hasOne(CheckoutFunnel::class);
    }

    /**
     * Get search queries for this session.
     */
    public function searchQueries()
    {
        return $this->hasMany(SearchQuery::class);
    }

    /**
     * Scope for active sessions (within last 30 minutes).
     */
    public function scopeActive($query)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes(30));
    }

    /**
     * Scope for sessions within date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('first_visit_at', [$from, $to]);
    }

    /**
     * Scope for authenticated sessions.
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope for guest sessions.
     */
    public function scopeGuest($query)
    {
        return $query->whereNull('user_id');
    }
}
