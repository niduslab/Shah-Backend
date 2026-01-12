<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use Faker\Factory as Faker;

class PromotionsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            Promotion::create([
                'promotion_name' => $faker->sentence(2),
                'promotion_code' => $faker->numberBetween(50, 200),
                'start_date' => $faker->dateTimeBetween('-1 month', '+1 month'),
                'end_date' => $faker->dateTimeBetween('+1 month', '+6 months'),
                'discount_percentage' => $faker->numberBetween(5, 50),
                'min_purchase_amount' => $faker->numberBetween(50, 200),
                'max_discount' => $faker->numberBetween(20, 100),
                'status' => $faker->randomElement(['active', 'inactive']),
            ]);
        }
    }
}

