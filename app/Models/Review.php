<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'helpful_count',
        'status',
        'admin_response',
    ];

    protected $casts = [
        'rating' => 'integer',
        'helpful_count' => 'integer',
    ];

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
     * Get the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get users who marked this review as helpful.
     */
    public function helpfulUsers()
    {
        return $this->belongsToMany(User::class, 'review_helpful');
    }

    /**
     * Check if review is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if review is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark as helpful by user.
     */
    public function markHelpfulBy(User $user): bool
    {
        if ($this->helpfulUsers()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $this->helpfulUsers()->attach($user->id);
        $this->increment('helpful_count');
        return true;
    }

    /**
     * Scope for approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for rejected reviews.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
