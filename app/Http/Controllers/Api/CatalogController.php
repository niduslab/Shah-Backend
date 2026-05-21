<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Contracts\CatalogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogServiceInterface $catalogService
    ) {}

    /**
     * Get all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->whereNull('parent_id')
            ->with([
                'children' => fn($q) => $q->active()->orderBy('sort_order'),
                'children.children' => fn($q) => $q->active()->orderBy('sort_order'),
                'children.children.children' => fn($q) => $q->active()->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get category with products.
     */
    public function category(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->with(['children' => fn($q) => $q->active()])
            ->first();

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
     * Get all brands.
     */
    public function brands(Request $request): JsonResponse
    {
        $brands = Brand::active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Search and filter products.
     */
    public function products(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category_id', 'category_slug', 'brand_id', 'brand_slug',
            'min_price', 'max_price', 'in_stock',
            'is_featured', 'is_trending', 'is_preorder',
            'flash_deal_id', 'has_flash_deal',
            'promotion_id', 'has_promotion',
            'coupon_id', 'has_coupon',
            'has_discount',
            'sort_by', 'sort_order', 'per_page'
        ]);

        $products = $this->catalogService->searchProducts($filters);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    //  public function products(Request $request): JsonResponse
    // {
    //     $filters = $request->only([
    //         'search', 'category_id', 'brand_id',
    //         'min_price', 'max_price', 'in_stock',
    //         'is_featured', 'is_trending', 'is_preorder',
    //         'sort_by', 'sort_order', 'per_page'
    //     ]);

    //     $products = $this->catalogService->searchProducts($filters);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $products,
    //     ]);
    // }

    /**
     * Get single product.
     */
    public function product(string $slug): JsonResponse
    {
        $product = $this->catalogService->getProductBySlug($slug);

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
     * Get products by category.
     */
    public function productsByCategory(Request $request, string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->active()->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $filters = $request->only([
            'brand_id', 'min_price', 'max_price', 'in_stock',
            'sort_by', 'sort_order', 'per_page'
        ]);

        $products = $this->catalogService->getProductsByCategory($category->id, $filters);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'products' => $products,
            ],
        ]);
    }

    /**
     * Get products by brand.
     */
    public function productsByBrand(Request $request, string $slug): JsonResponse
    {
        $brand = Brand::where('slug', $slug)->active()->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found.',
            ], 404);
        }

        $filters = $request->only([
            'category_id', 'min_price', 'max_price', 'in_stock',
            'sort_by', 'sort_order', 'per_page'
        ]);

        $products = $this->catalogService->getProductsByBrand($brand->id, $filters);

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $brand,
                'products' => $products,
            ],
        ]);
    }

    /**
     * Get featured products.
     */
    public function featured(): JsonResponse
    {
        $products = $this->catalogService->searchProducts([
            'is_featured' => true,
            'per_page' => 12,
        ]);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get trending products.
     */
    public function trending(): JsonResponse
    {
        $products = $this->catalogService->searchProducts([
            'is_trending' => true,
            'per_page' => 12,
        ]);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
