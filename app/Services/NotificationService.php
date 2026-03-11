<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Product;
use App\Models\FlashDeal;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\OrderConfirmedNotification;
use App\Notifications\NewReviewNotification;
use App\Notifications\ReviewResponseNotification;
use App\Notifications\LowStockNotification;
use App\Notifications\FlashDealStartingNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notify admins about new order.
     */
    public function notifyNewOrder(Order $order): void
    {
        $admins = User::where('user_type', 'admin')->get();
        Notification::send($admins, new NewOrderNotification($order));
    }

    /**
     * Notify customer about order confirmation.
     */
    public function notifyOrderConfirmed(Order $order): void
    {
        if ($order->user) {
            $order->user->notify(new OrderConfirmedNotification($order));
        }
    }

    /**
     * Notify about order cancellation.
     */
    public function notifyOrderCancelled(Order $order, string $cancelledBy = 'customer'): void
    {
        // Notify admins
        $admins = User::where('user_type', 'admin')->get();
        Notification::send($admins, new OrderCancelledNotification($order, $cancelledBy));

        // Notify customer if cancelled by admin
        if ($cancelledBy === 'admin' && $order->user) {
            $order->user->notify(new OrderCancelledNotification($order, $cancelledBy));
        }
    }

    /**
     * Notify customer about order status change.
     */
    public function notifyOrderStatusChanged(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($order->user) {
            $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus, $newStatus));
        }
    }

    /**
     * Notify admins about new review.
     */
    public function notifyNewReview(Review $review): void
    {
        $admins = User::where('user_type', 'admin')->get();
        Notification::send($admins, new NewReviewNotification($review));
    }

    /**
     * Notify customer about admin response to review.
     */
    public function notifyReviewResponse(Review $review): void
    {
        if ($review->user) {
            $review->user->notify(new ReviewResponseNotification($review));
        }
    }

    /**
     * Notify admins about low stock.
     */
    public function notifyLowStock(Product $product, int $currentStock): void
    {
        $admins = User::where('user_type', 'admin')->get();
        Notification::send($admins, new LowStockNotification($product, $currentStock));
    }

    /**
     * Notify customers about flash deal starting.
     */
    public function notifyFlashDealStarting(FlashDeal $flashDeal): void
    {
        // Get customers who have wishlisted products in this flash deal
        $productIds = $flashDeal->products()->pluck('products.id');
        
        $customers = User::whereHas('wishlists', function ($query) use ($productIds) {
            $query->whereIn('product_id', $productIds);
        })->get();

        Notification::send($customers, new FlashDealStartingNotification($flashDeal));
    }

    /**
     * Broadcast notification to specific user channel.
     */
    public function broadcastToUser(User $user, array $data): void
    {
        broadcast(new \App\Events\NotificationEvent((object) [
            'user_id' => $user->id,
            'data' => $data,
        ]));
    }

    /**
     * Broadcast notification to admin channel.
     */
    public function broadcastToAdmins(array $data): void
    {
        $admins = User::where('user_type', 'admin')->get();
        
        foreach ($admins as $admin) {
            $this->broadcastToUser($admin, $data);
        }
    }
}
