<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\VendorShop;
use Faker\Factory as Faker;

class VendorShopsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Fetch admin and vendor users
        $admin = User::where('user_type', 'admin')->first(); // Assuming 'user_type' is used to differentiate admin and vendors
        $vendors = User::where('user_type', 'vendor')->get();

        // Create shop for the admin
        VendorShop::create([
            'vendor_id' => $admin->id,
            'shopname' => 'Admin Shop',
            'shop_email' => $admin->email,
            'contact' => $faker->phoneNumber,
            'description' => 'This is the main admin shop.',
            'location' => $faker->address,
            'status' => true, // Assuming admin shop is active
            'sell_commission_percentage' => 10.00, // Example commission
            'product_tax_percent' => 5.00, // Example tax percentage
            'product_vat_percent' => 12.00, // Example VAT percentage
            'total_sell' => 10000.00, // Example total sell
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create shops for each vendor
        foreach ($vendors as $vendor) {
            VendorShop::create([
                'vendor_id' => $vendor->id,
                'shopname' => $vendor->name . "'s Shop", // Generating shop name based on vendor's name
                'shop_email' => $vendor->email,
                'contact' => $faker->phoneNumber,
                'description' => $faker->paragraph,
                'location' => $faker->address,
                'status' => $faker->boolean, // Random active/inactive status
                'sell_commission_percentage' => $faker->randomFloat(2, 5, 15), // Random commission between 5% and 15%
                'product_tax_percent' => $faker->randomFloat(2, 1, 10), // Random tax between 1% and 10%
                'product_vat_percent' => $faker->randomFloat(2, 5, 15), // Random VAT between 5% and 15%
                'total_sell' => $faker->randomFloat(2, 5000, 20000), // Random total sell value
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
