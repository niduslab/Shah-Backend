<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorPayout;
use App\Models\User; // Assuming vendor is a type of User
use Faker\Factory as Faker;

class VendorPayoutsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Get all users with user_type 'vendor'
        $vendors = User::where('user_type', 'vendor')->get(); // Updated column name to 'user_type'

        foreach ($vendors as $vendor) {
            VendorPayout::create([
                'vendor_id' => $vendor->id,
                'amount' => $faker->randomFloat(2, 100, 1000), // Random amount between 100 and 1000
                'status' => $faker->randomElement(['requested', 'approved', 'paid']),
                'requested_at' => $faker->date(),
                'paid_at' => $faker->randomElement([null, $faker->date()]), // Sometimes paid_at is null
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
