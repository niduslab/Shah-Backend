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

### 4. Shipping & Checkout

#### Get Available Shipping Methods
```
POST /api/checkout/shipping-methods
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": 5,
      "quantity": 2
    }
  ],
  "address_id": 10,
  "subtotal": 5000.00
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service for standard deliveries",
      "cost": 120.00,
      "delivery_time": "2-3 business days",
      "free_shipping_min_order": 3000.00,
      "is_free": false,
      "recommended": true
    },
    {
      "code": "shah_sports_team",
      "name": "Shah Sports Team Delivery",
      "description": "Our own delivery team for heavy and large items",
      "cost": 250.00,
      "delivery_time": "3-5 business days",
      "free_shipping_min_order": 5000.00,
      "is_free": false,
      "recommended": false
    }
  ]
}
```

**Shipping Methods:**
- `pathao_courier` - Standard courier service (recommended for items ≤20kg)
- `shah_sports_team` - Heavy/large item delivery (recommended for items >20kg)

**Notes:**
- `recommended` flag indicates the best method based on weight and dimensions
- `is_free` is true when subtotal meets free shipping threshold
- Costs are calculated based on product weight, dimensions, and delivery location

#### Checkout Preview
```
POST /api/checkout/preview
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2,
      "price": 1500.00,
      "is_preorder": false
    }
  ],
  "shipping_address_id": 10,
  "shipping_method": "pathao_courier",
  "coupon_code": "SAVE10",
  "is_preorder": false,
  "pay_deposit_only": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 3000.00,
    "shipping_cost": 120.00,
    "coupon_discount": 300.00,
    "total": 2820.00,
    "is_preorder": false,
    "deposit_amount": null,
    "remaining_amount": null,
    "payable_now": 2820.00
  }
}
```

#### Process Checkout
```
POST /api/checkout/process
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2,
      "price": 1500.00,
      "is_preorder": false
    }
  ],
  "shipping_address_id": 10,
  "billing_address_id": 10,
  "shipping_method": "pathao_courier",
  "payment_method": "ssl_commerz",
  "coupon_code": "SAVE10",
  "notes": "Please call before delivery",
  "is_preorder": false,
  "pay_deposit_only": false
}
```

**Payment Methods:**
- `ssl_commerz` - SSLCommerz payment gateway
- `bkash` - bKash mobile payment
- `nagad` - Nagad mobile payment

**Response:**
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order": {
      "id": 123,
      "order_number": "ORD-20260302-123",
      "status": "pending",
      "total": 2820.00,
      ...
    },
    "payment": {
      "gateway_url": "https://payment-gateway.com/...",
      "session_key": "...",
      ...
    }
  }
}
```

---

### 5. Notifications

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

## Admin Shipping Management

### List Shipping Rates
```
GET /api/admin/shipping-rates
Authorization: Bearer {admin_token}
```

### Create Shipping Rate
```
POST /api/admin/shipping-rates
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Pathao Standard",
  "shipping_class_id": null,
  "method": "pathao_courier",
  "country": "Bangladesh",
  "delivery_time": "2-3 business days",
  "free_shipping_min_order": 3000.00,
  "base_cost": 60.00,
  "is_active": true
}
```

### Update Shipping Rate
```
PUT /api/admin/shipping-rates/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json
```

### Delete Shipping Rate
```
DELETE /api/admin/shipping-rates/{id}
Authorization: Bearer {admin_token}
```

### List Shipping Classes
```
GET /api/admin/shipping-classes
Authorization: Bearer {admin_token}
```

### Create Shipping Class
```
POST /api/admin/shipping-classes
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Heavy Equipment",
  "description": "Large and heavy sports equipment"
}
```

### Update Shipping Class
```
PUT /api/admin/shipping-classes/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json
```

### Delete Shipping Class
```
DELETE /api/admin/shipping-classes/{id}
Authorization: Bearer {admin_token}
```

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

### Shipping & Checkout
✅ Calculate shipping costs based on weight and location
✅ Multiple shipping methods (Pathao Courier, Shah Sports Team)
✅ Location-based pricing (city/state/default)
✅ Weight-based cost calculation (per-unit or tiered rules)
✅ Free shipping thresholds
✅ Smart method recommendations
✅ Automatic weight unit conversion (kg, g, lb, oz)
✅ Shipping classes for product categorization
✅ Checkout preview with all costs
✅ Complete order processing with payment integration

### Admin Shipping Management
✅ Manage shipping rates (CRUD)
✅ Manage shipping classes (CRUD)
✅ Configure weight cost rules
✅ Set location-based pricing
✅ Enable/disable shipping methods
✅ Configure free shipping thresholds

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

### Example: Get Shipping Methods
```bash
curl -X POST http://your-domain.com/api/checkout/shipping-methods \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "variation_id": null, "quantity": 2}
    ],
    "address_id": 10,
    "subtotal": 5000
  }'
