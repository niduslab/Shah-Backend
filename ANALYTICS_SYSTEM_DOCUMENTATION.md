# E-Commerce Analytics System Documentation

## Overview

This comprehensive analytics system tracks visitor behavior, product views, cart activities, checkout funnel, and search queries for your e-commerce platform. It provides detailed insights into customer journey from landing to purchase or abandonment.

## Features

### 1. **Visitor Session Tracking**
- Tracks unique visitor sessions with device, browser, and location information
- Monitors session duration and page views
- Distinguishes between authenticated users and guests
- Tracks referrer and landing pages

### 2. **Page View Analytics**
- Records all page views with timestamps
- Tracks time spent on each page
- Categorizes pages by type (home, product, category, cart, checkout, etc.)
- Links page views to products and categories

### 3. **Product View Tracking**
- Aggregates product views per session
- Tracks view count and time spent on product pages
- Monitors conversion from view to cart addition
- Tracks conversion from view to purchase
- Identifies most viewed products

### 4. **Cart Event Tracking**
- Records all cart events (add, update, remove)
- Tracks product quantities and prices
- Monitors cart abandonment patterns
- Identifies most added/removed products

### 5. **Checkout Funnel Analytics**
- Tracks complete checkout journey:
  - Cart viewed
  - Checkout initiated
  - Shipping info entered
  - Payment info entered
  - Order completed
  - Abandoned
- Calculates conversion and abandonment rates
- Stores cart snapshots for abandoned carts
- Tracks total abandoned cart value

### 6. **Search Analytics**
- Records all search queries
- Tracks search results count
- Monitors click-through rates
- Identifies searches with no results
- Shows most popular search terms

## Database Schema

### Tables Created

1. **visitor_sessions** - Stores visitor session information
2. **page_views** - Records individual page views
3. **product_views** - Aggregates product view data
4. **cart_events** - Tracks cart add/update/remove events
5. **checkout_funnels** - Monitors checkout process stages
6. **search_queries** - Records search activity

## Installation Steps

### 1. Install Dependencies

```bash
composer require jenssegers/agent
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Schedule Abandoned Cart Tracking

The system automatically marks checkouts as abandoned after 30 minutes of inactivity. Ensure your cron is set up:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or manually run:
```bash
php artisan analytics:mark-abandoned-checkouts
```

## API Endpoints

### Public Tracking Endpoints (Frontend Integration)

#### Track Page View
```http
POST /api/analytics/track/page-view
Content-Type: application/json

{
  "page_type": "home|product|category|cart|checkout|other",
  "page_title": "Page Title",
  "product_id": 123,  // optional, for product pages
  "category_id": 45   // optional, for category pages
}
```

#### Track Product View
```http
POST /api/analytics/track/product-view
Content-Type: application/json

{
  "product_id": 123
}
```

#### Track Cart Event
```http
POST /api/analytics/track/cart-event
Content-Type: application/json

{
  "event_type": "added|updated|removed",
  "product_id": 123,
  "quantity": 2,
  "price": 99.99,
  "variation_id": 456  // optional
}
```

#### Track Checkout Stage
```http
POST /api/analytics/track/checkout
Content-Type: application/json

{
  "status": "cart_viewed|checkout_initiated|shipping_info_entered|payment_info_entered|order_completed|abandoned",
  "cart_items": [...],  // optional, array of cart items
  "cart_total": 299.99,
  "items_count": 3,
  "order_id": 789,      // required for order_completed
  "product_ids": [1,2,3], // required for order_completed
  "reason": "timeout"   // optional, for abandoned
}
```

#### Track Search
```http
POST /api/analytics/track/search
Content-Type: application/json

