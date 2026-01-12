<?php

namespace App\Services\Contracts;

use App\Models\Order;
use App\Models\ProductReturn;
use App\Models\Refund;

interface RefundServiceInterface
{
    /**
     * Create a refund for a return.
     */
    public function createRefund(ProductReturn $return, float $amount, string $method = 'original_payment'): Refund;

    /**
     * Create a refund for an order (without return).
     */
    public function createOrderRefund(Order $order, float $amount, string $method = 'original_payment'): Refund;

    /**
     * Process a pending refund.
     */
    public function processRefund(Refund $refund): Refund;

    /**
     * Get refunds for an order.
     */
    public function getOrderRefunds(Order $order): \Illuminate\Support\Collection;
}
