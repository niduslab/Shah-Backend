<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->get();

        $addresses = [
            [
                'address_line_1' => 'House 45, Road 12',
                'address_line_2' => 'Banani',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'zip_code' => '1213',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'Flat 3B, Building 78',
                'address_line_2' => 'Gulshan 2',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'zip_code' => '1212',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'House 23, Block C',
                'address_line_2' => 'Bashundhara R/A',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'zip_code' => '1229',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'Office Tower, Floor 5',
                'address_line_2' => 'Motijheel',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'zip_code' => '1000',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'House 12, Road 5',
                'address_line_2' => 'Dhanmondi',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'zip_code' => '1205',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'Flat 2A, Green Tower',
                'address_line_2' => 'Uttara Sector 7',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'zip_code' => '1230',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'House 67, Lane 3',
                'address_line_2' => 'Nasirabad',
                'city' => 'Chittagong',
                'state' => 'Chittagong Division',
                'zip_code' => '4000',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'Building 34, Road 2',
                'address_line_2' => 'Agrabad',
                'city' => 'Chittagong',
                'state' => 'Chittagong Division',
                'zip_code' => '4100',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'House 89, Block B',
                'address_line_2' => 'Zindabazar',
                'city' => 'Sylhet',
                'state' => 'Sylhet Division',
                'zip_code' => '3100',
                'address_type' => 'user_address',
            ],
            [
                'address_line_1' => 'Flat 5C, City Center',
                'address_line_2' => 'Shaheb Bazar',
                'city' => 'Rajshahi',
                'state' => 'Rajshahi Division',
                'zip_code' => '6000',
                'address_type' => 'user_address',
            ],
        ];

        foreach ($customers as $index => $customer) {
            $addressData = $addresses[$index % count($addresses)];
            $addressData['user_id'] = $customer->id;
            $addressData['contact_no'] = $customer->phone;
            
            Address::create($addressData);
            
            // Add a second address for some customers
            if ($index % 3 === 0) {
                $secondAddress = $addresses[($index + 3) % count($addresses)];
                $secondAddress['user_id'] = $customer->id;
                $secondAddress['contact_no'] = $customer->phone;
                $secondAddress['address_type'] = 'user_address';
                Address::create($secondAddress);
            }
        }
    }
}