{
  "query": "laptop",
  "results_count": 25,
  "clicked_product_id": 123  // optional, when user clicks a result
}
```

### Admin Analytics Endpoints

All admin endpoints require authentication and admin role: `middleware(['auth:sanctum', 'admin'])`

#### Get Analytics Dashboard
```http
GET /api/admin/analytics/dashboard?date_from=2024-01-01&date_to=2024-12-31
```

**Response:**
```json
{
  "success": true,
  "data": {
    "visitors": {
      "total_visitors": 5000,
      "unique_visitors": 4200,
      "authenticated_visitors": 1500,
      "guest_visitors": 3500,
      "avg_session_duration": 8.5,
      "by_device": {
        "mobile": 2500,
        "desktop": 2000,
        "tablet": 500
      }
    },
    "page_views": {...},
    "products": {...},
    "checkout_funnel": {...},
    "cart_events": {...},
    "search": {...}
  }
}
```

#### Get Visitor Sessions
```http
GET /api/admin/analytics/visitors?per_page=15&date_from=2024-01-01&date_to=2024-12-31&device_type=mobile&authenticated=true
```

#### Get Visitor Session Details
```http
GET /api/admin/analytics/visitors/{session_id}
```

#### Get Product View Analytics
```http
GET /api/admin/analytics/product-views?per_page=15&sort_by=views|conversions|purchases
```

#### Get Checkout Funnel Data
```http
GET /api/admin/analytics/checkout-funnel?status=abandoned&date_from=2024-01-01
```

#### Get Abandoned Carts
```http
GET /api/admin/analytics/abandoned-carts?min_value=100&date_from=2024-01-01
```

#### Get Cart Events
```http
GET /api/admin/analytics/cart-events?event_type=added&date_from=2024-01-01
```

#### Get Search Analytics
```http
GET /api/admin/analytics/search?no_results=true
```

#### Get Page Views
```http
GET /api/admin/analytics/page-views?page_type=product
```

#### Export Analytics Data
```http
GET /api/admin/analytics/export?type=visitors|products|checkouts|searches&date_from=2024-01-01&date_to=2024-12-31
```

Returns CSV file download.

## Frontend Integration Guide

### 1. Track Page Views Automatically

Add this to your main layout or router:

```javascript
// When page loads or route changes
async function trackPageView(pageType, pageTitle, productId = null, categoryId = null) {
  await fetch('/api/analytics/track/page-view', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      page_type: pageType,
      page_title: pageTitle,
      product_id: productId,
      category_id: categoryId
    })
  });
}

// Example usage
trackPageView('home', 'Home Page');
trackPageView('product', 'Product Name', 123);
trackPageView('category', 'Electronics', null, 45);
```

### 2. Track Product Views

```javascript
// On product page load
async function trackProductView(productId) {
  await fetch('/api/analytics/track/product-view', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      product_id: productId
    })
  });
}

// Call when product page loads
trackProductView(123);
```

### 3. Track Cart Events

```javascript
// When adding to cart
async function trackCartAdd(productId, quantity, price, variationId = null) {
  await fetch('/api/analytics/track/cart-event', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      event_type: 'added',
      product_id: productId,
      quantity: quantity,
      price: price,
      variation_id: variationId
    })
  });
}

// When updating cart
async function trackCartUpdate(productId, quantity, price) {
  await fetch('/api/analytics/track/cart-event', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      event_type: 'updated',
      product_id: productId,
      quantity: quantity,
      price: price
    })
  });
}

// When removing from cart
async function trackCartRemove(productId, quantity, price) {
  await fetch('/api/analytics/track/cart-event', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      event_type: 'removed',
      product_id: productId,
      quantity: quantity,
      price: price
    })
  });
}
```

### 4. Track Checkout Funnel

```javascript
// When cart page is viewed
async function trackCartViewed(cartItems, cartTotal, itemsCount) {
  await fetch('/api/analytics/track/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      status: 'cart_viewed',
      cart_items: cartItems,
      cart_total: cartTotal,
      items_count: itemsCount
    })
  });
}

// When checkout is initiated
async function trackCheckoutInitiated(cartItems, cartTotal, itemsCount) {
  await fetch('/api/analytics/track/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      status: 'checkout_initiated',
      cart_items: cartItems,
      cart_total: cartTotal,
      items_count: itemsCount
    })
  });
}

