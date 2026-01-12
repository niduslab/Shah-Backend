<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\ShippingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService,
        protected ShippingServiceInterface $shippingService
    ) {}

    /**
     * List all orders with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product', 'shippingAddress']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('shipping_method')) {
            $query->where('shipping_method', $request->shipping_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get a specific order.
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::with([
            'user', 'items.product', 'items.productVariation',
            'shippingAddress', 'billingAddress', 'coupon',
            'payments', 'invoice'
        ])->find($id);

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
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order = $this->orderService->updateStatus($order, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already cancelled.',
            ], 400);
        }

        if (in_array($order->status, ['shipped', 'delivered'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel shipped or delivered orders.',
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
     * Assign tracking number to order.
     */
    public function assignTracking(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        $this->shippingService->assignTrackingNumber($order, $validated['tracking_number']);

        return response()->json([
            'success' => true,
            'message' => 'Tracking number assigned successfully.',
            'data' => $order->fresh(),
        ]);
    }

    /**
     * Update order notes.
     */
    public function updateNotes(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $order->update(['notes' => $validated['notes']]);

        return response()->json([
            'success' => true,
            'message' => 'Order notes updated successfully.',
            'data' => $order,
        ]);
    }
}
