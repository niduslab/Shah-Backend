<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    protected $signature = 'notifications:test {type?}';
    protected $description = 'Test notification system';

    public function __construct(
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $type = $this->argument('type');

        if (!$type) {
            $type = $this->choice(
                'Which notification would you like to test?',
                [
                    'new_order',
                    'order_confirmed',
                    'order_cancelled',
                    'order_status_changed',
                    'new_review',
                    'review_response',
                    'low_stock',
                    'all'
                ],
                0
            );
        }

        if ($type === 'all') {
            $this->testAll();
            return 0;
        }

        $this->{'test' . str_replace('_', '', ucwords($type, '_'))}();
        return 0;
    }

    protected function testNewOrder()
    {
        $order = Order::first();
        if (!$order) {
            $this->error('No orders found. Please create an order first.');
            return;
        }

        $this->notificationService->notifyNewOrder($order);
        $this->info("✅ New Order notification sent to admins for order #{$order->order_number}");
    }

    protected function testOrderConfirmed()
    {
        $order = Order::whereNotNull('user_id')->first();
        if (!$order) {
            $this->error('No orders with users found.');
            return;
        }

        $this->notificationService->notifyOrderConfirmed($order);
        $this->info("✅ Order Confirmed notification sent to customer for order #{$order->order_number}");
    }

    protected function testOrderCancelled()
    {
        $order = Order::whereNotNull('user_id')->first();
        if (!$order) {
            $this->error('No orders with users found.');
            return;
        }

        $this->notificationService->notifyOrderCancelled($order, 'customer');
        $this->info("✅ Order Cancelled notification sent for order #{$order->order_number}");
    }

    protected function testOrderStatusChanged()
    {
        $order = Order::whereNotNull('user_id')->first();
        if (!$order) {
            $this->error('No orders with users found.');
            return;
        }

        $this->notificationService->notifyOrderStatusChanged($order, 'pending', 'shipped');
        $this->info("✅ Order Status Changed notification sent for order #{$order->order_number}");
    }

    protected function testNewReview()
    {
        $review = Review::with(['user', 'product'])->first();
        if (!$review) {
            $this->error('No reviews found. Please create a review first.');
            return;
        }

        $this->notificationService->notifyNewReview($review);
        $this->info("✅ New Review notification sent to admins for review #{$review->id}");
    }

    protected function testReviewResponse()
    {
        $review = Review::with(['user', 'product'])->first();
        if (!$review) {
            $this->error('No reviews found. Please create a review first.');
            return;
        }

        $this->notificationService->notifyReviewResponse($review);
        $this->info("✅ Review Response notification sent to customer for review #{$review->id}");
    }

    protected function testLowStock()
    {
        $product = Product::first();
        if (!$product) {
            $this->error('No products found.');
            return;
        }

        $this->notificationService->notifyLowStock($product, 5);
        $this->info("✅ Low Stock notification sent to admins for product: {$product->name}");
    }

    protected function testAll()
    {
        $this->info('Testing all notifications...');
        $this->newLine();

        $this->testNewOrder();
        $this->testOrderConfirmed();
        $this->testOrderCancelled();
        $this->testOrderStatusChanged();
        $this->testNewReview();
        $this->testReviewResponse();
        $this->testLowStock();

        $this->newLine();
        $this->info('✅ All notifications tested successfully!');
        $this->info('Check your Pusher Debug Console to see the events.');
    }
}
