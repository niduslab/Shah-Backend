<?php

namespace App\Services\Contracts;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;

interface CouponServiceInterface
{
    /**
     * Validate a coupon code.
     */
    public function validateCoupon(string $code, string $email, array $cartItems, float $subtotal): array;

    /**
     * Apply coupon to cart.
     */
    public function applyCoupon(Coupon $coupon, array $cartItems, float $subtotal): float;

    /**
     * Record coupon usage after order.
     */
    public function recordCouponUsage(Coupon $coupon, Order $order, string $email, float $discountApplied): void;

    /**
     * Check if coupon can be used by email (once per email restriction).
     */
    public function canBeUsedByEmail(Coupon $coupon, string $email): bool;

    /**
     * Get coupon by code.
     */
    public function getCouponByCode(string $code): ?Coupon;
}
