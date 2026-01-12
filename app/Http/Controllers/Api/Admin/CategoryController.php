<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * List all categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('children');

        if ($request->has('parent_only') && $request->parent_only) {
            $query->whereNull('parent_id');
        }

        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get category tree structure.
     */
    public function tree(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->with('children.children')
            ->active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Generate slug
        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    /**
     * Get a specific category.
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'products'])->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Update a category.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Category cannot be its own parent.',
            ], 422);
        }

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $id);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with products. Please reassign products first.',
            ], 422);
        }

        // Move children to parent
        if ($category->children()->count() > 0) {
            $category->children()->update(['parent_id' => $category->parent_id]);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    /**
     * Generate unique slug.
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Category::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
