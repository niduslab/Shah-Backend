<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * List all brands.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::withCount('products');

        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Store a new brand.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        $brand = Brand::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully.',
            'data' => $brand,
        ], 201);
    }

    /**
     * Get a specific brand.
     */
    public function show(int $id): JsonResponse
    {
        $brand = Brand::with('models')->withCount('products')->find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $brand,
        ]);
    }

    /**
     * Update a brand.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'logo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $brand->name) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $id);
        }

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully.',
            'data' => $brand->fresh(),
        ]);
    }

    /**
     * Delete a brand.
     */
    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);
        }

        if ($brand->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete brand with products.',
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully.',
        ]);
    }

    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Brand::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
