<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Refund;
use App\Services\Contracts\RefundServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(
        protected RefundServiceInterface $refundService
    ) {}

    /**
     * List all refunds.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Refund::with(['order', 'returnRequest', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('refund_method')) {
            $query->where('refund_method', $request->refund_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', fn($q) => 
                $q->where('order_number', 'like', "%{$search}%")
            );
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $refunds = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $refunds,
        ]);
    }

    /**
     * Get a specific refund.
     */
    public function show(int $id): JsonResponse
    {
        $refund = Refund::with(['order', 'returnRequest', 'processedBy'])->find($id);

        if (!$refund) {
            return response()->json([
                'success' => false,
                'message' => 'Refund not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $refund,
        ]);
    }

    /**
     * Create a refund for an order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'return_id' => 'nullable|exists:returns,id',
            'amount' => 'required|numeric|min:0.01',
            'refund_type' => 'required|in:full,partial',
            'refund_method' => 'required|in:original_payment,store_credit,bank_transfer',
            'reason' => 'nullable|string|max:500',
        ]);

        $order = Order::find($validated['order_id']);

        // Validate refund amount
        $existingRefunds = $order->refunds()->where('status', 'completed')->sum('amount');
        $maxRefundable = $order->total_amount - $existingRefunds;

        if ($validated['amount'] > $maxRefundable) {
            return response()->json([
                'success' => false,
                'message' => "Maximum refundable amount is {$maxRefundable}.",
            ], 400);
        }

        $refund = $this->refundService->createRefund(
            $order,
            $validated['amount'],
            $validated['refund_type'],
            $validated['refund_method'],
            $validated['reason'] ?? null,
            $validated['return_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund created successfully.',
            'data' => $refund,
        ], 201);
    }

    /**
     * Process a pending refund.
     */
    public function process(int $id): JsonResponse
    {
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json([
                'success' => false,
                'message' => 'Refund not found.',
            ], 404);
        }

        if ($refund->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Refund is not pending.',
            ], 400);
        }

        $refund = $this->refundService->processRefund($refund);

        return response()->json([
            'success' => true,
            'message' => 'Refund processed successfully.',
            'data' => $refund,
        ]);
    }

    /**
     * Cancel a pending refund.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json([
                'success' => false,
                'message' => 'Refund not found.',
            ], 404);
        }

        if ($refund->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending refunds can be cancelled.',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $refund->update([
            'status' => 'cancelled',
            'notes' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund cancelled.',
            'data' => $refund,
        ]);
    }
}
