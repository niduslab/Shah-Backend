# Notification System Flow Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     E-Commerce Application                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Event Triggers                                │
│  • Order Placed      • Order Cancelled    • Review Submitted    │
│  • Status Changed    • Low Stock          • Flash Deal Starting │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                  NotificationService                             │
│  Centralized service for triggering notifications                │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              Laravel Notification System                         │
│  • Creates notification instance                                 │
│  • Determines recipients                                         │
│  • Queues notification job                                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    ▼                   ▼
        ┌──────────────────┐  ┌──────────────────┐
        │   Database       │  │   Broadcasting   │
        │   Channel        │  │   Channel        │
        └──────────────────┘  └──────────────────┘
                │                       │
                ▼                       ▼
        ┌──────────────────┐  ┌──────────────────┐
        │  notifications   │  │     Pusher       │
        │     table        │  │   WebSocket      │
        └──────────────────┘  └──────────────────┘
                │                       │
                ▼                       ▼
        ┌──────────────────┐  ┌──────────────────┐
        │  REST API        │  │  Real-time       │
        │  /api/           │  │  Delivery        │
        │  notifications   │  │                  │
        └──────────────────┘  └──────────────────┘
                │                       │
                └───────────┬───────────┘
                            ▼
                ┌──────────────────────┐
                │   Frontend App       │
                │  • React/Vue/Angular │
                │  • Laravel Echo      │
                │  • Notification UI   │
                └──────────────────────┘
```

## Order Placement Flow

```
Customer Places Order
        │
        ▼
CheckoutController@process
        │
        ├─────────────────────────────────┐
        ▼                                 ▼
NotificationService              NotificationService
  .notifyNewOrder()                .notifyOrderConfirmed()
        │                                 │
        ▼                                 ▼
NewOrderNotification            OrderConfirmedNotification
        │                                 │
        ▼                                 ▼
   All Admins                        Customer
        │                                 │
        ▼                                 ▼
  admin channel                    user.{id} channel
        │                                 │
        ▼                                 ▼
   Pusher →→→→→→→→→→→→→→→→→→→→→→→→→→→→ Pusher
        │                                 │
        ▼                                 ▼
Admin Dashboard                    Customer App
  • Sound alert                      • Toast notification
  • Desktop notification             • Order confirmation
  • Badge update                     • Badge update
```

## Order Status Update Flow

```
Admin Updates Status
        │
        ▼
Admin/OrderController@updateStatus
        │
        ▼
NotificationService
  .notifyOrderStatusChanged()
        │
        ▼
OrderStatusChangedNotification
        │
        ▼
    Customer
        │
        ▼
user.{id} channel
        │
        ▼
    Pusher
        │
        ▼
Customer App
  • Status update notification
  • Tracking info (if shipped)
  • UI update
```

## Review Submission Flow

```
Customer Submits Review
        │
        ▼
ReviewController@store
        │
        ▼
NotificationService
  .notifyNewReview()
        │
        ▼
NewReviewNotification
        │
        ▼
   All Admins
        │
        ▼
  admin channel
        │
        ▼
    Pusher
        │
        ▼
Admin Dashboard
  • New review alert
  • Review details
  • Approve/Reject options
```

## Channel Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Broadcasting Channels                     │
└─────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    ▼                   ▼
        ┌──────────────────┐  ┌──────────────────┐
        │  Private Channel │  │  Private Channel │
        │   user.{id}      │  │      admin       │
        └──────────────────┘  └──────────────────┘
                │                       │
                ▼                       ▼
        ┌──────────────────┐  ┌──────────────────┐
        │  Authorization:  │  │  Authorization:  │
        │  User ID match   │  │  isAdmin() check │
        └──────────────────┘  └──────────────────┘
                │                       │
                ▼                       ▼
        ┌──────────────────┐  ┌──────────────────┐
        │  Customer        │  │  Admin           │
        │  Notifications:  │  │  Notifications:  │
        │  • Order updates │  │  • New orders    │
        │  • Review reply  │  │  • New reviews   │
        │  • Flash deals   │  │  • Low stock     │
        └──────────────────┘  └──────────────────┘
```

## Notification Lifecycle

