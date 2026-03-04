<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Contracts\CatalogServiceInterface;
use App\Services\Contracts\VariationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected CatalogServiceInterface $catalogService,
        protected VariationServiceInterface $variationService
    ) {}

    /**
     * List all products.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category_id', 'brand_id', 'status',
            'min_price', 'max_price', 'in_stock',
            'is_featured', 'is_trending',
            'sort_by', 'sort_order', 'per_page'
        ]);
        $filters['include_inactive'] = true;

        $products = $this->catalogService->searchProducts($filters);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a new product.
     */
    public function store(Request $request): JsonResponse
        {
            // Convert string booleans from form data
            $this->convertBooleanFields($request);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'model_id' => 'nullable|exists:product_models,id',
                'shipping_class_id' => 'nullable|exists:shipping_classes,id',
                'sku' => 'nullable|string|max:100|unique:products,sku',
                'short_description' => 'nullable|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'compare_price' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'quantity' => 'nullable|integer|min:0',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'weight_unit' => 'nullable|in:g,kg,lb',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'is_featured' => 'nullable|boolean',
                'is_trending' => 'nullable|boolean',
                'status' => 'nullable|in:active,inactive,draft',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'meta_keywords' => 'nullable|string|max:255',
                'is_preorder' => 'nullable|boolean',
                'preorder_release_date' => 'nullable|date|after:now',
                'preorder_limit' => 'nullable|integer|min:1',
                'preorder_deposit_amount' => 'nullable|numeric|min:0',
                'preorder_deposit_type' => 'nullable|in:percentage,fixed',
                // Support both file uploads and path strings
                'images' => 'nullable|array|max:10',
                'images.*.file' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'images.*.path' => 'nullable|string|max:500',
                'images.*.alt_text' => 'nullable|string|max:255',
                'images.*.is_primary' => 'nullable|boolean',
                'images.*.sort_order' => 'nullable|integer|min:0',
                // Variations support
                'variations' => 'nullable|array',
                'variations.*.sku' => 'nullable|string|max:100|unique:product_variations,sku',
                'variations.*.price' => 'nullable|numeric|min:0',
                'variations.*.quantity' => 'nullable|integer|min:0',
                'variations.*.is_default' => 'nullable|boolean',
                'variations.*.variation_values' => 'nullable|array',
                'variations.*.variation_values.*' => 'exists:variation_options,id',
            ]);

            // Handle file uploads
            if (!empty($validated['images'])) {
                $validated['images'] = $this->processImageUploads($validated['images']);
            }

            $product = $this->catalogService->createProduct($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product->load(['images' => fn($q) => $q->ordered(), 'variations.variationValues.variationOption.variation']),
            ], 201);
        }


    /**
     * Get a specific product.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->catalogService->getProductWithVariations($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update a product.
     */
    public function update(Request $request, int $id): JsonResponse
        {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            // Convert string booleans from form data
            $this->convertBooleanFields($request);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'category_id' => 'sometimes|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'model_id' => 'nullable|exists:product_models,id',
                'shipping_class_id' => 'nullable|exists:shipping_classes,id',
                'short_description' => 'nullable|string',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'compare_price' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'quantity' => 'nullable|integer|min:0',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'weight_unit' => 'nullable|in:g,kg,lb',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'is_featured' => 'nullable|boolean',
                'is_trending' => 'nullable|boolean',
                'status' => 'nullable|in:active,inactive,draft',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'meta_keywords' => 'nullable|string|max:255',
                'is_preorder' => 'nullable|boolean',
                'preorder_release_date' => 'nullable|date',
                'preorder_limit' => 'nullable|integer|min:1',
                'preorder_deposit_amount' => 'nullable|numeric|min:0',
                'preorder_deposit_type' => 'nullable|in:percentage,fixed',
                // Support both file uploads and path strings
                'images' => 'nullable|array|max:10',
                'images.*.file' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
                'images.*.path' => 'nullable|string|max:500',
                'images.*.alt_text' => 'nullable|string|max:255',
                'images.*.is_primary' => 'nullable|boolean',
                'images.*.sort_order' => 'nullable|integer|min:0',
                // Variations support
                'variations' => 'nullable|array',
                'variations.*.id' => 'nullable|exists:product_variations,id',
                'variations.*.sku' => 'nullable|string|max:100',
                'variations.*.price' => 'nullable|numeric|min:0',
                'variations.*.quantity' => 'nullable|integer|min:0',
                'variations.*.is_default' => 'nullable|boolean',
                'variations.*.variation_values' => 'nullable|array',
                'variations.*.variation_values.*' => 'exists:variation_options,id',
                'variations.*._delete' => 'nullable|boolean',
            ]);

            // Handle file uploads
            if (!empty($validated['images'])) {
                $validated['images'] = $this->processImageUploads($validated['images']);
            }

            $product = $this->catalogService->updateProduct($product, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $product->load(['images' => fn($q) => $q->ordered(), 'variations.variationValues.variationOption.variation']),
            ]);
        }


    /**
     * Delete a product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $this->catalogService->deleteProduct($product);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Add variation to product.
     */
    public function addVariation(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'sku' => 'nullable|string|max:100|unique:product_variations,sku',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
            'variation_values' => 'nullable|array',
            'variation_values.*' => 'exists:variation_options,id',
        ]);

        $variation = $this->variationService->createVariation($product, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation added successfully.',
            'data' => $variation,
        ], 201);
    }

    /**
     * Update product variation.
     */
    public function updateVariation(Request $request, int $productId, int $variationId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $variation = $product->variations()->find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        $validated = $request->validate([
            'sku' => 'nullable|string|max:100|unique:product_variations,sku,' . $variationId,
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
            'reason' => 'nullable|string',
        ]);

        $variation = $this->variationService->updateVariation($variation, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation updated successfully.',
            'data' => $variation,
        ]);
    }

    /**
     * Delete product variation.
     */
    public function deleteVariation(int $productId, int $variationId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $variation = $product->variations()->find($variationId);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found.',
            ], 404);
        }

        $this->variationService->deleteVariation($variation);

        return response()->json([
            'success' => true,
            'message' => 'Variation deleted successfully.',
        ]);
    }

    /**
     * Convert string boolean values to actual booleans for form data.
     */
    private function convertBooleanFields(Request $request): void
    {
        $booleanFields = ['is_featured', 'is_trending', 'is_preorder'];
        
        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $request->merge([$field => $this->toBool($value)]);
            }
        }

        // Convert boolean fields in images array
        if ($request->has('images') && is_array($request->input('images'))) {
            $images = $request->input('images');
            foreach ($images as $index => $image) {
                if (isset($image['is_primary'])) {
                    $images[$index]['is_primary'] = $this->toBool($image['is_primary']);
                }
            }
            $request->merge(['images' => $images]);
        }
    }

    /**
     * Convert various boolean representations to actual boolean.
     */
    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (int)$value === 1;
        }
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'yes', 'on'], true);
        }
        
        return false;
    }

    /**
     * Process image uploads and return formatted array.
     */
    private function processImageUploads(array $images): array
    {
        $processedImages = [];

        foreach ($images as $index => $imageData) {
            // If file is uploaded, store it
            if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $imageData['file'];
                
                // Generate unique filename
                $filename = time() . '_' . $index . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in public/products directory
                $path = $file->storeAs('products', $filename, 'public');
                
                $processedImages[] = [
                    'path' => $path,
                    'alt_text' => $imageData['alt_text'] ?? null,
                    'is_primary' => $imageData['is_primary'] ?? false,
                    'sort_order' => $imageData['sort_order'] ?? $index,
                ];
            }
            // If path is provided (already uploaded), use it
            elseif (isset($imageData['path'])) {
                $processedImages[] = [
                    'path' => $imageData['path'],
                    'alt_text' => $imageData['alt_text'] ?? null,
                    'is_primary' => $imageData['is_primary'] ?? false,
                    'sort_order' => $imageData['sort_order'] ?? $index,
                ];
            }
        }

        return $processedImages;
    }

    /**
     * Add images to product.
     */
    public function addImages(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Convert string booleans from form data
        $this->convertBooleanFields($request);

        $validated = $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*.file' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'images.*.path' => 'nullable|string|max:500',
            'images.*.alt_text' => 'nullable|string|max:255',
            'images.*.is_primary' => 'nullable|boolean',
            'images.*.sort_order' => 'nullable|integer|min:0',
        ]);

        // Process file uploads
        $validated['images'] = $this->processImageUploads($validated['images']);

        $currentImageCount = $product->images()->count();
        $newImageCount = count($validated['images']);

        if (($currentImageCount + $newImageCount) > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add images. Maximum 10 images allowed per product.',
            ], 422);
        }

        $maxSortOrder = $product->images()->max('sort_order') ?? -1;

        foreach ($validated['images'] as $index => $image) {
            $product->images()->create([
                'image_path' => $image['path'],
                'alt_text' => $image['alt_text'] ?? null,
                'is_primary' => $image['is_primary'] ?? false,
                'sort_order' => $image['sort_order'] ?? ($maxSortOrder + $index + 1),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Images added successfully.',
            'data' => $product->load(['images' => fn($q) => $q->ordered()]),
        ]);
    }

    /**
     * Update a specific product image.
     */
    public function updateImage(Request $request, int $productId, int $imageId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $image = $product->images()->find($imageId);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }

        $validated = $request->validate([
            'path' => 'sometimes|string|max:500',
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // If setting as primary, unset other primary images
        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $product->images()->where('id', '!=', $imageId)->update(['is_primary' => false]);
        }

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully.',
            'data' => $image->fresh(),
        ]);
    }

    /**
     * Delete a specific product image.
     */
    public function deleteImage(int $productId, int $imageId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $image = $product->images()->find($imageId);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // If deleted image was primary, set first remaining image as primary
        if ($wasPrimary) {
            $firstImage = $product->images()->orderBy('sort_order', 'asc')->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }

    /**
     * Set primary image for product.
     */
    public function setPrimaryImage(int $productId, int $imageId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $image = $product->images()->find($imageId);

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }

        // Unset all primary images
        $product->images()->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary image set successfully.',
            'data' => $image->fresh(),
        ]);
    }

    /**
     * Reorder product images.
     */
    public function reorderImages(Request $request, int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|integer|exists:product_images,id',
        ]);

        foreach ($validated['image_ids'] as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Images reordered successfully.',
            'data' => $product->load(['images' => fn($q) => $q->ordered()]),
        ]);
    }
}
