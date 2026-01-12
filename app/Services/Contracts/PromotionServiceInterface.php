<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Collection;

interface PromotionServiceInterface
{
    /**
     * Get all active promotions.
     */
    public function getActivePromotions(): Collection;

    /**
     * Apply promotion to cart items.
     */
    public function applyPromotion(array $cartItems): array;

    /**
     * Get best promotion for a product (non-stacking).
     */
    public function getBestPromotion(Product $product): ?Promotion;

    /**
     * Calculate discount for a product.
     */
    public function calculateProductDiscount(Product $product, float $price): float;

    /**
     * Calculate cart-level discount.
     */
    public function calculateCartDiscount(array $cartItems, float $subtotal): float;

    /**
     * Check if promotion is valid now.
     */
    public function isPromotionValid(Promotion $promotion): bool;
}
