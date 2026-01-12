<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;
use App\Models\Image;
use Faker\Factory as Faker;

class MessagesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $users = User::pluck('id')->toArray();
        $images = Image::pluck('id')->toArray();

        foreach (range(1, 50) as $index) {
            Message::create([
                'sender_id' => $faker->randomElement($users),
                'receiver_id' => $faker->randomElement($users),
                'image_id' => $faker->randomElement($images),
                'message' => $faker->sentence,
                'status' => $faker->randomElement(['read', 'unread']),
            ]);
        }
    }
}

