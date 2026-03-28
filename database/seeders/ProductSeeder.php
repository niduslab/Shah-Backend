<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingClass;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $standardShipping = ShippingClass::where('slug', 'standard')->first();
        $heavyShipping = ShippingClass::where('slug', 'heavy-items')->first();
        $oversizedShipping = ShippingClass::where('slug', 'oversized')->first();

        $products = [
            // FITNESS - Cardio - Bike
            [
                'category' => 'bike',
                'brand' => 'Shua',
                'name' => 'Shua SH-B5817U Upright Exercise Bike',
                'sku' => 'SH-B5817U',
                'short_description' => 'Commercial grade upright bike with magnetic resistance',
                'description' => 'Professional upright exercise bike with 16 levels of magnetic resistance. LCD display shows time, speed, distance, calories. Max user weight 150kg.',
                'price' => 45000.00,
                'compare_price' => 52000.00,
                'cost_price' => 35000.00,
                'quantity' => 15,
                'weight' => 45,
                'is_featured' => true,
                'is_trending' => true,
                'shipping_class' => 'heavy-items',
            ],
            [
                'category' => 'bike',
                'brand' => 'Body Solid',
                'name' => 'Body Solid BFCT1 Functional Trainer',
                'sku' => 'BS-BFCT1',
                'short_description' => 'Dual adjustable pulley system for functional training',
                'description' => 'Commercial functional trainer with dual 200lb weight stacks. 19 height adjustments per arm. Perfect for home and commercial gyms.',
                'price' => 185000.00,
                'compare_price' => 210000.00,
                'cost_price' => 145000.00,
                'quantity' => 5,
                'weight' => 280,
                'is_featured' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Cardio - Treadmill
            [
                'category' => 'treadmill',
                'brand' => 'Marcy',
                'name' => 'Marcy Pro Motorized Treadmill JX-651BW',
                'sku' => 'MC-JX651BW',
                'short_description' => '2.5HP motor treadmill with 12 programs',
                'description' => 'Marcy motorized treadmill with 2.5HP motor. Speed range 1-16 km/h. 12 preset programs. LCD display. Max user weight 120kg.',
                'price' => 65000.00,
                'compare_price' => 75000.00,
                'cost_price' => 48000.00,
                'quantity' => 10,
                'weight' => 65,
                'is_featured' => true,
                'is_trending' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Cardio - Elliptical
            [
                'category' => 'elliptical',
                'brand' => 'Shua',
                'name' => 'Shua SH-B5902E Elliptical Cross Trainer',
                'sku' => 'SH-B5902E',
                'short_description' => 'Commercial elliptical with 20" stride length',
                'description' => 'Professional elliptical cross trainer with 20" stride length. 16 resistance levels. Heart rate monitoring. Max user weight 150kg.',
                'price' => 85000.00,
                'compare_price' => 95000.00,
                'cost_price' => 65000.00,
                'quantity' => 8,
                'weight' => 95,
                'is_featured' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Cardio - Rowing
            [
                'category' => 'rowing',
                'brand' => 'Body Solid',
                'name' => 'Body Solid Endurance R300 Rower',
                'sku' => 'BS-R300',
                'short_description' => 'Air resistance rowing machine',
                'description' => 'Commercial air resistance rower with adjustable footplates. LCD monitor tracks time, distance, calories. Foldable design for easy storage.',
                'price' => 55000.00,
                'compare_price' => 62000.00,
                'cost_price' => 42000.00,
                'quantity' => 12,
                'weight' => 38,
                'is_trending' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Strength - Selectorized Series
            [
                'category' => 'selectorized-series',
                'brand' => 'Body Solid',
                'name' => 'Body Solid Pro Dual Lat Pulldown Machine',
                'sku' => 'BS-DLAT-PRO',
                'short_description' => 'Commercial lat pulldown with 200lb stack',
                'description' => 'Professional selectorized lat pulldown machine. 200lb weight stack. Adjustable thigh pads. Multiple grip positions.',
                'price' => 125000.00,
                'compare_price' => 145000.00,
                'cost_price' => 95000.00,
                'quantity' => 6,
                'weight' => 185,
                'is_featured' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Strength - Plate Loaded Series
            [
                'category' => 'plate-loaded-series',
                'brand' => 'Marcy',
                'name' => 'Marcy Pro Plate Loaded Leg Press',
                'sku' => 'MC-LP-PRO',
                'short_description' => '45-degree plate loaded leg press',
                'description' => 'Heavy-duty 45-degree leg press. Plate loaded design. Adjustable back pad. Max load capacity 400kg.',
                'price' => 95000.00,
                'compare_price' => 110000.00,
                'cost_price' => 72000.00,
                'quantity' => 8,
                'weight' => 145,
                'is_featured' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Strength - Multi Station Gym
            [
                'category' => 'multi-station-gym',
                'brand' => 'Body Solid',
                'name' => 'Body Solid EXM2500S Home Gym',
                'sku' => 'BS-EXM2500S',
                'short_description' => 'Complete home gym with 160lb stack',
                'description' => 'All-in-one home gym system. 160lb weight stack. Includes lat pulldown, chest press, leg developer, and more. Over 50 exercises.',
                'price' => 145000.00,
                'compare_price' => 165000.00,
                'cost_price' => 110000.00,
                'quantity' => 5,
                'weight' => 195,
                'is_featured' => true,
                'is_trending' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Free Weight - Barbell
            [
                'category' => 'barbell',
                'brand' => 'Body Solid',
                'name' => 'Body Solid Olympic Barbell 7ft 20kg',
                'sku' => 'BS-OB-7FT',
                'short_description' => 'Olympic standard barbell with rotating sleeves',
                'description' => 'Professional Olympic barbell. 7ft length, 20kg weight. Chrome finish. Rotating sleeves. Max load capacity 600kg.',
                'price' => 18000.00,
                'compare_price' => 22000.00,
                'cost_price' => 13000.00,
                'quantity' => 25,
                'weight' => 20,
                'is_featured' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Free Weight - Dumbbell
            [
                'category' => 'dumbbell',
                'brand' => 'Body Solid',
                'name' => 'Body Solid Rubber Hex Dumbbell Set 2.5-25kg',
                'sku' => 'BS-HEX-SET',
                'short_description' => 'Complete rubber hex dumbbell set',
                'description' => 'Professional rubber hex dumbbell set. Includes pairs from 2.5kg to 25kg. Rubber coated for floor protection. Anti-roll design.',
                'price' => 85000.00,
                'compare_price' => 95000.00,
                'cost_price' => 65000.00,
                'quantity' => 10,
                'weight' => 275,
                'is_trending' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Free Weight - Bench
            [
                'category' => 'bench',
                'brand' => 'Marcy',
                'name' => 'Marcy Pro Adjustable Olympic Bench',
                'sku' => 'MC-AOB-PRO',
                'short_description' => 'Heavy-duty adjustable weight bench',
                'description' => 'Professional adjustable bench with 7 back positions. Olympic width. Leg developer attachment. Max user weight 300kg.',
                'price' => 35000.00,
                'compare_price' => 42000.00,
                'cost_price' => 26000.00,
                'quantity' => 15,
                'weight' => 45,
                'is_featured' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Free Weight - Weight Plate
            [
                'category' => 'weight-plate',
                'brand' => 'Body Solid',
                'name' => 'Body Solid Olympic Rubber Weight Plate Set',
                'sku' => 'BS-ORP-SET',
                'short_description' => 'Olympic rubber weight plates 100kg set',
                'description' => 'Olympic rubber weight plate set. Includes 2x25kg, 2x20kg, 2x15kg, 2x10kg, 2x5kg. 2" Olympic hole. Rubber coated.',
                'price' => 65000.00,
                'compare_price' => 75000.00,
                'cost_price' => 48000.00,
                'quantity' => 20,
                'weight' => 100,
                'is_trending' => true,
                'shipping_class' => 'heavy-items',
            ],

            // FITNESS - Free Weight - Fitness Accessories
            [
                'category' => 'fitness-accessories',
                'brand' => 'Body Solid',
                'name' => 'Body Solid Resistance Band Set',
                'sku' => 'BS-RB-SET',
                'short_description' => '5-piece resistance band set with handles',
                'description' => 'Complete resistance band set with 5 resistance levels. Includes handles, door anchor, and ankle straps. Portable workout solution.',
                'price' => 3500.00,
                'compare_price' => 4500.00,
                'cost_price' => 2500.00,
                'quantity' => 50,
                'weight' => 1.5,
                'is_trending' => true,
                'shipping_class' => 'standard',
            ],
            [
                'category' => 'fitness-accessories',
                'brand' => 'Marcy',
                'name' => 'Marcy Yoga Mat Premium 6mm',
                'sku' => 'MC-YM-6MM',
                'short_description' => 'Non-slip yoga mat with carrying strap',
                'description' => 'Premium 6mm yoga mat with non-slip surface. Includes carrying strap. Perfect for yoga, pilates, and floor exercises.',
                'price' => 2500.00,
                'compare_price' => 3200.00,
                'cost_price' => 1800.00,
                'quantity' => 60,
                'weight' => 1.2,
                'shipping_class' => 'standard',
            ],

            // SPORTS - Cricket
            [
                'category' => 'cricket',
                'brand' => 'SS',
                'name' => 'SS Ton Reserve Edition English Willow Bat',
                'sku' => 'SS-TRE-EW',
                'short_description' => 'Premium English willow cricket bat',
                'description' => 'SS Ton Reserve Edition crafted from Grade 1 English willow. Mid-low sweet spot for powerful drives. Professional grade.',
                'price' => 25000.00,
                'compare_price' => 28000.00,
                'cost_price' => 18000.00,
                'quantity' => 20,
                'weight' => 1.2,
                'is_featured' => true,
                'is_trending' => true,
                'shipping_class' => 'standard',
            ],
            [
                'category' => 'cricket',
                'brand' => 'SG',
                'name' => 'SG Test Red Leather Cricket Ball',
                'sku' => 'SG-TEST-RED',
                'short_description' => 'Premium test match quality ball',
                'description' => 'SG Test ball used in international cricket. Premium alum tanned leather with hand-stitched seam. 156g weight.',
                'price' => 3200.00,
                'compare_price' => 3500.00,
                'cost_price' => 2400.00,
                'quantity' => 100,
                'weight' => 0.156,
                'shipping_class' => 'standard',
            ],

            // SPORTS - Football
            [
                'category' => 'football',
                'brand' => 'Nike',
                'name' => 'Nike Flight Official Match Ball',
                'sku' => 'NK-FLT-OMB',
                'short_description' => 'FIFA Quality Pro certified match ball',
                'description' => 'Nike Flight ball with Aerowsculpt technology for consistent flight. FIFA Quality Pro certified for professional matches.',
                'price' => 15000.00,
                'compare_price' => 17000.00,
                'cost_price' => 11000.00,
                'quantity' => 30,
                'weight' => 0.45,
                'is_featured' => true,
                'is_trending' => true,
                'shipping_class' => 'standard',
            ],
            [
                'category' => 'football',
                'brand' => 'Adidas',
                'name' => 'Adidas UCL Pro Match Ball',
                'sku' => 'AD-UCL-PRO',
                'short_description' => 'Official UEFA Champions League ball',
                'description' => 'Official match ball of the UEFA Champions League. Seamless surface for true flight and consistent touch.',
                'price' => 16500.00,
                'compare_price' => 18000.00,
                'cost_price' => 12000.00,
                'quantity' => 25,
                'weight' => 0.43,
                'is_featured' => true,
                'shipping_class' => 'standard',
            ],

            // SPORTS - Table Tennis
            [
                'category' => 'table-tennis',
                'brand' => 'Butterfly',
                'name' => 'Butterfly Tenergy 05 Rubber Sheet',
                'sku' => 'BF-T05-RUB',
                'short_description' => 'High tension table tennis rubber',
                'description' => 'Butterfly Tenergy 05 with High Tension technology. Maximum spin and speed. Professional grade rubber.',
                'price' => 6500.00,
                'compare_price' => 7500.00,
                'cost_price' => 4800.00,
                'quantity' => 40,
                'weight' => 0.08,
                'is_featured' => true,
                'shipping_class' => 'standard',
            ],

            // SPORTS - Basketball
            [
                'category' => 'basketball',
                'brand' => 'Spalding',
                'name' => 'Spalding NBA Official Game Ball',
                'sku' => 'SP-NBA-OGB',
                'short_description' => 'Official NBA game basketball',
                'description' => 'Official NBA game ball. Full-grain leather. Size 7. Used in professional NBA games.',
                'price' => 18000.00,
                'compare_price' => 21000.00,
                'cost_price' => 13000.00,
                'quantity' => 25,
                'weight' => 0.62,
                'is_featured' => true,
                'is_trending' => true,
                'shipping_class' => 'standard',
            ],

            // SPORTS - Boxing
            [
                'category' => 'boxing',
                'brand' => 'Everlast',
                'name' => 'Everlast Pro Style Training Gloves 16oz',
                'sku' => 'EV-PST-16OZ',
                'short_description' => 'Professional boxing training gloves',
                'description' => 'Everlast Pro Style training gloves with premium leather. Full padding for protection. 16oz weight.',
                'price' => 8500.00,
                'compare_price' => 9500.00,
                'cost_price' => 6200.00,
                'quantity' => 35,
                'weight' => 0.8,
                'is_featured' => true,
                'shipping_class' => 'standard',
            ],

            // SPORTS - Swimming
            [
                'category' => 'swimming',
                'brand' => 'Speedo',
                'name' => 'Speedo Fastskin Hyper Elite Goggles',
                'sku' => 'SP-FHE-GOG',
                'short_description' => 'Competition racing goggles',
                'description' => 'Speedo Fastskin Hyper Elite racing goggles with IQfit technology. Ultra-low profile for reduced drag.',
                'price' => 8500.00,
                'compare_price' => 9500.00,
                'cost_price' => 6000.00,
                'quantity' => 40,
                'weight' => 0.05,
                'is_featured' => true,
                'shipping_class' => 'standard',
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            $brand = Brand::where('name', $productData['brand'])->first();
            $shippingClass = ShippingClass::where('slug', $productData['shipping_class'])->first();

            if ($category && $brand) {
                Product::create([
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'shipping_class_id' => $shippingClass?->id,
                    'name' => $productData['name'],
                    'sku' => $productData['sku'],
                    'short_description' => $productData['short_description'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'compare_price' => $productData['compare_price'] ?? null,
                    'cost_price' => $productData['cost_price'] ?? null,
                    'quantity' => $productData['quantity'],
                    'low_stock_threshold' => 5,
                    'weight' => $productData['weight'],
                    'weight_unit' => 'kg',
                    'is_featured' => $productData['is_featured'] ?? false,
                    'is_trending' => $productData['is_trending'] ?? false,
                    'status' => 'active',
                    'meta_title' => $productData['name'] . ' | Shah Sports',
                    'meta_description' => $productData['short_description'],
                ]);
            }
        }
    }
}
