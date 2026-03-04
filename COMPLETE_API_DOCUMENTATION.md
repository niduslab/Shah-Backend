# Complete API Documentation - E-Commerce Platform

## Table of Contents
1. [Authentication](#authentication)
2. [Admin Endpoints](#admin-endpoints)
3. [Public Endpoints](#public-endpoints)
4. [User Endpoints](#user-endpoints)
5. [Pagination Format](#pagination-format)
6. [Error Responses](#error-responses)

---

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {your_token_here}
```

### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}

Response:
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": { ... },
    "token": "your_access_token"
  }
}
```

---

## Pagination Format

All paginated endpoints return data in this format:

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      { "id": 1, "name": "Item 1" },
      { "id": 2, "name": "Item 2" }
    ],
    "first_page_url": "http://localhost:8000/api/admin/brands?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost:8000/api/admin/brands?page=5",
    "links": [
      { "url": null, "label": "&laquo; Previous", "active": false },
      { "url": "http://localhost:8000/api/admin/brands?page=1", "label": "1", "active": true },
      { "url": "http://localhost:8000/api/admin/brands?page=2", "label": "2", "active": false },
      { "url": "http://localhost:8000/api/admin/brands?page=2", "label": "Next &raquo;", "active": false }
    ],
    "next_page_url": "http://localhost:8000/api/admin/brands?page=2",
    "path": "http://localhost:8000/api/admin/brands",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 67
  }
}
```

**Important:** Access the actual data array via `response.data.data`

---

## Admin Endpoints

All admin endpoints require authentication and admin role.

### 1. Brands

#### List All Brands (Paginated)
```
GET

### Change Password
```http
PUT /api/auth/password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "oldpass",
  "password": "newpass",
  "password_confirmation": "newpass"
}
```


### Forgot Password
```http
POST /api/auth/forgot-password
Content-Type: application/json

{
  "email": "john@example.com"
}
```

### Reset Password
```http
POST /api/auth/reset-password
Content-Type: application/json

{
  "token": "reset_token",
  "email": "john@example.com",
  "password": "newpass",
  "password_confirmation": "newpass"
}
```

---

## Public APIs

### 1. Catalog

#### Get All Categories
```http
GET /api/catalog/categories
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "children": [...]
    }
  ]
}
```

#### Get Category Details
```http
GET /api/catalog/categories/{slug}
```

#### Get Products by Category
```http
GET /api/catalog/categories/{slug}/products?page=1&per_page=20
```

#### Get All Brands
```http
GET /api/catalog/brands
```

#### Get Products by Brand
```http
GET /api/catalog/brands/{slug}/products?page=1&per_page=20
```

#### Search Products
```http
GET /api/catalog/products?search=laptop&category_id=1&brand_id=2&min_price=100&max_price=1000&in_stock=true&sort_by=price&sort_order=asc&per_page=20
```

**Query Parameters:**
- `search` - Search term
- `category_id` - Filter by category
- `brand_id` - Filter by brand
- `min_price` - Minimum price
- `max_price` - Maximum price
- `in_stock` - Only in-stock products
- `is_featured` - Featured products
- `is_trending` - Trending products
- `sort_by` - Sort field (price, name, created_at)
- `sort_order` - asc or desc
- `per_page` - Items per page

#### Get Product Details
```http
GET /api/catalog/products/{slug}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Name",
    "slug": "product-name",
    "price": 99.99,
    "compare_price": 129.99,
    "quantity": 50,
    "stock_status": "in_stock",
    "description": "...",
    "images": [...],
    "variations": [...],
    "category": {...},
    "brand": {...},
    "reviews": [...],
    "average_rating": 4.5,
    "review_count": 10
  }
}
```

#### Get Featured Products
```http
GET /api/catalog/products/featured?per_page=12
```

#### Get Trending Products
```http
GET /api/catalog/products/trending?per_page=12
```


### 2. Flash Deals

#### Get Active Flash Deals
```http
GET /api/flash-deals?status=active
```

#### Get Upcoming Flash Deals
```http
GET /api/flash-deals/upcoming
```

#### Get Flash Deal Details
```http
GET /api/flash-deals/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Weekend Sale",
    "starts_at": "2026-03-05 00:00:00",
    "ends_at": "2026-03-07 23:59:59",
    "products": [
      {
        "id": 1,
        "name": "Product Name",
        "original_price": 99.99,
        "flash_price": 79.99,
        "discount_percentage": 20,
        "quantity_limit": 100,
        "quantity_sold": 45
      }
    ]
  }
}
```

### 3. Galleries

#### Get All Galleries
```http
GET /api/galleries
```

#### Get Gallery Details
```http
GET /api/galleries/{slug}
```

### 4. Cart Operations

#### Calculate Cart Summary
```http
POST /api/cart/summary
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 199.98,
    "discount": 0,
    "tax": 0,
    "shipping": 0,
    "total": 199.98,
    "items": [...]
  }
}
```

#### Validate Coupon
```http
POST /api/cart/validate-coupon
Content-Type: application/json

{
  "code": "SAVE20",
  "subtotal": 199.98
}
```

#### Check Product Availability
```http
POST /api/cart/check-availability
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2
    }
  ]
}
```

### 5. Reviews (Public Read)

#### Get Product Reviews
```http
GET /api/products/{productId}/reviews?page=1&per_page=10
```

**Response:**
```json
{
  "success": true,
  "data": {
    "reviews": [...],
    "stats": {
      "average_rating": 4.5,
      "total_reviews": 50,
      "rating_distribution": {
        "5": 30,
        "4": 15,
        "3": 3,
        "2": 1,
        "1": 1
      }
    }
  }
}
```

### 6. CMS Pages

#### Get All Policies
```http
GET /api/policies
```

#### Get Specific Policy
```http
GET /api/policies/{type}
```
Types: `privacy`, `terms`, `shipping`, `return`, `refund`

#### Get CMS Page
```http
GET /api/pages/{slug}
```

#### Get Banners
```http
GET /api/banners/{position}
```
Positions: `home_slider`, `home_banner`, `sidebar`, etc.

### 7. Order Tracking (Public)

#### Track Order
```http
GET /api/orders/{orderNumber}/track
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_number": "SS123456",
    "status": "processing",
    "shipping_method": "Standard",
    "tracking_number": "TRACK123",
    "created_at": "2026-03-01",
    "updated_at": "2026-03-02"
  }
}
```


---

## Customer APIs

**All customer APIs require authentication:**
```
Authorization: Bearer {token}
```

### 1. User Dashboard

#### Get Dashboard Statistics
```http
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

#### Get User Profile
```http
GET /api/profile
Authorization: Bearer {token}
```

### 2. Address Management

#### List All Addresses
```http
GET /api/addresses
Authorization: Bearer {token}
```

#### Create Address
```http
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

**Address Types:** `user_address`, `shipping_address`, `billing_address`

#### Get Single Address
```http
GET /api/addresses/{id}
Authorization: Bearer {token}
```

#### Update Address
```http
PUT /api/addresses/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "city": "Los Angeles",
  "is_default": true
}
```

#### Delete Address
```http
DELETE /api/addresses/{id}
Authorization: Bearer {token}
```

#### Set Default Address
```http
POST /api/addresses/{id}/set-default
Authorization: Bearer {token}
```

### 3. Wishlist

#### Get Wishlist
```http
GET /api/wishlist
Authorization: Bearer {token}
```

#### Add to Wishlist
```http
POST /api/wishlist
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 10
}
```

#### Remove from Wishlist
```http
DELETE /api/wishlist/{id}
Authorization: Bearer {token}
```

#### Remove by Product ID
```http
DELETE /api/wishlist/product/{productId}
Authorization: Bearer {token}
```

#### Check if in Wishlist
```http
GET /api/wishlist/check/{productId}
Authorization: Bearer {token}
```

#### Clear Wishlist
```http
POST /api/wishlist/clear
Authorization: Bearer {token}
```


### 4. Checkout

#### Get Shipping Methods
```http
POST /api/checkout/shipping-methods
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [...],
  "shipping_address_id": 1
}
```

#### Preview Order
```http
POST /api/checkout/preview
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2
    }
  ],
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method": "standard",
  "coupon_code": "SAVE20"
}
```

#### Process Checkout
```http
POST /api/checkout/process
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [...],
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method": "standard",
  "payment_method": "ssl_commerz",
  "coupon_code": "SAVE20",
  "notes": "Please deliver after 5 PM"
}
```

### 5. Orders

#### Get Order History
```http
GET /api/orders?page=1&per_page=10
Authorization: Bearer {token}
```

#### Get Order Details
```http
GET /api/orders/{orderNumber}
Authorization: Bearer {token}
```

#### Cancel Order
```http
POST /api/orders/{orderNumber}/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
  "reason": "Changed my mind"
}
```

#### Download Invoice
```http
GET /api/orders/{orderNumber}/invoice
Authorization: Bearer {token}
```

### 6. Payments

#### Retry Payment
```http
POST /api/payments/{orderNumber}/retry
Authorization: Bearer {token}
```

#### Pay Preorder Balance
```http
POST /api/payments/{orderNumber}/pay-preorder-balance
Authorization: Bearer {token}
```

#### Check Payment Status
```http
GET /api/payments/{orderNumber}/status
Authorization: Bearer {token}
```

### 7. Reviews

#### Get My Reviews
```http
GET /api/reviews/my-reviews
Authorization: Bearer {token}
```

#### Submit Review
```http
POST /api/reviews
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "order_item_id": 10,
  "rating": 5,
  "title": "Great product!",
  "comment": "Highly recommended",
  "images": ["image1.jpg", "image2.jpg"]
}
```

#### Mark Review Helpful
```http
POST /api/reviews/{reviewId}/helpful
Authorization: Bearer {token}
```

### 8. Returns

#### Get My Returns
```http
GET /api/returns
Authorization: Bearer {token}
```

#### Create Return Request
```http
POST /api/returns
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_item_id": 10,
  "quantity": 1,
  "reason": "defective",
  "description": "Product not working",
  "images": ["image1.jpg"]
}
```

#### Get Return Details
```http
GET /api/returns/{id}
Authorization: Bearer {token}
```

### 9. Notifications

#### Get All Notifications
```http
GET /api/notifications?page=1&per_page=20
Authorization: Bearer {token}
```

#### Get Unread Count
```http
GET /api/notifications/unread-count
Authorization: Bearer {token}
```

#### Mark as Read
```http
POST /api/notifications/{id}/mark-as-read
Authorization: Bearer {token}
```

#### Mark All as Read
```http
POST /api/notifications/mark-all-as-read
Authorization: Bearer {token}
```

#### Delete Notification
```http
DELETE /api/notifications/{id}
Authorization: Bearer {token}
```

#### Clear All Notifications
```http
POST /api/notifications/clear
Authorization: Bearer {token}
```

.
---

## Admin APIs

**All admin APIs require admin authentication:**
```
Authorization: Bearer {admin_token}
Middleware: auth:sanctum, admin
```

### 1. Dashboard & Reports

#### Get Dashboard Statistics
```http
GET /api/admin/dashboard
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "today": {
      "orders": 15,
      "revenue": 5000.00
    },
    "this_month": {
      "orders": 450,
      "revenue": 150000.00
    },
    "pending": {
      "orders": 10,
      "reviews": 5,
      "returns": 3
    },
    "low_stock_count": 8
  }
}
```

#### Sales Report
```http
GET /api/admin/reports/sales?date_from=2026-01-01&date_to=2026-03-01&group_by=day
Authorization: Bearer {admin_token}
```

**Query Parameters:**
- `date_from` - Start date
- `date_to` - End date
- `group_by` - day, week, month, year

#### Products Report
```http
GET /api/admin/reports/products?date_from=2026-01-01&date_to=2026-03-01&limit=20
Authorization: Bearer {admin_token}
```

#### Customers Report
```http
GET /api/admin/reports/customers?date_from=2026-01-01&date_to=2026-03-01&limit=20
Authorization: Bearer {admin_token}
```

#### Inventory Report
```http
GET /api/admin/reports/inventory
Authorization: Bearer {admin_token}
```

#### Order Status Report
```http
GET /api/admin/reports/order-status?date_from=2026-01-01&date_to=2026-03-01
Authorization: Bearer {admin_token}
```


### 2. Product Management

#### List Products
```http
GET /api/admin/products?page=1&per_page=20&search=laptop&category_id=1&status=active
Authorization: Bearer {admin_token}
```

#### Create Product
```http
POST /api/admin/products
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "category_id": 1,
  "brand_id": 2,
  "name": "Product Name",
  "sku": "PROD-001",
  "description": "Product description",
  "short_description": "Short desc",
  "price": 99.99,
  "compare_price": 129.99,
  "cost_price": 50.00,
  "quantity": 100,
  "low_stock_threshold": 10,
  "weight": 1.5,
  "weight_unit": "kg",
  "is_featured": true,
  "is_trending": false,
  "status": "active",
  "meta_title": "SEO Title",
  "meta_description": "SEO Description"
}
```

#### Get Product Details
```http
GET /api/admin/products/{id}
Authorization: Bearer {admin_token}
```

#### Update Product
```http
PUT /api/admin/products/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "price": 89.99,
  "quantity": 150
}
```

#### Delete Product
```http
DELETE /api/admin/products/{id}
Authorization: Bearer {admin_token}
```

#### Add Product Variation
```http
POST /api/admin/products/{id}/variations
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "sku": "PROD-001-RED-L",
  "price": 99.99,
  "quantity": 50,
  "attributes": {
    "color": "Red",
    "size": "Large"
  }
}
```

#### Update Product Variation
```http
PUT /api/admin/products/{productId}/variations/{variationId}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "price": 89.99,
  "quantity": 60
}
```

#### Delete Product Variation
```http
DELETE /api/admin/products/{productId}/variations/{variationId}
Authorization: Bearer {admin_token}
```


### 3. Category Management

#### List Categories
```http
GET /api/admin/categories?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Get Category Tree
```http
GET /api/admin/categories/tree
Authorization: Bearer {admin_token}
```

#### Create Category
```http
POST /api/admin/categories
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "parent_id": null,
  "name": "Electronics",
  "description": "Electronic products",
  "image": "electronics.jpg",
  "is_active": true,
  "sort_order": 1,
  "meta_title": "Electronics",
  "meta_description": "Shop electronics"
}
```

#### Get Category Details
```http
GET /api/admin/categories/{id}
Authorization: Bearer {admin_token}
```

#### Update Category
```http
PUT /api/admin/categories/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Consumer Electronics",
  "is_active": true
}
```

#### Delete Category
```http
DELETE /api/admin/categories/{id}
Authorization: Bearer {admin_token}
```

### 4. Brand Management

#### List Brands
```http
GET /api/admin/brands?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create Brand
```http
POST /api/admin/brands
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Apple",
  "slug": "apple",
  "description": "Apple Inc.",
  "logo": "apple-logo.jpg",
  "is_active": true,
  "sort_order": 1
}
```

#### Get Brand Details
```http
GET /api/admin/brands/{id}
Authorization: Bearer {admin_token}
```

#### Update Brand
```http
PUT /api/admin/brands/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Apple Inc.",
  "is_active": true
}
```

#### Delete Brand
```http
DELETE /api/admin/brands/{id}
Authorization: Bearer {admin_token}
```

### 5. Order Management

#### List Orders
```http
GET /api/admin/orders?page=1&per_page=20&status=pending&payment_status=paid&date_from=2026-01-01
Authorization: Bearer {admin_token}
```

**Query Parameters:**
- `status` - pending, confirmed, processing, shipped, delivered, cancelled
- `payment_status` - pending, paid, failed, refunded
- `order_type` - regular, preorder
- `date_from` - Start date
- `date_to` - End date
- `search` - Order number or customer name

#### Get Order Details
```http
GET /api/admin/orders/{id}
Authorization: Bearer {admin_token}
```

#### Update Order Status
```http
PUT /api/admin/orders/{id}/status
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "status": "processing"
}
```

**Status Options:** `pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled`

#### Cancel Order
```http
POST /api/admin/orders/{id}/cancel
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "reason": "Out of stock"
}
```

#### Assign Tracking Number
```http
POST /api/admin/orders/{id}/tracking
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "tracking_number": "TRACK123456",
  "carrier": "DHL"
}
```

#### Update Order Notes
```http
PUT /api/admin/orders/{id}/notes
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "notes": "Customer requested gift wrapping"
}
```


### 6. POS (Point of Sale)

#### Search Products for POS
```http
GET /api/admin/pos/products/search?search=laptop
Authorization: Bearer {admin_token}
```

#### Get Product by SKU/Barcode
```http
GET /api/admin/pos/products/sku/{sku}
Authorization: Bearer {admin_token}
```

#### Calculate Order Totals
```http
POST /api/admin/pos/calculate
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2
    }
  ],
  "discount": 10.00
}
```

#### Create POS Order
```http
POST /api/admin/pos/orders
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+1234567890",
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2
    }
  ],
  "discount": 10.00,
  "payment_method": "cash",
  "notes": "Walk-in customer"
}
```

**Payment Methods:** `cash`, `card`, `manual`


### 7. Inventory Management

#### Get Inventory Overview
```http
GET /api/admin/inventory?search=laptop&category_id=1&stock_status=low&sort_by=quantity&per_page=20
Authorization: Bearer {admin_token}
```

**Stock Status:** `low`, `out`, `in`

#### Get Low Stock Products
```http
GET /api/admin/inventory/low-stock
Authorization: Bearer {admin_token}
```

#### Get Product Inventory Details
```http
GET /api/admin/inventory/{productId}
Authorization: Bearer {admin_token}
```

#### Adjust Product Stock
```http
POST /api/admin/inventory/{productId}/adjust
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "variation_id": null,
  "quantity": -5,
  "reason": "damage",
  "notes": "Water damage in warehouse"
}
```

**Reasons:** `adjustment`, `damage`, `return`, `recount`, `other`

#### Bulk Stock Adjustment
```http
POST /api/admin/inventory/bulk-adjust
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "adjustments": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 10
    },
    {
      "product_id": 2,
      "variation_id": null,
      "quantity": -5
    }
  ],
  "reason": "recount",
  "notes": "Monthly inventory count"
}
```

#### Get Inventory Logs
```http
GET /api/admin/inventory/logs?product_id=1&reason=sale&date_from=2026-01-01&per_page=20
Authorization: Bearer {admin_token}
```


### 8. Flash Deals Management

#### List Flash Deals
```http
GET /api/admin/flash-deals?status=active&page=1&per_page=15
Authorization: Bearer {admin_token}
```

#### Create Flash Deal
```http
POST /api/admin/flash-deals
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Weekend Sale",
  "description": "Special weekend discounts",
  "starts_at": "2026-03-05 00:00:00",
  "ends_at": "2026-03-07 23:59:59",
  "discount_type": "percentage",
  "discount_value": 20,
  "max_discount_amount": 100,
  "quantity_limit": 1000,
  "per_user_limit": 5,
  "is_active": true,
  "priority": 1,
  "products": [
    {
      "product_id": 1,
      "flash_price": 79.99,
      "quantity_limit": 100
    }
  ]
}
```

**Discount Types:** `percentage`, `fixed_amount`

#### Get Flash Deal Details
```http
GET /api/admin/flash-deals/{id}
Authorization: Bearer {admin_token}
```

#### Update Flash Deal
```http
PUT /api/admin/flash-deals/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Extended Weekend Sale",
  "ends_at": "2026-03-08 23:59:59"
}
```

#### Delete Flash Deal
```http
DELETE /api/admin/flash-deals/{id}
Authorization: Bearer {admin_token}
```

#### Toggle Flash Deal Status
```http
POST /api/admin/flash-deals/{id}/toggle
Authorization: Bearer {admin_token}
```

#### Get Flash Deal Statistics
```http
GET /api/admin/flash-deals/{id}/statistics
Authorization: Bearer {admin_token}
```


### 9. Promotions Management

#### List Promotions
```http
GET /api/admin/promotions?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create Promotion
```http
POST /api/admin/promotions
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Buy 2 Get 1 Free",
  "type": "buy_x_get_y",
  "starts_at": "2026-03-01 00:00:00",
  "ends_at": "2026-03-31 23:59:59",
  "is_active": true,
  "priority": 1,
  "conditions": {
    "buy_quantity": 2,
    "get_quantity": 1
  },
  "product_ids": [1, 2, 3]
}
```

**Promotion Types:** `buy_x_get_y`, `bundle`, `tiered_discount`

#### Get Promotion Details
```http
GET /api/admin/promotions/{id}
Authorization: Bearer {admin_token}
```

#### Update Promotion
```http
PUT /api/admin/promotions/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Buy 3 Get 1 Free",
  "is_active": true
}
```

#### Delete Promotion
```http
DELETE /api/admin/promotions/{id}
Authorization: Bearer {admin_token}
```

#### Toggle Promotion Status
```http
POST /api/admin/promotions/{id}/toggle
Authorization: Bearer {admin_token}
```

### 10. Coupons Management

#### List Coupons
```http
GET /api/admin/coupons?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create Coupon
```http
POST /api/admin/coupons
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "code": "SAVE20",
  "type": "percentage",
  "value": 20,
  "max_discount_amount": 100,
  "min_purchase_amount": 50,
  "usage_limit": 1000,
  "per_user_limit": 1,
  "starts_at": "2026-03-01 00:00:00",
  "expires_at": "2026-03-31 23:59:59",
  "is_active": true,
  "applicable_to": "all"
}
```

