<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variation;
use App\Models\VariationOption;

class VariationOptionsTableSeeder extends Seeder
{
    public function run()
    {
        $options = [
            // Color options
            ['variation_name' => 'Color', 'values' => ['Black', 'White', 'Silver', 'Gold', 'Deep Purple']],

            // Size options
            ['variation_name' => 'Size', 'values' => ['Small', 'Medium', 'Large', 'X-Large']],

            // Storage options
            ['variation_name' => 'Storage', 'values' => ['64GB', '128GB', '256GB', '512GB', '1TB']],

            // Condition options
            ['variation_name' => 'Condition', 'values' => ['New', 'Refurbished', 'Used']],

            // Warranty options
            ['variation_name' => 'Warranty', 'values' => ['1 Year', '2 Years', '3 Years']],
        ];

        foreach ($options as $option) {
            $variation = Variation::where('name', $option['variation_name'])->first();

            if ($variation) {
                foreach ($option['values'] as $value) {
                    VariationOption::create([
                        'variation_id' => $variation->id,
                        'value' => $value,
                    ]);
                }
            }
        }
    }
}
