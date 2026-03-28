<?php

namespace App\Services\Contracts;

use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderServiceInterface
{
    /**
     * Create an online order.
     */
    public function createOrder(User $user, array $cartItems, array $shippingData, ?string $couponCode = null): Order;

    /**
     * Create an order with guest support (can create user account).
     */
    public function createOrderWithGuest(?User $user, array $cartItems, array $shippingData, ?string $couponCode = null): Order;

    /**
     * Create a POS order for in-store sales.
     */
    public function createPosOrder(array $customerData, array $items, ?float $discount = null): Order;

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): Order;

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order, string $reason): Order;

    /**
     * Get order history for a user.
     */
    public function getOrderHistory(User $user): Collection;

    /**
     * Get paginated order history for a user.
     */
    public function getOrderHistoryPaginated(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get order by order number.
     */
    public function getOrderByNumber(string $orderNumber): ?Order;
}
