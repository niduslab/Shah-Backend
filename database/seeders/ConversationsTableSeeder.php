<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\User;
use App\Models\LiveChat;

class ConversationsTableSeeder extends Seeder
{
    public function run()
    {
        // Get all user IDs and live chat IDs
        $users = User::pluck('id')->toArray();
        $liveChats = LiveChat::pluck('id')->toArray();

        // Generate Conversations
        for ($i = 0; $i < 50; $i++) {
            Conversation::create([
                'sender_id' => $users[array_rand($users)],
                'receiver_id' => $users[array_rand($users)],
                'live_chat_id' => $liveChats[array_rand($liveChats)],
                'messages' => 'Sample message', // You may change this according to your needs
            ]);
        }
    }
}