```
1. Event Occurs
   └─> Order placed, status changed, review submitted, etc.

2. Controller/Service Triggers Notification
   └─> NotificationService method called

3. Notification Created
   └─> Notification class instantiated with data

4. Recipients Determined
   └─> User(s) or Admin(s) identified

5. Notification Queued
   └─> Job added to queue (if using queues)

6. Queue Worker Processes
   └─> Job executed by queue worker

7. Dual Channel Delivery
   ├─> Database: Stored in notifications table
   └─> Broadcasting: Sent to Pusher

8. Real-time Delivery
   └─> Pusher broadcasts to subscribed clients

9. Frontend Receives
   ├─> Laravel Echo listener triggered
   ├─> Toast notification displayed
   ├─> Badge count updated
   └─> UI updated

10. User Interaction
    ├─> Click notification → Navigate to resource
    ├─> Mark as read → API call
    └─> Dismiss → Remove from UI
```

## Data Flow

```
┌──────────────┐
│   Backend    │
│   Trigger    │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────────┐
│  Notification Data Structure         │
│  {                                   │
│    type: "order_confirmed",          │
│    title: "Order Confirmed",         │
│    message: "Your order...",         │
│    order_id: 123,                    │
│    order_number: "SS20240311ABCD",  │
│    action_url: "/orders/..."         │
│  }                                   │
└──────┬───────────────────────────────┘
       │
       ├─────────────────┬─────────────────┐
       ▼                 ▼                 ▼
┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│  Database   │  │   Pusher    │  │   Email     │
│   Storage   │  │  Broadcast  │  │  (Future)   │
└─────────────┘  └─────────────┘  └─────────────┘
       │                 │                 │
       ▼                 ▼                 ▼
┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│  REST API   │  │  WebSocket  │  │  Mail Queue │
│  Response   │  │  Real-time  │  │             │
└─────────────┘  └─────────────┘  └─────────────┘
       │                 │                 │
       └────────┬────────┴─────────────────┘
                ▼
        ┌──────────────┐
        │   Frontend   │
        │  Application │
        └──────────────┘
```

## Queue Processing Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    With Queue (Recommended)                  │
└─────────────────────────────────────────────────────────────┘

Event Trigger
     │
     ▼
Notification Dispatched
     │
     ▼
Job Added to Queue
     │
     ▼
Queue Worker Picks Up Job
     │
     ▼
Notification Processed
     │
     ├─────────────┬─────────────┐
     ▼             ▼             ▼
 Database      Pusher        Email
     │             │             │
     └─────────────┴─────────────┘
                   │
                   ▼
            User Receives
            Notification

Benefits:
✓ Non-blocking
✓ Retry on failure
✓ Better performance
✓ Scalable
```

## Error Handling Flow

```
Notification Triggered
        │
        ▼
    Try Send
        │
        ├─────────────┬─────────────┐
        ▼             ▼             ▼
    Success       Pusher Fail   Database Fail
        │             │             │
        ▼             ▼             ▼
    Delivered     Retry Job     Log Error
                      │             │
                      ▼             ▼
                  Max Retries   Alert Admin
                      │
                      ▼
                  Failed Job
                  Notification
```

## Real-time Notification Timeline

```
Time: 0ms
├─ User places order
│
Time: 10ms
├─ CheckoutController processes
│
Time: 20ms
├─ NotificationService triggered
│
Time: 30ms
├─ Notification queued
│
Time: 50ms
├─ Queue worker processes
│
Time: 100ms
├─ Database stored
├─ Pusher broadcast sent
│
Time: 150ms
├─ Pusher delivers to clients
│
Time: 200ms
├─ Frontend receives notification
├─ Toast displayed
└─ Badge updated

Total: ~200ms from trigger to display
```

## Integration Points

```
┌─────────────────────────────────────────────────────────────┐
│                    Your Application                          │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ Controllers  │    │   Services   │    │   Listeners  │
│              │    │              │    │              │
│ • Checkout   │    │ • Order      │    │ • LowStock   │
│ • Order      │    │ • Review     │    │ • Payment    │
│ • Review     │    │ • Inventory  │    │ • Return     │
└──────┬───────┘    └──────┬───────┘    └──────┬───────┘
       │                   │                   │
       └───────────────────┼───────────────────┘
                           ▼
                ┌──────────────────┐
                │ Notification     │
                │ Service          │
                └──────────────────┘
                           │
                           ▼
                ┌──────────────────┐
                │ Laravel          │
                │ Notifications    │
                └──────────────────┘
```

---

This diagram shows the complete flow of notifications from trigger to delivery in your e-commerce application.
