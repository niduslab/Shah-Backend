# 🔔 Real-Time Notification System

Complete real-time notification system for your e-commerce platform using Laravel, Pusher, and WebSockets.

## 📋 Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Notification Types](#notification-types)
- [Documentation](#documentation)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

## ✨ Features

### For Customers
- ✅ Order confirmation notifications
- ✅ Order status updates (processing, shipped, delivered)
- ✅ Order cancellation alerts
- ✅ Admin responses to reviews
- ✅ Flash deal starting alerts

### For Admins
- ✅ New order alerts
- ✅ Order cancellation notifications
- ✅ New review submissions
- ✅ Low stock alerts
- ✅ Real-time dashboard updates

### Technical Features
- ✅ Real-time WebSocket delivery via Pusher
- ✅ Database persistence for notification history
- ✅ Queue support for high performance
- ✅ Broadcasting to private channels
- ✅ RESTful API for notification management
- ✅ Scheduled notifications (flash deals)

## 🚀 Quick Start

### 1. Backend Setup (5 minutes)

```bash
# 1. Run migrations
php artisan notifications:table
php artisan migrate

# 2. Configure queue (optional but recommended)
php artisan queue:table
php artisan migrate

# 3. Start queue worker
php artisan queue:work

# 4. Start scheduler (for flash deals)
php artisan schedule:work
```

### 2. Environment Configuration

Your `.env` is already configured with Pusher:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2126256
PUSHER_APP_KEY=a0b93b5b3a7936dfac19
PUSHER_APP_SECRET=635607736d756d2555e8
PUSHER_APP_CLUSTER=ap2
```

### 3. Frontend Setup

```bash
# Install dependencies
npm install pusher-js laravel-echo
```

```javascript
// Configure Echo
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
            Authorization: `Bearer ${token}`,
        },
    },
});

// Listen to notifications
Echo.private(`user.${userId}`)
    .notification((notification) => {
        console.log(notification);
        // Show toast, update UI, etc.
    });
```

### 4. Test It!

```bash
# Test all notifications
php artisan notifications:test all

# Test specific notification
php artisan notifications:test new_order
```

## 📬 Notification Types

| Notification | Trigger | Recipients | Channel |
|-------------|---------|-----------|---------|
| New Order | Order placed | Admins | `admin` |
| Order Confirmed | Order placed | Customer | `user.{id}` |
| Order Cancelled | Order cancelled | Admins/Customer | `admin` or `user.{id}` |
| Order Status Changed | Status updated | Customer | `user.{id}` |
| New Review | Review submitted | Admins | `admin` |
| Review Response | Admin responds | Customer | `user.{id}` |
| Low Stock | Stock below threshold | Admins | `admin` |
| Flash Deal Starting | 30 min before deal | Customers | `user.{id}` |

## 📚 Documentation

### Complete Guides

1. **[WEBSOCKET_NOTIFICATION_GUIDE.md](WEBSOCKET_NOTIFICATION_GUIDE.md)**
   - Complete setup instructions
   - Detailed notification types
   - Frontend integration examples
   - Production checklist

2. **[NOTIFICATION_QUICK_REFERENCE.md](NOTIFICATION_QUICK_REFERENCE.md)**
   - Quick setup (5 minutes)
   - API endpoints
   - Common issues
   - Files created

3. **[NOTIFICATION_TESTING_GUIDE.md](NOTIFICATION_TESTING_GUIDE.md)**
   - Backend testing
   - Frontend testing
   - Pusher dashboard testing
   - Automated tests

4. **[NOTIFICATION_IMPLEMENTATION_SUMMARY.md](NOTIFICATION_IMPLEMENTATION_SUMMARY.md)**
   - What was implemented
   - Notification flow
   - Files created/modified
   - Next steps

5. **[frontend-notification-example.js](frontend-notification-example.js)**
   - React examples
   - Vue examples
   - Utility functions
   - Complete components

## 🧪 Testing

### Quick Test

```bash
# Test all notifications at once
php artisan notifications:test all
```

### Test Individual Notifications

```bash
php artisan notifications:test new_order
php artisan notifications:test order_confirmed
php artisan notifications:test order_cancelled
php artisan notifications:test order_status_changed
php artisan notifications:test new_review
php artisan notifications:test review_response
php artisan notifications:test low_stock
```

### Test via API

```bash
# Place an order (triggers notifications)
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":1,"quantity":1,"price":100}],"shipping_address_id":1,"shipping_method":"standard","payment_method":"cod"}'
```

### Monitor in Pusher Dashboard

1. Go to [Pusher Dashboard](https://dashboard.pusher.com)
2. Select your app (ID: 2126256)
3. Open Debug Console
4. Trigger a notification
5. Watch events in real-time

## 🔧 API Endpoints

All endpoints require authentication via Bearer token.

### Customer Endpoints

```
GET    /api/notifications              - List all notifications
GET    /api/notifications/unread-count - Get unread count
POST   /api/notifications/{id}/mark-as-read - Mark as read
POST   /api/notifications/mark-all-as-read  - Mark all as read
DELETE /api/notifications/{id}         - Delete notification
POST   /api/notifications/clear        - Clear all notifications
```

### Admin Endpoints

```
GET    /api/admin/notifications              - List all admin notifications
GET    /api/admin/notifications/unread-count - Get unread count
POST   /api/admin/notifications/{id}/mark-as-read - Mark as read
POST   /api/admin/notifications/mark-all-as-read  - Mark all as read
DELETE /api/admin/notifications/{id}         - Delete notification
POST   /api/admin/notifications/clear        - Clear all notifications
```

### Example Usage

```javascript
// Get notifications
const response = await fetch('/api/notifications', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});
const data = await response.json();

