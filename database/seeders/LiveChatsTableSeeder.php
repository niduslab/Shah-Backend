<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LiveChat;

class LiveChatsTableSeeder extends Seeder
{
    public function run()
    {
        // Generate LiveChats
        for ($i = 0; $i < 50; $i++) {
            LiveChat::create([
                'user_identifier' => rand(1, 100), // Assuming user identifier range
                'status' => 'close', // Default status
            ]);
        }
    }
}

