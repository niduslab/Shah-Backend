<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariationOptionDescription;
use App\Models\VariationOption;
use Faker\Factory as Faker;

class VariationOptionDescriptionsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (VariationOption::all() as $variationOption) {
            VariationOptionDescription::create([
                'variation_option_id' => $variationOption->id,
                'price' => $faker->randomFloat(2, 10, 1000), // Random price between 10 and 1000
                'image_ids' => json_encode([$faker->randomNumber(), $faker->randomNumber()]), // Random image IDs
            ]);
        }
    }
}