**Coupon Types:** `percentage`, `fixed_amount`, `free_shipping`  
**Applicable To:** `all`, `specific_products`, `specific_categories`

#### Get Coupon Details
```http
GET /api/admin/coupons/{id}
Authorization: Bearer {admin_token}
```

#### Update Coupon
```http
PUT /api/admin/coupons/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "value": 25,
  "is_active": true
}
```

#### Delete Coupon
```http
DELETE /api/admin/coupons/{id}
Authorization: Bearer {admin_token}
```

#### Get Coupon Usage History
```http
GET /api/admin/coupons/{id}/usage?page=1&per_page=20
Authorization: Bearer {admin_token}
```


### 11. Content Management (CMS)

#### List CMS Pages
```http
GET /api/admin/content/pages?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create CMS Page
```http
POST /api/admin/content/pages
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "About Us",
  "slug": "about-us",
  "content": "<p>Page content here</p>",
  "is_active": true,
  "meta_title": "About Us",
  "meta_description": "Learn about our company"
}
```

#### Get CMS Page Details
```http
GET /api/admin/content/pages/{id}
Authorization: Bearer {admin_token}
```

#### Update CMS Page
```http
PUT /api/admin/content/pages/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "About Our Company",
  "content": "<p>Updated content</p>"
}
```

#### Delete CMS Page
```http
DELETE /api/admin/content/pages/{id}
Authorization: Bearer {admin_token}
```

#### List Store Policies
```http
GET /api/admin/content/policies
Authorization: Bearer {admin_token}
```

#### Create/Update Policy
```http
POST /api/admin/content/policies
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "type": "privacy",
  "title": "Privacy Policy",
  "content": "<p>Privacy policy content</p>"
}
```

**Policy Types:** `privacy`, `terms`, `shipping`, `return`, `refund`

#### Get Policy Details
```http
GET /api/admin/content/policies/{id}
Authorization: Bearer {admin_token}
```

#### Update Policy
```http
PUT /api/admin/content/policies/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "content": "<p>Updated policy content</p>"
}
```

#### List Banners
```http
GET /api/admin/content/banners
Authorization: Bearer {admin_token}
```

#### Create Banner
```http
POST /api/admin/content/banners
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Summer Sale",
  "image": "banner.jpg",
  "link": "/sale",
  "position": "home_slider",
  "sort_order": 1,
  "is_active": true,
  "starts_at": "2026-06-01",
  "ends_at": "2026-08-31"
}
```

**Banner Positions:** `home_slider`, `home_banner`, `sidebar`, `footer`

#### Update Banner
```http
PUT /api/admin/content/banners/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Extended Summer Sale",
  "is_active": true
}
```

#### Delete Banner
```http
DELETE /api/admin/content/banners/{id}
Authorization: Bearer {admin_token}
```


### 12. User Management

#### List Users
```http
GET /api/admin/users?page=1&per_page=20&user_type=customer&status=active&search=john
Authorization: Bearer {admin_token}
```

**User Types:** `customer`, `admin`, `vendor`

#### Create User
```http
POST /api/admin/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password123",
  "user_type": "customer",
  "status": true
}
```

#### Get User Details
```http
GET /api/admin/users/{id}
Authorization: Bearer {admin_token}
```

#### Update User
```http
PUT /api/admin/users/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Smith",
  "status": true
}
```

#### Delete User
```http
DELETE /api/admin/users/{id}
Authorization: Bearer {admin_token}
```

#### Toggle User Status
```http
POST /api/admin/users/{id}/toggle-status
Authorization: Bearer {admin_token}
```

### 13. Review Management

#### List Reviews
```http
GET /api/admin/reviews?page=1&per_page=20&status=pending&product_id=1
Authorization: Bearer {admin_token}
```

**Status:** `pending`, `approved`, `rejected`

#### Get Review Statistics
```http
GET /api/admin/reviews/statistics
Authorization: Bearer {admin_token}
```

#### Get Review Details
```http
GET /api/admin/reviews/{id}
Authorization: Bearer {admin_token}
```

#### Approve Review
```http
POST /api/admin/reviews/{id}/approve
Authorization: Bearer {admin_token}
```

#### Reject Review
```http
POST /api/admin/reviews/{id}/reject
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "reason": "Inappropriate content"
}
```

#### Respond to Review
```http
POST /api/admin/reviews/{id}/respond
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "response": "Thank you for your feedback!"
}
```

#### Delete Review
```http
DELETE /api/admin/reviews/{id}
Authorization: Bearer {admin_token}
```


### 14. Returns Management

#### List Returns
```http
GET /api/admin/returns?page=1&per_page=20&status=pending
Authorization: Bearer {admin_token}
```

**Status:** `pending`, `approved`, `rejected`, `processing`, `completed`

#### Get Return Details
```http
GET /api/admin/returns/{id}
Authorization: Bearer {admin_token}
```

#### Approve Return
```http
POST /api/admin/returns/{id}/approve
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "notes": "Return approved, refund will be processed"
}
```

#### Reject Return
```http
POST /api/admin/returns/{id}/reject
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "reason": "Product not eligible for return"
}
```

#### Process Return
```http
POST /api/admin/returns/{id}/process
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "action": "refund",
  "notes": "Refund processed"
}
```

### 15. Refunds Management

#### List Refunds
```http
GET /api/admin/refunds?page=1&per_page=20&status=pending
Authorization: Bearer {admin_token}
```

#### Create Refund
```http
POST /api/admin/refunds
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "order_id": 1,
  "amount": 99.99,
  "reason": "Product defective",
  "refund_method": "original_payment"
}
```

#### Get Refund Details
```http
GET /api/admin/refunds/{id}
Authorization: Bearer {admin_token}
```

#### Process Refund
```http
POST /api/admin/refunds/{id}/process
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "transaction_id": "TXN123456",
  "notes": "Refund completed"
}
```

#### Cancel Refund
```http
POST /api/admin/refunds/{id}/cancel
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "reason": "Customer withdrew request"
}
```


### 16. Shipping Management

#### List Shipping Rates
```http
GET /api/admin/shipping-rates?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create Shipping Rate
```http
POST /api/admin/shipping-rates
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Standard Shipping",
  "shipping_class_id": 1,
  "min_weight": 0,
  "max_weight": 5,
  "rate": 10.00,
  "estimated_days": "3-5",
  "is_active": true
}
```

