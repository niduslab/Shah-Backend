# Frontend Analytics Tracking Guide

## Overview

This guide shows how to integrate analytics tracking into your frontend application (React, Vue, or vanilla JavaScript).

## 🔌 API Endpoints

All tracking endpoints are **public** (no authentication required) and accept POST requests:

```
POST /api/analytics/track/page-view
POST /api/analytics/track/product-view
POST /api/analytics/track/cart-event
POST /api/analytics/track/checkout
POST /api/analytics/track/search
```

---

## 📊 1. Track Page Views

**When to use:** Every page load (home, category, product, cart, checkout, etc.)

### Request
```http
POST /api/analytics/track/page-view
Content-Type: application/json

{
  "page_type": "home",           // Required: home|product|category|cart|checkout|other
  "page_title": "Home Page",     // Optional: Page title
  "product_id": 123,             // Optional: For product pages
  "category_id": 45              // Optional: For category pages
}
```

### Response
```json
{
  "success": true,
  "message": "Page view tracked"
}
```

### JavaScript Example
```javascript
async function trackPageView(pageType, pageTitle, productId = null, categoryId = null) {
  try {
    const response = await fetch('/api/analytics/track/page-view', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({
        page_type: pageType,
        page_title: pageTitle,
        product_id: productId,
        category_id: categoryId
      })
    });
    
    const data = await response.json();
    console.log('Page view tracked:', data);
  } catch (error) {
    console.error('Analytics error:', error);
  }
}

// Usage examples:
trackPageView('home', 'Home Page');
trackPageView('product', 'Product Name', 123);
trackPageView('category', 'Electronics', null, 45);
trackPageView('cart', 'Shopping Cart');
trackPageView('checkout', 'Checkout');
```

---

## 🛍️ 2. Track Product Views

**When to use:** When a product page loads or product details are viewed

### Request
```http
POST /api/analytics/track/product-view
Content-Type: application/json

{
  "product_id": 123              // Required: Product ID
}
```

### Response
```json
{
  "success": true,
  "message": "Product view tracked"
}
```

### JavaScript Example
```javascript
async function trackProductView(productId) {
  try {
    const response = await fetch('/api/analytics/track/product-view', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({
        product_id: productId
      })
    });
    
    const data = await response.json();
    console.log('Product view tracked:', data);
  } catch (error) {
    console.error('Analytics error:', error);
  }
}

// Usage:
trackProductView(123);
```

---

## 🛒 3. Track Cart Events

**When to use:** When items are added, updated, or removed from cart

### Request
```http
POST /api/analytics/track/cart-event
Content-Type: application/json

{
  "event_type": "added",         // Required: added|updated|removed
  "product_id": 123,             // Required: Product ID
  "quantity": 2,                 // Required: Quantity
  "price": 99.99,                // Required: Product price
  "variation_id": 456            // Optional: Product variation ID
}
```

### Response
```json
{
  "success": true,
  "message": "Cart event tracked"
}
```

### JavaScript Example
```javascript
async function trackCartEvent(eventType, productId, quantity, price, variationId = null) {
  try {
    const response = await fetch('/api/analytics/track/cart-event', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({
        event_type: eventType,
        product_id: productId,
        quantity: quantity,
        price: price,
        variation_id: variationId
      })
    });
    
    const data = await response.json();
    console.log('Cart event tracked:', data);
  } catch (error) {
    console.error('Analytics error:', error);
  }
}

// Usage examples:
// Add to cart
trackCartEvent('added', 123, 2, 99.99);

// Add with variation
trackCartEvent('added', 123, 1, 99.99, 456);

// Update quantity
trackCartEvent('updated', 123, 3, 99.99);

// Remove from cart
trackCartEvent('removed', 123, 1, 99.99);
```

---

## 💳 4. Track Checkout Funnel

**When to use:** At each stage of the checkout process

