# WebSocket Real-Time Notification System

Complete guide for implementing real-time notifications using Pusher and WebSockets in your Laravel e-commerce application.

## Overview

This system provides real-time notifications for:
- **Admin Notifications**: New orders, order cancellations, new reviews, low stock alerts
- **Customer Notifications**: Order confirmations, order status changes, review responses, flash deal alerts

## Setup Instructions

### 1. Install Dependencies

The required packages are already installed:
- `pusher/pusher-php-server` - Pusher PHP SDK
- Laravel Broadcasting support (built-in)

### 2. Configure Pusher

#### Get Pusher Credentials
1. Sign up at [https://pusher.com](https://pusher.com)
2. Create a new Channels app
3. Get your credentials from the App Keys section

#### Update .env File

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=2126256
PUSHER_APP_KEY=a0b93b5b3a7936dfac19
PUSHER_APP_SECRET=635607736d756d2555e8
PUSHER_APP_CLUSTER=ap2

# Optional: For self-hosted solutions
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### 3. Enable Broadcasting

Uncomment the BroadcastServiceProvider in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\BroadcastServiceProvider::class,
],
```

### 4. Run Database Migration

Create the notifications table:

```bash
php artisan notifications:table
php artisan migrate
```

### 5. Queue Configuration (Recommended)

For better performance, configure queues in `.env`:

```env
QUEUE_CONNECTION=database  # or redis for production
```

Run the queue worker:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## Notification Types

### 1. New Order Notification (Admin)
**Trigger**: When a customer places an order
**Recipients**: All admins
**Channel**: `admin`
**Data**:
```json
{
  "type": "new_order",
  "title": "New Order Received",
  "message": "New order #SS20240311ABCD has been placed.",
  "order_id": 123,
  "order_number": "SS20240311ABCD",
  "total_amount": 1500.00,
  "customer_name": "John Doe",
  "action_url": "/admin/orders/123"
}
```

### 2. Order Confirmed Notification (Customer)
**Trigger**: After successful order placement
**Recipients**: Order customer
**Channel**: `user.{userId}`
**Data**:
```json
{
  "type": "order_confirmed",
  "title": "Order Confirmed",
  "message": "Your order #SS20240311ABCD has been confirmed.",
  "order_id": 123,
  "order_number": "SS20240311ABCD",
  "total_amount": 1500.00,
  "action_url": "/orders/SS20240311ABCD"
}
```

### 3. Order Cancelled Notification
**Trigger**: When order is cancelled by customer or admin
**Recipients**: Admins (if cancelled by customer) or Customer (if cancelled by admin)
**Channel**: `admin` or `user.{userId}`
**Data**:
```json
{
  "type": "order_cancelled",
  "title": "Order Cancelled",
  "message": "Order #SS20240311ABCD has been cancelled.",
  "order_id": 123,
  "order_number": "SS20240311ABCD",
  "cancelled_by": "customer",
  "action_url": "/admin/orders/123"
}
```

### 4. Order Status Changed Notification (Customer)
**Trigger**: When admin updates order status
**Recipients**: Order customer
**Channel**: `user.{userId}`
**Data**:
```json
{
  "type": "order_status_changed",
  "title": "Order Status Updated",
  "message": "Your order #SS20240311ABCD has been shipped.",
  "order_id": 123,
  "order_number": "SS20240311ABCD",
  "old_status": "processing",
  "new_status": "shipped",
  "tracking_number": "TRACK123456",
  "action_url": "/orders/SS20240311ABCD"
}
```

### 5. New Review Notification (Admin)
**Trigger**: When customer submits a product review
**Recipients**: All admins
**Channel**: `admin`
**Data**:
```json
{
  "type": "new_review",
  "title": "New Product Review",
  "message": "John Doe left a 5-star review on Product Name.",
  "review_id": 45,
  "product_id": 12,
  "product_name": "Product Name",
  "rating": 5,
  "reviewer_name": "John Doe",
  "action_url": "/admin/reviews/45"
}
```

### 6. Review Response Notification (Customer)
**Trigger**: When admin responds to a customer review
**Recipients**: Review author
**Channel**: `user.{userId}`
**Data**:
```json
{
  "type": "review_response",
  "title": "Admin Responded to Your Review",
  "message": "Admin has responded to your review on Product Name.",
  "review_id": 45,
  "product_id": 12,
  "product_name": "Product Name",
  "action_url": "/products/product-slug#review-45"
}
```

### 7. Low Stock Alert (Admin)
**Trigger**: When product stock falls below threshold
**Recipients**: All admins
**Channel**: `admin`
**Data**:
```json
{
  "type": "low_stock",
  "title": "Low Stock Alert",
  "message": "Product Name is running low on stock. Only 5 units remaining.",
  "product_id": 12,
  "product_name": "Product Name",
  "current_stock": 5,
  "action_url": "/admin/products/12"
}
```

### 8. Flash Deal Starting Notification (Customer)
**Trigger**: Before flash deal starts (scheduled)
**Recipients**: Customers who wishlisted products in the deal
**Channel**: `user.{userId}`
**Data**:
```json
{
  "type": "flash_deal_starting",
  "title": "Flash Deal Starting Soon!",
  "message": "Summer Sale is starting soon! Don't miss out.",
  "flash_deal_id": 7,
  "flash_deal_title": "Summer Sale",
  "start_time": "2024-03-15 10:00:00",
  "end_time": "2024-03-15 18:00:00",
  "action_url": "/flash-deals/7"
}
```

## Frontend Integration

### 1. Install Pusher JS

```bash
npm install pusher-js laravel-echo
```

### 2. Configure Laravel Echo

Create or update `resources/js/echo.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'a0b93b5b3a7936dfac19',
    cluster: 'ap2',
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json',
        },
    },
});
```

### 3. Listen to Notifications (Customer)

```javascript
// After user login, subscribe to their private channel
const userId = user.id;

