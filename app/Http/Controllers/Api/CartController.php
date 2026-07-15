<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\Contracts\CouponServiceInterface;
use App\Services\Contracts\InventoryServiceInterface;
use App\Services\Contracts\PromotionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService,
        protected PromotionServiceInterface $promotionService,
        protected CouponServiceInterface $couponService
    ) {}

    /**
     * Get cart summary with pricing.
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
        ]);

        $cartItems = [];
        $errors = [];

        foreach ($validated['items'] as $item) {
            $product = Product::with('images')->find($item['product_id']);
            $variation = isset($item['variation_id']) 
                ? ProductVariation::find($item['variation_id']) 
                : null;

            // Check availability
            $available = $this->inventoryService->checkAvailability(
                $product,
                $item['quantity'],
                $variation
            );

            if (!$available) {
                $errors[] = "Insufficient stock for {$product->name}";
                continue;
            }

            $price = $variation?->price ?? $product->price;

            $cartItems[] = [
                'product_id' => $product->id,
                'variation_id' => $variation?->id,
                'name' => $product->name,
                'sku' => $variation?->sku ?? $product->sku,
                'image' => $product->images->first()?->path,
                'price' => $price,
                'quantity' => $item['quantity'],
                'total' => $price * $item['quantity'],
            ];
        }

        // Apply promotions
        $promotionResult = $this->promotionService->applyPromotion($cartItems);
        $subtotal = collect($promotionResult['items'])->sum(fn($i) => $i['discounted_price'] * $i['quantity']);

        // Apply coupon if provided
        $couponDiscount = 0;
        $couponError = null;
        if (!empty($validated['coupon_code'])) {
            $couponResult = $this->couponService->validateCoupon(
                $validated['coupon_code'],
                auth()->user()?->email ?? '',
                $cartItems,
                $subtotal
            );

            if ($couponResult['valid']) {
                $couponDiscount = $couponResult['discount'];
            } else {
                $couponError = $couponResult['message'];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $promotionResult['items'],
                'subtotal' => $subtotal,
                'promotion_discount' => $promotionResult['total_discount'],
                'coupon_discount' => $couponDiscount,
                'coupon_error' => $couponError,
                'total' => $subtotal - $couponDiscount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Validate coupon.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $result = $this->couponService->validateCoupon(
            $validated['code'],
            auth()->user()?->email ?? '',
            $validated['items'],
            $validated['subtotal']
        );

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['message'] ?? null,
            'data' => $result['valid'] ? [
                'discount' => $result['discount'],
                'coupon' => $result['coupon'],
            ] : null,
        ]);
    }

    /**
     * Check product availability.
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($validated['product_id']);
        $variation = isset($validated['variation_id']) 
            ? ProductVariation::find($validated['variation_id']) 
            : null;

        $available = $this->inventoryService->checkAvailability(
            $product,
            $validated['quantity'],
            $variation
        );

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $available,
            ],
        ]);
    }

    /**
     * Get all available valid coupons for public view.
     * Secret/hidden coupons (is_public = false) are excluded — they can
     * still be redeemed via validateCoupon() if the customer has the code.
     */
    public function getAvailableCoupons(): JsonResponse
    {
        $coupons = \App\Models\Coupon::valid()
            ->public()
            ->select([
                'id',
                'code',
                'name',
                'description',
                'discount_type',
                'discount_value',
                'min_order_amount',
                'max_discount_amount',
                'applies_to',
                'expires_at',
            ])
            ->orderBy('discount_value', 'desc')
            ->get()
            ->map(function ($coupon) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'max_discount_amount' => $coupon->max_discount_amount,
                    'applies_to' => $coupon->applies_to,
                    'expires_at' => $coupon->expires_at?->format('Y-m-d H:i:s'),
                    'is_free_shipping' => $coupon->discount_type === 'free_shipping',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }
}
