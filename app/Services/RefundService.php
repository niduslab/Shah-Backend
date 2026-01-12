<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductReturn;
use App\Models\Refund;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\RefundServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RefundService implements RefundServiceInterface
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * Create a refund for a return.
     * 
     * @param ProductReturn $return
     * @param float $amount
     * @param string $method
     * @return Refund
     */
    public function createRefund(ProductReturn $return, float $amount, string $method = 'original_payment'): Refund
    {
        $order = $return->order;
        $orderItem = $return->orderItem;

        // Calculate max refundable amount
        $maxRefundable = $orderItem->total_price * ($return->quantity / $orderItem->quantity);
        
        if ($amount > $maxRefundable) {
            throw new \InvalidArgumentException("Refund amount cannot exceed {$maxRefundable}");
        }

        // Determine refund type
        $refundType = $amount >= $maxRefundable ? 'full' : 'partial';

        return Refund::create([
            'order_id' => $order->id,
            'return_id' => $return->id,
            'user_id' => $return->user_id,
            'amount' => $amount,
            'refund_type' => $refundType,
            'refund_method' => $method,
            'status' => 'pending',
        ]);
    }

    /**
     * Create a refund for an order (without return).
     * 
     * @param Order $order
     * @param float $amount
     * @param string $method
     * @return Refund
     */
    public function createOrderRefund(Order $order, float $amount, string $method = 'original_payment'): Refund
    {
        // Calculate total already refunded
        $totalRefunded = $order->refunds()
            ->whereIn('status', ['completed', 'processing'])
            ->sum('amount');

        $maxRefundable = $order->total_amount - $totalRefunded;

        if ($amount > $maxRefundable) {
            throw new \InvalidArgumentException("Refund amount cannot exceed {$maxRefundable}");
        }

        // Determine refund type
        $refundType = $amount >= $order->total_amount ? 'full' : 'partial';

        return Refund::create([
            'order_id' => $order->id,
            'return_id' => null,
            'user_id' => $order->user_id,
            'amount' => $amount,
            'refund_type' => $refundType,
            'refund_method' => $method,
            'status' => 'pending',
        ]);
    }

    /**
     * Process a pending refund.
     * 
     * @param Refund $refund
     * @return Refund
     */
    public function processRefund(Refund $refund): Refund
    {
        if ($refund->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending refunds can be processed.');
        }

        return DB::transaction(function () use ($refund) {
            $refund->update(['status' => 'processing']);

            $order = $refund->order;
            $payment = $order->payment;

            // Process based on refund method
            $success = false;

            switch ($refund->refund_method) {
                case 'original_payment':
                    if ($payment && $payment->status === 'completed') {
                        $success = $this->paymentService->refundPayment($payment, $refund->amount);
                    } else {
                        // For manual/POS payments, just mark as completed
                        $success = true;
                    }
                    break;

                case 'bank_transfer':
                    // Bank transfer requires manual processing
                    // Just mark as processing and admin will complete manually
                    $success = true;
                    break;

                case 'store_credit':
                    // Store credit would be implemented if needed
                    $success = true;
                    break;
            }

            if ($success) {
                $refund->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                // Update order status if fully refunded
                $this->updateOrderRefundStatus($order);
            } else {
                $refund->update(['status' => 'failed']);
            }

            return $refund->fresh();
        });
    }

    /**
     * Get refunds for an order.
     * 
     * @param Order $order
     * @return Collection
     */
    public function getOrderRefunds(Order $order): Collection
    {
        return $order->refunds()
            ->with('return')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending refunds.
     * 
     * @return Collection
     */
    public function getPendingRefunds(): Collection
    {
        return Refund::where('status', 'pending')
            ->with(['order', 'user', 'return'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Update order refund status.
     * 
     * @param Order $order
     * @return void
     */
    protected function updateOrderRefundStatus(Order $order): void
    {
        $totalRefunded = $order->refunds()
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalRefunded >= $order->total_amount) {
            $order->update([
                'status' => 'refunded',
                'payment_status' => 'refunded',
            ]);
        }
    }

    /**
     * Get refund by ID.
     * 
     * @param int $id
     * @return Refund|null
     */
    public function getRefundById(int $id): ?Refund
    {
        return Refund::with(['order', 'user', 'return'])
            ->find($id);
    }
}
