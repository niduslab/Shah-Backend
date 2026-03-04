<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Variation;
use App\Models\VariationOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariationOptionController extends Controller
{
    /**
     * List all options for a variation
     */
    public function index(int $variationId): JsonResponse
    {
        $variation = Variation::find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        $options = $variation->options()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Create new option for a variation
     */
    public function store(Request $request, int $variationId): JsonResponse
    {
        $variation = Variation::find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['variation_id'] = $variationId;

        // Auto-set sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $variation->options()->max('sort_order') + 1;
        }

        $option = VariationOption::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Option created successfully.',
            'data' => $option,
        ], 201);
    }

    /**
     * Get single option
     */
    public function show(int $variationId, int $optionId): JsonResponse
    {
        $option = VariationOption::where('variation_id', $variationId)
            ->find($optionId);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $option,
        ]);
    }

    /**
     * Update option
     */
    public function update(Request $request, int $variationId, int $optionId): JsonResponse
    {
        $option = VariationOption::where('variation_id', $variationId)
            ->find($optionId);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found.',
            ], 404);
        }

        $validated = $request->validate([
            'value' => 'sometimes|string|max:255',
            'label' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $option->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Option updated successfully.',
            'data' => $option,
        ]);
    }

    /**
     * Delete option
     */
    public function destroy(int $variationId, int $optionId): JsonResponse
    {
        $option = VariationOption::where('variation_id', $variationId)
            ->find($optionId);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found.',
            ], 404);
        }

        // Check if option is used in any product variations
        if ($option->variationValues()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete option that is used in product variations.',
            ], 422);
        }

        $option->delete();

        return response()->json([
            'success' => true,
            'message' => 'Option deleted successfully.',
        ]);
    }

    /**
     * Bulk create options
     */
    public function bulkStore(Request $request, int $variationId): JsonResponse
    {
        $variation = Variation::find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        $validated = $request->validate([
            'options' => 'required|array|min:1',
            'options.*.value' => 'required|string|max:255',
            'options.*.label' => 'nullable|string|max:255',
            'options.*.sort_order' => 'nullable|integer|min:0',
            'options.*.is_active' => 'nullable|boolean',
        ]);

        $createdOptions = [];
        $maxSortOrder = $variation->options()->max('sort_order') ?? -1;

        foreach ($validated['options'] as $index => $optionData) {
            $optionData['variation_id'] = $variationId;
            $optionData['sort_order'] = $optionData['sort_order'] ?? ($maxSortOrder + $index + 1);
            
            $createdOptions[] = VariationOption::create($optionData);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdOptions) . ' options created successfully.',
            'data' => $createdOptions,
        ], 201);
    }
}
