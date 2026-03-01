<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_type',
        'shipping_address_id',
        'billing_address_id',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'coupon_id',
        'shipping_method',
        'tracking_number',
        'status',
        'payment_status',
        'notes',
        'customer_name',
        'customer_email',
        'customer_phone',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_preorder' => 'boolean',
        'preorder_deposit_paid' => 'decimal:2',
        'preorder_remaining_amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate order number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'SS';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = $prefix . $date . $random;

        while (self::where('order_number', $number)->exists()) {
            $random = strtoupper(Str::random(4));
            $number = $prefix . $date . $random;
        }

        return $number;
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get shipping address.
     */
    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get billing address.
     */
    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    /**
     * Get order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get coupon.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get payment.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get invoice.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get returns.
     */
    public function returns()
    {
        return $this->hasMany(ProductReturn::class);
    }

    /**
     * Get refunds.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if POS order.
     */
    public function isPosOrder(): bool
    {
        return $this->order_type === 'in_store';
    }

    /**
     * Check if online order.
     */
    public function isOnlineOrder(): bool
    {
        return $this->order_type === 'online';
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    /**
     * Calculate totals.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->total_amount = $this->subtotal + $this->shipping_cost + $this->tax_amount - $this->discount_amount;
    }

    /**
     * Get customer name (from user or POS data).
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer_name) {
            return $this->customer_name;
        }

        return $this->user ? $this->user->full_name : 'Guest';
    }

    /**
     * Get customer email (from user or POS data).
     */
    public function getCustomerDisplayEmailAttribute(): ?string
    {
        return $this->customer_email ?? $this->user?->email;
    }

    /**
     * Scope for POS orders.
     */
    public function scopePosOrders($query)
    {
        return $query->where('order_type', 'in_store');
    }

    /**
     * Scope for online orders.
     */
    public function scopeOnlineOrders($query)
    {
        return $query->where('order_type', 'online');
    }

    /**
     * Scope for pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Check if order is preorder.
     */
    public function isPreorder(): bool
    {
        return $this->is_preorder === true;
    }

    /**
     * Check if preorder deposit is paid.
     */
    public function isPreorderDepositPaid(): bool
    {
        return $this->is_preorder && $this->preorder_payment_status === 'deposit_paid';
    }

    /**
     * Check if preorder is fully paid.
     */
    public function isPreorderFullyPaid(): bool
    {
        return $this->is_preorder && $this->preorder_payment_status === 'fully_paid';
    }

    /**
     * Get remaining preorder amount.
     */
    public function getRemainingPreorderAmount(): float
    {
        if (!$this->is_preorder) {
            return 0;
        }

        return $this->preorder_remaining_amount ?? 0;
    }

    /**
     * Scope for preorder orders.
     */
    public function scopePreorders($query)
    {
        return $query->where('is_preorder', true);
    }

    /**
     * Scope for preorders with pending balance.
     */
    public function scopePreordersPendingBalance($query)
    {
        return $query->where('is_preorder', true)
            ->where('preorder_payment_status', 'deposit_paid');
    }
}
