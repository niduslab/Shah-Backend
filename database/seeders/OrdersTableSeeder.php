<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Address;
use App\Models\ShippingRate;
use Faker\Factory as Faker;

class OrdersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::all();
        $addresses = Address::all();
        $shippingRates = ShippingRate::all();

        foreach ($users as $user) {
            Order::create([
                'user_id' => $user->id,
                'shipping_address_id' => $addresses->random()->id,
                'billing_address_id' => $addresses->random()->id,
                // 'shipping_rate_id' => $shippingRates->random()->id,
                'status' => $faker->randomElement(['awaiting_payment', 'pending', 'processing', 'shipped', 'delivered', 'cancelled']),
                'total_amount' => $faker->randomFloat(2, 50, 500), // Random total amount between 50 and 500
                'tracking_number' => $faker->optional()->word, // Optional tracking number
                'notes' => $faker->optional()->sentence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
