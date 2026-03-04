<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingClass;
use App\Models\ShippingRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShippingController extends Controller
{
    /**
     * List all shipping rates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShippingRate::with('shippingClass');

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
            'method' => 'required|in:shah_sports_team,pathao_courier',
            'shipping_class_id' => 'nullable|exists:shipping_classes,id',
            'zone' => 'nullable|string|max:100',
            'base_cost' => 'required|numeric|min:0',
            'per_kg_cost' => 'nullable|numeric|min:0',
            'min_weight' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rate = ShippingRate::create([
            'name' => $validated['name'],
            'method' => $validated['method'],
            'shipping_class_id' => $validated['shipping_class_id'] ?? null,
            'zone' => $validated['zone'] ?? null,
            'base_cost' => $validated['base_cost'],
            'per_kg_cost' => $validated['per_kg_cost'] ?? 0,
            'min_weight' => $validated['min_weight'] ?? null,
            'max_weight' => $validated['max_weight'] ?? null,
            'free_shipping_threshold' => $validated['free_shipping_threshold'] ?? null,
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
        $rate = ShippingRate::with('shippingClass')->find($id);

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
            'method' => 'sometimes|in:shah_sports_team,pathao_courier',
            'shipping_class_id' => 'nullable|exists:shipping_classes,id',
            'zone' => 'nullable|string|max:100',
            'base_cost' => 'sometimes|numeric|min:0',
            'per_kg_cost' => 'nullable|numeric|min:0',
            'min_weight' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
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
