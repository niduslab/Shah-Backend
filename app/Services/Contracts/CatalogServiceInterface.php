<?php

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CatalogServiceInterface
{
    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product;

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product;

    /**
     * Get product with all variations loaded.
     */
    public function getProductWithVariations(int $productId): ?Product;

    /**
     * Search products with filters.
     */
    public function searchProducts(array $filters): LengthAwarePaginator;

    /**
     * Get products by category.
     */
    public function getProductsByCategory(int $categoryId, bool $includeSubcategories = true): LengthAwarePaginator;

    /**
     * Get products by brand.
     */
    public function getProductsByBrand(int $brandId): LengthAwarePaginator;

    /**
     * Get featured products.
     */
    public function getFeaturedProducts(int $limit = 10): Collection;

    /**
     * Get trending products.
     */
    public function getTrendingProducts(int $limit = 10): Collection;

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool;
}