#### Get Shipping Rate Details
```http
GET /api/admin/shipping-rates/{id}
Authorization: Bearer {admin_token}
```

#### Update Shipping Rate
```http
PUT /api/admin/shipping-rates/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "rate": 12.00,
  "is_active": true
}
```

#### Delete Shipping Rate
```http
DELETE /api/admin/shipping-rates/{id}
Authorization: Bearer {admin_token}
```

#### List Shipping Classes
```http
GET /api/admin/shipping-classes
Authorization: Bearer {admin_token}
```

#### Create Shipping Class
```http
POST /api/admin/shipping-classes
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Heavy Items",
  "description": "For heavy products"
}
```

#### Update Shipping Class
```http
PUT /api/admin/shipping-classes/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Oversized Items"
}
```

#### Delete Shipping Class
```http
DELETE /api/admin/shipping-classes/{id}
Authorization: Bearer {admin_token}
```


### 17. Gallery Management

#### List Galleries
```http
GET /api/admin/galleries?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create Gallery
```http
POST /api/admin/galleries
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Summer Collection 2026",
  "slug": "summer-2026",
  "description": "Our latest summer collection",
  "is_active": true
}
```

#### Get Gallery Details
```http
GET /api/admin/galleries/{id}
Authorization: Bearer {admin_token}
```

#### Update Gallery
```http
PUT /api/admin/galleries/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Summer Collection 2026 - Extended",
  "is_active": true
}
```

#### Delete Gallery
```http
DELETE /api/admin/galleries/{id}
Authorization: Bearer {admin_token}
```

#### Add Image to Gallery
```http
POST /api/admin/galleries/{id}/images
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "image_path": "gallery/image1.jpg",
  "title": "Product Showcase",
  "description": "Featured product",
  "sort_order": 1
}
```

#### Update Gallery Image
```http
PUT /api/admin/galleries/{galleryId}/images/{imageId}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Updated Title",
  "sort_order": 2
}
```

#### Delete Gallery Image
```http
DELETE /api/admin/galleries/{galleryId}/images/{imageId}
Authorization: Bearer {admin_token}
```

### 18. Campaign Management (Email Marketing)

#### List Campaigns
```http
GET /api/admin/campaigns?page=1&per_page=20
Authorization: Bearer {admin_token}
```

#### Create Campaign
```http
POST /api/admin/campaigns
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Summer Sale Announcement",
  "subject": "50% Off Summer Sale!",
  "content": "<html>Email content here</html>",
  "recipient_type": "all_customers",
  "scheduled_at": "2026-06-01 09:00:00"
}
```

**Recipient Types:** `all_customers`, `active_customers`, `inactive_customers`, `specific_segment`

#### Get Campaign Details
```http
GET /api/admin/campaigns/{id}
Authorization: Bearer {admin_token}
```

#### Update Campaign
```http
PUT /api/admin/campaigns/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "subject": "60% Off Summer Sale!",
  "scheduled_at": "2026-06-01 10:00:00"
}
```

#### Delete Campaign
```http
DELETE /api/admin/campaigns/{id}
Authorization: Bearer {admin_token}
```

#### Send Campaign
```http
POST /api/admin/campaigns/{id}/send
Authorization: Bearer {admin_token}
```

#### Get Campaign Statistics
```http
GET /api/admin/campaigns/{id}/statistics
Authorization: Bearer {admin_token}
```

#### Preview Recipients
```http
POST /api/admin/campaigns/preview-recipients
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "recipient_type": "active_customers",
  "filters": {...}
}
```


---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data here
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "first_page_url": "http://api.com/endpoint?page=1",
    "from": 1,
    "last_page": 10,
    "last_page_url": "http://api.com/endpoint?page=10",
    "next_page_url": "http://api.com/endpoint?page=2",
    "path": "http://api.com/endpoint",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 150
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message here"
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Authentication required or failed |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server error |

### Common Error Messages

#### Authentication Errors
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

#### Authorization Errors
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

#### Not Found Errors
```json
{
  "success": false,
  "message": "Product not found."
}
```

#### Validation Errors
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## Rate Limiting

- **Public APIs:** 60 requests per minute
- **Authenticated APIs:** 120 requests per minute
- **Admin APIs:** 180 requests per minute

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1646150400
```

