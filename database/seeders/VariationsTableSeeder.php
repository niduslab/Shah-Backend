<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variation;

class VariationsTableSeeder extends Seeder
{
    public function run()
    {
        $variations = [
            'Color',
            'Size',
            'Storage',
            'Condition',
            'Warranty',
        ];

        foreach ($variations as $variation) {
            Variation::create([
                'name' => $variation,
            ]);
        }
    }
}
