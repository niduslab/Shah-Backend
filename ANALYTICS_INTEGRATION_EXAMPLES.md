# Analytics Integration Examples

This document shows how to integrate analytics tracking into your existing controllers.

## 1. Integrate into CheckoutController

Add analytics tracking to your checkout process:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $analyticsService;

    public function __construct(
        protected OrderServiceInterface $orderService,
        AnalyticsService $analyticsService
    ) {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Preview checkout (before final submission).
     */
    public function preview(Request $request)
    {
        // Your existing preview logic...
        
        // Track checkout initiated
        $this->analyticsService->trackCheckoutFunnel($request, 'checkout_initiated', [
            'items' => $request->input('items'),
            'total' => $calculatedTotal,
            'items_count' => count($request->input('items'))
        ]);

        return response()->json([...]);
    }

    /**
     * Process checkout and create order.
     */
    public function process(Request $request)
    {
        // Your existing order creation logic...
        $order = $this->orderService->createOrder($validated);

        // Track order completed
        $productIds = collect($validated['items'])->pluck('product_id')->toArray();
        
        $this->analyticsService->trackCheckoutFunnel($request, 'order_completed', [
            'order_id' => $order->id,
            'product_ids' => $productIds,
            'total' => $order->total,
            'items_count' => $order->items->count()
        ]);

        return response()->json([...]);
    }
}
```

## 2. Integrate into CatalogController

Track product and category views:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get single product details.
     */
    public function product(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Track product view
        $this->analyticsService->trackProductView($request, $product->id);

        return response()->json([
            'success' => true,
            'data' => $product->load(['images', 'category', 'brand', 'variations'])
        ]);
    }

    /**
     * Get products by category.
     */
    public function productsByCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // Track category page view
        $this->analyticsService->trackPageView($request, 'category', null, $category->id);

        $products = Product::where('category_id', $category->id)
            ->active()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'products' => $products
            ]
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->active()
            ->get();

        // Track search
        $this->analyticsService->trackSearch($request, $query, $products->count());

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
```

## 3. Integrate into CartController

Track cart events:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Add item to cart (if you have a cart endpoint).
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Your cart logic...
        
        // Track cart event
        $this->analyticsService->trackCartEvent(
            $request,
            'added',
            $product->id,
            $validated['quantity'],
            $product->price,
            $validated['variation_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart'
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Your cart update logic...
        
        // Track cart event
        $eventType = $validated['quantity'] > 0 ? 'updated' : 'removed';
        
        $this->analyticsService->trackCartEvent(
            $request,
            $eventType,
            $product->id,
            $validated['quantity'],
            $product->price
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart updated'
        ]);
    }

    /**
     * View cart summary.
     */
    public function summary(Request $request)
    {
        // Your cart summary logic...
        $cartData = $this->getCartData($request);
        
        // Track cart viewed
        $this->analyticsService->trackCheckoutFunnel($request, 'cart_viewed', [
            'items' => $cartData['items'],
            'total' => $cartData['total'],
            'items_count' => count($cartData['items'])
        ]);

        return response()->json([
            'success' => true,
            'data' => $cartData
        ]);
    }
}
```

## 4. Integrate into WishlistController

Track wishlist additions:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Models\ProductView;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Add product to wishlist.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Your wishlist logic...
        
        // Update product view to mark wishlist addition
        $session = $this->analyticsService->getOrCreateSession($request);
        
        ProductView::where('visitor_session_id', $session->id)
            ->where('product_id', $validated['product_id'])
            ->update(['added_to_wishlist' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist'
        ]);
    }
}
```

## 5. Backend Integration in OrderService

Track when orders are completed:

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Services\AnalyticsService;

class OrderService
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Create order from checkout data.
     */
    public function createOrder(array $data, $request = null)
    {
        // Your order creation logic...
        $order = Order::create([...]);
        
        // If request is available, track analytics
        if ($request) {
            $productIds = collect($data['items'])->pluck('product_id')->toArray();
            
            $this->analyticsService->trackCheckoutFunnel($request, 'order_completed', [
                'order_id' => $order->id,
                'product_ids' => $productIds,
                'total' => $order->total,
                'items_count' => count($data['items'])
            ]);
        }

        return $order;
    }
}
```

## 6. Frontend JavaScript Integration

### React/Vue Example

```javascript
// analytics.js - Create a utility file
import axios from 'axios';

const API_BASE = '/api/analytics/track';

export const analytics = {
  // Track page view
  trackPageView(pageType, pageTitle, productId = null, categoryId = null) {
    return axios.post(`${API_BASE}/page-view`, {
      page_type: pageType,
      page_title: pageTitle,
      product_id: productId,
      category_id: categoryId
    }).catch(err => console.error('Analytics error:', err));
  },

  // Track product view
  trackProductView(productId) {
    return axios.post(`${API_BASE}/product-view`, {
      product_id: productId
    }).catch(err => console.error('Analytics error:', err));
  },

  // Track cart event
  trackCartEvent(eventType, productId, quantity, price, variationId = null) {
    return axios.post(`${API_BASE}/cart-event`, {
      event_type: eventType,
      product_id: productId,
      quantity: quantity,
      price: price,
      variation_id: variationId
    }).catch(err => console.error('Analytics error:', err));
  },

  // Track checkout stage
  trackCheckout(status, cartData = {}) {
    return axios.post(`${API_BASE}/checkout`, {
      status: status,
      ...cartData
    }).catch(err => console.error('Analytics error:', err));
  },

  // Track search
  trackSearch(query, resultsCount, clickedProductId = null) {
    return axios.post(`${API_BASE}/search`, {
      query: query,
      results_count: resultsCount,
      clicked_product_id: clickedProductId
    }).catch(err => console.error('Analytics error:', err));
  }
};

