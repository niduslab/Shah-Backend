<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\CouponServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\ShippingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService,
        protected ShippingServiceInterface $shippingService,
        protected PaymentServiceInterface $paymentService,
        protected CouponServiceInterface $couponService
    ) {}

    /**
     * Get available shipping methods.
     */
    public function shippingMethods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'address_id' => 'nullable|exists:addresses,id',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $address = null;
        if (!empty($validated['address_id'])) {
            $address = \App\Models\Address::find($validated['address_id']);
        }

        $costs = $this->shippingService->calculateShippingCost(
            $validated['items'],
            $address,
            $validated['subtotal']
        );

        return response()->json([
            'success' => true,
            'data' => array_values($costs),
        ]);
    }

    /**
     * Process checkout.
     */
    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.is_preorder' => 'nullable|boolean',
            'shipping_address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'nullable|exists:addresses,id',
            'shipping_method' => 'required|in:shah_sports_team,pathao_courier',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|in:ssl_commerz,bkash,nagad',
            'notes' => 'nullable|string|max:500',
            'is_preorder' => 'nullable|boolean',
            'pay_deposit_only' => 'nullable|boolean',
        ]);

        $user = $request->user();

        $order = $this->orderService->createOrder(
            $user,
            $validated['items'],
            [
                'shipping_address_id' => $validated['shipping_address_id'],
                'billing_address_id' => $validated['billing_address_id'],
                'shipping_method' => $validated['shipping_method'],
                'notes' => $validated['notes'] ?? null,
                'is_preorder' => $validated['is_preorder'] ?? false,
                'pay_deposit_only' => $validated['pay_deposit_only'] ?? false,
            ],
            $validated['coupon_code'] ?? null
        );

        // Initialize payment
        $paymentResult = $this->paymentService->processPayment(
            $order,
            $validated['payment_method']
        );

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => [
                'order' => $order,
                'payment' => $paymentResult,
            ],
        ], 201);
    }

    /**
     * Calculate order totals preview.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.is_preorder' => 'nullable|boolean',
            'shipping_address_id' => 'nullable|exists:addresses,id',
            'shipping_method' => 'nullable|in:shah_sports_team,pathao_courier',
            'coupon_code' => 'nullable|string',
            'is_preorder' => 'nullable|boolean',
            'pay_deposit_only' => 'nullable|boolean',
        ]);

        // Calculate subtotal
        $subtotal = collect($validated['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        // Calculate preorder deposit if applicable
        $depositAmount = null;
        $remainingAmount = null;
        if (!empty($validated['is_preorder']) && !empty($validated['pay_deposit_only'])) {
            $depositAmount = 0;
            foreach ($validated['items'] as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                if ($product && $product->is_preorder) {
                    $depositPerUnit = $product->calculatePreorderDeposit();
                    $depositAmount += $depositPerUnit * $item['quantity'];
                }
            }
            $remainingAmount = $subtotal - $depositAmount;
        }

        // Calculate shipping
        $shippingCost = 0;
        $address = null;
        if (!empty($validated['shipping_address_id'])) {
            $address = \App\Models\Address::find($validated['shipping_address_id']);
        }
        
        if (!empty($validated['shipping_method'])) {
            $shippingCost = $this->shippingService->getShippingCostForMethod(
                $validated['shipping_method'],
                $validated['items'],
                $address,
                $subtotal
            );
        }

        // Apply coupon
        $couponDiscount = 0;
        if (!empty($validated['coupon_code'])) {
            $couponResult = $this->couponService->validateCoupon(
                $validated['coupon_code'],
                auth()->user()?->email ?? '',
                $validated['items'],
                $subtotal
            );
            if ($couponResult['valid']) {
                $couponDiscount = $couponResult['discount'];
            }
        }

        $total = $subtotal + $shippingCost - $couponDiscount;
        $payableNow = $depositAmount ?? $total;

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'coupon_discount' => $couponDiscount,
                'total' => $total,
                'is_preorder' => $validated['is_preorder'] ?? false,
                'deposit_amount' => $depositAmount,
                'remaining_amount' => $remainingAmount,
                'payable_now' => $payableNow,
            ],
        ]);
    }
}
