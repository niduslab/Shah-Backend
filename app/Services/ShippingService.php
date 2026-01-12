<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Services\Contracts\ShippingServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShippingService implements ShippingServiceInterface
{
    /**
     * Available shipping methods.
     */
    public const METHOD_SHAH_SPORTS_TEAM = 'shah_sports_team';
    public const METHOD_PATHAO_COURIER = 'pathao_courier';

    /**
     * Calculate shipping cost for cart items.
     * 
     * @param array $items Array of items with product_id, quantity, weight, shipping_class_id
     * @param Address $address
     * @return array Contains costs for each available method
     */
    public function calculateShippingCost(array $items, Address $address): array
    {
        $totalWeight = $this->calculateTotalWeight($items);
        $availableMethods = $this->getAvailableMethods($address);
        $costs = [];

        foreach ($availableMethods as $method) {
            $rate = $this->getApplicableRate($method['method'], $address, $items);
            
            if ($rate) {
                $cost = $this->calculateCostFromRate($rate, $totalWeight, $items);
                $costs[$method['method']] = [
                    'method' => $method['method'],
                    'name' => $method['name'],
                    'cost' => $cost,
                    'delivery_time' => $rate->delivery_time,
                    'free_shipping_min_order' => $rate->free_shipping_min_order,
                ];
            }
        }

        return $costs;
    }

    /**
     * Get available shipping methods for an address.
     * 
     * @param Address $address
     * @return array
     */
    public function getAvailableMethods(Address $address): array
    {
        $methods = [];

        // Shah Sports Team - available for heavy/large items and local delivery
        $shahSportsRate = ShippingRate::where('method', self::METHOD_SHAH_SPORTS_TEAM)
            ->where('is_active', true)
            ->first();

        if ($shahSportsRate) {
            $methods[] = [
                'method' => self::METHOD_SHAH_SPORTS_TEAM,
                'name' => 'Shah Sports Team Delivery',
                'description' => 'Our own delivery team for heavy and large items',
            ];
        }

        // Pathao Courier - available for home delivery
        $pathaoRate = ShippingRate::where('method', self::METHOD_PATHAO_COURIER)
            ->where('is_active', true)
            ->first();

        if ($pathaoRate) {
            $methods[] = [
                'method' => self::METHOD_PATHAO_COURIER,
                'name' => 'Pathao Courier',
                'description' => 'Standard home delivery via Pathao',
            ];
        }

        return $methods;
    }

    /**
     * Assign tracking number to an order.
     * 
     * @param Order $order
     * @param string $trackingNumber
     * @return void
     */
    public function assignTrackingNumber(Order $order, string $trackingNumber): void
    {
        $order->update([
            'tracking_number' => $trackingNumber,
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
     * Calculate total weight of items.
     * 
     * @param array $items
     * @return float
     */
    protected function calculateTotalWeight(array $items): float
    {
        $totalWeight = 0;

        foreach ($items as $item) {
            $weight = $item['weight'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $weightUnit = $item['weight_unit'] ?? 'kg';

            // Convert to kg if needed
            if ($weightUnit === 'g') {
                $weight = $weight / 1000;
            } elseif ($weightUnit === 'lb') {
                $weight = $weight * 0.453592;
            }

            $totalWeight += $weight * $quantity;
        }

        return $totalWeight;
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
     * @param Address $address
     * @param array $items
     * @return ShippingRate|null
     */
    protected function getApplicableRate(string $method, Address $address, array $items): ?ShippingRate
    {
        // Get shipping class IDs from items
        $shippingClassIds = collect($items)
            ->pluck('shipping_class_id')
            ->filter()
            ->unique()
            ->toArray();

        // Try to find rate matching shipping class
        if (!empty($shippingClassIds)) {
            $rate = ShippingRate::where('method', $method)
                ->where('is_active', true)
                ->whereIn('shipping_class_id', $shippingClassIds)
                ->first();

            if ($rate) {
                return $rate;
            }
        }

        // Fall back to default rate (no shipping class)
        return ShippingRate::where('method', $method)
            ->where('is_active', true)
            ->whereNull('shipping_class_id')
            ->first();
    }

    /**
     * Calculate cost from rate based on weight.
     * 
     * @param ShippingRate $rate
     * @param float $totalWeight
     * @param array $items
     * @return float
     */
    protected function calculateCostFromRate(ShippingRate $rate, float $totalWeight, array $items): float
    {
        $baseCost = $rate->base_cost;

        // Add weight-based cost if applicable
        // This would use weight_cost_rules if implemented
        // For now, use a simple weight multiplier
        $weightCost = 0;
        if ($totalWeight > 1) {
            // Add cost per additional kg
            $additionalKg = ceil($totalWeight - 1);
            $costPerKg = $rate->method === self::METHOD_SHAH_SPORTS_TEAM ? 20 : 15;
            $weightCost = $additionalKg * $costPerKg;
        }

        return $baseCost + $weightCost;
    }

    /**
     * Get shipping cost for a specific method.
     * 
     * @param string $method
     * @param array $items
     * @param Address $address
     * @param float $subtotal For free shipping check
     * @return float
     */
    public function getShippingCostForMethod(string $method, array $items, Address $address, float $subtotal = 0): float
    {
        // Check free shipping first
        if ($this->checkFreeShipping($subtotal, $method)) {
            return 0;
        }

        $costs = $this->calculateShippingCost($items, $address);

        return $costs[$method]['cost'] ?? 0;
    }
}
