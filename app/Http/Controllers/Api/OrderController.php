<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService
    ) {}

    /**
     * Get user's order history.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getOrderHistory($request->user());

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get single order details.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->with([
                'items.product', 'items.productVariation',
                'shippingAddress', 'payments', 'invoice'
            ])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Track order status.
     */
    public function track(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->select(['order_number', 'status', 'shipping_method', 'tracking_number', 'created_at', 'updated_at'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Cancel an order (if allowed).
     */
    public function cancel(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled at this stage.',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order = $this->orderService->cancelOrder($order, $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Download invoice.
     */
    public function invoice(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->with('invoice')
            ->first();

        if (!$order || !$order->invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_number' => $order->invoice->invoice_number,
                'pdf_url' => $order->invoice->pdf_path,
            ],
        ]);
    }
}
