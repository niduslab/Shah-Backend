# Frontend SSL Commerz Integration Guide

## Overview
Complete guide for integrating SSL Commerz payment gateway in your React/Vue/Next.js frontend application.

## Table of Contents
1. [API Endpoints](#api-endpoints)
2. [React Implementation](#react-implementation)
3. [Vue.js Implementation](#vuejs-implementation)
4. [Next.js Implementation](#nextjs-implementation)
5. [Payment Flow](#payment-flow)
6. [Error Handling](#error-handling)
7. [Testing](#testing)

---

## API Endpoints

### Base URL
```
Development: http://127.0.0.1:8000/api
Production: https://your-domain.com/api
```

### Authentication
All authenticated endpoints require Bearer token:
```
Authorization: Bearer {your_token}
```

### Key Endpoints

#### 1. Checkout Process
```
POST /checkout/process
```

**Request Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "variation_id": 2,
      "quantity": 1,
      "price": 1500.00
    }
  ],
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz",
  "notes": "Optional delivery notes",
  "coupon_code": "SAVE10"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order": {
      "id": 1,
      "order_number": "SS20240309ABCD",
      "total_amount": 1650.00,
      "payment_status": "pending"
    },
    "payment": {
      "success": true,
      "redirect_url": "https://sandbox.sslcommerz.com/gwprocess/v4/gw.php?Q=...",
      "payment_id": 1,
      "session_key": "..."
    }
  }
}
```


#### 2. Payment Status
```
GET /payments/{orderNumber}/status
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_number": "SS20240309ABCD",
    "payment_status": "paid",
    "payments": [
      {
        "id": 1,
        "amount": 1650.00,
        "status": "completed",
        "payment_method": "ssl_commerz",
        "transaction_id": "TXN-20240309123456-ABC123",
        "paid_at": "2024-03-09T12:35:00.000000Z"
      }
    ]
  }
}
```

#### 3. Retry Payment
```
POST /payments/{orderNumber}/retry
```

**Request Body:**
```json
{
  "payment_method": "ssl_commerz"
}
```

---


## React Implementation

### 1. API Service Setup

Create `src/services/api.js`:
```javascript
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://127.0.0.1:8000/api';

export const api = {
  async checkout(orderData, token) {
    const response = await fetch(`${API_BASE_URL}/checkout/process`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(orderData)
    });
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Checkout failed');
    }
    
    return response.json();
  },

  async getPaymentStatus(orderNumber) {
    const response = await fetch(`${API_BASE_URL}/payments/${orderNumber}/status`);
    return response.json();
  },

  async retryPayment(orderNumber, paymentMethod, token) {
    const response = await fetch(`${API_BASE_URL}/payments/${orderNumber}/retry`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ payment_method: paymentMethod })
    });
    return response.json();
  }
};
```


### 2. Payment Method Selector Component

Create `src/components/PaymentMethodSelector.jsx`:
```javascript
import React from 'react';

