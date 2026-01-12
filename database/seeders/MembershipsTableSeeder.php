<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Membership;
use App\Models\User;
use Faker\Factory as Faker;

class MembershipsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (User::all() as $user) {
            Membership::create([
                'user_id' => $user->id,
                'point_earned' => $faker->randomFloat(2, 10, 1000),
                'points_redeemed' => $faker->randomFloat(2, 10, 1000),
            ]);
        }
    }
}

