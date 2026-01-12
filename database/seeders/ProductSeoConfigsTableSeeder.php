<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSeoConfig;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductSeoConfigsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Fetch all products
        $products = Product::all();

        foreach ($products as $product) {
            ProductSeoConfig::create([
                'product_id' => $product->id,
                'meta_title' => $faker->sentence(6, true),
                'meta_description' => $faker->paragraph,
                'slug' => $product->slug, // Using the slug from the product table
                'canonical_url' => $faker->url,
                'tags' => json_encode($faker->words(5)), // Random tags in JSON format
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
