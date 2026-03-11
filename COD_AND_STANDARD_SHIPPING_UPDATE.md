# Cash on Delivery & Standard Shipping Update

## Changes Made

### 1. Added Cash on Delivery (COD) Payment Method

#### Updated Files:
- `app/Http/Controllers/Api/CheckoutController.php`
- `app/Services/PaymentService.php`

#### Features:
✅ COD payment option added  
✅ No payment gateway redirect for COD  
✅ Order confirmed immediately  
✅ Payment status set to "pending" (will be paid on delivery)  
✅ Transaction ID generated with "COD" prefix  

#### How It Works:
1. Customer selects `payment_method: "cod"`
2. Order is created and confirmed immediately
3. Payment record created with status "pending"
4. No redirect to payment gateway
5. Customer pays when order is delivered

---

### 2. Added Standard Shipping Method

#### Updated Files:
- `app/Http/Controllers/Api/CheckoutController.php`
- `app/Services/ShippingService.php`

#### Features:
✅ Standard shipping method added  
✅ Weight-based pricing  
✅ Free shipping for orders ≥ 1000 BDT  
✅ Automatic cost calculation  

#### Pricing Structure:
- **Up to 1kg:** 60 BDT
- **1-5kg:** 100 BDT
- **5-10kg:** 150 BDT
- **Above 10kg:** 150 BDT + 15 BDT per additional kg
- **Free:** Orders ≥ 1000 BDT

---

## API Updates

### Payment Methods
Now accepts:
- `ssl_commerz` - Credit/Debit cards via SSL Commerz
- `bkash` - bKash mobile wallet
- `nagad` - Nagad mobile wallet
- `cod` - **NEW** Cash on Delivery

### Shipping Methods
Now accepts:
- `shah_sports_team` - Shah Sports Team delivery
- `pathao_courier` - Pathao Courier service
- `standard` - **NEW** Standard shipping

---

## Request Examples

### Checkout with COD
```json
POST /api/checkout/process

{
  "items": [
    {"product_id": 25, "quantity": 3, "price": 33}
  ],
  "guest_email": "user@example.com",
  "guest_name": "John Doe",
  "guest_phone": "01712345678",
  "shipping_address": {
    "address_line_1": "123 Street",
    "city": "Dhaka",
    "country": "Bangladesh",
    "phone": "01712345678"
  },
  "shipping_method": "standard",
  "payment_method": "cod"
}
```

### Response for COD
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order": {
      "id": 1,
      "order_number": "SS20240309ABCD",
      "status": "confirmed",
      "payment_status": "pending",
      "total_amount": 165.00
    },
    "payment": {
      "success": true,
      "payment_id": 1,
      "transaction_id": "COD-20240309123456-ABC123",
      "amount": 165.00,
      "method": "cod",
      "message": "Order placed successfully. Pay on delivery."
    },
    "message": "Order placed successfully. You will pay on delivery.",
    "account_created": false
  }
}
```

### Checkout with Standard Shipping
```json
POST /api/checkout/process

{
  "items": [
    {"product_id": 25, "quantity": 3, "price": 33}
  ],
  "guest_email": "user@example.com",
  "guest_name": "John Doe",
  "guest_phone": "01712345678",
  "shipping_address": {
    "address_line_1": "123 Street",
    "city": "Dhaka",
    "country": "Bangladesh",
    "phone": "01712345678"
  },
  "shipping_method": "standard",
  "payment_method": "ssl_commerz"
}
```

---

## Frontend Implementation

### Payment Method Selector

```javascript
const paymentMethods = [
  {
    id: 'ssl_commerz',
    name: 'Credit/Debit Card',
    description: 'Pay securely with your card',
    icon: '💳'
  },
  {
    id: 'bkash',
    name: 'bKash',
    description: 'Pay with bKash mobile wallet',
    icon: '📱'
  },
  {
    id: 'nagad',
    name: 'Nagad',
    description: 'Pay with Nagad mobile wallet',
    icon: '📱'
  },
  {
    id: 'cod',
    name: 'Cash on Delivery',
    description: 'Pay when you receive your order',
    icon: '💵',
    badge: 'Popular'
  }
];
```

### Shipping Method Selector

```javascript
const shippingMethods = [
  {
    id: 'standard',
    name: 'Standard Shipping',
    description: 'Free for orders over 1000 BDT',
    estimatedDays: '3-5 business days',
    icon: '📦'
  },
  {
    id: 'shah_sports_team',
    name: 'Shah Sports Team',
    description: 'Delivered by our team',
    estimatedDays: '1-2 business days',
    icon: '🚚'
  },
  {
    id: 'pathao_courier',
    name: 'Pathao Courier',
    description: 'Fast courier service',
    estimatedDays: '1-3 business days',
    icon: '🏍️'
  }
];
```

### Handle COD Response

```javascript
const handleCheckout = async () => {
  const response = await fetch('/api/checkout/process', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(checkoutData)
  });

  const result = await response.json();

  if (result.success) {
    // Check if COD
    if (checkoutData.payment_method === 'cod') {
      // No redirect needed, show success page directly
      navigate(`/order/success/${result.data.order.order_number}`);
    } else {
      // Redirect to payment gateway
      if (result.data.payment.redirect_url) {
        window.location.href = result.data.payment.redirect_url;
      }
    }
  }
};
```

### React Component Example

```javascript
import React, { useState } from 'react';