const PaymentMethodSelector = ({ selected, onChange }) => {
  const methods = [
    {
      id: 'ssl_commerz',
      name: 'Credit/Debit Card',
      description: 'Visa, MasterCard, Amex',
      icon: '💳',
      logos: ['visa', 'mastercard', 'amex']
    },
    {
      id: 'bkash',
      name: 'bKash',
      description: 'Pay with bKash mobile wallet',
      icon: '📱',
      color: '#E2136E'
    },
    {
      id: 'nagad',
      name: 'Nagad',
      description: 'Pay with Nagad mobile wallet',
      icon: '📱',
      color: '#EE4023'
    }
  ];

  return (
    <div className="payment-methods">
      <h3>Select Payment Method</h3>
      <div className="methods-grid">
        {methods.map(method => (
          <div
            key={method.id}
            className={`method-card ${selected === method.id ? 'selected' : ''}`}
            onClick={() => onChange(method.id)}
          >
            <div className="method-icon">{method.icon}</div>
            <div className="method-info">
              <h4>{method.name}</h4>
              <p>{method.description}</p>
            </div>
            {selected === method.id && (
              <div className="checkmark">✓</div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default PaymentMethodSelector;
```


### 3. Checkout Page Component

Create `src/pages/CheckoutPage.jsx`:
```javascript
import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../services/api';
import PaymentMethodSelector from '../components/PaymentMethodSelector';

const CheckoutPage = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState('ssl_commerz');
  const [cartItems, setCartItems] = useState([]);
  const [addresses, setAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [shippingMethod, setShippingMethod] = useState('shah_sports_team');
  const [couponCode, setCouponCode] = useState('');
  const [error, setError] = useState(null);

  const token = localStorage.getItem('auth_token');

  const handleCheckout = async () => {
    if (!selectedAddress) {
      setError('Please select a shipping address');
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const orderData = {
        items: cartItems.map(item => ({
          product_id: item.product_id,
          variation_id: item.variation_id || null,
          quantity: item.quantity,
          price: item.price
        })),
        shipping_address_id: selectedAddress,
        billing_address_id: selectedAddress,
        shipping_method: shippingMethod,
        payment_method: paymentMethod,
        coupon_code: couponCode || undefined
      };

      const result = await api.checkout(orderData, token);

      if (result.success) {
        const payment = result.data.payment;
        
        if (payment.success && payment.redirect_url) {
          // Redirect to SSL Commerz payment page
          window.location.href = payment.redirect_url;
        } else {
          setError(payment.error || 'Payment initiation failed');
        }
      } else {
        setError(result.message || 'Checkout failed');
      }
    } catch (err) {
      setError(err.message || 'An error occurred during checkout');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="checkout-page">
      <h1>Checkout</h1>

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      {/* Cart Items Summary */}
      <div className="cart-summary">
        {/* Display cart items */}
      </div>

      {/* Shipping Address */}
      <div className="shipping-section">
        {/* Address selector */}
      </div>

      {/* Shipping Method */}
      <div className="shipping-method">
        {/* Shipping method selector */}
      </div>

      {/* Payment Method */}
      <PaymentMethodSelector
        selected={paymentMethod}
        onChange={setPaymentMethod}
      />

      {/* Coupon Code */}
      <div className="coupon-section">
        <input
          type="text"
          placeholder="Enter coupon code"
          value={couponCode}
          onChange={(e) => setCouponCode(e.target.value)}
        />
      </div>

      {/* Place Order Button */}
      <button
        onClick={handleCheckout}
        disabled={loading || !selectedAddress}
        className="btn-checkout"
      >
        {loading ? 'Processing...' : 'Place Order & Pay'}
      </button>
    </div>
  );
};

export default CheckoutPage;
```


### 4. Order Success Page

Create `src/pages/OrderSuccessPage.jsx`:
```javascript
import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { api } from '../services/api';

const OrderSuccessPage = () => {
  const { orderNumber } = useParams();
  const [orderData, setOrderData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchOrderStatus = async () => {
      try {
        const result = await api.getPaymentStatus(orderNumber);
        
        if (result.success) {
          setOrderData(result.data);
        }
      } catch (error) {
        console.error('Failed to fetch order status:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchOrderStatus();
  }, [orderNumber]);

  if (loading) {
    return <div className="loading">Loading order details...</div>;
  }

  return (
    <div className="order-success-page">
      <div className="success-icon">✓</div>
      <h1>Payment Successful!</h1>
      <p className="success-message">
        Thank you for your order. Your payment has been processed successfully.
      </p>

      {orderData && (
        <div className="order-details">
          <h2>Order Details</h2>
          <div className="detail-row">
            <span>Order Number:</span>
            <strong>{orderData.order_number}</strong>
          </div>
          <div className="detail-row">
            <span>Payment Status:</span>
            <strong className="status-paid">{orderData.payment_status}</strong>
          </div>
          {orderData.payments && orderData.payments[0] && (
            <>
              <div className="detail-row">
                <span>Amount Paid:</span>
                <strong>৳{orderData.payments[0].amount}</strong>
              </div>
              <div className="detail-row">
                <span>Transaction ID:</span>
                <strong>{orderData.payments[0].transaction_id}</strong>
              </div>
            </>
          )}
        </div>
      )}

      <div className="action-buttons">
        <Link to="/orders" className="btn-primary">
          View My Orders
        </Link>
        <Link to="/" className="btn-secondary">
          Continue Shopping
        </Link>
      </div>
    </div>
  );
};

export default OrderSuccessPage;
```


### 5. Payment Failed Page

Create `src/pages/PaymentFailedPage.jsx`:
```javascript
import React, { useState } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { api } from '../services/api';

const PaymentFailedPage = () => {
  const [searchParams] = useSearchParams();
  const orderNumber = searchParams.get('order');
  const [retrying, setRetrying] = useState(false);
  const token = localStorage.getItem('auth_token');

  const handleRetry = async () => {
    setRetrying(true);
    
    try {
      const result = await api.retryPayment(orderNumber, 'ssl_commerz', token);
      
      if (result.success && result.data.redirect_url) {
        window.location.href = result.data.redirect_url;
      } else {
        alert('Failed to retry payment. Please try again.');
      }
    } catch (error) {
      alert('An error occurred. Please try again.');
    } finally {
      setRetrying(false);
    }
  };

  return (
    <div className="payment-failed-page">
      <div className="error-icon">✗</div>
      <h1>Payment Failed</h1>
      <p className="error-message">
        Unfortunately, your payment could not be processed.
      </p>

      {orderNumber && (
        <div className="order-info">
          <p>Order Number: <strong>{orderNumber}</strong></p>
        </div>
      )}

      <div className="action-buttons">
        <button
          onClick={handleRetry}
          disabled={retrying}
          className="btn-primary"
        >
          {retrying ? 'Processing...' : 'Retry Payment'}
        </button>
        <Link to="/orders" className="btn-secondary">
          View My Orders
        </Link>
      </div>

      <div className="help-section">
        <h3>Need Help?</h3>
        <p>If you continue to experience issues, please contact our support team.</p>
        <Link to="/contact">Contact Support</Link>
      </div>
    </div>
  );
};

export default PaymentFailedPage;
```


### 6. Payment Cancelled Page

Create `src/pages/PaymentCancelledPage.jsx`:
```javascript
import React from 'react';
import { useSearchParams, Link } from 'react-router-dom';

const PaymentCancelledPage = () => {
  const [searchParams] = useSearchParams();
  const orderNumber = searchParams.get('order');

  return (
    <div className="payment-cancelled-page">
      <div className="warning-icon">⚠</div>
      <h1>Payment Cancelled</h1>
      <p className="warning-message">
        You have cancelled the payment process.
      </p>

      {orderNumber && (
        <div className="order-info">
          <p>Order Number: <strong>{orderNumber}</strong></p>
          <p>Your order is still pending. You can complete the payment anytime from your orders page.</p>
        </div>
      )}

      <div className="action-buttons">
        <Link to="/orders" className="btn-primary">
          View My Orders
        </Link>
        <Link to="/" className="btn-secondary">
          Continue Shopping
        </Link>
      </div>
    </div>
  );
};

export default PaymentCancelledPage;
```


### 7. React Router Setup

Update `src/App.jsx`:
```javascript
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import CheckoutPage from './pages/CheckoutPage';
import OrderSuccessPage from './pages/OrderSuccessPage';
import PaymentFailedPage from './pages/PaymentFailedPage';
import PaymentCancelledPage from './pages/PaymentCancelledPage';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/checkout" element={<CheckoutPage />} />
        <Route path="/order/success/:orderNumber" element={<OrderSuccessPage />} />
        <Route path="/payment/failed" element={<PaymentFailedPage />} />
        <Route path="/payment/cancelled" element={<PaymentCancelledPage />} />
        {/* Other routes */}
      </Routes>
    </BrowserRouter>
  );
}

export default App;
```

### 8. Environment Variables

Create `.env`:
```
REACT_APP_API_URL=http://127.0.0.1:8000/api
```

For production:
```
REACT_APP_API_URL=https://your-domain.com/api
```

---


## Vue.js Implementation

### 1. API Service

Create `src/services/api.js`:
```javascript
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api';

export const checkoutService = {
  async processCheckout(orderData, token) {
    const response = await fetch(`${API_BASE_URL}/checkout/process`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(orderData)
    });
    return response.json();
  },

  async getPaymentStatus(orderNumber) {
    const response = await fetch(`${API_BASE_URL}/payments/${orderNumber}/status`);
    return response.json();
  },

  async retryPayment(orderNumber, paymentMethod, token) {
    const response = await fetch(`${API_BASE_URL}/payments/${orderNumber}/retry`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ payment_method: paymentMethod })
    });
    return response.json();
  }
};
```


### 2. Checkout Component

Create `src/views/CheckoutView.vue`:
```vue
<template>
  <div class="checkout-page">
    <h1>Checkout</h1>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Payment Method Selector -->
    <div class="payment-methods">
      <h3>Select Payment Method</h3>
      <div class="methods-grid">
        <div
          v-for="method in paymentMethods"
          :key="method.id"
          :class="['method-card', { selected: selectedMethod === method.id }]"
          @click="selectedMethod = method.id"
        >
          <div class="method-icon">{{ method.icon }}</div>
          <div class="method-info">
            <h4>{{ method.name }}</h4>
            <p>{{ method.description }}</p>
          </div>
          <div v-if="selectedMethod === method.id" class="checkmark">✓</div>
        </div>
      </div>
    </div>

    <!-- Place Order Button -->
    <button
      @click="handleCheckout"
      :disabled="loading || !selectedAddress"
      class="btn-checkout"
    >
      {{ loading ? 'Processing...' : 'Place Order & Pay' }}
    </button>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { checkoutService } from '@/services/api';

export default {
  name: 'CheckoutView',
  setup() {
    const router = useRouter();
    const loading = ref(false);
    const error = ref(null);
    const selectedMethod = ref('ssl_commerz');
    const selectedAddress = ref(null);
    const cartItems = ref([]);

    const paymentMethods = [
      { id: 'ssl_commerz', name: 'Credit/Debit Card', icon: '💳', description: 'Visa, MasterCard, Amex' },
      { id: 'bkash', name: 'bKash', icon: '📱', description: 'Pay with bKash' },
      { id: 'nagad', name: 'Nagad', icon: '📱', description: 'Pay with Nagad' }
    ];

    const handleCheckout = async () => {
      loading.value = true;
      error.value = null;

      try {
        const token = localStorage.getItem('auth_token');
        const orderData = {
          items: cartItems.value.map(item => ({
            product_id: item.product_id,
            variation_id: item.variation_id,
            quantity: item.quantity,
            price: item.price
          })),
          shipping_address_id: selectedAddress.value,
          billing_address_id: selectedAddress.value,
          shipping_method: 'shah_sports_team',
          payment_method: selectedMethod.value
        };

        const result = await checkoutService.processCheckout(orderData, token);

        if (result.success && result.data.payment.redirect_url) {
          window.location.href = result.data.payment.redirect_url;
        } else {
          error.value = result.data.payment.error || 'Payment initiation failed';
        }
      } catch (err) {
        error.value = err.message || 'Checkout failed';
      } finally {
        loading.value = false;
      }
    };

    return {
      loading,
      error,
      selectedMethod,
      selectedAddress,
      paymentMethods,
      handleCheckout
    };
  }
};
</script>
```


### 3. Order Success Component

Create `src/views/OrderSuccessView.vue`:
```vue
<template>
  <div class="order-success-page">
    <div class="success-icon">✓</div>
    <h1>Payment Successful!</h1>
    <p>Thank you for your order.</p>

    <div v-if="orderData" class="order-details">
      <h2>Order Details</h2>
      <div class="detail-row">
        <span>Order Number:</span>
        <strong>{{ orderData.order_number }}</strong>
      </div>
      <div class="detail-row">
        <span>Payment Status:</span>
        <strong>{{ orderData.payment_status }}</strong>
      </div>
    </div>

    <div class="action-buttons">
      <router-link to="/orders" class="btn-primary">View My Orders</router-link>
      <router-link to="/" class="btn-secondary">Continue Shopping</router-link>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { checkoutService } from '@/services/api';

export default {
  name: 'OrderSuccessView',
  setup() {
    const route = useRoute();
    const orderData = ref(null);
    const orderNumber = route.params.orderNumber;

    onMounted(async () => {
      try {
        const result = await checkoutService.getPaymentStatus(orderNumber);
        if (result.success) {
          orderData.value = result.data;
        }
      } catch (error) {
        console.error('Failed to fetch order status:', error);
      }
    });

    return { orderData };
  }
};
</script>
```

### 4. Vue Router Setup

Update `src/router/index.js`:
```javascript
import { createRouter, createWebHistory } from 'vue-router';
import CheckoutView from '@/views/CheckoutView.vue';
import OrderSuccessView from '@/views/OrderSuccessView.vue';
import PaymentFailedView from '@/views/PaymentFailedView.vue';
import PaymentCancelledView from '@/views/PaymentCancelledView.vue';

const routes = [
  { path: '/checkout', component: CheckoutView },
  { path: '/order/success/:orderNumber', component: OrderSuccessView },
  { path: '/payment/failed', component: PaymentFailedView },
  { path: '/payment/cancelled', component: PaymentCancelledView }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
```

---


## Next.js Implementation

### 1. API Service

Create `lib/api.js`:
```javascript
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api';

export async function processCheckout(orderData, token) {
  const response = await fetch(`${API_BASE_URL}/checkout/process`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(orderData)
  });

  if (!response.ok) {
    throw new Error('Checkout failed');
  }

  return response.json();
}

export async function getPaymentStatus(orderNumber) {
  const response = await fetch(`${API_BASE_URL}/payments/${orderNumber}/status`);
  return response.json();
}

export async function retryPayment(orderNumber, paymentMethod, token) {
  const response = await fetch(`${API_BASE_URL}/payments/${orderNumber}/retry`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ payment_method: paymentMethod })
  });
  return response.json();
}
```


### 2. Checkout Page

Create `pages/checkout.jsx`:
```javascript
import { useState } from 'react';
import { useRouter } from 'next/router';
import { processCheckout } from '@/lib/api';

export default function CheckoutPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState('ssl_commerz');

  const handleCheckout = async () => {
    setLoading(true);

    try {
      const token = localStorage.getItem('auth_token');
      const result = await processCheckout({
        items: [{ product_id: 1, quantity: 1, price: 1000 }],
        shipping_address_id: 1,
        payment_method: paymentMethod,
        shipping_method: 'shah_sports_team'
      }, token);

      if (result.success && result.data.payment.redirect_url) {
        window.location.href = result.data.payment.redirect_url;
      }
    } catch (error) {
      alert('Checkout failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="checkout-page">
      <h1>Checkout</h1>
      <button onClick={handleCheckout} disabled={loading}>
        {loading ? 'Processing...' : 'Place Order'}
      </button>
    </div>
  );
}
```

### 3. Order Success Page

Create `pages/order/success/[orderNumber].jsx`:
```javascript
import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { getPaymentStatus } from '@/lib/api';

export default function OrderSuccessPage() {
  const router = useRouter();
  const { orderNumber } = router.query;
  const [orderData, setOrderData] = useState(null);

  useEffect(() => {
    if (orderNumber) {
      getPaymentStatus(orderNumber).then(result => {
        if (result.success) {
          setOrderData(result.data);
        }
      });
    }
  }, [orderNumber]);

  return (
    <div className="order-success">
      <h1>Payment Successful!</h1>
      {orderData && (
        <div>
          <p>Order: {orderData.order_number}</p>
          <p>Status: {orderData.payment_status}</p>
        </div>
      )}
    </div>
  );
}
```

---


## Payment Flow

### Complete Flow Diagram

```
User Clicks "Place Order"
         ↓
Frontend sends POST to /checkout/process
         ↓
Backend creates order & initiates payment
         ↓
Backend returns redirect_url
         ↓
Frontend redirects to SSL Commerz
         ↓
User completes payment on SSL Commerz
         ↓
SSL Commerz processes payment
         ↓
         ├─ Success → Redirect to /order/success/:orderNumber
         ├─ Failed → Redirect to /payment/failed?order=:orderNumber
         └─ Cancel → Redirect to /payment/cancelled?order=:orderNumber
         ↓
Backend receives IPN callback (updates order status)
         ↓
Frontend displays result to user
```

### Step-by-Step Implementation

1. **User initiates checkout**
   - Collect cart items, shipping address, payment method
   - Validate all required fields

2. **Send checkout request**
   - POST to `/checkout/process` with order data
   - Include authentication token

3. **Handle response**
   - If successful, get `redirect_url`
   - Redirect user to SSL Commerz payment page

4. **User completes payment**
   - User enters card details on SSL Commerz
   - SSL Commerz processes payment

5. **Handle callback**
   - User redirected back to your frontend
   - Display appropriate success/failure page
   - Fetch order status to confirm

---


## Error Handling

### Common Errors and Solutions

#### 1. Network Errors
```javascript
try {
  const result = await api.checkout(orderData, token);
} catch (error) {
  if (error.message === 'Failed to fetch') {
    // Network error - check internet connection
    showError('Please check your internet connection');
  } else {
    showError('An error occurred. Please try again.');
  }
}
```

#### 2. Authentication Errors
```javascript
if (response.status === 401) {
  // Token expired or invalid
  localStorage.removeItem('auth_token');
  router.push('/login');
}
```

#### 3. Validation Errors
```javascript
if (!result.success && result.errors) {
  // Display validation errors
  Object.keys(result.errors).forEach(field => {
    showFieldError(field, result.errors[field][0]);
  });
}
```

#### 4. Payment Initiation Errors
```javascript
if (result.success && !result.data.payment.success) {
  // Payment gateway error
  const errorMsg = result.data.payment.error || 'Payment initiation failed';
  showError(errorMsg);
}
```

### Error Display Component

```javascript
const ErrorMessage = ({ error, onClose }) => {
  if (!error) return null;

  return (
    <div className="error-banner">
      <span className="error-icon">⚠</span>
      <span className="error-text">{error}</span>
      <button onClick={onClose} className="error-close">×</button>
    </div>
  );
};
```

---


## Testing

### Test Cards (Sandbox Mode)

**Visa:**
- Card Number: `4111 1111 1111 1111`
- Expiry: `12/25` (any future date)
- CVV: `123` (any 3 digits)

**MasterCard:**
- Card Number: `5555 5555 5555 4444`
- Expiry: `12/25`
- CVV: `123`

### Testing Checklist

- [ ] Checkout with SSL Commerz payment method
- [ ] Complete payment with test card
- [ ] Verify success page displays correctly
- [ ] Test payment failure scenario
- [ ] Test payment cancellation
- [ ] Verify payment retry functionality
- [ ] Check order status updates correctly
- [ ] Test with different browsers
- [ ] Test on mobile devices
- [ ] Verify error messages display properly

### Manual Testing Steps

1. **Test Successful Payment:**
   ```
   1. Add items to cart
   2. Go to checkout
   3. Select SSL Commerz payment
   4. Enter test card: 4111 1111 1111 1111
   5. Complete payment
   6. Verify redirect to success page
   7. Check order status is "paid"
   ```

2. **Test Failed Payment:**
   ```
   1. Go through checkout
   2. On SSL Commerz page, click "Cancel"
   3. Verify redirect to failed page
   4. Test retry payment button
   ```

3. **Test Payment Cancellation:**
   ```
   1. Start checkout process
   2. On SSL Commerz page, click "Cancel"
   3. Verify redirect to cancelled page
   4. Check order remains in pending status
   ```

### Automated Testing Example

```javascript
// Jest + React Testing Library
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import CheckoutPage from './CheckoutPage';
import * as api from '../services/api';

jest.mock('../services/api');

test('successful checkout redirects to payment gateway', async () => {
  api.checkout.mockResolvedValue({
    success: true,
    data: {
      payment: {
        success: true,
        redirect_url: 'https://sandbox.sslcommerz.com/test'
      }
    }
  });

  delete window.location;
  window.location = { href: '' };

  render(<CheckoutPage />);
  
  const checkoutButton = screen.getByText('Place Order');
  fireEvent.click(checkoutButton);

  await waitFor(() => {
    expect(window.location.href).toBe('https://sandbox.sslcommerz.com/test');
  });
});
```

---


## Styling Examples

### CSS for Payment Components

```css
/* Payment Method Selector */
.payment-methods {
  margin: 20px 0;
}

.methods-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
  margin-top: 15px;
}

.method-card {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 15px;
  position: relative;
}

.method-card:hover {
  border-color: #4CAF50;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.method-card.selected {
  border-color: #4CAF50;
  background-color: #f1f8f4;
}

.method-icon {
  font-size: 32px;
}

.method-info h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
}

