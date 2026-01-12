<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\Image;
use App\Models\Address;
use Faker\Factory as Faker;

class UserProfilesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Check if there are any records in the Image and Address tables
        $images = Image::all();
        $addresses = Address::all();

        foreach (User::all() as $user) {
            // Ensure there's at least one image and address available
            $imageId = $images->isEmpty() ? null : $images->random()->id;
            $addressId = $addresses->isEmpty() ? null : Address::where('user_id', $user->id)->inRandomOrder()->first()->id ?? null;

            UserProfile::create([
                'user_id' => $user->id,
                'image_id' => $imageId,
                'phone_no' => $faker->phoneNumber,
                'website' => $faker->url,
                'gender' => $faker->randomElement(['male', 'female', 'others']),
                'date_of_birth' => $faker->date,
                'def_address_id' => $addressId,
            ]);
        }
    }
}