---

## Pagination

Most list endpoints support pagination with these parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

**Example:**
```http
GET /api/catalog/products?page=2&per_page=20
```

---

## Filtering & Sorting

### Common Filter Parameters

- `search` - Search term
- `status` - Filter by status
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)
- `sort_by` - Field to sort by
- `sort_order` - asc or desc

**Example:**
```http
GET /api/admin/products?search=laptop&status=active&sort_by=price&sort_order=asc
```

---

## File Uploads

For endpoints that accept file uploads, use `multipart/form-data`:

```http
POST /api/admin/products
Authorization: Bearer {token}
Content-Type: multipart/form-data

name: Product Name
price: 99.99
image: [file]
```

---

## Webhooks

### Available Webhook Events

- `order.created` - New order placed
- `order.updated` - Order status changed
- `order.cancelled` - Order cancelled
- `payment.completed` - Payment successful
- `payment.failed` - Payment failed
- `product.low_stock` - Product below threshold
- `return.created` - Return request created
- `review.created` - New review submitted

### Webhook Payload Example
```json
{
  "event": "order.created",
  "data": {
    "order_id": 123,
    "order_number": "SS123456",
    "total": 199.99,
    "status": "pending"
  },
  "timestamp": "2026-03-02T10:30:00Z"
}
```

