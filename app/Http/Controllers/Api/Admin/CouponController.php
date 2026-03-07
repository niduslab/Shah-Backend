<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * List all coupons.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::with(['products', 'brands', 'categories'])
            ->withCount('usages');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $coupons = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    /**
     * Store a new coupon.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed_amount,free_shipping',
            'discount_value' => 'required_unless:discount_type,free_shipping|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'once_per_customer' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        // Determine applies_to based on what IDs are provided
        $appliesTo = $this->determineAppliesTo(
            !empty($validated['product_ids']),
            !empty($validated['brand_ids']),
            !empty($validated['category_ids'])
        );

        $code = $validated['code'] ?? strtoupper(Str::random(8));

        $coupon = Coupon::create([
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? 0,
            'applies_to' => $appliesTo,
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'max_discount_amount' => $validated['max_discount_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'once_per_customer' => $validated['once_per_customer'] ?? true,
            'starts_at' => $validated['starts_at'] ?? now(),
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['product_ids'])) {
            $coupon->products()->attach($validated['product_ids']);
        }
        if (!empty($validated['brand_ids'])) {
            $coupon->brands()->attach($validated['brand_ids']);
        }
        if (!empty($validated['category_ids'])) {
            $coupon->categories()->attach($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully.',
            'data' => $coupon->load(['products', 'brands', 'categories']),
        ], 201);
    }

    /**
     * Determine applies_to value based on provided IDs.
     * Returns values matching the database enum: all_products, specific_products, specific_brands, specific_categories
     */
    private function determineAppliesTo(bool $hasProducts, bool $hasBrands, bool $hasCategories): string
    {
        // Priority: products > brands > categories
        // If multiple types are provided, use the most specific one
        if ($hasProducts) {
            return 'specific_products';
        }
        if ($hasBrands) {
            return 'specific_brands';
        }
        if ($hasCategories) {
            return 'specific_categories';
        }

        // If no specific items selected, applies to all
        return 'all_products';
    }

    /**
     * Get a specific coupon.
     */
    public function show(int $id): JsonResponse
    {
        $coupon = Coupon::with(['products', 'brands', 'categories', 'usages.order'])
            ->withCount('usages')
            ->find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $coupon,
        ]);
    }

    /**
     * Update a coupon.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|in:percentage,fixed_amount,free_shipping',
            'discount_value' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'once_per_customer' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'brand_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
        ]);

        // Update applies_to if any IDs are being updated
        if (isset($validated['product_ids']) || isset($validated['brand_ids']) || isset($validated['category_ids'])) {
            $hasProducts = isset($validated['product_ids']) ? !empty($validated['product_ids']) : $coupon->products()->exists();
            $hasBrands = isset($validated['brand_ids']) ? !empty($validated['brand_ids']) : $coupon->brands()->exists();
            $hasCategories = isset($validated['category_ids']) ? !empty($validated['category_ids']) : $coupon->categories()->exists();
            
            $validated['applies_to'] = $this->determineAppliesTo($hasProducts, $hasBrands, $hasCategories);
        }

        $coupon->update($validated);

        if (isset($validated['product_ids'])) {
            $coupon->products()->sync($validated['product_ids']);
        }
        if (isset($validated['brand_ids'])) {
            $coupon->brands()->sync($validated['brand_ids']);
        }
        if (isset($validated['category_ids'])) {
            $coupon->categories()->sync($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully.',
            'data' => $coupon->load(['products', 'brands', 'categories']),
        ]);
    }

    /**
     * Delete a coupon.
     */
    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        }

        $coupon->products()->detach();
        $coupon->brands()->detach();
        $coupon->categories()->detach();
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully.',
        ]);
    }

    /**
     * Get coupon usage history.
     */
    public function usageHistory(int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        }

        $usages = $coupon->usages()
            ->with(['order', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $usages,
        ]);
    }
}
