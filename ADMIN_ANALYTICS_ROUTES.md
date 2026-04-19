# Admin Analytics Routes

## 🔐 Authentication Required

All admin analytics routes require:
- ✅ `auth:sanctum` middleware (Bearer token)
- ✅ `admin` middleware (Admin role)

**Base URL:** `http://localhost:8000/api/admin/analytics`

---

## 📊 Admin Analytics Endpoints

### 1. Dashboard Overview
```
GET /api/admin/analytics/dashboard
```

**Description:** Get comprehensive analytics dashboard with all key metrics

**Query Parameters:**
- `from` (optional): Start date (Y-m-d format)
- `to` (optional): End date (Y-m-d format)
- `period` (optional): today|yesterday|last_7_days|last_30_days|this_month|last_month

**Response:**
```json
{
  "success": true,
  "data": {
    "visitors": {
      "total": 1250,
      "new": 450,
      "returning": 800,
      "growth_percentage": 15.5
    },
    "page_views": {
      "total": 5420,
      "unique": 3210,
      "avg_per_session": 4.3
    },
    "products": {
      "total_views": 2340,
      "unique_products": 156,
      "most_viewed": [...]
    },
    "checkout_funnel": {
      "cart_viewed": 450,
      "checkout_initiated": 320,
      "shipping_entered": 280,
      "payment_entered": 250,
      "completed": 220,
      "abandoned": 230,
      "conversion_rate": 48.9
    },
    "cart_events": {
      "added": 890,
      "updated": 234,
      "removed": 156
    },
    "search": {
      "total_searches": 567,
      "unique_queries": 234,
      "avg_results": 12.5,
      "top_queries": [...]
    }
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/dashboard?period=last_7_days" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 2. Visitors List
```
GET /api/admin/analytics/visitors
```

**Description:** Get list of all visitor sessions with pagination

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15)
- `from` (optional): Start date
- `to` (optional): End date
- `device_type` (optional): mobile|tablet|desktop
- `user_id` (optional): Filter by user ID

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "session_id": "abc123...",
        "user_id": 45,
        "user": {
          "id": 45,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "ip_address": "192.168.1.1",
        "device_type": "desktop",
        "browser": "Chrome",
        "platform": "Windows",
        "page_views": 12,
        "duration_seconds": 450,
        "first_visit_at": "2026-04-18 10:30:00",
        "last_activity_at": "2026-04-18 10:37:30"
      }
    ],
    "total": 1250,
    "per_page": 15,
    "last_page": 84
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/visitors?device_type=mobile&per_page=20" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 3. Visitor Details
```
GET /api/admin/analytics/visitors/{id}
```

**Description:** Get detailed information about a specific visitor session

**Response:**
```json
{
  "success": true,
  "data": {
    "session": {
      "id": 1,
      "session_id": "abc123...",
      "user_id": 45,
      "device_type": "desktop",
      "browser": "Chrome",
      "platform": "Windows",
      "page_views": 12,
      "duration_seconds": 450
    },
    "page_views": [
      {
        "id": 1,
        "page_type": "home",
        "page_url": "http://example.com",
        "page_title": "Home Page",
        "viewed_at": "2026-04-18 10:30:00"
      }
    ],
    "product_views": [
      {
        "id": 1,
        "product_id": 123,
        "product": {
          "id": 123,
          "name": "Product Name",
          "slug": "product-name"
        },
        "view_count": 3,
        "added_to_cart": true,
        "purchased": false
      }
    ],
    "cart_events": [
      {
        "id": 1,
        "event_type": "added",
        "product_id": 123,
        "quantity": 2,
        "price": 99.99,
        "event_at": "2026-04-18 10:32:00"
      }
    ],
    "checkout_funnel": {
      "status": "completed",
      "cart_viewed_at": "2026-04-18 10:30:00",
      "checkout_initiated_at": "2026-04-18 10:32:00",
      "completed_at": "2026-04-18 10:35:00",
      "order_id": 789
    },
    "searches": [
      {
        "query": "laptop",
        "results_count": 25,
        "clicked_result": true,
        "searched_at": "2026-04-18 10:31:00"
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/visitors/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 4. Product Views Analytics
```
GET /api/admin/analytics/product-views
```

**Description:** Get product view statistics and most viewed products

**Query Parameters:**
- `from` (optional): Start date
- `to` (optional): End date
- `limit` (optional): Number of top products (default: 20)
- `sort` (optional): views|conversions|cart_adds
- `category_id` (optional): Filter by category

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_views": 2340,
      "unique_products": 156,
      "avg_views_per_product": 15.0,
      "products_added_to_cart": 89,
      "products_purchased": 45,
      "conversion_rate": 28.8
    },
    "top_products": [
      {
        "product_id": 123,
        "product": {
          "id": 123,
          "name": "Product Name",
          "slug": "product-name",
          "price": 99.99,
          "image": "image.jpg"
        },
        "total_views": 234,
        "unique_visitors": 189,
        "added_to_cart_count": 45,
        "purchased_count": 23,
        "conversion_rate": 19.2
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/product-views?limit=10&sort=conversions" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 5. Checkout Funnel Analytics
```
GET /api/admin/analytics/checkout-funnel
```

**Description:** Get checkout funnel statistics and conversion rates

**Query Parameters:**
- `from` (optional): Start date
- `to` (optional): End date

**Response:**
```json
{
  "success": true,
  "data": {
    "funnel": {
      "cart_viewed": 450,
      "checkout_initiated": 320,
      "shipping_entered": 280,
      "payment_entered": 250,
      "completed": 220,
      "abandoned": 230
    },
    "conversion_rates": {
      "cart_to_checkout": 71.1,
      "checkout_to_shipping": 87.5,
      "shipping_to_payment": 89.3,
      "payment_to_completed": 88.0,
      "overall": 48.9
    },
    "drop_off": {
      "at_cart": 130,
      "at_checkout": 40,
      "at_shipping": 30,
      "at_payment": 30
    },
    "avg_time_to_complete": 420,
    "abandonment_reasons": {
      "timeout": 180,
      "unknown": 50
    }
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/checkout-funnel?from=2026-04-01&to=2026-04-18" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 6. Abandoned Carts
```
GET /api/admin/analytics/abandoned-carts
```

**Description:** Get list of abandoned carts with details

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page (default: 15)
- `from` (optional): Start date
- `to` (optional): End date
- `min_value` (optional): Minimum cart value

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "visitor_session_id": 123,
        "user_id": 45,
        "user": {
          "id": 45,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "status": "abandoned",
        "cart_items": [
          {
            "product_id": 1,
            "name": "Product Name",
            "quantity": 2,
            "price": 99.99
          }
        ],
        "cart_total": 199.98,
        "items_count": 2,
        "cart_viewed_at": "2026-04-18 10:30:00",
        "abandoned_at": "2026-04-18 11:00:00",
        "abandonment_reason": "timeout"
      }
    ],
    "total": 230,
    "summary": {
      "total_abandoned": 230,
      "total_value": 45678.90,
      "avg_cart_value": 198.60,
      "recoverable": 180
    }
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/abandoned-carts?min_value=100" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 7. Cart Events Analytics
```
GET /api/admin/analytics/cart-events
```

**Description:** Get cart event statistics (add, update, remove)

**Query Parameters:**
- `from` (optional): Start date
- `to` (optional): End date
- `event_type` (optional): added|updated|removed
- `product_id` (optional): Filter by product

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_events": 1280,
      "added": 890,
      "updated": 234,
      "removed": 156,
      "add_to_cart_rate": 38.0
    },
    "by_product": [
      {
        "product_id": 123,
        "product": {
          "id": 123,
          "name": "Product Name"
        },
        "added": 45,
        "updated": 12,
        "removed": 8,
        "net_adds": 37
      }
    ],
    "timeline": [
      {
        "date": "2026-04-18",
        "added": 120,
        "updated": 30,
        "removed": 20
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/cart-events?event_type=added" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 8. Search Analytics
```
GET /api/admin/analytics/search
```

**Description:** Get search query statistics and popular searches

**Query Parameters:**
- `from` (optional): Start date
- `to` (optional): End date
- `limit` (optional): Number of top queries (default: 20)
- `min_results` (optional): Minimum result count

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_searches": 567,
      "unique_queries": 234,
      "avg_results_per_search": 12.5,
      "searches_with_clicks": 345,
      "click_through_rate": 60.8,
      "zero_result_searches": 23
    },
    "top_queries": [
      {
        "query": "laptop",
        "search_count": 45,
        "avg_results": 25,
        "click_count": 38,
        "click_rate": 84.4,
        "top_clicked_products": [
          {
            "product_id": 123,
            "product_name": "Gaming Laptop",
            "clicks": 15
          }
        ]
      }
    ],
    "zero_result_queries": [
      {
        "query": "xyz product",
        "search_count": 5
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/search?limit=10" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 9. Page Views Analytics
```
GET /api/admin/analytics/page-views
```

**Description:** Get page view statistics by page type

**Query Parameters:**
- `from` (optional): Start date
- `to` (optional): End date
- `page_type` (optional): home|product|category|cart|checkout|other

**Response:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_views": 5420,
      "unique_visitors": 1250,
      "avg_views_per_visitor": 4.3
    },
    "by_page_type": [
      {
        "page_type": "home",
        "total_views": 1250,
        "unique_visitors": 980,
        "avg_time_on_page": 45
      },
      {
        "page_type": "product",
        "total_views": 2340,
        "unique_visitors": 890,
        "avg_time_on_page": 120
      }
    ],
    "top_pages": [
      {
        "page_url": "http://example.com/products/laptop",
        "page_title": "Gaming Laptop",
        "views": 234,
        "unique_visitors": 189
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/page-views?page_type=product" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

### 10. Export Analytics Data
```
GET /api/admin/analytics/export
```

**Description:** Export analytics data to CSV

**Query Parameters:**
- `type` (required): visitors|product_views|checkouts|searches
- `from` (optional): Start date
- `to` (optional): End date

**Response:** CSV file download

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/analytics/export?type=visitors&from=2026-04-01&to=2026-04-18" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  --output analytics_export.csv
```

**Export Types:**
- `visitors` - Visitor sessions data
- `product_views` - Product view statistics
- `checkouts` - Checkout funnel data
- `searches` - Search query data

---

## 🔑 Authentication

### Get Admin Token

```bash
# Login as admin
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Response:**
```json
{
  "success": true,
  "token": "1|abc123xyz...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

### Use Token in Requests

```bash
curl -X GET http://localhost:8000/api/admin/analytics/dashboard \
  -H "Authorization: Bearer 1|abc123xyz..."
```

---

## 📋 Quick Reference

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/admin/analytics/dashboard` | GET | Overview dashboard |
| `/api/admin/analytics/visitors` | GET | Visitor list |
| `/api/admin/analytics/visitors/{id}` | GET | Visitor details |
| `/api/admin/analytics/product-views` | GET | Product analytics |
| `/api/admin/analytics/checkout-funnel` | GET | Funnel analytics |
| `/api/admin/analytics/abandoned-carts` | GET | Abandoned carts |
| `/api/admin/analytics/cart-events` | GET | Cart events |
| `/api/admin/analytics/search` | GET | Search analytics |
| `/api/admin/analytics/page-views` | GET | Page views |
| `/api/admin/analytics/export` | GET | Export data (CSV) |

---

## 🎯 Common Query Parameters

All endpoints support these common parameters:

- `from` - Start date (Y-m-d format, e.g., 2026-04-01)
- `to` - End date (Y-m-d format, e.g., 2026-04-18)
- `period` - Predefined period (today|yesterday|last_7_days|last_30_days|this_month|last_month)

---

## 📚 Related Documentation

- `FRONTEND_TRACKING_GUIDE.md` - Frontend integration
- `ANALYTICS_API_TEST_RESULTS.md` - API verification
- `ANALYTICS_SYSTEM_DOCUMENTATION.md` - Complete reference
- `WORKERS_AND_JOBS_SETUP_GUIDE.md` - Setup guide

---

**Generated:** April 18, 2026  
**Base URL:** `http://localhost:8000/api/admin/analytics`  
**Authentication:** Required (Bearer token + admin role)
