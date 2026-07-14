<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShippingRate;
use App\Models\WeightCostRule;
use App\Services\Contracts\ShippingServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippingService implements ShippingServiceInterface
{
    /**
     * Available shipping methods.
     */
    public const METHOD_SHAH_SPORTS_TEAM = 'shah_sports_team';
    public const METHOD_PATHAO_COURIER = 'pathao_courier';
    public const METHOD_STANDARD = 'standard';

    /**
     * Weight thresholds for method recommendations.
     */
    private const HEAVY_WEIGHT_THRESHOLD = 20; // kg
    private const LARGE_DIMENSION_THRESHOLD = 100; // cm
    private const LARGE_VOLUME_THRESHOLD = 0.5; // cubic meters

    /**
     * Calculate shipping cost for cart items.
     * 
     * @param array $items Array of items with product_id, variation_id, quantity
     * @param Address|null $address
     * @param float $subtotal For free shipping calculation
     * @return array Contains costs for each available method
     */
    public function calculateShippingCost(array $items, ?Address $address = null, float $subtotal = 0): array
    {
        $enrichedItems = $this->enrichItemsWithProductData($items);
        
        // Separate items by shipping requirements
        $shippingGroups = $this->groupItemsByShipping($enrichedItems);
        
        // If all items don't require shipping
        if (empty($shippingGroups['requires_shipping'])) {
            return [];
        }
        
        // Calculate custom shipping costs
        $customShippingCost = $this->calculateCustomShippingCost($shippingGroups['custom_shipping']);
        
        // If all items have custom shipping, return only custom cost
        if (empty($shippingGroups['default_shipping'])) {
            return [
                'custom' => [
                    'code' => 'custom',
                    'name' => 'Custom Shipping',
                    'description' => 'Product-specific shipping rates',
                    'cost' => round($customShippingCost, 2),
                    'delivery_time' => null,
                    'free_shipping_min_order' => 0,
                    'is_free' => $customShippingCost == 0,
                ],
            ];
        }
        
        // Calculate standard shipping for default items
        $totalWeight = $this->calculateTotalWeight($shippingGroups['default_shipping']);
        $availableMethods = $this->getAvailableMethods($shippingGroups['default_shipping'], $address);
        $costs = [];

        foreach ($availableMethods as $method) {
            $rate = $this->getApplicableRate($method['code'], $address, $shippingGroups['default_shipping']);
            
            if ($rate) {
                // Check free shipping
                if ($rate->free_shipping_min_order > 0 && $subtotal >= $rate->free_shipping_min_order) {
                    $cost = 0;
                } else {
                    $cost = $this->calculateCostFromRate($rate, $totalWeight, $shippingGroups['default_shipping'], $address);
                }

                // Add custom shipping cost to total
                $totalCost = $cost + $customShippingCost;

                $costs[$method['code']] = [
                    'code' => $method['code'],
                    'name' => $method['name'],
                    'description' => $method['description'],
                    'cost' => round($totalCost, 2),
                    'base_shipping_cost' => round($cost, 2),
                    'custom_shipping_cost' => round($customShippingCost, 2),
                    'delivery_time' => $rate->delivery_time,
                    'free_shipping_min_order' => $rate->free_shipping_min_order,
                    'is_free' => $totalCost == 0,
                ];
            }
        }

        return $costs;
    }

    /**
     * Get available shipping methods for items and address.
     * 
     * @param array $items
     * @param Address|null $address
     * @return array
     */
    public function getAvailableMethods(array $items = [], ?Address $address = null): array
    {
        $methods = [];
        $totalWeight = $this->calculateTotalWeight($items);
        $hasLargeItems = $this->hasLargeItems($items);

        // Standard Shipping - always available when an active rate exists
        $standardRate = ShippingRate::where('method', self::METHOD_STANDARD)
            ->where('is_active', true)
            ->first();

        if ($standardRate) {
            $methods[] = [
                'code' => self::METHOD_STANDARD,
                'name' => $standardRate->name,
                'description' => $this->describeRate($standardRate, 'Standard delivery'),
                'recommended' => false,
            ];
        }

        // Shah Sports Team - available for heavy/large items and local delivery
        $shahSportsRate = ShippingRate::where('method', self::METHOD_SHAH_SPORTS_TEAM)
            ->where('is_active', true)
            ->first();

        if ($shahSportsRate) {
            $methods[] = [
                'code' => self::METHOD_SHAH_SPORTS_TEAM,
                'name' => 'Shah Sports Team Delivery',
                'description' => $this->describeRate($shahSportsRate, 'Our own delivery team for heavy and large items'),
                'recommended' => $totalWeight > self::HEAVY_WEIGHT_THRESHOLD || $hasLargeItems,
            ];
        }

        // Pathao Courier - available for standard items
        $pathaoRate = ShippingRate::where('method', self::METHOD_PATHAO_COURIER)
            ->where('is_active', true)
            ->first();

        if ($pathaoRate) {
            $methods[] = [
                'code' => self::METHOD_PATHAO_COURIER,
                'name' => 'Pathao Courier',
                'description' => $this->describeRate($pathaoRate, 'Fast and reliable courier service for standard deliveries'),
                'recommended' => $totalWeight <= self::HEAVY_WEIGHT_THRESHOLD && !$hasLargeItems,
            ];
        }

        return $methods;
    }

    /**
     * Build a method description from its rate. Only mentions free shipping when
     * the rate actually has a threshold configured.
     *
     * @param ShippingRate $rate
     * @param string $fallback
     * @return string
     */
    protected function describeRate(ShippingRate $rate, string $fallback): string
    {
        if ($rate->free_shipping_min_order > 0) {
            return 'Free shipping on orders over ' . number_format((float) $rate->free_shipping_min_order, 0) . ' BDT';
        }

        return $fallback;
    }

    /**
     * Assign tracking number to an order.
     *
     * @param Order $order
     * @param string $trackingNumber
     * @param string|null $carrier
     * @param string|null $carrierUrl
     * @return void
     */
    public function assignTrackingNumber(Order $order, string $trackingNumber, ?string $carrier = null, ?string $carrierUrl = null): void
    {
        $order->update([
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'carrier_url' => $carrierUrl,
            'status' => 'shipped',
        ]);
    }

    /**
     * Get shipping rates for a location.
     * 
     * @param string $city
     * @param string|null $state
     * @return Collection
     */
    public function getShippingRates(string $city, ?string $state = null): Collection
    {
        return ShippingRate::where('is_active', true)
            ->with('shippingClass')
            ->get()
            ->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'method' => $rate->method,
                    'base_cost' => $rate->base_cost,
                    'delivery_time' => $rate->delivery_time,
                    'free_shipping_min_order' => $rate->free_shipping_min_order,
                    'shipping_class' => $rate->shippingClass?->name,
                ];
            });
    }

    /**
     * Check if free shipping applies.
     * 
     * @param float $subtotal
     * @param string $method
     * @return bool
     */
    public function checkFreeShipping(float $subtotal, string $method): bool
    {
        $rate = ShippingRate::where('method', $method)
            ->where('is_active', true)
            ->first();

        if (!$rate) {
            return false;
        }

        return $rate->free_shipping_min_order > 0 && $subtotal >= $rate->free_shipping_min_order;
    }

    /**
     * Recommend shipping method based on items.
     * 
     * @param array $items
     * @return string
     */
    public function recommendShippingMethod(array $items): string
    {
        $totalWeight = $this->calculateTotalWeight($items);
        $hasLargeItems = $this->hasLargeItems($items);

        // Shah Sports Team for heavy (>20kg) or large items
        if ($totalWeight > 20 || $hasLargeItems) {
            return self::METHOD_SHAH_SPORTS_TEAM;
        }

        // Default to Pathao for regular items
        return self::METHOD_PATHAO_COURIER;
    }

    /**
     * Enrich items with product data.
     * 
     * @param array $items
     * @return array
     */
    protected function enrichItemsWithProductData(array $items): array
    {
        return collect($items)->map(function ($item) {
            $productId = $item['product_id'];
            $variationId = $item['variation_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;

            // Get product
            $product = Product::with(['shippingClass'])->find($productId);
            
            if (!$product) {
                return $item;
            }

            // Get variation if specified
            $variation = null;
            if ($variationId) {
                $variation = ProductVariation::find($variationId);
            }

            // Use variation weight if available, otherwise product weight
            $weight = $variation->weight ?? $product->weight ?? 0;
            $weightUnit = $variation->weight_unit ?? $product->weight_unit ?? 'kg';

            // Determine shipping configuration
            $shippingType = 'default';
            $shippingCost = null;
            $requiresShipping = $product->requires_shipping ?? true;

            if ($variation && $variation->shipping_type && $variation->shipping_type !== 'inherit') {
                $shippingType = $variation->shipping_type;
                $shippingCost = $variation->shipping_cost;
            } elseif ($product->shipping_type && $product->shipping_type !== 'default') {
                $shippingType = $product->shipping_type;
                $shippingCost = $product->shipping_cost;
            }

            return array_merge($item, [
                'weight' => $weight,
                'weight_unit' => $weightUnit,
                'length' => $product->length ?? 0,
                'width' => $product->width ?? 0,
                'height' => $product->height ?? 0,
                'shipping_class_id' => $product->shipping_class_id,
                'shipping_type' => $shippingType,
                'shipping_cost' => $shippingCost,
                'requires_shipping' => $requiresShipping,
                'separate_shipping' => $product->separate_shipping ?? false,
                'quantity' => $quantity,
            ]);
        })->toArray();
    }

    /**
     * Calculate total weight of items.
     * 
     * @param array $items
     * @return float Weight in kg
     */
    protected function calculateTotalWeight(array $items): float
    {
        $totalWeight = 0;

        foreach ($items as $item) {
            $weight = $item['weight'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $weightUnit = $item['weight_unit'] ?? 'kg';

            // Convert to kg if needed
            $weightInKg = $this->convertWeightToKg($weight, $weightUnit);
            $totalWeight += $weightInKg * $quantity;
        }

        return round($totalWeight, 2);
    }

    /**
     * Convert weight to kg.
     * 
     * @param float $weight
     * @param string $unit
     * @return float
     */
    protected function convertWeightToKg(float $weight, string $unit): float
    {
        return match (strtolower($unit)) {
            'g', 'gram', 'grams' => $weight / 1000,
            'lb', 'lbs', 'pound', 'pounds' => $weight * 0.453592,
            'oz', 'ounce', 'ounces' => $weight * 0.0283495,
            default => $weight, // Assume kg
        };
    }

    /**
     * Check if items contain large items.
     * 
     * @param array $items
     * @return bool
     */
    protected function hasLargeItems(array $items): bool
    {
        foreach ($items as $item) {
            $length = $item['length'] ?? 0;
            $width = $item['width'] ?? 0;
            $height = $item['height'] ?? 0;

            // Consider item large if any dimension > 100cm
            if ($length > 100 || $width > 100 || $height > 100) {
                return true;
            }

            // Or if volume > 0.5 cubic meters
            $volume = ($length * $width * $height) / 1000000; // Convert to cubic meters
            if ($volume > 0.5) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get applicable shipping rate.
     * 
     * @param string $method
     * @param Address|null $address
     * @param array $items
     * @return ShippingRate|null
     */
    protected function getApplicableRate(string $method, ?Address $address, array $items): ?ShippingRate
    {
        // Get shipping class IDs from items
        $shippingClassIds = collect($items)
            ->pluck('shipping_class_id')
            ->filter()
            ->unique()
            ->toArray();

        // A cart can hold several shipping classes (e.g. one standard item and one
        // heavy item). Charge the highest applicable rate so a heavy item is never
        // shipped at a lighter class's price.
        if (!empty($shippingClassIds)) {
            $rate = ShippingRate::where('method', $method)
                ->where('is_active', true)
                ->whereIn('shipping_class_id', $shippingClassIds)
                ->orderByDesc('base_cost')
                ->first();

            if ($rate) {
                return $rate;
            }
        }

        // Fall back to the method's class-less rate (covers products with no
        // shipping class assigned).
        return ShippingRate::where('method', $method)
            ->where('is_active', true)
            ->whereNull('shipping_class_id')
            ->orderBy('base_cost')
            ->first();
    }

    /**
     * Calculate cost from rate based on weight and location.
     * 
     * @param ShippingRate $rate
     * @param float $totalWeight
     * @param array $items
     * @param Address|null $address
     * @return float
     */
    protected function calculateCostFromRate(ShippingRate $rate, float $totalWeight, array $items, ?Address $address): float
    {
        $baseCost = $rate->base_cost;

        // Admin has explicitly disabled weight-based pricing for this rate: charge
        // the flat base cost regardless of cart weight.
        if (!$rate->weight_pricing_enabled) {
            return $baseCost;
        }

        // Try to find applicable weight cost rule
        $weightCostRule = $this->getApplicableWeightCostRule(
            $rate->id,
            $address?->state,
            $address?->city
        );

        if ($weightCostRule) {
            $weightCost = $weightCostRule->calculateCost($totalWeight);
            return $baseCost + $weightCost;
        }

        // Fallback: Simple weight-based calculation
        return $this->calculateSimpleWeightCost($baseCost, $totalWeight, $rate->method);
    }

    /**
     * Calculate simple weight-based cost (fallback).
     *
     * @param float $baseCost
     * @param float $totalWeight
     * @param string $method
     * @return float
     */
    protected function calculateSimpleWeightCost(float $baseCost, float $totalWeight, string $method): float
    {
        if ($totalWeight <= 1) {
            return $baseCost;
        }

        // Cost per additional kg
        $costPerKg = match ($method) {
            self::METHOD_SHAH_SPORTS_TEAM => 20,
            self::METHOD_PATHAO_COURIER => 15,
            default => 10,
        };

        $additionalKg = ceil($totalWeight - 1);
        return $baseCost + ($additionalKg * $costPerKg);
    }

    /**
     * Get applicable weight cost rule for location.
     * 
     * @param int $shippingRateId
     * @param string|null $state
     * @param string|null $city
     * @return WeightCostRule|null
     */
    protected function getApplicableWeightCostRule(int $shippingRateId, ?string $state, ?string $city): ?WeightCostRule
    {
        // Priority: City > State > Default (null location)
        return WeightCostRule::where('shipping_rate_id', $shippingRateId)
            ->where(function ($query) use ($state, $city) {
                if ($city) {
                    $query->where('city', $city);
                } elseif ($state) {
                    $query->where('state', $state)->whereNull('city');
                } else {
                    $query->whereNull('city')->whereNull('state');
                }
            })
            ->orderByRaw('
                CASE 
                    WHEN city IS NOT NULL THEN 1 
                    WHEN state IS NOT NULL THEN 2 
                    ELSE 3 
                END
            ')
            ->first();
    }

    /**
     * Get shipping cost for a specific method.
     * 
     * @param string $method
     * @param array $items
     * @param Address|null $address
     * @param float $subtotal For free shipping check
     * @return float
     */
    public function getShippingCostForMethod(string $method, array $items, ?Address $address = null, float $subtotal = 0): float
    {
        $enrichedItems = $this->enrichItemsWithProductData($items);
        
        // Separate items by shipping requirements
        $shippingGroups = $this->groupItemsByShipping($enrichedItems);
        
        // Calculate custom shipping costs
        $customShippingCost = $this->calculateCustomShippingCost($shippingGroups['custom_shipping']);
        
        // If no default shipping items, return only custom cost
        if (empty($shippingGroups['default_shipping'])) {
            return $customShippingCost;
        }
        
        $totalWeight = $this->calculateTotalWeight($shippingGroups['default_shipping']);
        
        $rate = $this->getApplicableRate($method, $address, $shippingGroups['default_shipping']);
        
        if (!$rate) {
            Log::warning("No shipping rate found for method: {$method}");
            return $customShippingCost;
        }

        // Check free shipping
        if ($rate->free_shipping_min_order > 0 && $subtotal >= $rate->free_shipping_min_order) {
            return $customShippingCost;
        }

        $standardCost = $this->calculateCostFromRate($rate, $totalWeight, $shippingGroups['default_shipping'], $address);
        
        return $standardCost + $customShippingCost;
    }

    /**
     * Group items by shipping type.
     * 
     * @param array $items
     * @return array
     */
    protected function groupItemsByShipping(array $items): array
    {
        $groups = [
            'requires_shipping' => [],
            'no_shipping' => [],
            'custom_shipping' => [],
            'default_shipping' => [],
        ];

        foreach ($items as $item) {
            // Items that don't require shipping
            if (!($item['requires_shipping'] ?? true)) {
                $groups['no_shipping'][] = $item;
                continue;
            }

            $groups['requires_shipping'][] = $item;

            // Items with custom shipping (free, fixed, per_item)
            if (in_array($item['shipping_type'] ?? 'default', ['free', 'fixed', 'per_item'])) {
                $groups['custom_shipping'][] = $item;
            } else {
                // Items using default shipping calculation
                $groups['default_shipping'][] = $item;
            }
        }

        return $groups;
    }

    /**
     * Calculate total custom shipping cost.
     * 
     * @param array $items
     * @return float
     */
    protected function calculateCustomShippingCost(array $items): float
    {
        $totalCost = 0;

        foreach ($items as $item) {
            $shippingType = $item['shipping_type'] ?? 'default';
            $shippingCost = $item['shipping_cost'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $separateShipping = $item['separate_shipping'] ?? false;

            $itemCost = match ($shippingType) {
                'free' => 0,
                'fixed' => $shippingCost,
                'per_item' => $shippingCost * $quantity,
                default => 0,
            };

            // If item ships separately, multiply by quantity
            if ($separateShipping && $shippingType === 'fixed') {
                $itemCost *= $quantity;
            }

            $totalCost += $itemCost;
        }

        return $totalCost;
    }

    /**
     * Get detailed shipping quote with breakdown.
     * 
     * @param string $method
     * @param array $items
     * @param Address|null $address
     * @param float $subtotal
     * @return array
     */
    public function getShippingQuote(string $method, array $items, ?Address $address = null, float $subtotal = 0): array
    {
        $enrichedItems = $this->enrichItemsWithProductData($items);
        $totalWeight = $this->calculateTotalWeight($enrichedItems);
        
        $rate = $this->getApplicableRate($method, $address, $enrichedItems);
        
        if (!$rate) {
            return [
                'success' => false,
                'message' => 'Shipping method not available',
            ];
        }

        $isFreeShipping = $rate->free_shipping_min_order > 0 && $subtotal >= $rate->free_shipping_min_order;
        $cost = $isFreeShipping ? 0 : $this->calculateCostFromRate($rate, $totalWeight, $enrichedItems, $address);

        return [
            'success' => true,
            'method' => $method,
            'name' => $rate->name,
            'base_cost' => $rate->base_cost,
            'total_cost' => round($cost, 2),
            'total_weight' => $totalWeight,
            'delivery_time' => $rate->delivery_time,
            'is_free_shipping' => $isFreeShipping,
            'free_shipping_threshold' => $rate->free_shipping_min_order,
            'amount_to_free_shipping' => $isFreeShipping ? 0 : max(0, $rate->free_shipping_min_order - $subtotal),
        ];
    }
}
