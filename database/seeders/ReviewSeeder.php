<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->get();
        $products = Product::all();
        $orders = Order::all(); // Get all orders, not just delivered ones

        // Check if we have customers and orders
        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please run UserSeeder first.');
            return;
        }

        if ($orders->isEmpty()) {
            $this->command->error('No orders found. Please run OrderSeeder first.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please run ProductSeeder first.');
            return;
        }

        $reviewTitles = [
            5 => ['Excellent product!', 'Highly recommended!', 'Best purchase ever!', 'Amazing quality!', 'Love it!'],
            4 => ['Great product', 'Very good quality', 'Happy with purchase', 'Good value for money', 'Satisfied'],
            3 => ['Decent product', 'Average quality', 'Okay for the price', 'Could be better', 'Fair enough'],
            2 => ['Below expectations', 'Not great', 'Disappointed', 'Quality issues', 'Not worth it'],
            1 => ['Poor quality', 'Very disappointed', 'Waste of money', 'Do not buy', 'Terrible'],
        ];

        $reviewComments = [
            5 => [
                'This is exactly what I was looking for. The quality is outstanding and delivery was super fast!',
                'Exceeded my expectations. Will definitely buy from Shah Sports again.',
                'Premium quality product. Worth every taka spent.',
                'My son loves this! Perfect for his cricket practice.',
                'Authentic product with great build quality. Highly recommend Shah Sports!',
            ],
            4 => [
                'Good product overall. Minor issues with packaging but product is fine.',
                'Quality is good. Delivery took a bit longer than expected.',
                'Happy with the purchase. Good value for the price.',
                'Nice product. Would have given 5 stars if delivery was faster.',
                'Solid product. Does what it\'s supposed to do.',
            ],
            3 => [
                'Product is okay. Nothing special but does the job.',
                'Average quality. Expected better for this price.',
                'It\'s decent. Not bad but not great either.',
                'Okay product. Might look for alternatives next time.',
                'Fair quality. Delivery was good though.',
            ],
            2 => [
                'Quality is not as shown in pictures. Disappointed.',
                'Product arrived with minor defects. Not happy.',
                'Expected better quality for this brand.',
                'Not worth the price. Would not recommend.',
                'Below average quality. Customer service was helpful though.',
            ],
            1 => [
                'Very poor quality. Returning this product.',
                'Completely different from what was advertised.',
                'Waste of money. Do not buy this.',
                'Terrible experience. Product broke within a week.',
                'Worst purchase ever. Avoid this product.',
            ],
        ];

        $statuses = ['approved', 'approved', 'approved', 'pending', 'rejected']; // 60% approved

        // Create reviews for ALL products (at least 3 reviews per product)
        foreach ($products as $product) {
            $reviewCount = rand(3, 6); // At least 3 reviews, up to 6 per product
            
            // Get random customers for this product's reviews
            $selectedCustomers = $customers->random(min($reviewCount, $customers->count()));
            
            foreach ($selectedCustomers as $customer) {
                $rating = $this->weightedRating(); // More 4-5 star reviews
                $status = $statuses[array_rand($statuses)];
                
                // Get an order for this customer - prefer delivered orders, but use any if needed
                $order = $orders->where('user_id', $customer->id)
                    ->where('status', 'delivered')
                    ->first();
                
                // If no delivered order, use any order from this customer
                if (!$order) {
                    $order = $orders->where('user_id', $customer->id)->first();
                }
                
                // If still no order, use any random order (fallback)
                if (!$order) {
                    $order = $orders->random();
                }

                $review = Review::create([
                    'user_id' => $customer->id,
                    'product_id' => $product->id,
                    'order_id' => $order->id, // Always has a value now
                    'rating' => $rating,
                    'title' => $reviewTitles[$rating][array_rand($reviewTitles[$rating])],
                    'comment' => $reviewComments[$rating][array_rand($reviewComments[$rating])],
                    'helpful_count' => rand(0, 15),
                    'status' => $status,
                    'admin_response' => $status === 'approved' && rand(0, 3) === 0 
                        ? 'Thank you for your feedback! We appreciate your support.' 
                        : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
            }
        }

        $this->command->info('Reviews seeded successfully for all products!');
    }

    /**
     * Generate weighted rating (more 4-5 star reviews)
     */
    private function weightedRating(): int
    {
        $weights = [
            5 => 40,  // 40% chance
            4 => 35,  // 35% chance
            3 => 15,  // 15% chance
            2 => 7,   // 7% chance
            1 => 3,   // 3% chance
        ];

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $rating => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $rating;
            }
        }

        return 5;
    }
}
