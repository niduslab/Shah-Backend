# E-Commerce API Documentation

## New User Features Implemented

### 1. User Dashboard

#### Get Dashboard Statistics
```
GET /api/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "statistics": {
      "total_orders": 15,
      "pending_orders": 2,
      "processing_orders": 3,
      "delivered_orders": 8,
      "cancelled_orders": 2,
      "total_spent": 15000.00,
      "pending_reviews": 3,
      "active_returns": 1,
      "wishlist_count": 5,
      "preorder_balance": 2000.00
    },
    "recent_orders": [...]
  }
}
```

#### Get User Profile with Addresses
```
GET /api/profile
Authorization: Bearer {token}
```

---

### 2. Address Management

#### Get All Addresses
```
GET /api/addresses
Authorization: Bearer {token}
```

#### Create New Address
```
POST /api/addresses
Authorization: Bearer {token}
Content-Type: application/json

{
  "address_line_1": "123 Main Street",
  "address_line_2": "Apt 4B",
  "contact_no": "+1234567890",
  "city": "New York",
  "state": "NY",
  "zip_code": "10001",
  "address_type": "shipping_address",
  "is_default": true
}
```

**Address Types:**
- `user_address`
- `shipping_address`
- `billing_address`

#### Get Single Address
```
GET /api/addresses/{id}
Authorization: Bearer {token}
```

#### Update Address
```
PUT /api/addresses/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "address_line_1": "456 Oak Avenue",
  "city": "Los Angeles",
  "is_default": true
}
```

#### Delete Address
```
DELETE /api/addresses/{id}
Authorization: Bearer {token}
```

**Note:** Cannot delete addresses associated with orders.

#### Set Default Address
```
POST /api/addresses/{id}/set-default
Authorization: Bearer {token}
```

---

### 3. Wishlist Management

#### Get Wishlist
```
GET /api/wishlist
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "product_id": 10,
      "created_at": "2026-03-02T10:00:00.000000Z",
      "product": {
        "id": 10,
        "name": "Product Name",
        "price": 99.99,
        "images": [...],
        "category": {...},
        "brand": {...}
      }
    }
  ]
}
```

#### Add to Wishlist
```
POST /api/wishlist
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 10
}
```

#### Remove from Wishlist (by wishlist ID)
```
DELETE /api/wishlist/{id}
Authorization: Bearer {token}
```

#### Remove from Wishlist (by product ID)
```
DELETE /api/wishlist/product/{productId}
Authorization: Bearer {token}
```

#### Check if Product in Wishlist
```
GET /api/wishlist/check/{productId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "in_wishlist": true
  }
}
```

#### Clear Entire Wishlist
```
POST /api/wishlist/clear
Authorization: Bearer {token}
```

---

### 4. Notifications

#### Get All Notifications
```
GET /api/notifications
Authorization: Bearer {token}
```

**Response:** Paginated list of notifications (20 per page)

#### Get Unread Count
```
GET /api/notifications/unread-count
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

#### Mark Notification as Read
```
POST /api/notifications/{id}/mark-as-read
Authorization: Bearer {token}
```

#### Mark All as Read
```
POST /api/notifications/mark-all-as-read
Authorization: Bearer {token}
```

#### Delete Notification
```
DELETE /api/notifications/{id}
Authorization: Bearer {token}
```

#### Clear All Notifications
```
POST /api/notifications/clear
Authorization: Bearer {token}
```

---

## Database Migrations

Run the following command to create the new tables:

```bash
php artisan migrate
```

**New Migrations:**
1. `create_wishlists_table` - Wishlist functionality
2. `add_is_default_to_addresses_table` - Default address support
3. `create_notifications_table` - User notifications

---

## Models Updated

### Address Model
- Added `is_default` field
- Added `getFullAddressAttribute()` accessor
- Added scopes: `default()`, `shipping()`, `billing()`

### Wishlist Model
- Complete implementation with relationships
- Unique constraint on user_id + product_id

### User Model
- Added `getName()` accessor for compatibility
- Existing relationships maintained

---

## Features Summary

### User Dashboard
✅ Order statistics (total, pending, processing, delivered, cancelled)
✅ Total amount spent
✅ Recent orders list
✅ Pending reviews count
✅ Active returns count
✅ Wishlist count
✅ Preorder balance tracking

### Address Management
✅ CRUD operations for addresses
✅ Multiple address types (user, shipping, billing)
✅ Default address per type
✅ Validation to prevent deletion of addresses used in orders
✅ Full address string accessor

### Wishlist
✅ Add/remove products
✅ View wishlist with product details
✅ Check if product is in wishlist
✅ Clear entire wishlist
✅ Duplicate prevention (unique constraint)

### Notifications
✅ View all notifications (paginated)
✅ Unread count
✅ Mark as read (single/all)
✅ Delete notifications
✅ Clear all notifications

---

## Testing the APIs

### Example: Add Product to Wishlist
```bash
curl -X POST http://your-domain.com/api/wishlist \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'
```

### Example: Get Dashboard Stats
```bash
curl -X GET http://your-domain.com/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Example: Create Address
```bash
curl -X POST http://your-domain.com/api/addresses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "address_line_1": "123 Main St",
    "contact_no": "+1234567890",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001",
    "address_type": "shipping_address",
    "is_default": true
  }'
```

---

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error message here"
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `422` - Validation Error

---

## Next Steps

1. Run migrations: `php artisan migrate`
2. Test the endpoints using Postman or similar tool
3. Integrate with your frontend application
4. Configure notification channels as needed
5. Add custom notification types for order updates, low stock alerts, etc.
