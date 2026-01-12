<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingCountry;
use App\Models\ShippingZone;
use App\Models\ShippingCountryArea;
use Faker\Factory as Faker;
use DB;

class ShippingCountriesTableSeeder extends Seeder
{
    public function run()
    {
        // Get zone and country area IDs
        $zoneIds = DB::table('shipping_zones')->pluck('id');
        $countryAreaIds = DB::table('shipping_country_areas')->pluck('id');

        // Define sample countries and currencies
        $countries = [
            ['United States', 'USD'],
            ['Canada', 'CAD'],
            ['United Kingdom', 'GBP'],
            ['Germany', 'EUR'],
            ['Australia', 'AUD'],
            ['France', 'EUR'],
            ['Japan', 'JPY'],
            ['China', 'CNY'],
            ['India', 'INR'],
            ['Brazil', 'BRL'],
        ];

        foreach ($countries as $country) {
            DB::table('shipping_countries')->insert([
                'zone_id' => $zoneIds->random(),
                'country_area_id' => $countryAreaIds->random(),
                'country_name' => $country[0],
                'currency_code' => $country[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

