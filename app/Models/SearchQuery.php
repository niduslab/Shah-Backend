<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_session_id',
        'user_id',
        'query',
        'results_count',
        'clicked_result',
        'clicked_product_id',
        'searched_at',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'clicked_result' => 'boolean',
        'searched_at' => 'datetime',
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
     * Get the clicked product.
     */
    public function clickedProduct()
    {
        return $this->belongsTo(Product::class, 'clicked_product_id');
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('searched_at', [$from, $to]);
    }

    /**
     * Scope for queries with no results.
     */
    public function scopeNoResults($query)
    {
        return $query->where('results_count', 0);
    }
}