// Get unread count
const countResponse = await fetch('/api/notifications/unread-count', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});
const countData = await countResponse.json();
console.log('Unread:', countData.data.unread_count);
```

## 🐛 Troubleshooting

### Notifications not sending?

```bash
# Check queue worker
php artisan queue:work

# Check configuration
php artisan config:show broadcasting.default
# Should output: pusher

# Check logs
tail -f storage/logs/laravel.log
```

### WebSocket connection failed?

1. Verify Pusher credentials in `.env`
2. Check browser console for errors
3. Test with Pusher Debug Console
4. Ensure `/broadcasting/auth` is accessible

### Notifications not real-time?

1. Check Echo is initialized in frontend
2. Verify Bearer token is sent in headers
3. Check channel authorization in `routes/channels.php`
4. Test connection: `window.Echo.connector.pusher.connection.state`

### Common Issues

| Issue | Solution |
|-------|----------|
| "Channel not authorized" | Check Bearer token and channel authorization |
| Notifications stored but not broadcast | Verify queue worker is running |
| High latency | Use Redis for queue driver |
| Missing notifications | Check Pusher usage limits |

## 📁 Project Structure

```
app/
├── Notifications/              # 8 notification classes
│   ├── NewOrderNotification.php
│   ├── OrderConfirmedNotification.php
│   ├── OrderCancelledNotification.php
│   ├── OrderStatusChangedNotification.php
│   ├── NewReviewNotification.php
│   ├── ReviewResponseNotification.php
│   ├── LowStockNotification.php
│   └── FlashDealStartingNotification.php
├── Services/
│   └── NotificationService.php # Centralized notification service
├── Console/Commands/
│   ├── SendFlashDealNotifications.php
│   └── TestNotifications.php   # Testing command
└── Listeners/
    └── NotifyLowStock.php      # Low stock event listener

routes/
└── channels.php                # Broadcasting channels

Documentation/
├── README_NOTIFICATIONS.md
├── WEBSOCKET_NOTIFICATION_GUIDE.md
├── NOTIFICATION_QUICK_REFERENCE.md
├── NOTIFICATION_TESTING_GUIDE.md
├── NOTIFICATION_IMPLEMENTATION_SUMMARY.md
└── frontend-notification-example.js
```

## 🎯 Next Steps

### Immediate
1. ✅ Test notifications with `php artisan notifications:test all`
2. ✅ Integrate frontend notification bell component
3. ✅ Test with real user flows

### Short Term
1. Add notification preferences for users
2. Implement desktop notifications
3. Add notification sounds
4. Create notification templates

### Long Term
1. Add email notifications
2. Implement SMS notifications
3. Add notification analytics
4. Create notification scheduling

## 🔐 Security

- ✅ Private channels with authentication
- ✅ Bearer token validation
- ✅ Channel authorization callbacks
- ✅ HTTPS for WebSocket connections (production)

## 📊 Performance

- ✅ Queue support for async processing
- ✅ Database indexing on notifications table
- ✅ Efficient channel subscriptions
- ✅ Pusher connection pooling

## 🌐 Production Checklist

- [ ] Set `QUEUE_CONNECTION=redis` in production
- [ ] Configure supervisor for queue workers
- [ ] Set up cron job for scheduler
- [ ] Enable HTTPS for WebSocket connections
- [ ] Monitor Pusher usage and limits
- [ ] Implement notification preferences
- [ ] Add rate limiting
- [ ] Set up error monitoring
- [ ] Test under load
- [ ] Configure notification archiving

## 💡 Tips

1. **Use the test command** for quick testing:
   ```bash
   php artisan notifications:test all
   ```

2. **Monitor Pusher usage** in the dashboard to avoid hitting limits

3. **Use Redis** for queue driver in production for better performance

4. **Implement notification preferences** to let users control what they receive

5. **Add desktop notifications** for better user engagement

## 📞 Support

- **Documentation**: See guides in this directory
- **Laravel Docs**: https://laravel.com/docs/notifications
- **Pusher Docs**: https://pusher.com/docs
- **Laravel Broadcasting**: https://laravel.com/docs/broadcasting

## 🎉 Success!

Your notification system is now fully configured and ready to use!

Test it now:
```bash
php artisan notifications:test all
```

Then check:
1. Pusher Debug Console for events
2. Database `notifications` table for stored notifications
3. Frontend console for received notifications

---

**Built with ❤️ using Laravel, Pusher, and WebSockets**
