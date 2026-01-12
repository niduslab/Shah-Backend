<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    /**
     * List all promotions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::with(['products', 'brands', 'categories']);

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('ends_at', '<', now());
            } elseif ($request->status === 'scheduled') {
                $query->where('starts_at', '>', now());
            }
        }

        if ($request->filled('promotion_type')) {
            $query->where('promotion_type', $request->promotion_type);
        }

        $sortBy = $request->get('sort_by', 'priority');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $promotions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $promotions,
        ]);
    }

    /**
     * Store a new promotion.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'promotion_type' => 'required|in:percentage,fixed,free_shipping',
            'discount_value' => 'required_unless:promotion_type,free_shipping|numeric|min:0',
            'applies_to' => 'required|in:all,products,brands,categories',
            'apply_level' => 'nullable|in:item,order',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'priority' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $promotion = Promotion::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'promotion_type' => $validated['promotion_type'],
            'discount_value' => $validated['discount_value'] ?? 0,
            'applies_to' => $validated['applies_to'],
            'apply_level' => $validated['apply_level'] ?? 'item',
            'min_purchase_amount' => $validated['min_purchase_amount'] ?? null,
            'max_discount_amount' => $validated['max_discount_amount'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'priority' => $validated['priority'] ?? 10,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach relationships
        if (!empty($validated['product_ids'])) {
            $promotion->products()->attach($validated['product_ids']);
        }
        if (!empty($validated['brand_ids'])) {
            $promotion->brands()->attach($validated['brand_ids']);
        }
        if (!empty($validated['category_ids'])) {
            $promotion->categories()->attach($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Promotion created successfully.',
            'data' => $promotion->load(['products', 'brands', 'categories']),
        ], 201);
    }

    /**
     * Get a specific promotion.
     */
    public function show(int $id): JsonResponse
    {
        $promotion = Promotion::with(['products', 'brands', 'categories'])->find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $promotion,
        ]);
    }

    /**
     * Update a promotion.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'promotion_type' => 'sometimes|in:percentage,fixed,free_shipping',
            'discount_value' => 'nullable|numeric|min:0',
            'applies_to' => 'sometimes|in:all,products,brands,categories',
            'apply_level' => 'nullable|in:item,order',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'priority' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $promotion->update($validated);

        // Sync relationships
        if (isset($validated['product_ids'])) {
            $promotion->products()->sync($validated['product_ids']);
        }
        if (isset($validated['brand_ids'])) {
            $promotion->brands()->sync($validated['brand_ids']);
        }
        if (isset($validated['category_ids'])) {
            $promotion->categories()->sync($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Promotion updated successfully.',
            'data' => $promotion->load(['products', 'brands', 'categories']),
        ]);
    }

    /**
     * Delete a promotion.
     */
    public function destroy(int $id): JsonResponse
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found.',
            ], 404);
        }

        $promotion->products()->detach();
        $promotion->brands()->detach();
        $promotion->categories()->detach();
        $promotion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Promotion deleted successfully.',
        ]);
    }

    /**
     * Toggle promotion active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found.',
            ], 404);
        }

        $promotion->update(['is_active' => !$promotion->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Promotion status toggled successfully.',
            'data' => $promotion,
        ]);
    }
}
