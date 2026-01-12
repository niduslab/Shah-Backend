<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductModelController extends Controller
{
    /**
     * List all product models.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductModel::with('brand');

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $models = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $models,
        ]);
    }

    /**
     * Store a new product model.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
        ]);

        $model = ProductModel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product model created successfully.',
            'data' => $model->load('brand'),
        ], 201);
    }

    /**
     * Get a specific product model.
     */
    public function show(int $id): JsonResponse
    {
        $model = ProductModel::with('brand')->find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Product model not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $model,
        ]);
    }

    /**
     * Update a product model.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $model = ProductModel::find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Product model not found.',
            ], 404);
        }

        $validated = $request->validate([
            'brand_id' => 'sometimes|exists:brands,id',
            'name' => 'sometimes|string|max:255',
        ]);

        $model->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product model updated successfully.',
            'data' => $model->fresh('brand'),
        ]);
    }

    /**
     * Delete a product model.
     */
    public function destroy(int $id): JsonResponse
    {
        $model = ProductModel::find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Product model not found.',
            ], 404);
        }

        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product model deleted successfully.',
        ]);
    }
}
