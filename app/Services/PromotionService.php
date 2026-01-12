<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;
use App\Services\Contracts\PromotionServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PromotionService implements PromotionServiceInterface
{
    /**
     * Get all active promotions.
     * 
     * @return Collection
     */
    public function getActivePromotions(): Collection
    {
        $now = Carbon::now();

        return Promotion::where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->with(['products', 'brands', 'categories'])
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Apply promotion to cart items.
     * Returns cart items with applied discounts.
     * 
     * @param array $cartItems Array of items with product_id, price, quantity
     * @return array
     */
    public function applyPromotion(array $cartItems): array
    {
        $activePromotions = $this->getActivePromotions();
        $result = [
            'items' => [],
            'product_discounts' => 0,
            'cart_discount' => 0,
            'total_discount' => 0,
            'applied_promotion' => null,
        ];

        // First, apply product-level promotions
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                $result['items'][] = $item;
                continue;
            }

            $bestPromotion = $this->getBestProductPromotion($product, $activePromotions);
            $discount = 0;

            if ($bestPromotion && $bestPromotion->apply_level === 'product') {
                $discount = $this->calculateDiscountAmount($bestPromotion, $item['price']);
                $result['product_discounts'] += $discount * $item['quantity'];
            }

            $result['items'][] = array_merge($item, [
                'discount' => $discount,
                'discounted_price' => $item['price'] - $discount,
                'promotion_id' => $bestPromotion?->id,
            ]);
        }

        // Then, check for cart-level promotions (only one applies - non-stacking)
        $subtotal = collect($result['items'])->sum(fn($item) => $item['discounted_price'] * $item['quantity']);
        $cartPromotion = $this->getBestCartPromotion($activePromotions, $subtotal);

        if ($cartPromotion) {
            $result['cart_discount'] = $this->calculateDiscountAmount($cartPromotion, $subtotal);
            $result['applied_promotion'] = $cartPromotion;
        }

        $result['total_discount'] = $result['product_discounts'] + $result['cart_discount'];

        return $result;
    }

    /**
     * Get best promotion for a product (non-stacking).
     * 
     * @param Product $product
     * @return Promotion|null
     */
    public function getBestPromotion(Product $product): ?Promotion
    {
        $activePromotions = $this->getActivePromotions();
        return $this->getBestProductPromotion($product, $activePromotions);
    }

    /**
     * Calculate discount for a product.
     * 
     * @param Product $product
     * @param float $price
     * @return float
     */
    public function calculateProductDiscount(Product $product, float $price): float
    {
        $promotion = $this->getBestPromotion($product);

        if (!$promotion) {
            return 0;
        }

        return $this->calculateDiscountAmount($promotion, $price);
    }

    /**
     * Calculate cart-level discount.
     * 
     * @param array $cartItems
     * @param float $subtotal
     * @return float
     */
    public function calculateCartDiscount(array $cartItems, float $subtotal): float
    {
        $activePromotions = $this->getActivePromotions();
        $cartPromotion = $this->getBestCartPromotion($activePromotions, $subtotal);

        if (!$cartPromotion) {
            return 0;
        }

        return $this->calculateDiscountAmount($cartPromotion, $subtotal);
    }

    /**
     * Check if promotion is valid now.
     * 
     * @param Promotion $promotion
     * @return bool
     */
    public function isPromotionValid(Promotion $promotion): bool
    {
        if (!$promotion->is_active) {
            return false;
        }

        $now = Carbon::now();

        return $now->between($promotion->starts_at, $promotion->ends_at);
    }

    /**
     * Get best product-level promotion for a product.
     * 
     * @param Product $product
     * @param Collection $promotions
     * @return Promotion|null
     */
    protected function getBestProductPromotion(Product $product, Collection $promotions): ?Promotion
    {
        $applicablePromotions = $promotions->filter(function ($promotion) use ($product) {
            if ($promotion->apply_level !== 'product') {
                return false;
            }

            return $this->isPromotionApplicableToProduct($promotion, $product);
        });

        if ($applicablePromotions->isEmpty()) {
            return null;
        }

        // Return highest priority promotion (or best value if same priority)
        return $applicablePromotions->sortByDesc('priority')->first();
    }

    /**
     * Get best cart-level promotion.
     * 
     * @param Collection $promotions
     * @param float $subtotal
     * @return Promotion|null
     */
    protected function getBestCartPromotion(Collection $promotions, float $subtotal): ?Promotion
    {
        $cartPromotions = $promotions->filter(function ($promotion) use ($subtotal) {
            if ($promotion->apply_level !== 'cart') {
                return false;
            }

            // Check minimum purchase amount
            if ($promotion->min_purchase_amount > 0 && $subtotal < $promotion->min_purchase_amount) {
                return false;
            }

            return true;
        });

        if ($cartPromotions->isEmpty()) {
            return null;
        }

        // Return highest priority promotion
        return $cartPromotions->sortByDesc('priority')->first();
    }

    /**
     * Check if promotion applies to a product.
     * 
     * @param Promotion $promotion
     * @param Product $product
     * @return bool
     */
    protected function isPromotionApplicableToProduct(Promotion $promotion, Product $product): bool
    {
        switch ($promotion->applies_to) {
            case 'all_products':
                return true;

            case 'specific_products':
                return $promotion->products->contains('id', $product->id);

            case 'specific_brands':
                return $product->brand_id && $promotion->brands->contains('id', $product->brand_id);

            case 'specific_categories':
                return $promotion->categories->contains('id', $product->category_id);

            default:
                return false;
        }
    }

    /**
     * Calculate discount amount based on promotion type.
     * 
     * @param Promotion $promotion
     * @param float $amount
     * @return float
     */
    protected function calculateDiscountAmount(Promotion $promotion, float $amount): float
    {
        $discount = 0;

        switch ($promotion->promotion_type) {
            case 'percentage':
                $discount = $amount * ($promotion->discount_value / 100);
                break;

            case 'fixed_amount':
                $discount = min($promotion->discount_value, $amount);
                break;

            case 'flash_sale':
                $discount = $amount * ($promotion->discount_value / 100);
                break;

            case 'combo_offer':
                $discount = $promotion->discount_value;
                break;

            case 'free_delivery':
                // Free delivery is handled separately in shipping
                $discount = 0;
                break;
        }

        // Apply max discount cap if set
        if ($promotion->max_discount_amount && $discount > $promotion->max_discount_amount) {
            $discount = $promotion->max_discount_amount;
        }

        return round($discount, 2);
    }

    /**
     * Check if free delivery promotion applies.
     * 
     * @param float $subtotal
     * @return bool
     */
    public function hasFreeDeliveryPromotion(float $subtotal): bool
    {
        $activePromotions = $this->getActivePromotions();

        return $activePromotions->contains(function ($promotion) use ($subtotal) {
            if ($promotion->promotion_type !== 'free_delivery') {
                return false;
            }

            if ($promotion->min_purchase_amount > 0 && $subtotal < $promotion->min_purchase_amount) {
                return false;
            }

            return true;
        });
    }
}
