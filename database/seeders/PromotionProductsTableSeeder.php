<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromotionProduct;
use App\Models\Promotion;
use App\Models\Product;

class PromotionProductsTableSeeder extends Seeder
{
    public function run()
    {
        // Get all promotion IDs and product IDs
        $promotions = Promotion::pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();

        // Generate PromotionProducts
        foreach ($promotions as $promotionId) {
            foreach ($products as $productId) {
                PromotionProduct::create([
                    'promotion_id' => $promotionId,
                    'product_id' => $productId,
                ]);
            }
        }
    }
}

