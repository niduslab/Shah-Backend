# Notification System Testing Guide

Complete guide for testing the real-time notification system.

## Prerequisites

1. ✅ Pusher configured in `.env`
2. ✅ Queue worker running: `php artisan queue:work`
3. ✅ Scheduler running (for flash deals): `php artisan schedule:work`
4. ✅ Frontend Echo configured

## Backend Testing

### 1. Test with Tinker

```bash
php artisan tinker
```

#### Test Customer Notifications

```php
// Get a test user and order
$user = App\Models\User::where('user_type', 'customer')->first();
$order = App\Models\Order::first();

// Test Order Confirmed Notification
$user->notify(new App\Notifications\OrderConfirmedNotification($order));

// Test Order Status Changed Notification
$user->notify(new App\Notifications\OrderStatusChangedNotification($order, 'pending', 'shipped'));

// Test Review Response Notification
$review = App\Models\Review::first();
$user->notify(new App\Notifications\ReviewResponseNotification($review));
```

#### Test Admin Notifications

```php
// Get all admins
$admins = App\Models\User::where('user_type', 'admin')->get();

// Test New Order Notification
Illuminate\Support\Facades\Notification::send($admins, new App\Notifications\NewOrderNotification($order));

// Test New Review Notification
$review = App\Models\Review::first();
Illuminate\Support\Facades\Notification::send($admins, new App\Notifications\NewReviewNotification($review));

// Test Low Stock Notification
$product = App\Models\Product::first();
Illuminate\Support\Facades\Notification::send($admins, new App\Notifications\LowStockNotification($product, 5));
```

### 2. Test via API Endpoints

#### Place an Order (Triggers New Order + Order Confirmed)

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1,
        "price": 100
      }
    ],
    "shipping_address_id": 1,
    "shipping_method": "standard",
    "payment_method": "cod"
  }'
```

#### Cancel Order (Triggers Order Cancelled)

```bash
curl -X POST http://127.0.0.1:8000/api/orders/SS20240311ABCD/cancel \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "reason": "Changed my mind"
  }'
```

#### Update Order Status (Triggers Order Status Changed)

```bash
curl -X PUT http://127.0.0.1:8000/api/admin/orders/1/status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -d '{
    "status": "shipped"
  }'
```

#### Submit Review (Triggers New Review)

```bash
curl -X POST http://127.0.0.1:8000/api/reviews \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "product_id": 1,
    "rating": 5,
    "title": "Great product!",
    "comment": "Really satisfied with this purchase."
  }'
```

#### Respond to Review (Triggers Review Response)

```bash
curl -X POST http://127.0.0.1:8000/api/admin/reviews/1/respond \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -d '{
    "admin_response": "Thank you for your feedback!"
  }'
```

### 3. Test Flash Deal Notifications

```bash
# Run the command manually
php artisan notifications:flash-deals

# Or test in tinker
php artisan tinker
```

```php
$flashDeal = App\Models\FlashDeal::first();
$notificationService = app(App\Services\NotificationService::class);
$notificationService->notifyFlashDealStarting($flashDeal);
```

### 4. Check Notification Database

```bash
php artisan tinker
```

```php
// Check notifications table
DB::table('notifications')->count();

// Get recent notifications
DB::table('notifications')->latest()->take(5)->get();

// Get unread notifications for a user
$user = App\Models\User::find(1);
$user->unreadNotifications;

// Get all notifications for a user
$user->notifications;
```

## Frontend Testing

### 1. Test Echo Connection

Open browser console and run:

```javascript
// Check if Echo is initialized
console.log(window.Echo);

// Check Pusher connection state
console.log(window.Echo.connector.pusher.connection.state);
// Should output: "connected"
```

### 2. Test Customer Notifications

```javascript
// Subscribe to user channel
const userId = 1; // Replace with actual user ID

Echo.private(`user.${userId}`)
    .notification((notification) => {
        console.log('Notification received:', notification);
        alert(`New notification: ${notification.message}`);
    });

// Now trigger a notification from backend (via API or tinker)
// You should see the alert
```

### 3. Test Admin Notifications

```javascript
// Subscribe to admin channel (must be logged in as admin)
Echo.private('admin')
    .notification((notification) => {
        console.log('Admin notification:', notification);
        alert(`Admin notification: ${notification.message}`);
    });

// Trigger a new order from another browser/user
// Admin should receive notification
```

### 4. Test Notification API

```javascript
// Get notifications
fetch('/api/notifications', {
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN',
        'Accept': 'application/json'
    }
})
.then(r => r.json())
.then(data => console.log(data));

// Get unread count
fetch('/api/notifications/unread-count', {
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN',
        'Accept': 'application/json'
    }
})
.then(r => r.json())
.then(data => console.log('Unread:', data.data.unread_count));

