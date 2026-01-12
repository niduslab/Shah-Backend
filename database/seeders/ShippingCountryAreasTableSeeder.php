<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingCountryArea;
use Faker\Factory as Faker;
use DB;

class ShippingCountryAreasTableSeeder extends Seeder
{
    public function run()
    {
        $areas = [
            'North East',
            'North West',
            'South East',
            'South West',
            'Central',
            'Eastern',
            'Western',
        ];

        foreach ($areas as $area) {
            DB::table('shipping_country_areas')->insert([
                'area_name' => $area,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

