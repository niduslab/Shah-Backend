<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\ShippingCountry;
use App\Models\ShippingCountryArea;
use Faker\Factory as Faker;
use DB;

class ShippingRatesTableSeeder extends Seeder
{
    public function run()
    {
        // Get IDs for related tables
        $zoneIds = DB::table('shipping_zones')->pluck('id');
        $shopIds = DB::table('vendor_shops')->pluck('id');
        $countryIds = DB::table('shipping_countries')->pluck('id');
        $countryAreaIds = DB::table('shipping_country_areas')->pluck('id');

        // Generate sample shipping rates
        for ($i = 1; $i <= 20; $i++) {
            DB::table('shipping_rates')->insert([
                'zone_id' => $zoneIds->random(),
                'shop_id' => $shopIds->isNotEmpty() ? $shopIds->random() : null,
                'country_id' => $countryIds->random(),
                'country_area_id' => $countryAreaIds->random(),
                'weight_type' => ['kg', 'lb'][array_rand(['kg', 'lb'])],
                'min_weight' => rand(1, 10) + rand(0, 99) / 100,
                'max_weight' => rand(11, 50) + rand(0, 99) / 100,
                'rate_amount' => rand(5, 30) + rand(0, 99) / 100,
                'shipping_method' => ['Standard', 'Express', 'Overnight'][array_rand(['Standard', 'Express', 'Overnight'])],
                'delivery_time' => rand(1, 7) . ' days',
                'handling_fee' => rand(1, 5) + rand(0, 99) / 100,
                'free_shipping_threshold' => rand(50, 200) + rand(0, 99) / 100,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}