.method-info p {
  margin: 0;
  font-size: 14px;
  color: #666;
}

.checkmark {
  position: absolute;
  top: 10px;
  right: 10px;
  color: #4CAF50;
  font-size: 24px;
  font-weight: bold;
}

/* Success Page */
.order-success-page {
  max-width: 600px;
  margin: 50px auto;
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #4CAF50;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  margin: 0 auto 20px;
}

.order-details {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  margin: 20px 0;
  text-align: left;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
}

.detail-row:last-child {
  border-bottom: none;
}

/* Error Page */
.payment-failed-page {
  max-width: 600px;
  margin: 50px auto;
  text-align: center;
  padding: 40px;
}

.error-icon {
  width: 80px;
  height: 80px;
  background: #f44336;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  margin: 0 auto 20px;
}

/* Buttons */
.btn-primary {
  background: #4CAF50;
  color: white;
  padding: 12px 30px;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-primary:hover {
  background: #45a049;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  background: white;
  color: #333;
  padding: 12px 30px;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
}

.action-buttons {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 30px;
}

/* Loading Spinner */
.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #4CAF50;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
```

---


## Best Practices

### 1. Security
- Never store payment card details in frontend
- Always use HTTPS in production
- Validate all inputs before sending to backend
- Store authentication tokens securely
- Clear sensitive data after use

### 2. User Experience
- Show loading indicators during payment processing
- Provide clear error messages
- Allow users to retry failed payments
- Display order confirmation immediately
- Send email confirmation

### 3. Error Handling
- Handle network errors gracefully
- Validate data before submission
- Show user-friendly error messages
- Log errors for debugging
- Provide fallback options

### 4. Performance
- Minimize API calls
- Cache static data
- Use loading states
- Optimize images and assets
- Implement lazy loading

### 5. Testing
- Test all payment scenarios
- Test on multiple devices
- Test with different browsers
- Test error conditions
- Test with slow network

---

## Production Deployment

### Environment Variables

**Development (.env.development):**
```
REACT_APP_API_URL=http://127.0.0.1:8000/api
REACT_APP_FRONTEND_URL=http://localhost:3000
```

**Production (.env.production):**
```
REACT_APP_API_URL=https://api.your-domain.com/api
REACT_APP_FRONTEND_URL=https://your-domain.com
```

### Pre-Deployment Checklist

- [ ] Update API URLs to production
- [ ] Test with production backend
- [ ] Verify SSL certificates
- [ ] Test all payment flows
- [ ] Check error handling
- [ ] Verify callback URLs
- [ ] Test on multiple devices
- [ ] Set up error monitoring
- [ ] Configure analytics
- [ ] Test performance

### CORS Configuration

Ensure your backend allows requests from your frontend domain:

```php
// Laravel config/cors.php
'allowed_origins' => [
    'https://your-frontend-domain.com',
    'http://localhost:3000', // for development
],
```

---

## Troubleshooting

### Issue: Payment redirect not working
**Solution:**
- Check if `redirect_url` is received in response
- Verify `window.location.href` assignment
- Check browser console for errors

### Issue: Callback pages not loading
**Solution:**
- Verify route configuration
- Check if routes match backend redirect URLs
- Ensure frontend is accessible

### Issue: Payment status not updating
**Solution:**
- Check if IPN endpoint is reachable
- Verify backend is processing callbacks
- Check network tab for API calls

### Issue: CORS errors
**Solution:**
- Configure CORS in backend
- Add frontend domain to allowed origins
- Check preflight requests

---

## Support and Resources

### Documentation
- **SSL Commerz API Docs:** https://developer.sslcommerz.com/
- **Backend Setup Guide:** See `SSL_COMMERZ_SETUP.md`
- **Testing Guide:** See `SSL_PAYMENT_TESTING_GUIDE.md`

### Contact
- **SSL Commerz Support:** support@sslcommerz.com
- **Merchant Panel:** https://merchant.sslcommerz.com/

### Additional Resources
- React Router: https://reactrouter.com/
- Vue Router: https://router.vuejs.org/
- Next.js Routing: https://nextjs.org/docs/routing

---

## Summary

This guide covers complete frontend integration for SSL Commerz payment gateway including:

✅ API service setup  
✅ Checkout page implementation  
✅ Payment callback handling  
✅ Success/failure pages  
✅ Error handling  
✅ Testing strategies  
✅ Styling examples  
✅ Best practices  
✅ Production deployment  

The implementation is ready for both development and production use. Follow the testing checklist before going live.


---

## Guest Checkout Support

The checkout system now supports both authenticated and guest users.

### Guest Checkout Request

```javascript
// Guest checkout without account creation
const guestCheckout = async (guestData, cartItems) => {
  const response = await fetch('http://127.0.0.1:8000/api/checkout/process', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
      // No Authorization header needed for guest
    },
    body: JSON.stringify({
      items: cartItems,
      guest_email: guestData.email,
      guest_name: guestData.name,
      guest_phone: guestData.phone,
      shipping_address: {
        address_line_1: guestData.address,
        city: guestData.city,
        postal_code: guestData.postalCode,
        country: 'Bangladesh',
        phone: guestData.phone
      },
      shipping_method: 'shah_sports_team',
      payment_method: 'ssl_commerz'
    })
  });

  const result = await response.json();
  
  if (result.success && result.data.payment.redirect_url) {
    window.location.href = result.data.payment.redirect_url;
  }
};

