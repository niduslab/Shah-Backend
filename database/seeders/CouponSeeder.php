<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Welcome coupon for new customers
        Coupon::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome Discount',
            'description' => '10% off for new customers',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'min_order_amount' => 1000.00,
            'max_discount_amount' => 1000.00,
            'applies_to' => 'all_products',
            'usage_limit' => 1000,
            'once_per_customer' => true,
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Fixed amount coupon
        Coupon::create([
            'code' => 'SAVE500',
            'name' => 'Save ৳500',
            'description' => 'Flat ৳500 off on orders above ৳5000',
            'discount_type' => 'fixed_amount',
            'discount_value' => 500.00,
            'min_order_amount' => 5000.00,
            'applies_to' => 'all_products',
            'usage_limit' => 500,
            'once_per_customer' => false,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);

        // Free shipping coupon
        Coupon::create([
            'code' => 'FREESHIP',
            'name' => 'Free Shipping',
            'description' => 'Free shipping on any order',
            'discount_type' => 'free_shipping',
            'discount_value' => 0,
            'min_order_amount' => 2000.00,
            'applies_to' => 'all_products',
            'usage_limit' => 200,
            'once_per_customer' => true,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(2),
            'is_active' => true,
        ]);

        // Cricket category coupon
        $cricketCoupon = Coupon::create([
            'code' => 'CRICKET20',
            'name' => 'Cricket Special',
            'description' => '20% off on cricket equipment',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'min_order_amount' => 3000.00,
            'max_discount_amount' => 3000.00,
            'applies_to' => 'specific_categories',
            'usage_limit' => 100,
            'once_per_customer' => false,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(1),
            'is_active' => true,
        ]);
        $cricketCategory = Category::where('slug', 'cricket')->first();
        if ($cricketCategory) {
            $cricketCoupon->categories()->attach($cricketCategory->id);
            $cricketCategory->children->each(function ($child) use ($cricketCoupon) {
                $cricketCoupon->categories()->attach($child->id);
            });
        }

        // Brand-specific coupon
        $yonexCoupon = Coupon::create([
            'code' => 'YONEX15',
            'name' => 'Yonex Discount',
            'description' => '15% off on Yonex products',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'min_order_amount' => 5000.00,
            'max_discount_amount' => 2500.00,
            'applies_to' => 'specific_brands',
            'usage_limit' => 50,
            'once_per_customer' => true,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(2),
            'is_active' => true,
        ]);
        $yonexBrand = Brand::where('slug', 'yonex')->first();
        if ($yonexBrand) {
            $yonexCoupon->brands()->attach($yonexBrand->id);
        }

        // Big spender coupon
        Coupon::create([
            'code' => 'BIGORDER',
            'name' => 'Big Order Discount',
            'description' => '৳2000 off on orders above ৳20000',
            'discount_type' => 'fixed_amount',
            'discount_value' => 2000.00,
            'min_order_amount' => 20000.00,
            'applies_to' => 'all_products',
            'usage_limit' => 100,
            'once_per_customer' => false,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        // Expired coupon (for testing)
        Coupon::create([
            'code' => 'EXPIRED10',
            'name' => 'Expired Coupon',
            'description' => 'This coupon has expired',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'min_order_amount' => 1000.00,
            'applies_to' => 'all_products',
            'usage_limit' => 100,
            'once_per_customer' => false,
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subMonth(),
            'is_active' => true,
        ]);

        // Inactive coupon (for testing)
        Coupon::create([
            'code' => 'INACTIVE',
            'name' => 'Inactive Coupon',
            'description' => 'This coupon is inactive',
            'discount_type' => 'percentage',
            'discount_value' => 25.00,
            'min_order_amount' => 1000.00,
            'applies_to' => 'all_products',
            'usage_limit' => 100,
            'once_per_customer' => false,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(3),
            'is_active' => false,
        ]);
    }
}
