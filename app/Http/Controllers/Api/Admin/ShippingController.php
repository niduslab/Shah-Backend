<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingClass;
use App\Models\ShippingRate;
use App\Models\WeightCostRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShippingController extends Controller
{
    /**
     * List all shipping rates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShippingRate::with(['shippingClass', 'weightCostRules.items']);

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $query->orderBy('method')->orderBy('name');

        $perPage = $request->get('per_page', 15);
        $rates = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    /**
     * Store a new shipping rate.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'method' => 'required|in:shah_sports_team,pathao_courier,standard',
            'shipping_class_id' => 'nullable|exists:shipping_classes,id',
            'base_cost' => 'required|numeric|min:0',
            'free_shipping_min_order' => 'nullable|numeric|min:0',
            'delivery_time' => 'nullable|string|max:100',
            'weight_pricing_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $rate = ShippingRate::create([
            'name' => $validated['name'],
            'method' => $validated['method'],
            'shipping_class_id' => $validated['shipping_class_id'] ?? null,
            'base_cost' => $validated['base_cost'],
            'free_shipping_min_order' => $validated['free_shipping_min_order'] ?? 0,
            'delivery_time' => $validated['delivery_time'] ?? null,
            'weight_pricing_enabled' => $validated['weight_pricing_enabled'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate created successfully.',
            'data' => $rate,
        ], 201);
    }

    /**
     * Get a specific shipping rate.
     */
    public function show(int $id): JsonResponse
    {
        $rate = ShippingRate::with(['shippingClass', 'weightCostRules.items'])->find($id);

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping rate not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rate,
        ]);
    }

    /**
     * Update a shipping rate.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rate = ShippingRate::find($id);

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping rate not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'method' => 'sometimes|in:shah_sports_team,pathao_courier,standard',
            'shipping_class_id' => 'nullable|exists:shipping_classes,id',
            'base_cost' => 'sometimes|numeric|min:0',
            'free_shipping_min_order' => 'sometimes|numeric|min:0',
            'delivery_time' => 'nullable|string|max:100',
            'weight_pricing_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $rate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate updated successfully.',
            'data' => $rate,
        ]);
    }

    /**
     * Delete a shipping rate.
     */
    public function destroy(int $id): JsonResponse
    {
        $rate = ShippingRate::find($id);

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping rate not found.',
            ], 404);
        }

        $rate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate deleted successfully.',
        ]);
    }

    /**
     * Get the default (non location-specific) weight cost rule for a shipping rate.
     */
    public function weightCostRule(int $id): JsonResponse
    {
        $rate = ShippingRate::find($id);

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping rate not found.',
            ], 404);
        }

        $rule = $rate->weightCostRules()
            ->whereNull('state')
            ->whereNull('city')
            ->with('items')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $rule,
        ]);
    }

    /**
     * Create or replace the default weight cost rule (and its tier items) for a shipping rate.
     */
    public function saveWeightCostRule(Request $request, int $id): JsonResponse
    {
        $rate = ShippingRate::find($id);

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping rate not found.',
            ], 404);
        }

        $validated = $request->validate([
            'weight_pricing_enabled' => 'required|boolean',
            'shipping_calculation_method' => 'required_if:weight_pricing_enabled,true|in:per_unit,rules',
            'per_unit_cost' => 'required_if:shipping_calculation_method,per_unit|nullable|numeric|min:0',
            'default_rule_cost' => 'nullable|numeric|min:0',
            'items' => 'required_if:shipping_calculation_method,rules|array',
            'items.*.weight' => 'required_with:items|numeric|min:0.01',
            'items.*.cost' => 'required_with:items|numeric|min:0',
        ]);

        $rate->update(['weight_pricing_enabled' => $validated['weight_pricing_enabled']]);

        $rule = $rate->weightCostRules()->whereNull('state')->whereNull('city')->first();

        if (!$validated['weight_pricing_enabled']) {
            return response()->json([
                'success' => true,
                'message' => 'Weight-based pricing disabled.',
                'data' => $rule?->load('items'),
            ]);
        }

        $rule = DB::transaction(function () use ($rate, $rule, $validated) {
            $rule = $rule ?: new WeightCostRule(['shipping_rate_id' => $rate->id]);

            $rule->fill([
                'shipping_calculation_method' => $validated['shipping_calculation_method'],
                'per_unit_cost' => $validated['shipping_calculation_method'] === 'per_unit'
                    ? ($validated['per_unit_cost'] ?? 0)
                    : null,
                'default_rule_cost' => $validated['default_rule_cost'] ?? 0,
            ]);
            $rule->save();

            if ($validated['shipping_calculation_method'] === 'rules') {
                $rule->items()->delete();

                foreach ($validated['items'] as $item) {
                    $rule->items()->create([
                        'weight' => $item['weight'],
                        'cost' => $item['cost'],
                    ]);
                }
            } else {
                $rule->items()->delete();
            }

            return $rule;
        });

        return response()->json([
            'success' => true,
            'message' => 'Weight-based pricing saved successfully.',
            'data' => $rule->load('items'),
        ]);
    }

    // Shipping Classes

    /**
     * List all shipping classes.
     */
    public function classes(): JsonResponse
    {
        $classes = ShippingClass::withCount('products')->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    /**
     * Store a new shipping class.
     */
    public function storeClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class = ShippingClass::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipping class created successfully.',
            'data' => $class,
        ], 201);
    }

    /**
     * Update a shipping class.
     */
    public function updateClass(Request $request, int $id): JsonResponse
    {
        $class = ShippingClass::find($id);

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping class not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $class->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Shipping class updated successfully.',
            'data' => $class,
        ]);
    }

    /**
     * Delete a shipping class.
     */
    public function destroyClass(int $id): JsonResponse
    {
        $class = ShippingClass::find($id);

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping class not found.',
            ], 404);
        }

        if ($class->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete shipping class with assigned products.',
            ], 400);
        }

        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping class deleted successfully.',
        ]);
    }
}
