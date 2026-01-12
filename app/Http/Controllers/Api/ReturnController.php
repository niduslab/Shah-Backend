<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\ProductReturn;
use App\Services\Contracts\ReturnServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnServiceInterface $returnService
    ) {}

    /**
     * Get user's return requests.
     */
    public function index(Request $request): JsonResponse
    {
        $returns = ProductReturn::whereHas('orderItem.order', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
            ->with(['orderItem.product', 'orderItem.order:id,order_number'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $returns,
        ]);
    }

    /**
     * Create a return request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:defective,wrong_item,not_as_described,changed_mind,other',
            'reason_details' => 'nullable|string|max:1000',
        ]);

        $orderItem = OrderItem::with('order')->find($validated['order_item_id']);

        // Verify ownership
        if ($orderItem->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.',
            ], 404);
        }

        // Check if order is delivered
        if ($orderItem->order->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Returns can only be requested for delivered orders.',
            ], 400);
        }

        // Check quantity
        if ($validated['quantity'] > $orderItem->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Return quantity exceeds ordered quantity.',
            ], 400);
        }

        // Check if return already exists
        $existingReturn = ProductReturn::where('order_item_id', $orderItem->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingReturn) {
            return response()->json([
                'success' => false,
                'message' => 'A return request already exists for this item.',
            ], 400);
        }

        $return = $this->returnService->createReturnRequest(
            $orderItem,
            $validated['quantity'],
            $validated['reason'],
            $validated['reason_details'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully.',
            'data' => $return,
        ], 201);
    }

    /**
     * Get single return request.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $return = ProductReturn::whereHas('orderItem.order', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
            ->with(['orderItem.product', 'orderItem.order', 'refund'])
            ->find($id);

        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $return,
        ]);
    }
}
