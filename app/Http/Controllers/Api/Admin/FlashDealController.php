<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlashDealController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FlashDeal::with('products');

        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'ended':
                    $query->where('ends_at', '<', now());
                    break;
            }
        }

        $flashDeals = $query->orderBy('priority', 'desc')
            ->orderBy('starts_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $flashDeals,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.flash_price' => 'required|numeric|min:0',
            'products.*.quantity_limit' => 'nullable|integer|min:1',
        ]);

        $flashDeal = FlashDeal::create($validated);

        foreach ($validated['products'] as $productData) {
            $flashDeal->products()->attach($productData['product_id'], [
                'flash_price' => $productData['flash_price'],
                'quantity_limit' => $productData['quantity_limit'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Flash deal created successfully.',
            'data' => $flashDeal->load('products'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $flashDeal = FlashDeal::with('products')->find($id);

        if (!$flashDeal) {
            return response()->json([
                'success' => false,
                'message' => 'Flash deal not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $flashDeal,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $flashDeal = FlashDeal::find($id);

        if (!$flashDeal) {
            return response()->json([
                'success' => false,
                'message' => 'Flash deal not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'discount_type' => 'sometimes|in:percentage,fixed_amount',
            'discount_value' => 'sometimes|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'products' => 'sometimes|array|min:1',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.flash_price' => 'required_with:products|numeric|min:0',
            'products.*.quantity_limit' => 'nullable|integer|min:1',
        ]);

        $flashDeal->update($validated);

        if (isset($validated['products'])) {
            $flashDeal->products()->detach();
            foreach ($validated['products'] as $productData) {
                $flashDeal->products()->attach($productData['product_id'], [
                    'flash_price' => $productData['flash_price'],
                    'quantity_limit' => $productData['quantity_limit'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Flash deal updated successfully.',
            'data' => $flashDeal->fresh()->load('products'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $flashDeal = FlashDeal::find($id);

        if (!$flashDeal) {
            return response()->json([
                'success' => false,
                'message' => 'Flash deal not found.',
            ], 404);
        }

        $flashDeal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flash deal deleted successfully.',
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $flashDeal = FlashDeal::find($id);

        if (!$flashDeal) {
            return response()->json([
                'success' => false,
                'message' => 'Flash deal not found.',
            ], 404);
        }

        $flashDeal->update(['is_active' => !$flashDeal->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Flash deal status updated.',
            'data' => $flashDeal,
        ]);
    }

    public function statistics(int $id): JsonResponse
    {
        $flashDeal = FlashDeal::with('products')->find($id);

        if (!$flashDeal) {
            return response()->json([
                'success' => false,
                'message' => 'Flash deal not found.',
            ], 404);
        }

        $stats = [
            'total_products' => $flashDeal->products->count(),
            'total_quantity_sold' => $flashDeal->quantity_sold,
            'remaining_quantity' => $flashDeal->remaining_quantity,
            'time_remaining' => $flashDeal->time_remaining,
            'is_active' => $flashDeal->isActive(),
            'has_stock' => $flashDeal->hasStock(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