```

### Example: Checkout Preview
```bash
curl -X POST http://your-domain.com/api/checkout/preview \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2, "price": 1500}
    ],
    "shipping_address_id": 10,
    "shipping_method": "pathao_courier",
    "coupon_code": "SAVE10"
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

## Shipping System Details

### How Shipping Calculation Works

1. **Product Data Enrichment**
   - System automatically fetches product weight, dimensions, and shipping class
   - Supports weight units: kg, g, lb, oz (auto-converted to kg)

2. **Weight Calculation**
   - Total weight = sum of (product_weight × quantity) for all items
   - Variations can override product weight

3. **Method Selection**
   - Pathao Courier: Recommended for items ≤20kg
   - Shah Sports Team: Recommended for items >20kg or large dimensions

4. **Cost Calculation**
   - **Per Unit Method**: base_cost + (total_weight × per_unit_cost)
   - **Rules Method**: base_cost + cost_from_weight_tier
   - **Location-based**: Different rates for city/state/default

5. **Free Shipping**
   - Applied when: subtotal ≥ free_shipping_min_order
   - Configured per shipping method

### Shipping Configuration

**Database Tables:**
- `shipping_classes` - Product categories (e.g., Heavy, Standard)
- `shipping_rates` - Base method configuration
- `weight_cost_rules` - Location and calculation rules
- `weight_cost_rule_items` - Weight tiers for rules-based pricing

**Example Configuration:**

```sql
-- Create shipping rate
INSERT INTO shipping_rates (name, method, base_cost, free_shipping_min_order, delivery_time, is_active)
VALUES ('Pathao Standard', 'pathao_courier', 60, 3000, '2-3 business days', 1);

-- Create per-unit weight rule
INSERT INTO weight_cost_rules (shipping_rate_id, shipping_calculation_method, per_unit_cost)
VALUES (1, 'per_unit', 15);

-- Or create tiered weight rules
INSERT INTO weight_cost_rules (shipping_rate_id, shipping_calculation_method, default_rule_cost)
VALUES (1, 'rules', 500);

INSERT INTO weight_cost_rule_items (weight_cost_rule_id, weight, cost) VALUES
(1, 5, 50),   -- Up to 5kg: ৳50
(1, 10, 100), -- Up to 10kg: ৳100
(1, 20, 200); -- Up to 20kg: ৳200
```

For detailed shipping system documentation, see:
- **SHIPPING_SYSTEM_GUIDE.md** - Comprehensive guide with examples
- **SHIPPING_API_REFERENCE.md** - Complete API reference
- **SHIPPING_SYSTEM_SUMMARY.md** - Quick implementation summary

---

## Next Steps

1. Run migrations: `php artisan migrate`
2. Configure shipping rates and rules (see SHIPPING_SYSTEM_GUIDE.md)
3. Test the endpoints using Postman or similar tool
4. Integrate with your frontend application
5. Configure notification channels as needed
6. Add custom notification types for order updates, low stock alerts, etc.