Echo.private(`user.${userId}`)
    .notification((notification) => {
        console.log('Notification received:', notification);
        
        // Display notification
        showNotification(notification);
        
        // Update notification badge
        updateNotificationBadge();
        
        // Handle specific notification types
        switch(notification.type) {
            case 'order_confirmed':
                // Show success message
                break;
            case 'order_status_changed':
                // Update order status in UI
                break;
            case 'review_response':
                // Show admin response
                break;
        }
    });
```

### 4. Listen to Notifications (Admin)

```javascript
// Admin dashboard - listen to admin channel
Echo.private('admin')
    .notification((notification) => {
        console.log('Admin notification:', notification);
        
        // Display notification
        showAdminNotification(notification);
        
        // Update notification badge
        updateAdminNotificationBadge();
        
        // Handle specific notification types
        switch(notification.type) {
            case 'new_order':
                // Play sound, show popup
                playNotificationSound();
                showNewOrderPopup(notification);
                break;
            case 'new_review':
                // Update review count
                break;
            case 'low_stock':
                // Show urgent alert
                showUrgentAlert(notification);
                break;
        }
    });
```

### 5. Example React Component

```jsx
import { useEffect, useState } from 'react';
import Echo from 'laravel-echo';

function NotificationBell({ userId }) {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        // Subscribe to user's private channel
        const channel = Echo.private(`user.${userId}`);
        
        channel.notification((notification) => {
            setNotifications(prev => [notification, ...prev]);
            setUnreadCount(prev => prev + 1);
            
            // Show toast notification
            toast.success(notification.message);
        });

        // Cleanup
        return () => {
            channel.stopListening('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated');
        };
    }, [userId]);

    return (
        <div className="notification-bell">
            <button onClick={() => setShowDropdown(!showDropdown)}>
                <BellIcon />
                {unreadCount > 0 && (
                    <span className="badge">{unreadCount}</span>
                )}
            </button>
            {/* Notification dropdown */}
        </div>
    );
}
```

### 6. Example Vue Component

```vue
<template>
  <div class="notification-bell">
    <button @click="toggleDropdown">
      <bell-icon />
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
    </button>
    <div v-if="showDropdown" class="notifications-dropdown">
      <div v-for="notification in notifications" :key="notification.id">
        {{ notification.message }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      notifications: [],
      unreadCount: 0,
      showDropdown: false
    }
  },
  mounted() {
    Echo.private(`user.${this.userId}`)
      .notification((notification) => {
        this.notifications.unshift(notification);
        this.unreadCount++;
        this.$toast.success(notification.message);
      });
  }
}
</script>
```

## API Endpoints

### Get Notifications
```
GET /api/notifications
Authorization: Bearer {token}
```

Response:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "type": "App\\Notifications\\OrderConfirmedNotification",
        "data": {
          "type": "order_confirmed",
          "title": "Order Confirmed",
          "message": "Your order has been confirmed."
        },
        "read_at": null,
        "created_at": "2024-03-11T10:00:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 50
  }
}
```

