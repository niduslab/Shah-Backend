# SSL Commerz API Integration Guide

## Overview
This document describes how to integrate SSL Commerz payment gateway in your frontend application.

## Configuration Status
✅ Backend is fully configured with SSL Commerz sandbox credentials  
✅ All payment endpoints are ready  
✅ Callback handlers are implemented  
✅ Error handling and logging in place

## Frontend Integration Steps

### Step 1: Checkout Process

```javascript
const checkoutWithSSL = async (orderData) => {
  const response = await fetch('http://127.0.0.1:8000/api/checkout/process', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${userToken}`
    },
    body: JSON.stringify({
      items: [{product_id: 1, quantity: 2, price: 1500.00}],
      shipping_address_id: 1,
      payment_method: 'ssl_commerz',
      shipping_method: 'shah_sports_team'
    })
  });

  const result = await response.json();
  
  if (result.success && result.data.payment.redirect_url) {
    window.location.href = result.data.payment.redirect_url;
  }
};
```

### Step 2: Handle Callbacks

Create these routes in your frontend:
- `/order/success/:orderNumber` - Payment successful
- `/payment/failed?order=:orderNumber` - Payment failed
- `/payment/cancelled?order=:orderNumber` - Payment cancelled

### Step 3: Check Payment Status

```javascript
const checkStatus = async (orderNumber) => {
  const response = await fetch(
    `http://127.0.0.1:8000/api/payments/${orderNumber}/status`
  );
  return await response.json();
};
```

## Test Cards (Sandbox)
- Visa: 4111 1111 1111 1111
- MasterCard: 5555 5555 5555 4444
- Expiry: Any future date
- CVV: Any 3 digits

## Production Checklist
- [ ] Update to production credentials
- [ ] Update API URLs
- [ ] Test with real cards
- [ ] Verify callbacks work
- [ ] Set up monitoring