### Request
```http
POST /api/analytics/track/checkout
Content-Type: application/json

{
  "status": "cart_viewed",       // Required: See statuses below
  "cart_items": [...],           // Optional: Array of cart items
  "cart_total": 299.99,          // Optional: Total cart value
  "items_count": 3,              // Optional: Number of items
  "order_id": 789,               // Required for order_completed
  "product_ids": [1, 2, 3],      // Required for order_completed
  "reason": "timeout"            // Optional: For abandoned status
}
```

### Status Values
- `cart_viewed` - Customer viewed their cart
- `checkout_initiated` - Customer clicked checkout button
- `shipping_info_entered` - Shipping details provided
- `payment_info_entered` - Payment details provided
- `order_completed` - Purchase successful ✅
- `abandoned` - Customer left without completing ❌

### Response
```json
{
  "success": true,
  "message": "Checkout stage tracked"
}
```

### JavaScript Example
```javascript
async function trackCheckout(status, cartData = {}) {
  try {
    const response = await fetch('/api/analytics/track/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({
        status: status,
        ...cartData
      })
    });
    
    const data = await response.json();
    console.log('Checkout tracked:', data);
  } catch (error) {
    console.error('Analytics error:', error);
  }
}

// Usage examples:

// 1. Cart page viewed
trackCheckout('cart_viewed', {
  cart_items: [
    { product_id: 1, name: 'Product A', quantity: 2, price: 99.99 },
    { product_id: 2, name: 'Product B', quantity: 1, price: 149.99 }
  ],
  cart_total: 349.97,
  items_count: 3
});

// 2. Checkout button clicked
trackCheckout('checkout_initiated', {
  cart_total: 349.97,
  items_count: 3
});

// 3. Shipping info entered
trackCheckout('shipping_info_entered');

// 4. Payment info entered
trackCheckout('payment_info_entered');

// 5. Order completed
trackCheckout('order_completed', {
  order_id: 789,
  product_ids: [1, 2]
});

// 6. Abandoned (optional - system auto-detects after 30 min)
trackCheckout('abandoned', {
  reason: 'user_closed_tab'
});
```

---

## 🔍 5. Track Search

**When to use:** When users search for products

### Request
```http
POST /api/analytics/track/search
Content-Type: application/json

{
  "query": "laptop",             // Required: Search query
  "results_count": 25,           // Required: Number of results
  "clicked_product_id": 123      // Optional: When user clicks a result
}
```

### Response
```json
{
  "success": true,
  "message": "Search tracked"
}
```

### JavaScript Example
```javascript
async function trackSearch(query, resultsCount, clickedProductId = null) {
  try {
    const response = await fetch('/api/analytics/track/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({
        query: query,
        results_count: resultsCount,
        clicked_product_id: clickedProductId
      })
    });
    
    const data = await response.json();
    console.log('Search tracked:', data);
  } catch (error) {
    console.error('Analytics error:', error);
  }
}

// Usage examples:

// When search is performed
trackSearch('laptop', 25);

// When user clicks a search result
trackSearch('laptop', 25, 123);
```

---

## 🎯 Complete Integration Example

### React Component Example

```javascript
import { useEffect } from 'react';

// Create analytics utility
const analytics = {
  async track(endpoint, data) {
    try {
      const response = await fetch(`/api/analytics/track/${endpoint}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify(data)
      });
      return await response.json();
    } catch (error) {
      console.error('Analytics error:', error);
    }
  },

  trackPageView: (pageType, pageTitle, productId, categoryId) => 
    analytics.track('page-view', { page_type: pageType, page_title: pageTitle, product_id: productId, category_id: categoryId }),

  trackProductView: (productId) => 
    analytics.track('product-view', { product_id: productId }),

  trackCartEvent: (eventType, productId, quantity, price, variationId) => 
    analytics.track('cart-event', { event_type: eventType, product_id: productId, quantity, price, variation_id: variationId }),

  trackCheckout: (status, cartData) => 
    analytics.track('checkout', { status, ...cartData }),

  trackSearch: (query, resultsCount, clickedProductId) => 
    analytics.track('search', { query, results_count: resultsCount, clicked_product_id: clickedProductId })
};

