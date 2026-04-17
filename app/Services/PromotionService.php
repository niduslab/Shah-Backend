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
        
        // Calculate initial subtotal and collect product IDs for eligibility checks
        $initialSubtotal = 0;
        $cartProductIds = [];
        foreach ($cartItems as &$item) {
            if (!isset($item['price'])) {
                $product = Product::find($item['product_id']);
                $item['price'] = $product ? $product->price : 0;
            }
            $quantity = $item['quantity'] ?? 1;
            $initialSubtotal += $item['price'] * $quantity;
            $cartProductIds[] = $item['product_id'];
        }
        unset($item);

        // Filter promotions based on global constraints (min purchase, combo requirements)
        $eligiblePromotions = $activePromotions->filter(function ($promotion) use ($initialSubtotal, $cartProductIds) {
            // Check min purchase amount (Global check against initial subtotal)
            // Note: For product-level promotions, this implies the cart must meet this threshold to activate the per-product discount.
            if ($promotion->min_purchase_amount > 0 && $initialSubtotal < $promotion->min_purchase_amount) {
                return false;
            }

            // Check combo requirements if applicable
            if ($promotion->promotion_type === 'combo_offer') {
                // If the promotion specifies specific products, ALL of them must be present in the cart to activate the combo.
                // If it applies to brands/categories, we'd need more complex logic, but usually combo is specific products.
                if ($promotion->products->isNotEmpty()) {
                    $requiredIds = $promotion->products->pluck('id')->toArray();
                    // Check if all required IDs are present in cartProductIds
                    if (count(array_diff($requiredIds, $cartProductIds)) > 0) {
                        return false; // Missing required products for this combo
                    }
                }
            }
            
            return true;
        });

        $result = [
            'items' => [],
            'product_discounts' => 0,
            'cart_discount' => 0,
            'total_discount' => 0,
            'applied_promotion' => null,
        ];

        // Track how much discount has been applied per promotion to respect max_discount_amount
        $promotionDiscountsApplied = [];

        // First, apply product-level promotions
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            $price = $item['price'] ?? ($product ? $product->price : 0);
            $quantity = $item['quantity'] ?? 1;

            if (!$product) {
                $result['items'][] = $item;
                continue;
            }

            // Pass filtered promotions to get the best one for this product
            $bestPromotion = $this->getBestProductPromotion($product, $eligiblePromotions);
            $discount = 0;

            if ($bestPromotion) {
                // Calculate raw discount for 1 unit
                $unitDiscount = $this->calculateDiscountAmount($bestPromotion, $price);
                $totalItemDiscount = $unitDiscount * $quantity;
                
                // Enforce max_discount_amount across all items using this promotion
                if ($bestPromotion->max_discount_amount > 0) {
                    $alreadyApplied = $promotionDiscountsApplied[$bestPromotion->id] ?? 0;
                    $remainingAllowed = max(0, $bestPromotion->max_discount_amount - $alreadyApplied);
                    
                    if ($totalItemDiscount > $remainingAllowed) {
                        $totalItemDiscount = $remainingAllowed;
                        // Recalculate unit discount (might result in fractional values, so we use total)
                        $unitDiscount = $quantity > 0 ? $totalItemDiscount / $quantity : 0;
                    }
                    
                    $promotionDiscountsApplied[$bestPromotion->id] = $alreadyApplied + $totalItemDiscount;
                }

                $discount = $unitDiscount;
                $result['product_discounts'] += $totalItemDiscount;
            }

            $result['items'][] = array_merge($item, [
                'price' => $price,
                'discount' => $discount,
                'discounted_price' => max(0, $price - $discount),
                'promotion_id' => $bestPromotion?->id,
            ]);
        }

        // Then, check for cart-level promotions (only one applies - non-stacking)
        // We use the discounted subtotal as the basis for cart-level discounts
        $discountedSubtotal = collect($result['items'])->sum(fn($item) => $item['discounted_price'] * $item['quantity']);
        
        $cartPromotion = $this->getBestCartPromotion($eligiblePromotions, $discountedSubtotal);

        if ($cartPromotion) {
            $result['cart_discount'] = $this->calculateDiscountAmount($cartPromotion, $discountedSubtotal);
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
        // Note: This simple method doesn't account for complex combo logic requiring cart inspection.
        // It assumes standard promotions.
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

        // Return highest priority promotion
        // If priorities are equal, we could pick the one with higher discount, but priority field should govern this.
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

            // Exclude free_delivery from cart promotions as it doesn't give a monetary subtotal discount
            // It is handled separately by hasFreeDeliveryPromotion
            if ($promotion->promotion_type === 'free_delivery') {
                return false;
            }

            // Double check minimum purchase amount (though filtered earlier, subtotal might have changed if this is called independently)
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
                // Check if product has brand_id and it matches promotion brands
                return $product->brand_id && $promotion->brands->contains('id', $product->brand_id);

            case 'specific_categories':
                // Check if product has category_id and it matches promotion categories
                // Note: If product belongs to a subcategory, logic might need to be recursive, 
                // but usually the seeder attaches both parent and child categories to the promotion.
                return $product->category_id && $promotion->categories->contains('id', $product->category_id);

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
                // Treat flash_sale as percentage discount for consistency with FlashDeal model defaults
                // and typical ecommerce behavior.
                $discount = $amount * ($promotion->discount_value / 100);
                break;

            case 'combo_offer':
                // Combo offer is typically a fixed discount amount
                $discount = $promotion->discount_value;
                break;

            case 'free_delivery':
                // Free delivery is handled separately in shipping, doesn't reduce product/cart price directly
                $discount = 0;
                break;
        }

        // Apply max discount cap if set
        if ($promotion->max_discount_amount > 0 && $discount > $promotion->max_discount_amount) {
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
