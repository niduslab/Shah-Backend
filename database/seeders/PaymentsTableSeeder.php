<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Faker\Factory as Faker;

class PaymentsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Get all user IDs and order IDs
        $users = User::pluck('id')->toArray();
        $orders = Order::pluck('id')->toArray();

        // Generate Payments
        for ($i = 0; $i < 50; $i++) {
            Payment::create([
                'user_id' => $faker->randomElement($users),
                'order_id' => $faker->randomElement($orders),
                'amount' => $faker->randomFloat(2, 10, 500),
                'currency' => $faker->randomElement(['USD', 'EUR', 'GBP']),
                'transaction_id' => $faker->unique()->randomNumber(8, true), // Generate a random number with minimum 8 digits
                'payment_method' => $faker->randomElement(['credit_card', 'paypal', 'bank_transfer', 'stripe', 'cash_on_delivery']),
                'payment_status' => $faker->randomElement(['pending', 'completed', 'failed']),
            ]);
        }
    }
}