// When shipping info is entered
async function trackShippingEntered() {
  await fetch('/api/analytics/track/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      status: 'shipping_info_entered'
    })
  });
}

// When payment info is entered
async function trackPaymentEntered() {
  await fetch('/api/analytics/track/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      status: 'payment_info_entered'
    })
  });
}

// When order is completed
async function trackOrderCompleted(orderId, productIds) {
  await fetch('/api/analytics/track/checkout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      status: 'order_completed',
      order_id: orderId,
      product_ids: productIds
    })
  });
}
```

### 5. Track Search

```javascript
// When search is performed
async function trackSearch(query, resultsCount) {
  await fetch('/api/analytics/track/search', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      query: query,
      results_count: resultsCount
    })
  });
}

// When search result is clicked
async function trackSearchClick(query, resultsCount, clickedProductId) {
  await fetch('/api/analytics/track/search', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      query: query,
      results_count: resultsCount,
      clicked_product_id: clickedProductId
    })
  });
}
```

## Key Metrics Available

### Visitor Metrics
- Total visitors (unique and returning)
- Session duration
- Device breakdown (mobile, tablet, desktop)
- Browser and platform statistics
- Geographic data (if available)
- Authenticated vs guest visitors

### Product Metrics
- Total product views
- Unique products viewed
- Average time spent on product pages
- View-to-cart conversion rate
- View-to-purchase conversion rate
- Top viewed products
- Products with highest conversion rates

### Cart Metrics
- Items added to cart
- Items removed from cart
- Most added products
- Cart abandonment patterns

### Checkout Funnel Metrics
- Total checkouts initiated
- Completion rate
- Abandonment rate at each stage
- Average cart value
- Total abandoned cart value
- Conversion rate from cart to purchase

### Search Metrics
- Total searches
- Unique search queries
- Average results per search
- Searches with no results
- Click-through rate
- Most popular search terms

## Use Cases

### 1. Identify Popular Products
Use product view analytics to see which products get the most attention and optimize inventory accordingly.

### 2. Reduce Cart Abandonment
Analyze abandoned carts to identify patterns and implement recovery strategies (email campaigns, exit-intent popups, etc.).

### 3. Optimize Checkout Process
Track where users drop off in the checkout funnel and optimize those specific steps.

### 4. Improve Search Functionality
Identify searches with no results and add those products or improve search algorithms.

### 5. Understand Customer Journey
Track complete visitor sessions to understand how customers navigate your site before making a purchase.

### 6. Device Optimization
See which devices your customers use most and optimize the experience for those platforms.

### 7. Marketing Attribution
Track referrers and landing pages to understand which marketing channels drive the most traffic and conversions.

## Performance Considerations

1. **Asynchronous Tracking**: All tracking calls should be made asynchronously to not block the user experience.

2. **Batch Processing**: Consider batching analytics events on the frontend and sending them in bulk to reduce server requests.

3. **Database Indexing**: The migration includes proper indexes on frequently queried columns.

4. **Data Retention**: Consider implementing a data retention policy to archive or delete old analytics data.

5. **Caching**: Use Redis or similar for caching frequently accessed analytics summaries.

## Privacy & GDPR Compliance

1. **IP Address Storage**: Consider anonymizing IP addresses for GDPR compliance.

2. **User Consent**: Implement cookie consent banners before tracking.

3. **Data Access**: Provide users ability to request their tracked data.

4. **Data Deletion**: Implement functionality to delete user analytics data upon request.

## Future Enhancements

1. **Real-time Dashboard**: Implement WebSocket-based real-time analytics dashboard
2. **Heatmaps**: Add click heatmap tracking
3. **A/B Testing**: Integrate A/B testing capabilities
4. **Cohort Analysis**: Track user cohorts over time
5. **Predictive Analytics**: Use ML to predict purchase likelihood
6. **Email Recovery**: Automated abandoned cart email campaigns
7. **Custom Events**: Allow tracking of custom business events

## Support

For issues or questions, refer to the Laravel documentation or contact your development team.

---

**Version**: 1.0.0  
**Last Updated**: April 18, 2026
