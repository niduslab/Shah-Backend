# Guest Checkout Implementation Guide

## Overview
The checkout system now supports both authenticated users and guest checkout. Guests can complete purchases without creating an account, or optionally create an account during checkout.

## Features

✅ Guest checkout without registration  
✅ Optional account creation during checkout  
✅ Authenticated user checkout  
✅ Automatic address creation for guests  
✅ SSL Commerz payment for both guest and authenticated users  

---

## API Endpoint

### Checkout Process
```
POST /api/checkout/process
```

**Authentication:** Optional (supports both guest and authenticated users)

---

## Request Formats

### 1. Guest Checkout (Without Account Creation)

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
  "guest_email": "customer@example.com",
  "guest_name": "John Doe",
  "guest_phone": "01712345678",
  "shipping_address": {
    "address_line_1": "123 Main Street",
    "address_line_2": "Apt 4B",
    "city": "Dhaka",
    "state": "Dhaka",
    "postal_code": "1200",
    "country": "Bangladesh",
    "phone": "01712345678"
  },
  "use_shipping_for_billing": true,
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz",
  "notes": "Please deliver before 5 PM"
}
```

### 2. Guest Checkout (With Account Creation)

```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 1,
      "price": 1500.00
    }
  ],
  "guest_email": "newuser@example.com",
  "guest_name": "Jane Smith",
  "guest_phone": "01798765432",
  "shipping_address": {
    "address_line_1": "456 Park Avenue",
    "city": "Dhaka",
    "postal_code": "1205",
    "country": "Bangladesh",
    "phone": "01798765432"
  },
  "shipping_method": "pathao_courier",
  "payment_method": "ssl_commerz",
  "create_account": true,
  "password": "SecurePass123!"
}
```

### 3. Authenticated User Checkout

```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 1500.00
    }
  ],
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz",
  "coupon_code": "SAVE10"
}
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## Request Fields

### Required Fields (All Users)

| Field | Type | Description |
|-------|------|-------------|
| `items` | array | Cart items with product_id, quantity, price |
| `shipping_method` | string | `shah_sports_team` or `pathao_courier` |
| `payment_method` | string | `ssl_commerz`, `bkash`, or `nagad` |

### Guest-Specific Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `guest_email` | string | Yes (for guests) | Guest email address |
| `guest_name` | string | Yes (for guests) | Guest full name |
| `guest_phone` | string | Yes (for guests) | Guest phone number |
| `shipping_address` | object | Yes (if no address_id) | Inline shipping address |
| `create_account` | boolean | No | Create account after checkout |
| `password` | string | Yes (if create_account=true) | Password for new account (min 8 chars) |

### Authenticated User Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `shipping_address_id` | integer | Yes | ID of saved shipping address |
| `billing_address_id` | integer | No | ID of saved billing address |
| `coupon_code` | string | No | Coupon code to apply |

### Optional Fields (All Users)

| Field | Type | Description |
|-------|------|-------------|
| `billing_address` | object | Separate billing address |
| `use_shipping_for_billing` | boolean | Use shipping address for billing (default: true) |
| `notes` | string | Delivery notes (max 500 chars) |
| `is_preorder` | boolean | Is this a preorder |
| `pay_deposit_only` | boolean | Pay deposit only for preorder |

---

## Response Format

### Successful Response

```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order": {
      "id": 1,
      "order_number": "SS20240309ABCD",
      "user_id": null,
      "customer_name": "John Doe",
      "customer_email": "customer@example.com",
      "customer_phone": "01712345678",
      "total_amount": 1650.00,
      "payment_status": "pending",
      "status": "pending"
    },
    "payment": {
      "success": true,
      "redirect_url": "https://sandbox.sslcommerz.com/gwprocess/v4/gw.php?Q=...",
      "payment_id": 1,
      "session_key": "..."
    },
    "account_created": true
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "guest_email": ["The guest email field is required when user is not present."],
    "shipping_address": ["The shipping address field is required when shipping address id is not present."]
  }
}
```

