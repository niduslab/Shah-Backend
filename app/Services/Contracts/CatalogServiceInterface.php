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
     * Get products by category, optionally filtered by brand, price, stock, and sort/pagination.
     */
    public function getProductsByCategory(int $categoryId, array $filters = [], bool $includeSubcategories = true): LengthAwarePaginator;

    /**
     * Get products by brand, optionally filtered by category, price, stock, and sort/pagination.
     */
    public function getProductsByBrand(int $brandId, array $filters = []): LengthAwarePaginator;

    /**
     * Get featured products.
     */
    public function getFeaturedProducts(int $limit = 10): Collection;

    /**
     * Get trending products.
     */
    public function getTrendingProducts(int $limit = 10): Collection;

    /**
     * Get product by slug with all relations.
     */
    public function getProductBySlug(string $slug): ?Product;

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool;
}
