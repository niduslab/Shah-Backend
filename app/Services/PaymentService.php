<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService implements PaymentServiceInterface
{
    /**
     * SSL Commerz configuration.
     */
    protected string $sslStoreId;
    protected string $sslStorePassword;
    protected string $sslBaseUrl;
    protected bool $sslSandbox;

    public function __construct()
    {
        $this->sslStoreId = config('services.sslcommerz.store_id', '');
        $this->sslStorePassword = config('services.sslcommerz.store_password', '');
        $this->sslSandbox = config('services.sslcommerz.sandbox', true);
        $this->sslBaseUrl = $this->sslSandbox 
            ? 'https://sandbox.sslcommerz.com' 
            : 'https://securepay.sslcommerz.com';
    }

    /**
     * Process payment through SSL gateway.
     * 
     * @param Order $order
     * @param string $method ssl_commerz, bkash, nagad, cod
     * @return array Contains 'success', 'redirect_url', 'payment_id'
     */
    public function processPayment(Order $order, string $method): array
    {
        // Handle Cash on Delivery
        if (in_array($method, ['cod', 'cash_on_delivery'])) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->total_amount,
                'currency' => 'BDT',
                'payment_method' => 'manual', // Store as manual in DB
                'transaction_id' => $this->generateTransactionId('COD'),
                'status' => 'pending',
                'gateway_response' => ['method' => $method],
            ]);

            // Update order status for COD
            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'pending', // Will be paid on delivery
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'method' => $method,
                'message' => 'Order placed successfully. Pay on delivery.',
            ];
        }

        // Create pending payment record for online payments
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total_amount,
            'currency' => 'BDT',
            'payment_method' => $method,
            'transaction_id' => $this->generateTransactionId(),
            'status' => 'pending',
        ]);

        if ($method === 'ssl_commerz') {
            return $this->initiateSslPayment($order, $payment);
        }

        // For bkash/nagad, return payment info for mobile integration
        return [
            'success' => true,
            'payment_id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
            'method' => $method,
        ];
    }

    /**
     * Record manual payment for POS orders.
     * 
     * @param Order $order
     * @param float $amount
     * @param string $method
     * @return Payment
     */
    public function recordManualPayment(Order $order, float $amount, string $method = 'manual'): Payment
    {
        return DB::transaction(function () use ($order, $amount, $method) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $amount,
                'currency' => 'BDT',
                'payment_method' => 'manual', // DB enum constraint
                'transaction_id' => $this->generateTransactionId('MAN'),
                'status' => 'completed',
                'gateway_response' => ['method' => $method], // Store actual method (cash/card) here
                'paid_at' => now(),
            ]);

            // Update order payment status
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);

            return $payment;
        });
    }

    /**
     * Handle payment gateway callback.
     * 
     * @param string $method
     * @param array $data
     * @return array
     */
    public function handlePaymentCallback(string $method, array $data): array
    {
        return DB::transaction(function () use ($method, $data) {
            $transactionId = $data['tran_id'] ?? $data['transaction_id'] ?? null;
            
            if (!$transactionId) {
                return [
                    'success' => false,
                    'message' => 'Transaction ID not found',
                ];
            }
            
            $payment = $this->getPaymentByTransactionId($transactionId);
            
            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Payment not found',
                ];
            }

            $status = $this->mapGatewayStatus($data['status'] ?? 'FAILED');

            $payment->update([
                'status' => $status,
                'gateway_response' => $data,
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            // Update order payment status
            if ($status === 'completed') {
                $payment->order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);
            } elseif ($status === 'failed') {
                $payment->order->update([
                    'payment_status' => 'failed',
                ]);
            }

            return [
                'success' => true,
                'message' => 'Payment callback processed successfully',
                'payment_status' => $status,
            ];
        });
    }

    /**
     * Get payment by transaction ID.
     * 
     * @param string $transactionId
     * @return Payment|null
     */
    public function getPaymentByTransactionId(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }

    /**
     * Refund a payment.
     * 
     * @param Payment $payment
     * @param float $amount
     * @return bool
     */
    public function refundPayment(Payment $payment, float $amount): bool
    {
        if ($payment->status !== 'completed') {
            return false;
        }

        if ($amount > $payment->amount) {
            return false;
        }

        // For manual payments, just update status
        if ($payment->payment_method === 'manual') {
            $payment->update(['status' => 'refunded']);
            return true;
        }

        // For SSL Commerz, initiate refund through API
        if ($payment->payment_method === 'ssl_commerz') {
            return $this->initiateSslRefund($payment, $amount);
        }

        return false;
    }

    /**
     * Initiate SSL Commerz payment.
     * 
     * @param Order $order
     * @param Payment $payment
     * @return array
     */
    protected function initiateSslPayment(Order $order, Payment $payment): array
    {
        $postData = [
            'store_id' => $this->sslStoreId,
            'store_passwd' => $this->sslStorePassword,
            'total_amount' => $order->total_amount,
            'currency' => 'BDT',
            'tran_id' => $payment->transaction_id,
            'success_url' => url('/api/payments/ssl-commerz/success'),
            'fail_url' => url('/api/payments/ssl-commerz/fail'),
            'cancel_url' => url('/api/payments/ssl-commerz/cancel'),
            'ipn_url' => url('/api/payments/ssl-commerz/ipn'),
            'cus_name' => $order->customer_name ?? $order->user?->full_name ?? 'Customer',
            'cus_email' => $order->customer_email ?? $order->user?->email ?? 'customer@example.com',
            'cus_phone' => $order->customer_phone ?? $order->user?->phone ?? '01700000000',
            'cus_add1' => $order->shippingAddress?->address_line_1 ?? 'N/A',
            'cus_city' => $order->shippingAddress?->city ?? 'Dhaka',
            'cus_country' => 'Bangladesh',
            'shipping_method' => 'NO',
            'product_name' => 'Shah Sports Order #' . $order->order_number,
            'product_category' => 'Sports Equipment',
            'product_profile' => 'physical-goods',
        ];

        try {
            $response = Http::asForm()->post("{$this->sslBaseUrl}/gwprocess/v4/api.php", $postData);
            $result = $response->json();

            if (isset($result['GatewayPageURL']) && $result['status'] === 'SUCCESS') {
                return [
                    'success' => true,
                    'redirect_url' => $result['GatewayPageURL'],
                    'payment_id' => $payment->id,
                    'session_key' => $result['sessionkey'] ?? null,
                ];
            }

            Log::error('SSL Commerz initiation failed', ['response' => $result]);

            return [
                'success' => false,
                'error' => $result['failedreason'] ?? 'Payment initiation failed',
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            Log::error('SSL Commerz exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Payment gateway error',
                'payment_id' => $payment->id,
            ];
        }
    }

    /**
     * Initiate SSL Commerz refund.
     * 
     * @param Payment $payment
     * @param float $amount
     * @return bool
     */
    protected function initiateSslRefund(Payment $payment, float $amount): bool
    {
        $gatewayResponse = $payment->gateway_response;
        $bankTranId = $gatewayResponse['bank_tran_id'] ?? null;

        if (!$bankTranId) {
            return false;
        }

        try {
            $response = Http::get("{$this->sslBaseUrl}/validator/api/merchantTransIDvalidationAPI.php", [
                'store_id' => $this->sslStoreId,
                'store_passwd' => $this->sslStorePassword,
                'refund_amount' => $amount,
                'refund_remarks' => 'Customer refund',
                'bank_tran_id' => $bankTranId,
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === 'success') {
                $payment->update(['status' => 'refunded']);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('SSL Commerz refund failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate unique transaction ID.
     * 
     * @param string $prefix
     * @return string
     */
    protected function generateTransactionId(string $prefix = 'TXN'): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }

    /**
     * Map gateway status to internal status.
     * 
     * @param string $gatewayStatus
     * @return string
     */
    protected function mapGatewayStatus(string $gatewayStatus): string
    {
        return match (strtoupper($gatewayStatus)) {
            'VALID', 'VALIDATED', 'SUCCESS' => 'completed',
            'FAILED', 'INVALID_TRANSACTION' => 'failed',
            'CANCELLED' => 'failed',
            default => 'pending',
        };
    }
}