---

## Frontend Implementation

### React Example - Guest Checkout

```javascript
import { useState } from 'react';

const GuestCheckoutForm = () => {
  const [formData, setFormData] = useState({
    guest_email: '',
    guest_name: '',
    guest_phone: '',
    shipping_address: {
      address_line_1: '',
      city: '',
      postal_code: '',
      country: 'Bangladesh',
      phone: ''
    },
    create_account: false,
    password: ''
  });

  const handleCheckout = async () => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/checkout/process', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          ...formData,
          items: cartItems,
          shipping_method: 'shah_sports_team',
          payment_method: 'ssl_commerz'
        })
      });

      const result = await response.json();

      if (result.success && result.data.payment.redirect_url) {
        // Redirect to payment gateway
        window.location.href = result.data.payment.redirect_url;
      }
    } catch (error) {
      console.error('Checkout failed:', error);
    }
  };

  return (
    <form onSubmit={(e) => { e.preventDefault(); handleCheckout(); }}>
      <h2>Guest Checkout</h2>
      
      {/* Contact Information */}
      <div>
        <input
          type="email"
          placeholder="Email"
          value={formData.guest_email}
          onChange={(e) => setFormData({...formData, guest_email: e.target.value})}
          required
        />
      </div>
      
      <div>
        <input
          type="text"
          placeholder="Full Name"
          value={formData.guest_name}
          onChange={(e) => setFormData({...formData, guest_name: e.target.value})}
          required
        />
      </div>
      
      <div>
        <input
          type="tel"
          placeholder="Phone"
          value={formData.guest_phone}
          onChange={(e) => setFormData({...formData, guest_phone: e.target.value})}
          required
        />
      </div>

      {/* Shipping Address */}
      <h3>Shipping Address</h3>
      <div>
        <input
          type="text"
          placeholder="Address Line 1"
          value={formData.shipping_address.address_line_1}
          onChange={(e) => setFormData({
            ...formData,
            shipping_address: {...formData.shipping_address, address_line_1: e.target.value}
          })}
          required
        />
      </div>
      
      <div>
        <input
          type="text"
          placeholder="City"
          value={formData.shipping_address.city}
          onChange={(e) => setFormData({
            ...formData,
            shipping_address: {...formData.shipping_address, city: e.target.value}
          })}
          required
        />
      </div>

      {/* Create Account Option */}
      <div>
        <label>
          <input
            type="checkbox"
            checked={formData.create_account}
            onChange={(e) => setFormData({...formData, create_account: e.target.checked})}
          />
          Create an account for faster checkout next time
        </label>
      </div>

      {formData.create_account && (
        <div>
          <input
            type="password"
            placeholder="Password (min 8 characters)"
            value={formData.password}
            onChange={(e) => setFormData({...formData, password: e.target.value})}
            required={formData.create_account}
            minLength={8}
          />
        </div>
      )}

      <button type="submit">Place Order & Pay</button>
    </form>
  );
};

export default GuestCheckoutForm;
```

### Vue.js Example - Guest Checkout

