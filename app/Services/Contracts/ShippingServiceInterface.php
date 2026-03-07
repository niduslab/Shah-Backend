<?php

namespace App\Services\Contracts;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Support\Collection;

interface ShippingServiceInterface
{
    /**
     * Calculate shipping cost for cart items.
     */
    public function calculateShippingCost(array $items, Address $address): array;

    /**
     * Get available shipping methods for an address.
     */
    public function getAvailableMethods(array $items = [], ?Address $address = null): array;

    /**
     * Assign tracking number to an order.
     */
    public function assignTrackingNumber(Order $order, string $trackingNumber): void;

    /**
     * Get shipping rates for a location.
     */
    public function getShippingRates(string $city, ?string $state = null): Collection;

    /**
     * Check if free shipping applies.
     */
    public function checkFreeShipping(float $subtotal, string $method): bool;
}
