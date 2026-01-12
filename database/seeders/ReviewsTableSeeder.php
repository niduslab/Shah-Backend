<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Faker\Factory as Faker;

class ReviewsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Get all users and products
        $users = User::all();
        $products = Product::all();

        foreach ($products as $product) {
            // Randomly select a user for each review
            foreach ($users->random(rand(1, 4)) as $user) {
                Review::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'user_name' => $user->fname . ' ' . $user->lname, // Corrected string concatenation
                    'rating' => rand(1, 5), // Random rating between 1 and 5
                    'comment' => $faker->sentence(rand(10, 20)), // Random comment
                    'status' => $faker->randomElement(['pending', 'approved', 'rejected']), // Random status
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
