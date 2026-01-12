<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * Handle SSL Commerz IPN callback.
     */
    public function sslCommerzIpn(Request $request): JsonResponse
    {
        $result = $this->paymentService->handlePaymentCallback(
            'ssl_commerz',
            $request->all()
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? null,
        ]);
    }

    /**
     * Handle SSL Commerz success redirect.
     */
    public function sslCommerzSuccess(Request $request)
    {
        $orderNumber = $request->get('tran_id');
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return redirect()->away(config('app.frontend_url') . '/payment/failed');
        }

        return redirect()->away(
            config('app.frontend_url') . '/order/success/' . $order->order_number
        );
    }

    /**
     * Handle SSL Commerz failure redirect.
     */
    public function sslCommerzFail(Request $request)
    {
        $orderNumber = $request->get('tran_id');

        return redirect()->away(
            config('app.frontend_url') . '/payment/failed?order=' . $orderNumber
        );
    }

    /**
     * Handle SSL Commerz cancel redirect.
     */
    public function sslCommerzCancel(Request $request)
    {
        $orderNumber = $request->get('tran_id');

        return redirect()->away(
            config('app.frontend_url') . '/payment/cancelled?order=' . $orderNumber
        );
    }

    /**
     * Get payment status for an order.
     */
    public function status(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('payments')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'payments' => $order->payments,
            ],
        ]);
    }

    /**
     * Retry payment for a pending order.
     */
    public function retry(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->where('payment_status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or payment already completed.',
            ], 404);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:ssl_commerz,bkash,nagad',
        ]);

        $paymentResult = $this->paymentService->processPayment(
            $order,
            $validated['payment_method']
        );

        return response()->json([
            'success' => true,
            'data' => $paymentResult,
        ]);
    }
}
