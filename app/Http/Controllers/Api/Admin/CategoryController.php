<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $query->orderBy('sort_order')->orderBy('name');

        $perPage = $request->get('per_page', 15);
        $categories = $query->paginate($perPage);

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
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $uploadedPath = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $uploadedPath = $request->file('image')->store('storage/categories', 'public');
                $validated['image'] = $uploadedPath;
            }

            $validated['slug'] = $this->generateUniqueSlug($validated['name']);

            $category = Category::create($validated);

            DB::commit();

            // Return full URL for image
            $categoryData = $category->toArray();
            if ($category->image) {
                $categoryData['image'] = Storage::disk('public')->url($category->image);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $categoryData,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if DB transaction fails
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            throw $e;
        }
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
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
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

        $uploadedPath = null;
        $oldImage = $category->image;

        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $uploadedPath = $request->file('image')->store('storage/categories', 'public');
                $validated['image'] = $uploadedPath;
            }

            if (isset($validated['name']) && $validated['name'] !== $category->name) {
                $validated['slug'] = $this->generateUniqueSlug($validated['name'], $id);
            }

            $category->update($validated);

            DB::commit();

            // Delete old image if a new one was uploaded and update was successful
            if ($uploadedPath && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            // Return full URL for image
            $categoryData = $category->fresh()->toArray();
            if ($category->image) {
                $categoryData['image'] = Storage::disk('public')->url($category->image);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $categoryData,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if DB transaction fails
            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            throw $e;
        }
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

        // Delete category image if exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
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
