# SSL Commerz Quick Reference

## Credentials (Sandbox)
```
Store ID: ntime692c10b55069c
Password: ntime692c10b55069c@ssl
Mode: Test/Sandbox
```

## API Endpoints

### Checkout
```
POST /api/checkout/process
Authorization: Bearer {token}

{
  "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz"
}
```

### Payment Status
```
GET /api/payments/{orderNumber}/status
```

### Retry Payment
```
POST /api/payments/{orderNumber}/retry
Authorization: Bearer {token}

{
  "payment_method": "ssl_commerz"
}
```

## Test Cards

### Visa
```
Card: 4111 1111 1111 1111
Expiry: 12/25
CVV: 123
```

### MasterCard
```
Card: 5555 5555 5555 4444
Expiry: 12/25
CVV: 123
```

## Callback URLs
```
Success: /api/payments/ssl-commerz/success
Fail: /api/payments/ssl-commerz/fail
Cancel: /api/payments/ssl-commerz/cancel
IPN: /api/payments/ssl-commerz/ipn
```

## Quick Test
```bash
# 1. Clear config
php artisan config:clear

# 2. Check routes
php artisan route:list --path=payments

# 3. Test checkout (replace token and IDs)
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":1,"quantity":1,"price":1000}],"shipping_address_id":1,"payment_method":"ssl_commerz","shipping_method":"shah_sports_team"}'
```

## Production Switch
```env
# Change in .env
SSLCZ_TESTMODE=false
SSLCZ_STORE_ID=production_store_id
SSLCZ_STORE_PASSWORD=production_password
```

## Logs
```bash
tail -f storage/logs/laravel.log | grep -i "ssl\|payment"
```
