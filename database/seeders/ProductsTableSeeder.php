<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $products = [
            [
                'category_id' => 1, // Smartphones
                'brand_id' => 1, // Samsung
                'model_id' => 1, // Samsung Galaxy Z Fold 5
                'vendor_shop_id' => 1,
                'product_name' => 'Samsung Galaxy Z Fold 5 (12/256GB)',
                'short_description' => 'Samsung Galaxy Z Fold 5 with 12GB RAM and 256GB Storage',
                'description' => $faker->paragraph,
                'price' => 1799.99,
                'variations' => '[{"variation":"Color","options":[{"option":"Black","price":""},{"option":"Blue","price":""}]},{"variation":"Size","options":[{"option":"M","price":"1150.00"},{"option":"XL","price":"1200.00"}]}]',
                'trending' => true,
                'featured' => true,
                'sku' => 'SAM-GZF-256GB',
                'stock' => '50',
                'weight_name' => 'gm',
                'weight_value' => 282,
                'slug' => Str::slug('Samsung Galaxy Z Fold 5 (12/256GB)'),
                'status' => 'published',
            ],
            [
                'category_id' => 1,
                'brand_id' => 2, // Apple
                'model_id' => 2,
                'vendor_shop_id' => 2,
                'product_name' => 'iPhone 15 Pro',
                'short_description' => 'Apple iPhone 15 Pro with A17 Bionic chip',
                'description' => $faker->paragraph,
                'price' => 1199.99,
                'variations' => '[{"variation":"Color","options":[{"option":"Deep Purple","price":""},{"option":"Silver","price":""}]},{"variation":"Storage","options":[{"option":"128GB","price":"1199.99"},{"option":"256GB","price":"1299.99"}]}]',
                'trending' => true,
                'featured' => true,
                'sku' => 'APL-IP15PRO',
                'stock' => '80',
                'weight_name' => 'gm',
                'weight_value' => 210,
                'slug' => Str::slug('iPhone 15 Pro'),
                'status' => 'published',
            ],
            [
                'category_id' => 1,
                'brand_id' => 2, // Apple
                'model_id' => 3,
                'vendor_shop_id' => 1,
                'product_name' => 'Apple iPhone 14 Pro Max, 256GB, Deep Purple - Unlocked',
                'short_description' => 'iPhone 14 Pro Max with 256GB Storage, Unlocked',
                'description' => $faker->paragraph,
                'price' => 1299.99,
                'variations' => '[{"variation":"Color","options":[{"option":"Deep Purple","price":""},{"option":"Gold","price":""}]},{"variation":"Storage","options":[{"option":"256GB","price":"1299.99"},{"option":"512GB","price":"1499.99"}]}]',
                'trending' => true,
                'featured' => true,
                'sku' => 'APL-IP14PM256',
                'stock' => '70',
                'weight_name' => 'gm',
                'weight_value' => 240,
                'slug' => Str::slug('Apple iPhone 14 Pro Max 256GB Deep Purple Unlocked'),
                'status' => 'published',
            ],
            [
                'category_id' => 2, // Laptops
                'brand_id' => 2, // Apple
                'model_id' => 4, // MacBook Pro
                'vendor_shop_id' => 2,
                'product_name' => 'Apple MacBook Pro 16 inch M3 Max Chip Liquid Retina Display 128GB RAM 8TB SSD Space Black',
                'short_description' => 'MacBook Pro 16 inch M3 Max Chip, 128GB RAM, 8TB SSD',
                'description' => $faker->paragraph,
                'price' => 5999.99,
                'variations' => '[{"variation":"Color","options":[{"option":"Space Black","price":""}]}]',
                'trending' => true,
                'featured' => true,
                'sku' => 'MBP16-M3MAX',
                'stock' => '20',
                'weight_name' => 'kg',
                'weight_value' => 2.2,
                'slug' => Str::slug('Apple MacBook Pro 16 M3 Max 128GB RAM 8TB SSD Space Black'),
                'status' => 'published',
            ],
            [
                'category_id' => 3, // Gaming Laptops
                'brand_id' => 3, // MSI
                'model_id' => 5,
                'vendor_shop_id' => 1,
                'product_name' => 'MSI Raider 18 HX A14VIG Core i9 14th Gen RTX 4090 16GB GDDR6 Graphics 18" Gaming Laptop',
                'short_description' => 'MSI Raider 18, Core i9 14th Gen, RTX 4090 16GB Gaming Laptop',
                'description' => $faker->paragraph,
                'price' => 4999.99,
                'variations' => '[{"variation":"RAM","options":[{"option":"32GB","price":""},{"option":"64GB","price":"200.00"}]},{"variation":"Storage","options":[{"option":"1TB","price":""},{"option":"2TB","price":"300.00"}]}]',
                'trending' => true,
                'featured' => true,
                'sku' => 'MSI-RAIDER18',
                'stock' => '30',
                'weight_name' => 'kg',
                'weight_value' => 3.5,
                'slug' => Str::slug('MSI Raider 18 HX A14VIG Core i9 14th Gen RTX 4090 16GB GDDR6 Graphics 18'),
                'status' => 'published',
            ],
        ];

        foreach ($products as $product) {
            Product::create([
                'category_id' => $product['category_id'],
                'brand_id' => $product['brand_id'],
                'model_id' => $product['model_id'],
                'vendor_shop_id' => $product['vendor_shop_id'],
                'product_name' => $product['product_name'],
                'short_description' => $product['short_description'],
                'description' => $product['description'],
                'price' => $product['price'],
                'variations' => $product['variations'],
                'slug' => $product['slug'],
                'trending' => $product['trending'],
                'featured' => $product['featured'],
                'status' => $product['status'],
                'sku' => $product['sku'],
                'stock' => $product['stock'],
                'weight_name' => $product['weight_name'],
                'weight_value' => $product['weight_value'],
                'length' => $faker->randomFloat(2, 10, 30),
                'width' => $faker->randomFloat(2, 5, 15),
                'height' => $faker->randomFloat(2, 0.1, 5),
                'is_virtual' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
