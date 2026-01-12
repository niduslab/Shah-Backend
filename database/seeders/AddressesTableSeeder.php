<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address;

class AddressesTableSeeder extends Seeder
{
    public function run()
    {
        Address::create([
            'user_id' => 1,
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Apt 4B',
            'contact_no' => '123-456-7890',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'address_type' => 'user_address'
        ]);

        Address::create([
            'user_id' => 2,
            'address_line_1' => '456 Market St',
            'address_line_2' => null,
            'contact_no' => '987-654-3210',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90001',
            'address_type' => 'shipping_address'
        ]);

        Address::create([
            'user_id' => 3,
            'address_line_1' => '789 Broadway Ave',
            'address_line_2' => null,
            'contact_no' => '555-123-4567',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip_code' => '94103',
            'address_type' => 'billing_address'
        ]);

        Address::create([
            'user_id' => 4,
            'address_line_1' => '101 First St',
            'address_line_2' => 'Suite 5A',
            'contact_no' => '111-222-3333',
            'city' => 'Chicago',
            'state' => 'IL',
            'zip_code' => '60601',
            'address_type' => 'user_address'
        ]);
    }
}
