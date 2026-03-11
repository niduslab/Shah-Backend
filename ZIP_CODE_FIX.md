# Zip Code Null Error Fix

## Issue
```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'zip_code' cannot be null
```

## Root Cause
The `zip_code` column in the `addresses` table is defined as `NOT NULL`, but the code was allowing it to be `null` when not provided.

## Solution
Changed the default value from `null` to `'00000'` when zip_code is not provided.

### Before (Caused Error)
```php
'zip_code' => $data['zip_code'] ?? $data['postal_code'] ?? null,  // ❌ null not allowed
```

### After (Fixed)
```php
'zip_code' => $data['zip_code'] ?? $data['postal_code'] ?? '00000',  // ✅ Default value
```

## Changes Made

### File: `app/Services/OrderService.php`

Updated both shipping and billing address creation to use `'00000'` as default zip_code instead of `null`.

## Why '00000'?
- Database requires a value (NOT NULL constraint)
- '00000' is a common placeholder for unknown/not provided zip codes
- Allows orders to proceed even if zip code is not provided
- Can be updated later if needed

## Your Payload Works Now

```json
{
  "shipping_address": {
    "address_line_1": "Corporis iusto facil",
    "city": "Chittagong",
    "state": "Manikganj",
    "zip_code": "73361",  // ✅ Will be stored as "73361"
    "country": "Bangladesh",
    "phone": "+1 (708) 862-5499"
  }
}
```

If zip_code is missing:
```json
{
  "shipping_address": {
    "address_line_1": "123 Street",
    "city": "Dhaka",
    // zip_code not provided
    "phone": "01712345678"
  }
}
```
Will store: `zip_code: "00000"`

## Alternative Solutions

If you prefer a different approach:

### Option 1: Make zip_code nullable in database
```sql
ALTER TABLE addresses MODIFY zip_code VARCHAR(20) NULL;
```

### Option 2: Require zip_code in validation
```php
// In CheckoutController
'shipping_address.zip_code' => 'required_with:shipping_address|string',
```

### Option 3: Use empty string
```php
'zip_code' => $data['zip_code'] ?? $data['postal_code'] ?? '',
```

## Current Implementation
Using `'00000'` as default - no database changes needed, orders can proceed without zip code.

## Testing

### With zip_code
```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 25, "quantity": 1, "price": 33}],
    "guest_email": "user@example.com",
    "guest_name": "Test User",
    "guest_phone": "01712345678",
    "shipping_address": {
      "address_line_1": "123 Street",
      "city": "Dhaka",
      "zip_code": "1200",
      "phone": "01712345678"
    },
    "shipping_method": "standard",
    "payment_method": "cod"
  }'
```
Result: `zip_code: "1200"`

### Without zip_code
```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 25, "quantity": 1, "price": 33}],
    "guest_email": "user@example.com",
    "guest_name": "Test User",
    "guest_phone": "01712345678",
    "shipping_address": {
      "address_line_1": "123 Street",
      "city": "Dhaka",
      "phone": "01712345678"
    },
    "shipping_method": "standard",
    "payment_method": "cod"
  }'
```
Result: `zip_code: "00000"`

## Summary

✅ Fixed zip_code null constraint violation  
✅ Uses '00000' as default when not provided  
✅ Accepts both zip_code and postal_code from frontend  
✅ No database migration needed  
✅ Orders can proceed without zip code  

The checkout now works whether zip_code is provided or not!
