# Notification System Quick Reference

## Quick Setup (5 Minutes)

### 1. Configure Pusher (.env)
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2126256
PUSHER_APP_KEY=a0b93b5b3a7936dfac19
PUSHER_APP_SECRET=635607736d756d2555e8
PUSHER_APP_CLUSTER=ap2
```

### 2. Run Migration
```bash
php artisan notifications:table
php artisan migrate
```

### 3. Enable Broadcasting
Uncomment in `config/app.php`:
```php
App\Providers\BroadcastServiceProvider::class,
```

### 4. Start Queue Worker
```bash
php artisan queue:work
```

## Notification Triggers

| Event | Notification | Recipients | Controller |
|-------|-------------|-----------|------------|
| Order placed | NewOrderNotification | Admins | CheckoutController@process |
| Order confirmed | OrderConfirmedNotification | Customer | CheckoutController@process |
| Order cancelled (by customer) | OrderCancelledNotification | Admins | OrderController@cancel |
| Order cancelled (by admin) | OrderCancelledNotification | Customer | Admin/OrderController@cancel |
| Order status changed | OrderStatusChangedNotification | Customer | Admin/OrderController@updateStatus |
| Review submitted | NewReviewNotification | Admins | ReviewController@store |
| Admin responds to review | ReviewResponseNotification | Customer | Admin/ReviewController@respond |
| Low stock | LowStockNotification | Admins | (Event listener) |
| Flash deal starting | FlashDealStartingNotification | Customers | (Scheduled) |

## Frontend Integration

### React Example
```jsx
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'a0b93b5b3a7936dfac19',
    cluster: 'ap2',
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    },
});

// Listen (Customer)
Echo.private(`user.${userId}`)
    .notification((notification) => {
        toast.success(notification.message);
    });

// Listen (Admin)
Echo.private('admin')
    .notification((notification) => {
        if (notification.type === 'new_order') {
            playSound();
        }
        toast.info(notification.message);
    });
```

### Vue Example
```javascript
// In component
mounted() {
  Echo.private(`user.${this.userId}`)
    .notification((notification) => {
      this.$toast.success(notification.message);
      this.notifications.unshift(notification);
    });
}
```

## API Endpoints

### Customer Endpoints
```
GET    /api/notifications              - List notifications
GET    /api/notifications/unread-count - Get unread count
POST   /api/notifications/{id}/mark-as-read - Mark as read
POST   /api/notifications/mark-all-as-read  - Mark all as read
DELETE /api/notifications/{id}         - Delete notification
POST   /api/notifications/clear        - Clear all
```

### Admin Endpoints
```
GET    /api/admin/notifications              - List admin notifications
GET    /api/admin/notifications/unread-count - Get unread count
POST   /api/admin/notifications/{id}/mark-as-read - Mark as read
POST   /api/admin/notifications/mark-all-as-read  - Mark all as read
DELETE /api/admin/notifications/{id}         - Delete notification
POST   /api/admin/notifications/clear        - Clear all
```

## Notification Channels

| Channel | Who Can Listen | Purpose |
|---------|---------------|---------|
| `user.{userId}` | Specific user | Personal notifications |
| `admin` | Admin users only | Admin notifications |

## Common Issues

### Not receiving notifications?
1. Check queue worker is running: `php artisan queue:work`
2. Verify Pusher credentials in `.env`
3. Check browser console for errors
4. Test with: `php artisan tinker` → `User::find(1)->notify(...)`

### Authentication failed?
1. Ensure Bearer token is sent in Echo config
2. Check `/broadcasting/auth` is accessible
3. Verify channel authorization in `routes/channels.php`

## Testing

```bash
# Test in tinker
php artisan tinker

# Send test notification
$user = App\Models\User::find(1);
$order = App\Models\Order::first();
$user->notify(new App\Notifications\OrderConfirmedNotification($order));
```

## Notification Data Structure

All notifications include:
- `type`: Notification type identifier
- `title`: Notification title
- `message`: Human-readable message
- `action_url`: URL to navigate to
- Additional context-specific fields

Example:
```json
{
  "type": "order_confirmed",
  "title": "Order Confirmed",
  "message": "Your order #SS20240311ABCD has been confirmed.",
  "order_id": 123,
  "order_number": "SS20240311ABCD",
  "action_url": "/orders/SS20240311ABCD"
}
```

## Production Checklist

- [ ] Set `QUEUE_CONNECTION=redis` in production
- [ ] Configure supervisor for queue workers
- [ ] Enable HTTPS for WebSocket connections
- [ ] Monitor Pusher usage and limits
- [ ] Implement notification preferences
- [ ] Add rate limiting
- [ ] Set up error logging

## Files Created

```
app/Notifications/
├── NewOrderNotification.php
├── OrderConfirmedNotification.php
├── OrderCancelledNotification.php
├── OrderStatusChangedNotification.php
├── NewReviewNotification.php
├── ReviewResponseNotification.php
├── LowStockNotification.php
└── FlashDealStartingNotification.php

app/Services/
└── NotificationService.php

routes/
└── channels.php (updated)

Controllers updated:
├── Api/CheckoutController.php
├── Api/OrderController.php
├── Api/ReviewController.php
├── Api/Admin/OrderController.php
└── Api/Admin/ReviewController.php
```