// Usage in components:

// ProductPage.vue
import { analytics } from '@/utils/analytics';

export default {
  mounted() {
    // Track product view when component mounts
    analytics.trackProductView(this.product.id);
  },
  methods: {
    addToCart() {
      // Your add to cart logic...
      
      // Track cart event
      analytics.trackCartEvent(
        'added',
        this.product.id,
        this.quantity,
        this.product.price,
        this.selectedVariation?.id
      );
    }
  }
}

// CartPage.vue
export default {
  mounted() {
    // Track cart viewed
    analytics.trackCheckout('cart_viewed', {
      cart_items: this.cartItems,
      cart_total: this.cartTotal,
      items_count: this.cartItems.length
    });
  }
}

// CheckoutPage.vue
export default {
  methods: {
    proceedToCheckout() {
      // Track checkout initiated
      analytics.trackCheckout('checkout_initiated', {
        cart_items: this.cartItems,
        cart_total: this.cartTotal,
        items_count: this.cartItems.length
      });
      
      // Navigate to checkout...
    },
    
    submitShippingInfo() {
      // Your shipping logic...
      
      // Track shipping info entered
      analytics.trackCheckout('shipping_info_entered');
    },
    
    submitPaymentInfo() {
      // Your payment logic...
      
      // Track payment info entered
      analytics.trackCheckout('payment_info_entered');
    },
    
    orderCompleted(orderId) {
      // Track order completed
      const productIds = this.cartItems.map(item => item.product_id);
      
      analytics.trackCheckout('order_completed', {
        order_id: orderId,
        product_ids: productIds
      });
    }
  }
}

// SearchComponent.vue
export default {
  methods: {
    async performSearch() {
      const results = await this.searchProducts(this.searchQuery);
      
      // Track search
      analytics.trackSearch(this.searchQuery, results.length);
    },
    
    clickProduct(productId) {
      // Track search result click
      analytics.trackSearch(
        this.searchQuery,
        this.searchResults.length,
        productId
      );
      
      // Navigate to product...
    }
  }
}
```

## 7. Vanilla JavaScript Integration

```javascript
// For plain JavaScript/jQuery projects

class Analytics {
  constructor() {
    this.baseUrl = '/api/analytics/track';
  }

  async track(endpoint, data) {
    try {
      const response = await fetch(`${this.baseUrl}/${endpoint}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
      });
      return await response.json();
    } catch (error) {
      console.error('Analytics error:', error);
    }
  }

  trackPageView(pageType, pageTitle, productId = null, categoryId = null) {
    return this.track('page-view', {
      page_type: pageType,
      page_title: pageTitle,
      product_id: productId,
      category_id: categoryId
    });
  }

  trackProductView(productId) {
    return this.track('product-view', { product_id: productId });
  }

  trackCartEvent(eventType, productId, quantity, price, variationId = null) {
    return this.track('cart-event', {
      event_type: eventType,
      product_id: productId,
      quantity: quantity,
      price: price,
      variation_id: variationId
    });
  }

  trackCheckout(status, cartData = {}) {
    return this.track('checkout', { status: status, ...cartData });
  }

  trackSearch(query, resultsCount, clickedProductId = null) {
    return this.track('search', {
      query: query,
      results_count: resultsCount,
      clicked_product_id: clickedProductId
    });
  }
}

// Initialize
const analytics = new Analytics();

// Usage examples:

// On product page load
analytics.trackProductView(123);

// On add to cart button click
document.getElementById('add-to-cart').addEventListener('click', function() {
  const productId = this.dataset.productId;
  const quantity = document.getElementById('quantity').value;
  const price = this.dataset.price;
  
  analytics.trackCartEvent('added', productId, quantity, price);
});

// On cart page load
window.addEventListener('load', function() {
  if (window.location.pathname === '/cart') {
    analytics.trackCheckout('cart_viewed', {
      cart_items: window.cartItems,
      cart_total: window.cartTotal,
      items_count: window.cartItems.length
    });
  }
});
```

## 8. Automatic Page View Tracking

Add this to your main layout file to automatically track all page views:

```javascript
// In your main layout (e.g., app.blade.php or main.js)

document.addEventListener('DOMContentLoaded', function() {
  // Determine page type from URL or data attribute
  const pageType = document.body.dataset.pageType || 'other';
  const pageTitle = document.title;
  const productId = document.body.dataset.productId || null;
  const categoryId = document.body.dataset.categoryId || null;
  
  // Track page view
  analytics.trackPageView(pageType, pageTitle, productId, categoryId);
});
```

Then in your Blade templates, add data attributes:

```html
<!-- Product page -->
<body data-page-type="product" data-product-id="{{ $product->id }}">

<!-- Category page -->
<body data-page-type="category" data-category-id="{{ $category->id }}">

<!-- Home page -->
<body data-page-type="home">

<!-- Cart page -->
<body data-page-type="cart">

<!-- Checkout page -->
<body data-page-type="checkout">
```

## Summary

The analytics system is designed to be:
- **Non-intrusive**: Tracking happens asynchronously
- **Flexible**: Can be integrated gradually
- **Comprehensive**: Tracks entire customer journey
- **Privacy-aware**: Respects user sessions

Start by integrating the most critical touchpoints (product views, cart events, checkout stages) and expand from there.
