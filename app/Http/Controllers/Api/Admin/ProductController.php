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
            'images' => 'nullable|array',
            'images.*.path' => 'required_with:images|string',
            'images.*.alt_text' => 'nullable|string',
            'images.*.is_primary' => 'nullable|boolean',
        ]);

        $product = $this->catalogService->createProduct($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
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
            'images' => 'nullable|array',
        ]);

        $product = $this->catalogService->updateProduct($product, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product,
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
}
