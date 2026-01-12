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
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'nullable|numeric',
            'address_id' => 'nullable|exists:addresses,id',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $methods = $this->shippingService->getAvailableMethods(
            $validated['items'],
            $validated['address_id'] ?? null
        );

        // Calculate costs for each method
        $methodsWithCost = [];
        foreach ($methods as $method) {
            $cost = $this->shippingService->getShippingCostForMethod(
                $method['code'],
                $validated['items'],
                null,
                $validated['subtotal']
            );

            $methodsWithCost[] = [
                'code' => $method['code'],
                'name' => $method['name'],
                'description' => $method['description'],
                'cost' => $cost,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $methodsWithCost,
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
            'shipping_address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'nullable|exists:addresses,id',
            'shipping_method' => 'required|in:shah_sports_team,pathao_courier',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|in:ssl_commerz,bkash,nagad',
            'notes' => 'nullable|string|max:500',
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
            'shipping_address_id' => 'nullable|exists:addresses,id',
            'shipping_method' => 'nullable|in:shah_sports_team,pathao_courier',
            'coupon_code' => 'nullable|string',
        ]);

        // Calculate subtotal
        $subtotal = collect($validated['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        // Calculate shipping
        $shippingCost = 0;
        if (!empty($validated['shipping_method'])) {
            $shippingCost = $this->shippingService->getShippingCostForMethod(
                $validated['shipping_method'],
                $validated['items'],
                null,
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

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'coupon_discount' => $couponDiscount,
                'total' => $total,
            ],
        ]);
    }
}