// Mark as read
fetch('/api/notifications/NOTIFICATION_ID/mark-as-read', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN',
        'Accept': 'application/json'
    }
})
.then(r => r.json())
.then(data => console.log(data));
```

## Pusher Dashboard Testing

### 1. Access Debug Console

1. Go to [https://dashboard.pusher.com](https://dashboard.pusher.com)
2. Select your app (ID: 2126256)
3. Click on "Debug Console"

### 2. Monitor Events

When you trigger a notification, you should see events like:

```
Channel: private-user.1
Event: Illuminate\Notifications\Events\BroadcastNotificationCreated
Data: {
  "type": "order_confirmed",
  "title": "Order Confirmed",
  "message": "Your order has been confirmed."
}
```

### 3. Test Connection

In Debug Console, you can see:
- Active connections
- Channel subscriptions
- Events being broadcast

## Testing Scenarios

### Scenario 1: Complete Order Flow

1. **Customer places order**
   - ✅ Admin receives "New Order" notification
   - ✅ Customer receives "Order Confirmed" notification

2. **Admin updates order to "processing"**
   - ✅ Customer receives "Order Status Changed" notification

3. **Admin updates order to "shipped"**
   - ✅ Customer receives "Order Status Changed" notification with tracking

4. **Admin updates order to "delivered"**
   - ✅ Customer receives "Order Status Changed" notification

### Scenario 2: Order Cancellation

1. **Customer cancels order**
   - ✅ Admin receives "Order Cancelled" notification

2. **Admin cancels order**
   - ✅ Customer receives "Order Cancelled" notification

### Scenario 3: Review Flow

1. **Customer submits review**
   - ✅ Admin receives "New Review" notification

2. **Admin responds to review**
   - ✅ Customer receives "Review Response" notification

### Scenario 4: Inventory Alert

1. **Product stock falls below threshold**
   - ✅ Admin receives "Low Stock" notification

### Scenario 5: Flash Deal

1. **Flash deal starts in 30 minutes**
   - ✅ Customers with wishlisted products receive notification

## Troubleshooting Tests

### Test 1: Queue is Working

```bash
# Check queue jobs
php artisan queue:work --once

# Should process any pending jobs
```

### Test 2: Broadcasting is Enabled

```bash
php artisan config:show broadcasting.default
# Should output: pusher
```

### Test 3: Pusher Credentials

```bash
php artisan tinker
```

```php
config('broadcasting.connections.pusher.key');
config('broadcasting.connections.pusher.secret');
config('broadcasting.connections.pusher.app_id');
config('broadcasting.connections.pusher.options.cluster');
```

### Test 4: Channel Authorization

```bash
# Test with curl
curl -X POST http://127.0.0.1:8000/broadcasting/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "socket_id": "123.456",
    "channel_name": "private-user.1"
  }'

# Should return auth signature
```

### Test 5: Notification Storage

```bash
php artisan tinker
```

```php
// Check if notifications are being stored
$user = App\Models\User::find(1);
$user->notifications()->count();

// Should return number of notifications
```

## Performance Testing

### Test Concurrent Notifications

```php
// In tinker
$admins = App\Models\User::where('user_type', 'admin')->get();
$order = App\Models\Order::first();

// Send 100 notifications
for ($i = 0; $i < 100; $i++) {
    Illuminate\Support\Facades\Notification::send(
        $admins, 
        new App\Notifications\NewOrderNotification($order)
    );
}

// Check queue
DB::table('jobs')->count();
```

### Monitor Pusher Usage

1. Go to Pusher Dashboard
2. Check "Usage" tab
3. Monitor:
   - Messages sent
   - Connections
   - Channels

## Automated Testing

### Create Test Case

```php
// tests/Feature/NotificationTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class NotificationTest extends TestCase
{
    public function test_order_confirmed_notification_sent()
    {
        Notification::fake();

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $user->notify(new OrderConfirmedNotification($order));

        Notification::assertSentTo($user, OrderConfirmedNotification::class);
    }

    public function test_admin_receives_new_order_notification()
    {
        Notification::fake();

        $admin = User::factory()->create(['user_type' => 'admin']);
        $order = Order::factory()->create();

        Notification::send($admin, new NewOrderNotification($order));

        Notification::assertSentTo($admin, NewOrderNotification::class);
    }
}
```

Run tests:

```bash
php artisan test --filter NotificationTest
```

## Checklist

Before going to production, verify:

- [ ] Pusher credentials are correct
- [ ] Queue worker is running
- [ ] Scheduler is configured (cron job)
- [ ] Broadcasting is enabled
- [ ] Channel authorization works
- [ ] Notifications are stored in database
- [ ] Real-time delivery works
- [ ] Frontend Echo is configured
- [ ] All notification types tested
- [ ] Error handling works
- [ ] Performance is acceptable
- [ ] Pusher usage is within limits

## Common Issues & Solutions

### Issue: Notifications not real-time

**Solution:**
1. Check queue worker: `php artisan queue:work`
2. Verify Pusher credentials
3. Check browser console for errors
4. Test with Pusher Debug Console

### Issue: "Channel not authorized"

**Solution:**
1. Check `routes/channels.php` authorization
2. Verify Bearer token is sent
3. Check user permissions

### Issue: Notifications stored but not broadcast

**Solution:**
1. Verify `implements ShouldBroadcast` in notification class
2. Check `via()` method includes 'broadcast'
3. Verify queue worker is processing jobs

### Issue: High Pusher usage

**Solution:**
1. Implement notification batching
2. Add rate limiting
3. Use notification preferences
4. Consider upgrading Pusher plan

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log`
2. Check browser console
3. Check Pusher Debug Console
4. Review this testing guide
5. Check Laravel documentation

---

**Happy Testing! 🚀**
