<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        // Add placeholder images to all products
        $products = Product::all();

        foreach ($products as $product) {
            // Primary image
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/' . $product->slug . '-1.jpg',
                'alt_text' => $product->name . ' - Main Image',
                'is_primary' => true,
                'sort_order' => 1,
            ]);

            // Additional images (2-3 per product)
            $additionalImages = rand(2, 3);
            for ($i = 2; $i <= $additionalImages + 1; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'products/' . $product->slug . '-' . $i . '.jpg',
                    'alt_text' => $product->name . ' - Image ' . $i,
                    'is_primary' => false,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
