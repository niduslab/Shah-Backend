<?php

namespace App\Services\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Process payment through SSL gateway.
     */
    public function processPayment(Order $order, string $method): array;

    /**
     * Record manual payment for POS orders.
     */
    public function recordManualPayment(Order $order, float $amount, string $method = 'manual'): Payment;

    /**
     * Handle payment gateway callback.
     */
    public function handlePaymentCallback(string $method, array $data): array;

    /**
     * Get payment by transaction ID.
     */
    public function getPaymentByTransactionId(string $transactionId): ?Payment;

    /**
     * Refund a payment.
     */
    public function refundPayment(Payment $payment, float $amount): bool;
}
