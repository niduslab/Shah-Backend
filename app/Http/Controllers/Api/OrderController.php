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
        $perPage = $request->query('per_page', 15);
        $orders = $this->orderService->getOrderHistoryPaginated($request->user(), (int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]);
    }

    /**
     * Get single order details.
     * Supports both authenticated and guest users.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $query = Order::where('order_number', $orderNumber);
        
        // If user is authenticated, verify ownership
        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }
        
        $order = $query->with([
                'items.product.images', 
                'items.productVariation',
                'shippingAddress', 
                'payments', 
                'invoice'
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

        // Notify admins about cancellation
        app(\App\Services\NotificationService::class)->notifyOrderCancelled($order, 'customer');

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Download invoice.
     */
    public function invoice(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->with('invoice')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $invoiceService = app(\App\Services\Contracts\InvoiceServiceInterface::class);

        // If invoice doesn't exist, generate it synchronously for immediate download
        if (!$order->invoice) {
            try {
                $invoice = $invoiceService->generateInvoice($order);
            } catch (\Exception $e) {
                \Log::error('Failed to generate invoice', [
                    'order_number' => $orderNumber,
                    'error' => $e->getMessage(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate invoice. Please try again later.',
                ], 500);
            }
        } else {
            $invoice = $order->invoice;
        }

        // Download the invoice PDF
        return $invoiceService->downloadInvoice($invoice);
    }
}
