<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core data
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductModelSeeder::class,
            ShippingClassSeeder::class,
            
            // Products
            ProductSeeder::class,
            ProductVariationSeeder::class,
            ProductImageSeeder::class,
            
            // Shipping
            ShippingRateSeeder::class,
            
            // Promotions & Coupons
            PromotionSeeder::class,
            CouponSeeder::class,
            
            // Content
            BannerSeeder::class,
            StorePolicySeeder::class,
            CmsPageSeeder::class,
            
            // Orders & Reviews (sample data)
            AddressSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
