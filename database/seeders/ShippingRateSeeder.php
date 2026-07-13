<?php

namespace Database\Seeders;

use App\Models\ShippingClass;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        $standardClass = ShippingClass::where('slug', 'standard')->first();
        $heavyClass = ShippingClass::where('slug', 'heavy-items')->first();
        $oversizedClass = ShippingClass::where('slug', 'oversized')->first();

        // Standard Shipping - no free shipping threshold until an admin sets one
        ShippingRate::create([
            'name' => 'Standard Shipping',
            'shipping_class_id' => null,
            'method' => 'standard',
            'country' => 'BD',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 0.00,
            'base_cost' => 60.00,
            'is_active' => true,
        ]);

        // Shah Sports Team Delivery - Standard
        ShippingRate::create([
            'name' => 'Shah Sports Team - Standard Delivery',
            'shipping_class_id' => $standardClass?->id,
            'method' => 'shah_sports_team',
            'country' => 'BD',
            'delivery_time' => '2-3 business days',
            'free_shipping_min_order' => 5000.00,
            'base_cost' => 100.00,
            'is_active' => true,
        ]);

        // Shah Sports Team Delivery - Heavy Items
        ShippingRate::create([
            'name' => 'Shah Sports Team - Heavy Items',
            'shipping_class_id' => $heavyClass?->id,
            'method' => 'shah_sports_team',
            'country' => 'BD',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 10000.00,
            'base_cost' => 250.00,
            'is_active' => true,
        ]);

        // Shah Sports Team Delivery - Oversized
        ShippingRate::create([
            'name' => 'Shah Sports Team - Oversized Items',
            'shipping_class_id' => $oversizedClass?->id,
            'method' => 'shah_sports_team',
            'country' => 'BD',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 15000.00,
            'base_cost' => 350.00,
            'is_active' => true,
        ]);

        // Pathao Courier - Standard
        ShippingRate::create([
            'name' => 'Pathao Courier - Standard',
            'shipping_class_id' => $standardClass?->id,
            'method' => 'pathao_courier',
            'country' => 'BD',
            'delivery_time' => '3-5 business days',
            'free_shipping_min_order' => 8000.00,
            'base_cost' => 80.00,
            'is_active' => true,
        ]);

        // Pathao Courier - Heavy Items
        ShippingRate::create([
            'name' => 'Pathao Courier - Heavy Items',
            'shipping_class_id' => $heavyClass?->id,
            'method' => 'pathao_courier',
            'country' => 'BD',
            'delivery_time' => '5-7 business days',
            'free_shipping_min_order' => 15000.00,
            'base_cost' => 200.00,
            'is_active' => true,
        ]);
    }
}
