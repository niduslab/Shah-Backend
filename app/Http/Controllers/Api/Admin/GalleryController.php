<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Gallery::withCount('images');

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $galleries = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $galleries,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:product,banner,general',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'images' => 'nullable|array',
            'images.*.image_path' => 'required_with:images|string',
            'images.*.title' => 'nullable|string',
            'images.*.description' => 'nullable|string',
            'images.*.alt_text' => 'nullable|string',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_featured' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['title']);
        
        $gallery = Gallery::create($validated);

        if (isset($validated['images'])) {
            foreach ($validated['images'] as $imageData) {
                $gallery->images()->create($imageData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Gallery created successfully.',
            'data' => $gallery->load('images'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $gallery = Gallery::with('images')->find($id);

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $gallery,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:product,banner,general',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (isset($validated['title']) && $validated['title'] !== $gallery->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $id);
        }

        $gallery->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gallery updated successfully.',
            'data' => $gallery->fresh()->load('images'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        $gallery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery deleted successfully.',
        ]);
    }

    public function addImage(Request $request, int $id): JsonResponse
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        $validated = $request->validate([
            'image_path' => 'required|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'alt_text' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
        ]);

        $image = $gallery->images()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image added to gallery.',
            'data' => $image,
        ], 201);
    }

    public function updateImage(Request $request, int $galleryId, int $imageId): JsonResponse
    {
        $gallery = Gallery::find($galleryId);

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        $image = $gallery->images()->find($imageId);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }

        $validated = $request->validate([
            'image_path' => 'sometimes|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'alt_text' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
        ]);

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully.',
            'data' => $image,
        ]);
    }

    public function deleteImage(int $galleryId, int $imageId): JsonResponse
    {
        $gallery = Gallery::find($galleryId);

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        $image = $gallery->images()->find($imageId);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }

    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Gallery::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
