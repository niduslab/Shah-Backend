<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoyaltyPoint;
use App\Models\User;
use Faker\Factory as Faker;

class LoyaltyPointsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (User::all() as $user) {
            LoyaltyPoint::create([
                'user_id' => $user->id,
                'membership_name' => $faker->word,
                'minimum_points' => $faker->randomFloat(2, 10, 1000),
                'benefits_amount' => $faker->randomFloat(2, 10, 1000),
                'description' => $faker->sentence,
            ]);
        }
    }
}