// Guest checkout with account creation
const guestCheckoutWithAccount = async (guestData, cartItems, password) => {
  const response = await fetch('http://127.0.0.1:8000/api/checkout/process', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      items: cartItems,
      guest_email: guestData.email,
      guest_name: guestData.name,
      guest_phone: guestData.phone,
      shipping_address: {
        address_line_1: guestData.address,
        city: guestData.city,
        country: 'Bangladesh',
        phone: guestData.phone
      },
      shipping_method: 'shah_sports_team',
      payment_method: 'ssl_commerz',
      create_account: true,
      password: password
    })
  });

  const result = await response.json();
  
  if (result.success) {
    if (result.data.account_created) {
      // Account was created, user can login later
      console.log('Account created successfully!');
    }
    
    if (result.data.payment.redirect_url) {
      window.location.href = result.data.payment.redirect_url;
    }
  }
};
```

### Unified Checkout Component

```javascript
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const UnifiedCheckoutPage = () => {
  const navigate = useNavigate();
  const [isGuest, setIsGuest] = useState(!localStorage.getItem('auth_token'));
  const [createAccount, setCreateAccount] = useState(false);
  const [loading, setLoading] = useState(false);
  
  const [guestData, setGuestData] = useState({
    email: '',
    name: '',
    phone: '',
    address: '',
    city: '',
    password: ''
  });

  const handleCheckout = async () => {
    setLoading(true);

    try {
      const token = localStorage.getItem('auth_token');
      const headers = {
        'Content-Type': 'application/json'
      };

      // Add auth header only if user is logged in
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const requestBody = {
        items: cartItems,
        shipping_method: 'shah_sports_team',
        payment_method: 'ssl_commerz'
      };

      // Add guest-specific fields
      if (isGuest) {
        requestBody.guest_email = guestData.email;
        requestBody.guest_name = guestData.name;
        requestBody.guest_phone = guestData.phone;
        requestBody.shipping_address = {
          address_line_1: guestData.address,
          city: guestData.city,
          country: 'Bangladesh',
          phone: guestData.phone
        };

        if (createAccount) {
          requestBody.create_account = true;
          requestBody.password = guestData.password;
        }
      } else {
        // Authenticated user
        requestBody.shipping_address_id = selectedAddressId;
      }

      const response = await fetch('http://127.0.0.1:8000/api/checkout/process', {
        method: 'POST',
        headers,
        body: JSON.stringify(requestBody)
      });

      const result = await response.json();

      if (result.success && result.data.payment.redirect_url) {
        window.location.href = result.data.payment.redirect_url;
      } else {
        alert(result.message || 'Checkout failed');
      }
    } catch (error) {
      console.error('Checkout error:', error);
      alert('An error occurred during checkout');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="checkout-page">
      <h1>Checkout</h1>

      {isGuest ? (
        <div className="guest-checkout">
          <h2>Guest Checkout</h2>
          
          <input
            type="email"
            placeholder="Email"
            value={guestData.email}
            onChange={(e) => setGuestData({...guestData, email: e.target.value})}
            required
          />
          
          <input
            type="text"
            placeholder="Full Name"
            value={guestData.name}
            onChange={(e) => setGuestData({...guestData, name: e.target.value})}
            required
          />
          
          <input
            type="tel"
            placeholder="Phone"
            value={guestData.phone}
            onChange={(e) => setGuestData({...guestData, phone: e.target.value})}
            required
          />
          
          <input
            type="text"
            placeholder="Address"
            value={guestData.address}
            onChange={(e) => setGuestData({...guestData, address: e.target.value})}
            required
          />
          
          <input
            type="text"
            placeholder="City"
            value={guestData.city}
            onChange={(e) => setGuestData({...guestData, city: e.target.value})}
            required
          />

          <div className="create-account-option">
            <label>
              <input
                type="checkbox"
                checked={createAccount}
                onChange={(e) => setCreateAccount(e.target.checked)}
              />
              Create an account for faster checkout next time
            </label>
          </div>

          {createAccount && (
            <input
              type="password"
              placeholder="Password (min 8 characters)"
              value={guestData.password}
              onChange={(e) => setGuestData({...guestData, password: e.target.value})}
              minLength={8}
              required
            />
          )}

          <p>
            Already have an account?{' '}
            <button onClick={() => navigate('/login')}>Login</button>
          </p>
        </div>
      ) : (
        <div className="authenticated-checkout">
          <h2>Checkout</h2>
          {/* Show saved addresses for authenticated users */}
        </div>
      )}

      <button onClick={handleCheckout} disabled={loading}>
        {loading ? 'Processing...' : 'Place Order & Pay'}
      </button>
    </div>
  );
};

export default UnifiedCheckoutPage;
```

---

## Key Differences: Guest vs Authenticated

| Feature | Guest Checkout | Authenticated Checkout |
|---------|---------------|------------------------|
| Authentication | Not required | Required (Bearer token) |
| Address | Inline address object | Address ID from saved addresses |
| User Info | guest_email, guest_name, guest_phone | Retrieved from token |
| Account Creation | Optional (create_account flag) | Already has account |
| Order Tracking | Via order number only | Via account dashboard |
| Saved Addresses | Not saved (unless account created) | Can use saved addresses |

---

## Complete Testing Checklist

- [ ] Guest checkout without account creation
- [ ] Guest checkout with account creation
- [ ] Authenticated user checkout
- [ ] Guest payment with SSL Commerz
- [ ] Authenticated payment with SSL Commerz
- [ ] Guest order tracking via order number
- [ ] Account creation during checkout
- [ ] Duplicate email validation
- [ ] Address validation for guests
- [ ] Payment success for both user types
- [ ] Payment failure handling
- [ ] Order confirmation emails

---