const CheckoutPage = () => {
  const [paymentMethod, setPaymentMethod] = useState('cod');
  const [shippingMethod, setShippingMethod] = useState('standard');

  const handleCheckout = async () => {
    const response = await fetch('/api/checkout/process', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        items: cartItems,
        guest_email: guestData.email,
        guest_name: guestData.name,
        guest_phone: guestData.phone,
        shipping_address: shippingAddress,
        shipping_method: shippingMethod,
        payment_method: paymentMethod
      })
    });

    const result = await response.json();

    if (result.success) {
      if (paymentMethod === 'cod') {
        // Show success message
        alert('Order placed successfully! Pay on delivery.');
        navigate(`/order/success/${result.data.order.order_number}`);
      } else {
        // Redirect to payment gateway
        window.location.href = result.data.payment.redirect_url;
      }
    }
  };

  return (
    <div>
      <h2>Select Shipping Method</h2>
      <select value={shippingMethod} onChange={(e) => setShippingMethod(e.target.value)}>
        <option value="standard">Standard Shipping</option>
        <option value="shah_sports_team">Shah Sports Team</option>
        <option value="pathao_courier">Pathao Courier</option>
      </select>

      <h2>Select Payment Method</h2>
      <select value={paymentMethod} onChange={(e) => setPaymentMethod(e.target.value)}>
        <option value="cod">Cash on Delivery</option>
        <option value="ssl_commerz">Credit/Debit Card</option>
        <option value="bkash">bKash</option>
        <option value="nagad">Nagad</option>
      </select>

      <button onClick={handleCheckout}>Place Order</button>
    </div>
  );
};
```

---

## Testing

### Test COD Order

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 25, "quantity": 1, "price": 100}],
    "guest_email": "test@example.com",
    "guest_name": "Test User",
    "guest_phone": "01712345678",
    "shipping_address": {
      "address_line_1": "123 Test St",
      "city": "Dhaka",
      "country": "Bangladesh",
      "phone": "01712345678"
    },
    "shipping_method": "standard",
    "payment_method": "cod"
  }'
```

### Expected Response
- Order status: `confirmed`
- Payment status: `pending`
- No redirect_url in response
- Transaction ID starts with "COD-"

---

## Order Flow Comparison

### Online Payment (SSL Commerz/bKash/Nagad)
```
Checkout → Order Created (pending) → Redirect to Gateway
                                            ↓
                                    Payment Complete
                                            ↓
                                    Order Confirmed (paid)
```

### Cash on Delivery
```
Checkout → Order Created & Confirmed (pending payment)
                                            ↓
                                    Order Delivered
                                            ↓
                                    Payment Collected
                                            ↓
                                    Payment Status Updated (paid)
```

---

## Database Records

### COD Payment Record
```
payment_method: 'manual'
status: 'pending'
transaction_id: 'COD-20240309123456-ABC123'
gateway_response: {"method": "cod"}
```

### COD Order Record
```
status: 'confirmed'
payment_status: 'pending'
```

---

## Important Notes

1. **COD Orders:** Automatically confirmed but payment is pending
2. **Standard Shipping:** Free for orders ≥ 1000 BDT
3. **Weight Calculation:** Uses product weight or defaults to 0.5kg
4. **No Redirect:** COD orders don't redirect to payment gateway
5. **Transaction ID:** COD transactions have "COD-" prefix

---

## Admin Panel Updates Needed

For admin panel, you may want to:
- [ ] Add filter for COD orders
- [ ] Add button to mark COD payment as received
- [ ] Show shipping cost breakdown
- [ ] Display payment method clearly
- [ ] Add COD collection tracking

---

## Summary

✅ Cash on Delivery payment method added  
✅ Standard shipping method added  
✅ Weight-based shipping calculation  
✅ Free shipping for orders ≥ 1000 BDT  
✅ No breaking changes to existing functionality  
✅ Frontend examples provided  

The system now supports 4 payment methods and 3 shipping methods!
