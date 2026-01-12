<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'customer_email',
        'discount_applied',
        'created_at',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the coupon.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
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
}
