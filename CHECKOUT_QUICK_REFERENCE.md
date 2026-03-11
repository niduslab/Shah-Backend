# Checkout Quick Reference

## Endpoints

All checkout endpoints are PUBLIC (no authentication required):

```
POST /api/checkout/preview
POST /api/checkout/process
POST /api/checkout/shipping-methods
```

## Guest Checkout (Minimal)

```json
POST /api/checkout/process

{
  "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
  "guest_email": "user@example.com",
  "guest_name": "John Doe",
  "guest_phone": "01712345678",
  "shipping_address": {
    "address_line_1": "123 Street",
    "city": "Dhaka",
    "country": "Bangladesh",
    "phone": "01712345678"
  },
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz"
}
```

## Guest with Account Creation

Add these fields to guest checkout:
```json
{
  "create_account": true,
  "password": "SecurePass123"
}
```

## Authenticated User

```json
POST /api/checkout/process
Authorization: Bearer {token}

{
  "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
  "shipping_address_id": 1,
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz"
}
```

## Response

```json
{
  "success": true,
  "data": {
    "order": {...},
    "payment": {
      "redirect_url": "https://sandbox.sslcommerz.com/..."
    },
    "account_created": true
  }
}
```

## Frontend Detection

```javascript
const isAuthenticated = !!localStorage.getItem('auth_token');

if (isAuthenticated) {
  // Show saved addresses
  // Use shipping_address_id
} else {
  // Show guest form
  // Use inline shipping_address
}
```

## Required Fields

### Guest:
- guest_email, guest_name, guest_phone
- shipping_address (object)

### Authenticated:
- shipping_address_id (integer)

### Both:
- items, shipping_method, payment_method

## Payment Methods
- `ssl_commerz` - Credit/Debit cards
- `bkash` - bKash mobile wallet
- `nagad` - Nagad mobile wallet
- `cod` - Cash on Delivery (NEW)

## Shipping Methods
- `shah_sports_team` - Shah Sports delivery
- `pathao_courier` - Pathao courier
- `standard` - Standard shipping (NEW)

## Test Cards (Sandbox)
- Visa: 4111 1111 1111 1111
- MasterCard: 5555 5555 5555 4444
- Expiry: Any future date
- CVV: Any 3 digits


## COD Order Example

```json
{
  "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
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

## COD Response

```json
{
  "success": true,
  "data": {
    "order": {
      "status": "confirmed",
      "payment_status": "pending"
    },
    "payment": {
      "method": "cod",
      "message": "Order placed successfully. Pay on delivery."
    }
  }
}
```

## Standard Shipping Costs
- Up to 1kg: 60 BDT
- 1-5kg: 100 BDT
- 5-10kg: 150 BDT
- Above 10kg: 150 BDT + 15 BDT/kg
- FREE for orders ≥ 1000 BDT
