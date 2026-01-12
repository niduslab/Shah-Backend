<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
     * List all return requests.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductReturn::with(['orderItem.order', 'orderItem.product', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('orderItem.order', fn($q) => 
                $q->where('order_number', 'like', "%{$search}%")
            );
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
        $returns = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $returns,
        ]);
    }

    /**
     * Get a specific return request.
     */
    public function show(int $id): JsonResponse
    {
        $return = ProductReturn::with([
            'orderItem.order', 'orderItem.product', 
            'orderItem.productVariation', 'user', 'refund'
        ])->find($id);

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

    /**
     * Approve a return request.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $return = ProductReturn::find($id);

        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found.',
            ], 404);
        }

        if ($return->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Return request is not pending.',
            ], 400);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $return = $this->returnService->approveReturn(
            $return,
            $validated['admin_notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Return request approved.',
            'data' => $return,
        ]);
    }

    /**
     * Reject a return request.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $return = ProductReturn::find($id);

        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found.',
            ], 404);
        }

        if ($return->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Return request is not pending.',
            ], 400);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $return = $this->returnService->rejectReturn(
            $return,
            $validated['rejection_reason']
        );

        return response()->json([
            'success' => true,
            'message' => 'Return request rejected.',
            'data' => $return,
        ]);
    }

    /**
     * Process a return (mark as received and restore inventory).
     */
    public function process(int $id): JsonResponse
    {
        $return = ProductReturn::find($id);

        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found.',
            ], 404);
        }

        if ($return->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Return request must be approved first.',
            ], 400);
        }

        $return = $this->returnService->processReturn($return);

        return response()->json([
            'success' => true,
            'message' => 'Return processed and inventory restored.',
            'data' => $return,
        ]);
    }
}
