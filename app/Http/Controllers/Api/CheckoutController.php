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
        $user = $request->user();
        
        // Base validation rules
        $rules = [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.is_preorder' => 'nullable|boolean',
            // Shipping address (can be ID for auth users or inline for guests)
            'shipping_address_id' => 'nullable|exists:addresses,id',
            'shipping_address' => 'required_without:shipping_address_id|array',
            'shipping_address.address_line_1' => 'required_with:shipping_address|string',
            'shipping_address.city' => 'required_with:shipping_address|string',
            'shipping_address.state' => 'nullable|string',
            'shipping_address.postal_code' => 'nullable|string',
            'shipping_address.country' => 'required_with:shipping_address|string',
            'shipping_address.phone' => 'required_with:shipping_address|string',
            // Billing address
            'billing_address_id' => 'nullable|exists:addresses,id',
            'billing_address' => 'nullable|array',
            'use_shipping_for_billing' => 'nullable|boolean',
            // Other fields
            'shipping_method' => 'required|in:shah_sports_team,pathao_courier,standard',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|in:ssl_commerz,bkash,nagad,cod,cash_on_delivery',
            'notes' => 'nullable|string|max:500',
            'is_preorder' => 'nullable|boolean',
            'pay_deposit_only' => 'nullable|boolean',
            // Account creation for guest
            'create_account' => 'nullable|boolean',
            'password' => 'required_if:create_account,true|string|min:8',
        ];

        // Add guest fields validation only if user is not authenticated
        if (!$user) {
            $rules['guest_email'] = 'required|email';
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_phone'] = 'required|string|max:20';
        } else {
            $rules['guest_email'] = 'nullable|email';
            $rules['guest_name'] = 'nullable|string|max:255';
            $rules['guest_phone'] = 'nullable|string|max:20';
        }

        $validated = $request->validate($rules);

        $order = $this->orderService->createOrderWithGuest(
            $user,
            $validated['items'],
            [
                'shipping_address_id' => $validated['shipping_address_id'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'billing_address_id' => $validated['billing_address_id'] ?? null,
                'billing_address' => $validated['billing_address'] ?? null,
                'use_shipping_for_billing' => $validated['use_shipping_for_billing'] ?? true,
                'shipping_method' => $validated['shipping_method'],
                'notes' => $validated['notes'] ?? null,
                'is_preorder' => $validated['is_preorder'] ?? false,
                'pay_deposit_only' => $validated['pay_deposit_only'] ?? false,
                // Guest info
                'guest_email' => $validated['guest_email'] ?? null,
                'guest_name' => $validated['guest_name'] ?? null,
                'guest_phone' => $validated['guest_phone'] ?? null,
                'create_account' => $validated['create_account'] ?? false,
                'password' => $validated['password'] ?? null,
            ],
            $validated['coupon_code'] ?? null
        );

        // Initialize payment
        $paymentResult = $this->paymentService->processPayment(
            $order,
            $validated['payment_method']
        );

        // Notify admins about new order
        app(\App\Services\NotificationService::class)->notifyNewOrder($order);

        // Notify customer about order confirmation
        app(\App\Services\NotificationService::class)->notifyOrderConfirmed($order);

        $responseData = [
            'order' => $order,
            'payment' => $paymentResult,
            'account_created' => $order->user_id && ($validated['create_account'] ?? false),
        ];

        // For COD, add success message
        if (in_array($validated['payment_method'], ['cod', 'cash_on_delivery'])) {
            $responseData['message'] = 'Order placed successfully. You will pay on delivery.';
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => $responseData,
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
