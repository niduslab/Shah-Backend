<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingZone;
use App\Models\User;
use Faker\Factory as Faker;
use Auth;
use DB;

class ShippingZonesTableSeeder extends Seeder
{
    public function run()
    {
        $userId = Auth::id() ?? 1; // Default to 1 if no user is authenticated

        $zones = [
            'North America',
            'Europe',
            'Asia',
            'Oceania',
            'South America',
            'Africa',
        ];

        foreach ($zones as $zone) {
            DB::table('shipping_zones')->insert([
                'created_by' => $userId,
                'zone_name' => $zone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