// Product Page Component
function ProductPage({ product }) {
  useEffect(() => {
    // Track product view when component mounts
    analytics.trackProductView(product.id);
  }, [product.id]);

  const handleAddToCart = () => {
    // Your add to cart logic...
    
    // Track cart event
    analytics.trackCartEvent('added', product.id, quantity, product.price, selectedVariation?.id);
  };

  return (
    <div>
      <h1>{product.name}</h1>
      <button onClick={handleAddToCart}>Add to Cart</button>
    </div>
  );
}

// Cart Page Component
function CartPage({ cartItems, cartTotal }) {
  useEffect(() => {
    // Track cart viewed
    analytics.trackCheckout('cart_viewed', {
      cart_items: cartItems,
      cart_total: cartTotal,
      items_count: cartItems.length
    });
  }, []);

  const handleCheckout = () => {
    // Track checkout initiated
    analytics.trackCheckout('checkout_initiated', {
      cart_total: cartTotal,
      items_count: cartItems.length
    });
    
    // Navigate to checkout...
  };

  return (
    <div>
      <h1>Shopping Cart</h1>
      <button onClick={handleCheckout}>Proceed to Checkout</button>
    </div>
  );
}

// Checkout Component
function CheckoutPage() {
  const handleShippingSubmit = () => {
    // Your shipping logic...
    
    // Track shipping entered
    analytics.trackCheckout('shipping_info_entered');
  };

  const handlePaymentSubmit = () => {
    // Your payment logic...
    
    // Track payment entered
    analytics.trackCheckout('payment_info_entered');
  };

  const handleOrderComplete = (orderId, productIds) => {
    // Track order completed
    analytics.trackCheckout('order_completed', {
      order_id: orderId,
      product_ids: productIds
    });
  };

  return <div>Checkout Form...</div>;
}

// Search Component
function SearchBar() {
  const handleSearch = async (query) => {
    const results = await searchProducts(query);
    
    // Track search
    analytics.trackSearch(query, results.length);
  };

  const handleResultClick = (productId, query, resultsCount) => {
    // Track search result click
    analytics.trackSearch(query, resultsCount, productId);
  };

  return <input type="search" onChange={(e) => handleSearch(e.target.value)} />;
}

export default analytics;
```

---

## 🚀 Quick Setup Checklist

- [ ] Copy the analytics utility code to your project
- [ ] Add tracking to product pages (trackProductView)
- [ ] Add tracking to cart actions (trackCartEvent)
- [ ] Add tracking to cart page (trackCheckout - cart_viewed)
- [ ] Add tracking to checkout button (trackCheckout - checkout_initiated)
- [ ] Add tracking to checkout steps (shipping, payment)
- [ ] Add tracking to order completion (trackCheckout - order_completed)
- [ ] Add tracking to search (trackSearch)
- [ ] Test all tracking endpoints

---

## 🐛 Troubleshooting

### CSRF Token Error
Make sure you have the CSRF token meta tag in your HTML:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### CORS Error
If calling from a different domain, ensure CORS is configured in Laravel.

### 404 Error
Verify routes are registered:
```bash
php artisan route:list | grep analytics
```

### Not Tracking
Check browser console for errors and verify the API endpoints are accessible.

---

## 📊 What Gets Tracked Automatically

Once you integrate the tracking calls:
- ✅ Visitor sessions (automatic)
- ✅ Device type (automatic)
- ✅ Browser info (automatic)
- ✅ Session duration (automatic)
- ✅ Page views (when you call trackPageView)
- ✅ Product views (when you call trackProductView)
- ✅ Cart events (when you call trackCartEvent)
- ✅ Checkout funnel (when you call trackCheckout)
- ✅ Search queries (when you call trackSearch)

---

## 🎉 You're Done!

Your frontend is now tracking all customer interactions. View the data in the admin dashboard:

```
GET /api/admin/analytics/dashboard
```

For more details, see:
- `ADMIN_ANALYTICS_QUICK_GUIDE.md` - Admin dashboard usage
- `ANALYTICS_INTEGRATION_EXAMPLES.md` - More code examples
- `ANALYTICS_SYSTEM_DOCUMENTATION.md` - Complete API reference
