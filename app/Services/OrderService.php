<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\Contracts\CouponServiceInterface;
use App\Services\Contracts\InventoryServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PromotionServiceInterface;
use App\Services\Contracts\ShippingServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService,
        protected PromotionServiceInterface $promotionService,
        protected CouponServiceInterface $couponService,
        protected ShippingServiceInterface $shippingService
    ) {}

    /**
     * Create an online order.
     * 
     * @param User $user
     * @param array $cartItems
     * @param array $shippingData
     * @param string|null $couponCode
     * @return Order
     */
    public function createOrder(User $user, array $cartItems, array $shippingData, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $shippingData, $couponCode) {
            // Check if this is a preorder
            $isPreorder = $shippingData['is_preorder'] ?? false;
            $payDepositOnly = $shippingData['pay_deposit_only'] ?? false;

            // Apply promotions
            $promotionResult = $this->promotionService->applyPromotion($cartItems);
            $subtotal = collect($promotionResult['items'])->sum(fn($item) => $item['discounted_price'] * $item['quantity']);
            $promotionDiscount = $promotionResult['total_discount'];

            // Calculate preorder deposit if applicable
            $depositAmount = null;
            $remainingAmount = null;
            if ($isPreorder && $payDepositOnly) {
                $depositAmount = 0;
                foreach ($cartItems as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->is_preorder) {
                        $depositPerUnit = $product->calculatePreorderDeposit();
                        $depositAmount += $depositPerUnit * $item['quantity'];
                    }
                }
                $remainingAmount = $subtotal - $depositAmount;
            }

            // Apply coupon if provided
            $coupon = null;
            $couponDiscount = 0;
            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $user->email,
                    $cartItems,
                    $subtotal
                );
                if ($couponResult['valid']) {
                    $coupon = $couponResult['coupon'];
                    $couponDiscount = $couponResult['discount'];
                }
            }

            // Calculate shipping
            $address = Address::find($shippingData['shipping_address_id']);
            $shippingMethod = $shippingData['shipping_method'] ?? ShippingService::METHOD_PATHAO_COURIER;
            
            $itemsForShipping = $this->prepareItemsForShipping($cartItems);
            $shippingCost = $this->shippingService->getShippingCostForMethod(
                $shippingMethod,
                $itemsForShipping,
                $address,
                $subtotal - $couponDiscount
            );

            // Check for free delivery promotion
            if ($this->promotionService->hasFreeDeliveryPromotion($subtotal)) {
                $shippingCost = 0;
            }

            // Check for free shipping coupon
            if ($coupon && $this->couponService->providesFreeShipping($coupon)) {
                $shippingCost = 0;
            }

            // Calculate totals
            $totalDiscount = $promotionDiscount + $couponDiscount;
            $taxAmount = 0; // Bangladesh typically doesn't have VAT on e-commerce
            $totalAmount = $subtotal + $shippingCost + $taxAmount - $couponDiscount;

            // Determine preorder payment status
            $preorderPaymentStatus = 'pending';
            if ($isPreorder && $payDepositOnly) {
                $preorderPaymentStatus = 'deposit_paid';
            } elseif ($isPreorder && !$payDepositOnly) {
                $preorderPaymentStatus = 'fully_paid';
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'order_type' => 'online',
                'is_preorder' => $isPreorder,
                'preorder_deposit_paid' => $depositAmount,
                'preorder_remaining_amount' => $remainingAmount,
                'preorder_payment_status' => $preorderPaymentStatus,
                'shipping_address_id' => $shippingData['shipping_address_id'],
                'billing_address_id' => $shippingData['billing_address_id'] ?? $shippingData['shipping_address_id'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $payDepositOnly ? $depositAmount : $totalAmount,
                'coupon_id' => $coupon?->id,
                'shipping_method' => $shippingMethod,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $shippingData['notes'] ?? null,
            ]);

            // Create order items and reserve inventory
            foreach ($promotionResult['items'] as $item) {
                $orderItem = $this->createOrderItem($order, $item);
                $this->inventoryService->reserveStock($orderItem);
            }

            // Record coupon usage
            if ($coupon && $couponDiscount > 0) {
                $this->couponService->recordCouponUsage($coupon, $order, $user->email, $couponDiscount);
            }

            return $order->load(['items', 'user', 'shippingAddress']);
        });
    }

    /**
     * Create an order with guest support.
     */
    public function createOrderWithGuest(?User $user, array $cartItems, array $shippingData, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $shippingData, $couponCode) {
            // Handle guest checkout - create user if requested
            if (!$user && !empty($shippingData['create_account'])) {
                // Split guest name into first and last name
                $nameParts = explode(' ', $shippingData['guest_name'], 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';

                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $shippingData['guest_email'],
                    'phone' => $shippingData['guest_phone'],
                    'password' => bcrypt($shippingData['password']),
                    'user_type' => 'customer',
                    'status' => true,
                ]);
            }

            // Handle addresses for guest or authenticated users
            $shippingAddressId = $shippingData['shipping_address_id'] ?? null;
            $billingAddressId = $shippingData['billing_address_id'] ?? null;

            // Create temporary addresses for guest if needed
            if (!$shippingAddressId && !empty($shippingData['shipping_address'])) {
                $shippingAddress = \App\Models\Address::create([
                    'user_id' => $user?->id,
                    'address_type' => 'shipping_address',
                    'address_line_1' => $shippingData['shipping_address']['address_line_1'],
                    'address_line_2' => $shippingData['shipping_address']['address_line_2'] ?? null,
                    'city' => $shippingData['shipping_address']['city'],
                    'state' => $shippingData['shipping_address']['state'] ?? null,
                    'zip_code' => $shippingData['shipping_address']['zip_code'] ?? $shippingData['shipping_address']['postal_code'] ?? '00000',
                    'contact_no' => $shippingData['shipping_address']['phone'],
                    'is_default' => $user ? true : false,
                ]);
                $shippingAddressId = $shippingAddress->id;
            }

            // Handle billing address
            if ($shippingData['use_shipping_for_billing'] ?? true) {
                $billingAddressId = $shippingAddressId;
            } elseif (!$billingAddressId && !empty($shippingData['billing_address'])) {
                $billingAddress = \App\Models\Address::create([
                    'user_id' => $user?->id,
                    'address_type' => 'billing_address',
                    'address_line_1' => $shippingData['billing_address']['address_line_1'],
                    'address_line_2' => $shippingData['billing_address']['address_line_2'] ?? null,
                    'city' => $shippingData['billing_address']['city'],
                    'state' => $shippingData['billing_address']['state'] ?? null,
                    'zip_code' => $shippingData['billing_address']['zip_code'] ?? $shippingData['billing_address']['postal_code'] ?? '00000',
                    'contact_no' => $shippingData['billing_address']['phone'],
                    'is_default' => false,
                ]);
                $billingAddressId = $billingAddress->id;
            }

            // Update shipping data with address IDs
            $shippingData['shipping_address_id'] = $shippingAddressId;
            $shippingData['billing_address_id'] = $billingAddressId;

            // Store guest info for order
            $guestEmail = $shippingData['guest_email'] ?? null;
            $guestName = $shippingData['guest_name'] ?? null;
            $guestPhone = $shippingData['guest_phone'] ?? null;

            // Check if this is a preorder
            $isPreorder = $shippingData['is_preorder'] ?? false;
            $payDepositOnly = $shippingData['pay_deposit_only'] ?? false;

            // Apply promotions
            $promotionResult = $this->promotionService->applyPromotion($cartItems);
            $subtotal = collect($promotionResult['items'])->sum(fn($item) => $item['discounted_price'] * $item['quantity']);
            $promotionDiscount = $promotionResult['total_discount'];

            // Calculate preorder deposit if applicable
            $depositAmount = null;
            $remainingAmount = null;
            if ($isPreorder && $payDepositOnly) {
                $depositAmount = 0;
                foreach ($cartItems as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->is_preorder) {
                        $depositPerUnit = $product->calculatePreorderDeposit();
                        $depositAmount += $depositPerUnit * $item['quantity'];
                    }
                }
                $remainingAmount = $subtotal - $depositAmount;
            }

            // Apply coupon if provided
            $coupon = null;
            $couponDiscount = 0;
            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $user?->email ?? $guestEmail,
                    $cartItems,
                    $subtotal
                );
                if ($couponResult['valid']) {
                    $coupon = $couponResult['coupon'];
                    $couponDiscount = $couponResult['discount'];
                }
            }

            // Calculate shipping
            $address = \App\Models\Address::find($shippingAddressId);
            $shippingMethod = $shippingData['shipping_method'] ?? ShippingService::METHOD_PATHAO_COURIER;

            $itemsForShipping = $this->prepareItemsForShipping($cartItems);
            $shippingCost = $this->shippingService->getShippingCostForMethod(
                $shippingMethod,
                $itemsForShipping,
                $address,
                $subtotal - $couponDiscount
            );

            // Check for free delivery promotion
            if ($this->promotionService->hasFreeDeliveryPromotion($subtotal)) {
                $shippingCost = 0;
            }

            // Check for free shipping coupon
            if ($coupon && $this->couponService->providesFreeShipping($coupon)) {
                $shippingCost = 0;
            }

            // Calculate totals
            $totalDiscount = $promotionDiscount + $couponDiscount;
            $taxAmount = 0;
            $totalAmount = $subtotal + $shippingCost + $taxAmount - $couponDiscount;

            // Determine preorder payment status
            $preorderPaymentStatus = 'pending';
            if ($isPreorder && $payDepositOnly) {
                $preorderPaymentStatus = 'deposit_paid';
            } elseif ($isPreorder && !$payDepositOnly) {
                $preorderPaymentStatus = 'fully_paid';
            }

            // Create order
            $order = Order::create([
                'user_id' => $user?->id,
                'order_number' => $this->generateOrderNumber(),
                'order_type' => 'online',
                'is_preorder' => $isPreorder,
                'preorder_deposit_paid' => $depositAmount,
                'preorder_remaining_amount' => $remainingAmount,
                'preorder_payment_status' => $preorderPaymentStatus,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $billingAddressId,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $payDepositOnly ? $depositAmount : $totalAmount,
                'coupon_id' => $coupon?->id,
                'shipping_method' => $shippingMethod,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $shippingData['notes'] ?? null,
                // Store guest info if not a registered user
                'customer_name' => $user ? null : $guestName,
                'customer_email' => $user ? null : $guestEmail,
                'customer_phone' => $user ? null : $guestPhone,
            ]);

            // Create order items and reserve inventory
            foreach ($promotionResult['items'] as $item) {
                $orderItem = $this->createOrderItem($order, $item);
                $this->inventoryService->reserveStock($orderItem);
            }

            // Record coupon usage
            if ($coupon && $couponDiscount > 0) {
                $this->couponService->recordCouponUsage($coupon, $order, $user?->email ?? $guestEmail, $couponDiscount);
            }

            return $order->load(['items', 'user', 'shippingAddress', 'billingAddress']);
        });
    }


    /**
     * Create a POS order for in-store sales.
     * 
     * @param array $customerData Contains name, email, phone, address (optional)
     * @param array $items
     * @param float|null $discount Manual discount amount
     * @return Order
     */
    public function createPosOrder(array $customerData, array $items, ?float $discount = null): Order
    {
        return DB::transaction(function () use ($customerData, $items, $discount) {
            // Calculate subtotal
            $subtotal = 0;
            $processedItems = [];

            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    continue;
                }

                $variation = isset($item['variation_id']) 
                    ? ProductVariation::find($item['variation_id']) 
                    : null;

                $price = $variation?->price ?? $product->price;
                $quantity = $item['quantity'];
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;

                $processedItems[] = [
                    'product_id' => $product->id,
                    'product_variation_id' => $variation?->id,
                    'product_name' => $product->name,
                    'variation_details' => $variation ? $this->getVariationDetails($variation) : null,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $itemTotal,
                ];
            }

            // Apply discount
            $discountAmount = $discount ?? 0;
            $totalAmount = max(0, $subtotal - $discountAmount);

            // Create order
            $order = Order::create([
                'user_id' => null, // POS orders may not have registered user
                'order_number' => $this->generateOrderNumber('POS'),
                'order_type' => 'in_store',
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'shipping_method' => 'none',
                'discount_amount' => $discountAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'] ?? null,
            ]);

            // Create order items and reserve inventory
            foreach ($processedItems as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'],
                    'product_name' => $item['product_name'],
                    'variation_details' => $item['variation_details'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $this->inventoryService->reserveStock($orderItem);
            }

            return $order->load('items');
        });
    }

    /**
     * Update order status.
     * 
     * @param Order $order
     * @param string $status
     * @return Order
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        // TODO: Dispatch OrderStatusChanged event for email notification

        return $order->fresh();
    }

    /**
     * Cancel an order.
     * 
     * @param Order $order
     * @param string $reason
     * @return Order
     */
    public function cancelOrder(Order $order, string $reason): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            // Release inventory for all items
            foreach ($order->items as $item) {
                $this->inventoryService->releaseStock($item);
            }

            $order->update([
                'status' => 'cancelled',
                'notes' => $order->notes . "\nCancellation reason: " . $reason,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Get order history for a user.
     * 
     * @param User $user
     * @return Collection
     */
    public function getOrderHistory(User $user): Collection
    {
        return $user->orders()
            ->with(['items.product.images', 'items.productVariation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get order by order number.
     * 
     * @param string $orderNumber
     * @return Order|null
     */
    public function getOrderByNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->with(['items.product', 'items.productVariation', 'user', 'shippingAddress'])
            ->first();
    }

    /**
     * Generate unique order number.
     * 
     * @param string $prefix
     * @return string
     */
    protected function generateOrderNumber(string $prefix = 'SS'): string
    {
        $date = now()->format('Ymd');
        
        do {
            $random = strtoupper(Str::random(6));
            $orderNumber = "{$prefix}-{$date}-{$random}";
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Create order item from cart item.
     * 
     * @param Order $order
     * @param array $item
     * @return OrderItem
     */
    protected function createOrderItem(Order $order, array $item): OrderItem
    {
        $product = Product::find($item['product_id']);
        $variation = isset($item['variation_id']) 
            ? ProductVariation::find($item['variation_id']) 
            : null;

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product_id'],
            'product_variation_id' => $variation?->id,
            'product_name' => $product->name,
            'variation_details' => $variation ? $this->getVariationDetails($variation) : null,
            'quantity' => $item['quantity'],
            'unit_price' => $item['discounted_price'] ?? $item['price'],
            'total_price' => ($item['discounted_price'] ?? $item['price']) * $item['quantity'],
        ]);
    }

    /**
     * Get variation details as JSON.
     * 
     * @param ProductVariation $variation
     * @return array
     */
    protected function getVariationDetails(ProductVariation $variation): array
    {
        $details = [];
        
        foreach ($variation->variationValues as $value) {
            // This would need the variation option relationship
            $details[] = [
                'option_id' => $value->variation_option_id,
                // 'name' => $value->variationOption->name ?? '',
                // 'value' => $value->variationOption->value ?? '',
            ];
        }

        return $details;
    }

    /**
     * Prepare items for shipping calculation.
     * 
     * @param array $cartItems
     * @return array
     */
    protected function prepareItemsForShipping(array $cartItems): array
    {
        $items = [];

        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                continue;
            }

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'weight' => $product->weight ?? 0,
                'weight_unit' => $product->weight_unit ?? 'kg',
                'length' => $product->length ?? 0,
                'width' => $product->width ?? 0,
                'height' => $product->height ?? 0,
                'shipping_class_id' => $product->shipping_class_id,
            ];
        }

        return $items;
    }
}
