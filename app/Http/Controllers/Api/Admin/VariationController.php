<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariationController extends Controller
{
    /**
     * List all variations (Size, Color, etc.)
     */
    public function index(): JsonResponse
    {
        $variations = Variation::with('options')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $variations,
        ]);
    }

    /**
     * Create new variation type (e.g., Size, Color)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:variations,name',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $variation = Variation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation type created successfully.',
            'data' => $variation,
        ], 201);
    }

    /**
     * Get single variation with options
     */
    public function show(int $id): JsonResponse
    {
        $variation = Variation::with('options')->find($id);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $variation,
        ]);
    }

    /**
     * Update variation type
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $variation = Variation::find($id);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:variations,name,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $variation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation updated successfully.',
            'data' => $variation,
        ]);
    }

    /**
     * Delete variation type
     */
    public function destroy(int $id): JsonResponse
    {
        $variation = Variation::find($id);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        // Check if variation has options
        if ($variation->options()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete variation with existing options. Delete options first.',
            ], 422);
        }

        $variation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variation deleted successfully.',
        ]);
    }
}