### Get Unread Count
```
GET /api/notifications/unread-count
Authorization: Bearer {token}
```

### Mark as Read
```
POST /api/notifications/{id}/mark-as-read
Authorization: Bearer {token}
```

### Mark All as Read
```
POST /api/notifications/mark-all-as-read
Authorization: Bearer {token}
```

### Delete Notification
```
DELETE /api/notifications/{id}
Authorization: Bearer {token}
```

### Clear All Notifications
```
POST /api/notifications/clear
Authorization: Bearer {token}
```

## Testing

### Test Pusher Connection

```bash
php artisan tinker
```

```php
// Test broadcasting
$user = App\Models\User::find(1);
$user->notify(new App\Notifications\OrderConfirmedNotification($order));
```

### Test with Pusher Debug Console

1. Go to your Pusher dashboard
2. Open the Debug Console
3. Trigger a notification
4. Watch for events in real-time

## Troubleshooting

### Notifications not broadcasting

1. Check `.env` configuration
2. Verify BroadcastServiceProvider is enabled
3. Check queue worker is running: `php artisan queue:work`
4. Verify Pusher credentials are correct
5. Check browser console for JavaScript errors

### Authentication issues

1. Ensure `/broadcasting/auth` endpoint is accessible
2. Verify Bearer token is being sent in headers
3. Check channel authorization in `routes/channels.php`

### Performance optimization

1. Use Redis for queue driver in production
2. Enable Pusher batching for multiple notifications
3. Implement notification preferences (allow users to opt-out)
4. Use database indexes on notifications table

## Production Checklist

- [ ] Configure Pusher with production credentials
- [ ] Set up Redis for queue driver
- [ ] Configure supervisor for queue workers
- [ ] Enable HTTPS for secure WebSocket connections
- [ ] Implement rate limiting for notifications
- [ ] Add notification preferences for users
- [ ] Set up monitoring for Pusher usage
- [ ] Test notification delivery under load
- [ ] Implement notification archiving strategy
- [ ] Add analytics for notification engagement

## Additional Features

### Notification Preferences

Allow users to control which notifications they receive:

```php
// Add to users table migration
$table->json('notification_preferences')->nullable();
```

### Notification Sounds

Add sound alerts for important notifications:

```javascript
function playNotificationSound() {
    const audio = new Audio('/sounds/notification.mp3');
    audio.play();
}
```

### Desktop Notifications

Request permission and show browser notifications:

```javascript
if (Notification.permission === 'granted') {
    new Notification(notification.title, {
        body: notification.message,
        icon: '/logo.png'
    });
}
```

## Support

For issues or questions:
- Check Laravel Broadcasting documentation
- Visit Pusher documentation
- Review Laravel Notifications guide