```vue
<template>
  <form @submit.prevent="handleCheckout">
    <h2>Guest Checkout</h2>
    
    <!-- Contact Information -->
    <div>
      <input v-model="formData.guest_email" type="email" placeholder="Email" required />
    </div>
    <div>
      <input v-model="formData.guest_name" type="text" placeholder="Full Name" required />
    </div>
    <div>
      <input v-model="formData.guest_phone" type="tel" placeholder="Phone" required />
    </div>

    <!-- Shipping Address -->
    <h3>Shipping Address</h3>
    <div>
      <input v-model="formData.shipping_address.address_line_1" placeholder="Address" required />
    </div>
    <div>
      <input v-model="formData.shipping_address.city" placeholder="City" required />
    </div>

    <!-- Create Account -->
    <div>
      <label>
        <input v-model="formData.create_account" type="checkbox" />
        Create an account
      </label>
    </div>

    <div v-if="formData.create_account">
      <input v-model="formData.password" type="password" placeholder="Password" minlength="8" required />
    </div>

    <button type="submit">Place Order & Pay</button>
  </form>
</template>

<script>
export default {
  data() {
    return {
      formData: {
        guest_email: '',
        guest_name: '',
        guest_phone: '',
        shipping_address: {
          address_line_1: '',
          city: '',
          country: 'Bangladesh',
          phone: ''
        },
        create_account: false,
        password: ''
      }
    };
  },
  methods: {
    async handleCheckout() {
      try {
        const response = await fetch('http://127.0.0.1:8000/api/checkout/process', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            ...this.formData,
            items: this.cartItems,
            shipping_method: 'shah_sports_team',
            payment_method: 'ssl_commerz'
          })
        });

        const result = await response.json();

        if (result.success && result.data.payment.redirect_url) {
          window.location.href = result.data.payment.redirect_url;
        }
      } catch (error) {
        console.error('Checkout failed:', error);
      }
    }
  }
};
</script>
```

---

## Flow Diagrams

### Guest Checkout Flow

```
Guest User → Fill Checkout Form → Submit Order
                                        ↓
                              Backend Creates:
                              - Temporary Address
                              - Order (no user_id)
                              - Payment Record
                                        ↓
                              Return Payment URL
                                        ↓
                              Redirect to SSL Commerz
                                        ↓
                              Complete Payment
                                        ↓
                              Order Confirmed
```

### Guest Checkout with Account Creation

```
Guest User → Fill Form + Check "Create Account"
                                        ↓
                              Backend Creates:
                              - New User Account
                              - Address (linked to user)
                              - Order (with user_id)
                              - Payment Record
                                        ↓
                              Return Payment URL + account_created: true
                                        ↓
                              Redirect to SSL Commerz
                                        ↓
                              Complete Payment
                                        ↓
                              Order Confirmed + Account Active
```

---

## Testing

### Test Guest Checkout

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
    "guest_email": "test@example.com",
    "guest_name": "Test User",
    "guest_phone": "01712345678",
    "shipping_address": {
      "address_line_1": "123 Test St",
      "city": "Dhaka",
      "country": "Bangladesh",
      "phone": "01712345678"
    },
    "shipping_method": "shah_sports_team",
    "payment_method": "ssl_commerz"
  }'
```

### Test Guest Checkout with Account Creation

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
    "guest_email": "newuser@example.com",
    "guest_name": "New User",
    "guest_phone": "01798765432",
    "shipping_address": {
      "address_line_1": "456 New St",
      "city": "Dhaka",
      "country": "Bangladesh",
      "phone": "01798765432"
    },
    "shipping_method": "shah_sports_team",
    "payment_method": "ssl_commerz",
    "create_account": true,
    "password": "password123"
  }'
```

---

## Important Notes

1. **Guest Orders:** Orders without `user_id` store customer info in `customer_name`, `customer_email`, `customer_phone` fields

2. **Address Creation:** Temporary addresses are created for guests and linked to user if account is created

3. **Account Creation:** When `create_account=true`, a new user account is created before order processing

4. **Email Uniqueness:** If guest email already exists and `create_account=true`, validation will fail

5. **Payment:** Both guest and authenticated users follow the same payment flow

6. **Order Tracking:** Guest users can track orders using order number (no authentication required)

---

## Security Considerations

- Guest checkout doesn't require authentication
- Passwords are hashed using bcrypt
- Email validation prevents duplicate accounts
- Payment callbacks are verified by SSL Commerz
- Guest addresses are stored securely

---

## Summary

✅ Checkout endpoint is now public  
✅ Supports guest checkout without registration  
✅ Optional account creation during checkout  
✅ Authenticated users can still use saved addresses  
✅ Both flows support SSL Commerz payment  
✅ Guest orders tracked via order number  

The system is ready for both guest and authenticated checkout!
