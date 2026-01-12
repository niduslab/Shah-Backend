<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorShop;
use App\Models\VendorShopSeoConfig;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class VendorShopSeoConfigsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Fetch all vendor shops
        $shops = VendorShop::all();

        foreach ($shops as $shop) {
            VendorShopSeoConfig::create([
                'shop_id' => $shop->id,
                'meta_title' => $shop->shopname . ' - ' . 'Buy Now!',
                'meta_description' => $faker->sentence(20), // Random meta description
                'slug' => Str::slug($shop->shopname), // Generate a unique slug from the shop name
                'canonical_url' => $faker->unique()->url, // Random canonical URL
                'fb_profile_link' => $faker->optional()->url, // Optional Facebook profile link
                'x_profile_link' => $faker->optional()->url, // Optional X (Twitter) profile link
                'instra_profile_link' => $faker->optional()->url, // Optional Instagram profile link
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
