<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Faker\Factory as Faker;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (User::all() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'notification_type' => $faker->randomElement(['status', 'promotion']),
                'title' => $faker->sentence,
                'message' => $faker->paragraph,
            ]);
        }
    }
}

