<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Store a new flash deal.
     */
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
            // Products can be passed directly OR categories can be selected
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.flash_price' => 'nullable|numeric|min:0', // Optional if calculating from discount
            'products.*.quantity_limit' => 'nullable|integer|min:1',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if (empty($validated['products']) && empty($validated['categories'])) {
            return response()->json([
                'success' => false,
                'message' => 'You must select either products or categories.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $flashDeal = FlashDeal::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'max_discount_amount' => $validated['max_discount_amount'] ?? null,
                'quantity_limit' => $validated['quantity_limit'] ?? null,
                'per_user_limit' => $validated['per_user_limit'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'priority' => $validated['priority'] ?? 0,
            ]);

            $productsToAttach = [];

            // 1. Handle manually selected products
            if (!empty($validated['products'])) {
                foreach ($validated['products'] as $item) {
                    $productId = $item['product_id'];
                    // Use provided flash price or calculate it later
                    $productsToAttach[$productId] = [
                        'flash_price' => $item['flash_price'] ?? null,
                        'quantity_limit' => $item['quantity_limit'] ?? null,
                    ];
                }
            }

            // 2. Handle category-based product selection
            if (!empty($validated['categories'])) {
                $categoryProducts = Product::whereIn('category_id', $validated['categories'])->get();

                foreach ($categoryProducts as $product) {
                    // Avoid duplicates if product was also selected manually
                    if (!isset($productsToAttach[$product->id])) {
                        $productsToAttach[$product->id] = [
                            'flash_price' => null, // Will be calculated
                            'quantity_limit' => null,
                        ];
                    }
                }
            }

            // 3. Attach products and calculate prices if needed
            foreach ($productsToAttach as $productId => $data) {
                // If flash_price is not provided, calculate it based on product price and discount
                if (is_null($data['flash_price'])) {
                    $product = Product::find($productId);
                    if ($product) {
                        $price = $product->price;
                        $discount = 0;

                        if ($validated['discount_type'] === 'percentage') {
                            $discount = ($price * $validated['discount_value']) / 100;
                            if (isset($validated['max_discount_amount']) && $discount > $validated['max_discount_amount']) {
                                $discount = $validated['max_discount_amount'];
                            }
                        } else {
                            $discount = $validated['discount_value'];
                        }

                        $data['flash_price'] = max(0, $price - $discount);
                    } else {
                        continue; // Skip if product not found (shouldn't happen due to validation/query)
                    }
                }

                $flashDeal->products()->attach($productId, [
                    'flash_price' => $data['flash_price'],
                    'quantity_limit' => $data['quantity_limit'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Flash deal created successfully.',
                'data' => $flashDeal->load('products'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