---

## Testing

### Test Credentials

**Customer Account:**
```
Email: customer@example.com
Password: password123
```

**Admin Account:**
```
Email: admin@example.com
Password: admin123
```

### Test Payment Cards

**Success:**
```
Card: 4242 4242 4242 4242
Expiry: Any future date
CVV: Any 3 digits
```

**Failure:**
```
Card: 4000 0000 0000 0002
Expiry: Any future date
CVV: Any 3 digits
```

---

## Best Practices

### 1. Always Use HTTPS
```
https://your-domain.com/api/endpoint
```

### 2. Include Authorization Header
```
Authorization: Bearer {your_token_here}
```

### 3. Set Content-Type
```
Content-Type: application/json
```

### 4. Handle Errors Gracefully
```javascript
try {
  const response = await fetch('/api/endpoint');
  const data = await response.json();
  
  if (!data.success) {
    // Handle error
    console.error(data.message);
  }
} catch (error) {
  // Handle network error
  console.error('Network error:', error);
}
```

### 5. Implement Retry Logic
```javascript
async function fetchWithRetry(url, options, retries = 3) {
  for (let i = 0; i < retries; i++) {
    try {
      const response = await fetch(url, options);
      if (response.ok) return response;
    } catch (error) {
      if (i === retries - 1) throw error;
      await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
    }
  }
}
```

