<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Services\Contracts\CouponServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CouponService implements CouponServiceInterface
{
    /**
     * Validate a coupon code.
     * 
     * @param string $code
     * @param string $email
     * @param array $cartItems
     * @param float $subtotal
     * @return array Contains 'valid', 'coupon', 'discount', 'message'
     */
    public function validateCoupon(string $code, string $email, array $cartItems, float $subtotal): array
    {
        $coupon = $this->getCouponByCode($code);

        if (!$coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount' => 0,
                'message' => 'Invalid coupon code.',
            ];
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => 'This coupon is no longer active.',
            ];
        }

        // Check expiration
        if ($coupon->expires_at && Carbon::now()->gt($coupon->expires_at)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => 'This coupon has expired.',
            ];
        }

        // Check start date
        if ($coupon->starts_at && Carbon::now()->lt($coupon->starts_at)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => 'This coupon is not yet active.',
            ];
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => 'This coupon has reached its usage limit.',
            ];
        }

        // Check once per email restriction
        if ($coupon->once_per_customer && !$this->canBeUsedByEmail($coupon, $email)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => 'This coupon has already been used with this email address.',
            ];
        }

        // Check minimum order amount
        if ($coupon->min_order_amount > 0 && $subtotal < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => "Minimum order amount of {$coupon->min_order_amount} BDT required.",
            ];
        }

        // Check if coupon applies to cart items
        if (!$this->couponAppliesToCart($coupon, $cartItems)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0,
                'message' => 'This coupon does not apply to items in your cart.',
            ];
        }

        // Calculate discount
        $discount = $this->applyCoupon($coupon, $cartItems, $subtotal);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon applied successfully!',
        ];
    }

    /**
     * Apply coupon to cart.
     * 
     * @param Coupon $coupon
     * @param array $cartItems
     * @param float $subtotal
     * @return float Discount amount
     */
    public function applyCoupon(Coupon $coupon, array $cartItems, float $subtotal): float
    {
        $eligibleAmount = $this->calculateEligibleAmount($coupon, $cartItems, $subtotal);
        $discount = 0;

        switch ($coupon->discount_type) {
            case 'percentage':
                $discount = $eligibleAmount * ($coupon->discount_value / 100);
                break;

            case 'fixed_amount':
                $discount = min($coupon->discount_value, $eligibleAmount);
                break;

            case 'free_shipping':
                // Free shipping is handled separately
                $discount = 0;
                break;
        }

        // Apply max discount cap if set
        if ($coupon->max_discount_amount && $discount > $coupon->max_discount_amount) {
            $discount = $coupon->max_discount_amount;
        }

        return round($discount, 2);
    }

    /**
     * Record coupon usage after order.
     * 
     * @param Coupon $coupon
     * @param Order $order
     * @param string $email
     * @param float $discountApplied
     * @return void
     */
    public function recordCouponUsage(Coupon $coupon, Order $order, string $email, float $discountApplied): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'customer_email' => strtolower($email),
            'discount_applied' => $discountApplied,
        ]);

        // Increment usage count
        $coupon->increment('usage_count');
    }

    /**
     * Check if coupon can be used by email (once per email restriction).
     * 
     * @param Coupon $coupon
     * @param string $email
     * @return bool
     */
    public function canBeUsedByEmail(Coupon $coupon, string $email): bool
    {
        if (!$coupon->once_per_customer) {
            return true;
        }

        return !CouponUsage::where('coupon_id', $coupon->id)
            ->where('customer_email', strtolower($email))
            ->exists();
    }

    /**
     * Get coupon by code.
     * 
     * @param string $code
     * @return Coupon|null
     */
    public function getCouponByCode(string $code): ?Coupon
    {
        return Coupon::where('code', strtoupper($code))
            ->with(['products', 'brands', 'categories'])
            ->first();
    }

    /**
     * Check if coupon applies to cart items.
     * 
     * @param Coupon $coupon
     * @param array $cartItems
     * @return bool
     */
    protected function couponAppliesToCart(Coupon $coupon, array $cartItems): bool
    {
        // If applies to all, always valid
        if ($coupon->applies_to === 'all_products') {
            return true;
        }

        // Check if any cart item matches the coupon criteria
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                continue;
            }

            if ($this->couponAppliesToProduct($coupon, $product)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if coupon applies to a specific product.
     * 
     * @param Coupon $coupon
     * @param Product $product
     * @return bool
     */
    protected function couponAppliesToProduct(Coupon $coupon, Product $product): bool
    {
        // If applies to all, always valid
        if ($coupon->applies_to === 'all_products') {
            return true;
        }

        // Check specific application types
        switch ($coupon->applies_to) {
            case 'specific_products':
                return $coupon->products->contains('id', $product->id);

            case 'specific_brands':
                return $product->brand_id && $coupon->brands->contains('id', $product->brand_id);

            case 'specific_categories':
                return $coupon->categories->contains('id', $product->category_id);
        }

        return false;
    }

    /**
     * Calculate eligible amount for coupon discount.
     * 
     * @param Coupon $coupon
     * @param array $cartItems
     * @param float $subtotal
     * @return float
     */
    protected function calculateEligibleAmount(Coupon $coupon, array $cartItems, float $subtotal): float
    {
        // If applies to all, entire subtotal is eligible
        if ($coupon->applies_to === 'all_products') {
            return $subtotal;
        }

        $eligibleAmount = 0;

        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                continue;
            }

            if ($this->couponAppliesToProduct($coupon, $product)) {
                // Get price from variation if exists, otherwise from product
                $price = 0;
                if (!empty($item['variation_id'])) {
                    $variation = $product->variations()->find($item['variation_id']);
                    $price = $variation ? $variation->price : $product->price;
                } else {
                    $price = $product->price;
                }
                
                $eligibleAmount += $price * $item['quantity'];
            }
        }

        return $eligibleAmount;
    }

    /**
     * Check if coupon provides free shipping.
     * 
     * @param Coupon $coupon
     * @return bool
     */
    public function providesFreeShipping(Coupon $coupon): bool
    {
        return $coupon->discount_type === 'free_shipping';
    }
}
