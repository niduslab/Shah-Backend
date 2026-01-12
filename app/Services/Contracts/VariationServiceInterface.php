<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Models\ProductVariation;

interface VariationServiceInterface
{
    /**
     * Create a new variation for a product.
     */
    public function createVariation(Product $product, array $options): ProductVariation;

    /**
     * Update variation stock.
     */
    public function updateStock(ProductVariation $variation, int $quantity, string $reason): void;

    /**
     * Get available variation options for a product.
     */
    public function getAvailableOptions(Product $product): array;

    /**
     * Update variation details.
     */
    public function updateVariation(ProductVariation $variation, array $data): ProductVariation;

    /**
     * Delete a variation.
     */
    public function deleteVariation(ProductVariation $variation): bool;

    /**
     * Find variation by attributes.
     */
    public function findVariationByAttributes(Product $product, array $attributes): ?ProductVariation;
}
