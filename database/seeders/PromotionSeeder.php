<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        // Sitewide Sale
        Promotion::create([
            'name' => 'New Year Sale',
            'description' => 'Get 10% off on all products',
            'promotion_type' => 'percentage',
            'discount_value' => 10.00,
            'applies_to' => 'all_products',
            'apply_level' => 'cart',
            'min_purchase_amount' => 2000.00,
            'max_discount_amount' => 2000.00,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(1),
            'is_active' => true,
            'priority' => 1,
        ]);

        // Cricket Category Sale
        $cricketCategory = Category::where('slug', 'cricket')->first();
        $cricketPromo = Promotion::create([
            'name' => 'Cricket Season Sale',
            'description' => '15% off on all cricket equipment',
            'promotion_type' => 'percentage',
            'discount_value' => 15.00,
            'applies_to' => 'specific_categories',
            'apply_level' => 'product',
            'min_purchase_amount' => 3000.00,
            'max_discount_amount' => 5000.00,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(2),
            'is_active' => true,
            'priority' => 2,
        ]);
        if ($cricketCategory) {
            $cricketPromo->categories()->attach($cricketCategory->id);
            // Also attach child categories
            $cricketCategory->children->each(function ($child) use ($cricketPromo) {
                $cricketPromo->categories()->attach($child->id);
            });
        }

        // Brand-specific promotion
        $nikePromo = Promotion::create([
            'name' => 'Nike Flash Sale',
            'description' => 'Flat ৳1000 off on Nike products',
            'promotion_type' => 'fixed_amount',
            'discount_value' => 1000.00,
            'applies_to' => 'specific_brands',
            'apply_level' => 'product',
            'min_purchase_amount' => 5000.00,
            'starts_at' => now(),
            'ends_at' => now()->addWeeks(2),
            'is_active' => true,
            'priority' => 3,
        ]);
        $nikeBrand = Brand::where('slug', 'nike')->first();
        if ($nikeBrand) {
            $nikePromo->brands()->attach($nikeBrand->id);
        }

        // Free Delivery Promotion
        Promotion::create([
            'name' => 'Free Delivery Weekend',
            'description' => 'Free delivery on orders above ৳3000',
            'promotion_type' => 'free_delivery',
            'discount_value' => 0,
            'applies_to' => 'all_products',
            'apply_level' => 'cart',
            'min_purchase_amount' => 3000.00,
            'starts_at' => now()->startOfWeek()->addDays(5), // Saturday
            'ends_at' => now()->startOfWeek()->addDays(7), // End of Sunday
            'is_active' => true,
            'priority' => 4,
        ]);

        // Fitness Equipment Sale
        $fitnessCategory = Category::where('slug', 'fitness')->first();
        $fitnessPromo = Promotion::create([
            'name' => 'Fitness First Sale',
            'description' => '20% off on fitness equipment',
            'promotion_type' => 'percentage',
            'discount_value' => 20.00,
            'applies_to' => 'specific_categories',
            'apply_level' => 'product',
            'min_purchase_amount' => 1500.00,
            'max_discount_amount' => 3000.00,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(1),
            'is_active' => true,
            'priority' => 2,
        ]);
        if ($fitnessCategory) {
            $fitnessPromo->categories()->attach($fitnessCategory->id);
            $fitnessCategory->children->each(function ($child) use ($fitnessPromo) {
                $fitnessPromo->categories()->attach($child->id);
            });
        }
    }
}