### 6. Cache Responses
```javascript
const cache = new Map();

async function fetchWithCache(url, options) {
  if (cache.has(url)) {
    return cache.get(url);
  }
  
  const response = await fetch(url, options);
  const data = await response.json();
  cache.set(url, data);
  
  return data;
}
```

---

## SDK Examples

### JavaScript/TypeScript
```javascript
class ECommerceAPI {
  constructor(baseURL, token) {
    this.baseURL = baseURL;
    this.token = token;
  }
  
  async request(endpoint, options = {}) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      ...options,
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
        ...options.headers
      }
    });
    
    return response.json();
  }
  
  // Products
  async getProducts(params) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/catalog/products?${query}`);
  }
  
  async getProduct(slug) {
    return this.request(`/catalog/products/${slug}`);
  }
  
  // Cart
  async calculateCart(items) {
    return this.request('/cart/summary', {
      method: 'POST',
      body: JSON.stringify({ items })
    });
  }
  
  // Orders
  async createOrder(orderData) {
    return this.request('/checkout/process', {
      method: 'POST',
      body: JSON.stringify(orderData)
    });
  }
}

// Usage
const api = new ECommerceAPI('https://api.example.com/api', 'your_token');
const products = await api.getProducts({ category_id: 1, per_page: 20 });
```

### PHP
```php
class ECommerceAPI {
    private $baseURL;
    private $token;
    
    public function __construct($baseURL, $token) {
        $this->baseURL = $baseURL;
        $this->token = $token;
    }
    
    private function request($endpoint, $method = 'GET', $data = null) {
        $ch = curl_init($this->baseURL . $endpoint);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    public function getProducts($params = []) {
        $query = http_build_query($params);
        return $this->request("/catalog/products?{$query}");
    }
    
    public function createOrder($orderData) {
        return $this->request('/checkout/process', 'POST', $orderData);
    }
}

// Usage
$api = new ECommerceAPI('https://api.example.com/api', 'your_token');
$products = $api->getProducts(['category_id' => 1, 'per_page' => 20]);
```

---

## Support

For API support and questions:
- **Email:** api-support@example.com
- **Documentation:** https://docs.example.com
- **Status Page:** https://status.example.com

---

## Changelog

### Version 1.0.0 (2026-03-02)
- Initial API release
- 197 endpoints
- Full e-commerce functionality
- Admin dashboard
- POS system
- Inventory management
- Flash deals
- Promotions & coupons
- CMS management
- User management
- Complete order lifecycle

---

**API Version:** 1.0.0  
**Last Updated:** March 2, 2026  
**Total Endpoints:** 197  
**Status:** Production Ready ✅
