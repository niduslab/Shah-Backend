<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'first_name' => 'Shah',
            'last_name' => 'Admin',
            'email' => 'admin@shahsports.com',
            'phone' => '+8801700000001',
            'user_type' => 'admin',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'status' => true,
        ]);

        // Sample customers
        $customers = [
            ['first_name' => 'Rahim', 'last_name' => 'Ahmed', 'email' => 'rahim@example.com', 'phone' => '+8801711111111'],
            ['first_name' => 'Karim', 'last_name' => 'Hassan', 'email' => 'karim@example.com', 'phone' => '+8801722222222'],
            ['first_name' => 'Fatima', 'last_name' => 'Begum', 'email' => 'fatima@example.com', 'phone' => '+8801733333333'],
            ['first_name' => 'Jamal', 'last_name' => 'Uddin', 'email' => 'jamal@example.com', 'phone' => '+8801744444444'],
            ['first_name' => 'Nadia', 'last_name' => 'Islam', 'email' => 'nadia@example.com', 'phone' => '+8801755555555'],
            ['first_name' => 'Sakib', 'last_name' => 'Khan', 'email' => 'sakib@example.com', 'phone' => '+8801766666666'],
            ['first_name' => 'Tamim', 'last_name' => 'Iqbal', 'email' => 'tamim@example.com', 'phone' => '+8801777777777'],
            ['first_name' => 'Mushfiq', 'last_name' => 'Rahman', 'email' => 'mushfiq@example.com', 'phone' => '+8801788888888'],
            ['first_name' => 'Liton', 'last_name' => 'Das', 'email' => 'liton@example.com', 'phone' => '+8801799999999'],
            ['first_name' => 'Taskin', 'last_name' => 'Ahmed', 'email' => 'taskin@example.com', 'phone' => '+8801700000010'],
        ];

        foreach ($customers as $customer) {
            User::create([
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'user_type' => 'customer',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => true,
            ]);
        }
    }
}
