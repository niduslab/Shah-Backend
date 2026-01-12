<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Faker\Factory as Faker;

class OrderItemsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $orders = Order::all();
        $products = Product::all();

        foreach ($orders as $order) {
            $products->each(function ($product) use ($order, $faker) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $faker->numberBetween(1, 5), // Random quantity between 1 and 5
                    'price' => $faker->randomFloat(2, 10, 200), // Random price between 10 and 200
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }
    }
}
