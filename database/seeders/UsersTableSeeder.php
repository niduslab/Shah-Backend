<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'fname' => 'John',
            'lname' => 'Doe',
            'email' => 'user@niduscart.com',
            'user_type' => 'user',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // Hashed password
            'status' => true,
        ]);

        User::create([
            'fname' => 'Jane',
            'lname' => 'Smith',
            'email' => 'vendor@niduscart.com',
            'user_type' => 'vendor',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'status' => true,
        ]);

        User::create([
            'fname' => 'Michael',
            'lname' => 'Johnson',
            'email' => 'admin@niduscart.com',
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'status' => true,
        ]);

        User::create([
            'fname' => 'Emily',
            'lname' => 'Davis',
            'email' => 'user2@niduscart.com',
            'user_type' => 'user',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'status' => true,
        ]);
    }
}
