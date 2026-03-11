# SSL Commerz Payment Testing Guide

## Quick Setup Verification

### 1. Check Configuration
```bash
# Verify .env has SSL Commerz credentials
cat .env | grep SSLCZ

# Expected output:
# SSLCZ_STORE_ID=ntime692c10b55069c
# SSLCZ_STORE_PASSWORD=ntime692c10b55069c@ssl
# SSLCZ_TESTMODE=true
```

### 2. Clear Configuration Cache
```bash
php artisan config:clear
```

### 3. Verify Routes
```bash
php artisan route:list --path=payments
```

## Testing Payment Flow

### Step 1: Create Test User and Login
```bash
# Register a test user
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "email": "test@example.com",
    "phone": "01712345678",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login to get token
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

Save the token from the response.

### Step 2: Create Shipping Address
```bash
curl -X POST http://127.0.0.1:8000/api/addresses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "shipping",
    "address_line_1": "123 Test Street",
    "city": "Dhaka",
    "state": "Dhaka",
    "postal_code": "1200",
    "country": "Bangladesh",
    "phone": "01712345678",
    "is_default": true
  }'
```

Save the address ID from the response.

### Step 3: Get Available Products
```bash
curl http://127.0.0.1:8000/api/catalog/products
```

Note a product ID and price.

### Step 4: Preview Checkout
```bash
curl -X POST http://127.0.0.1:8000/api/checkout/preview \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1,
        "price": 1000.00
      }
    ],
    "shipping_address_id": 1,
    "shipping_method": "shah_sports_team"
  }'
```

### Step 5: Process Checkout with SSL Commerz
```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1,
        "price": 1000.00
      }
    ],
    "shipping_address_id": 1,
    "billing_address_id": 1,
    "shipping_method": "shah_sports_team",
    "payment_method": "ssl_commerz",
    "notes": "Test order"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Order created successfully.",
  "data": {
    "order": {
      "id": 1,
      "order_number": "SS20240309ABCD",
      "total_amount": 1100.00,
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

### Step 6: Complete Payment on SSL Commerz
1. Copy the `redirect_url` from the response
2. Open it in a browser
3. Use test card details:
   - **Card Number:** 4111 1111 1111 1111
   - **Expiry:** 12/25 (any future date)
   - **CVV:** 123

### Step 7: Verify Payment Status
```bash
curl http://127.0.0.1:8000/api/payments/SS20240309ABCD/status
```

**Expected Response (after successful payment):**
```json
{
  "success": true,
  "data": {
    "order_number": "SS20240309ABCD",
    "payment_status": "paid",
    "payments": [
      {
        "id": 1,
        "amount": 1100.00,
        "status": "completed",
        "payment_method": "ssl_commerz",
        "transaction_id": "TXN-20240309123456-ABC123",
        "paid_at": "2024-03-09T12:35:00.000000Z"
      }
    ]
  }
}
```

## Testing Different Scenarios

### Test Failed Payment
1. Follow steps 1-5 above
2. On SSL Commerz payment page, click "Cancel" or use an invalid card
3. Verify order status remains "pending" and payment_status is "failed"

### Test Payment Retry
```bash
curl -X POST http://127.0.0.1:8000/api/payments/SS20240309ABCD/retry \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_method": "ssl_commerz"
  }'
```

## Callback URLs

The following URLs are configured for SSL Commerz callbacks:

- **Success:** `http://127.0.0.1:8000/api/payments/ssl-commerz/success`
- **Fail:** `http://127.0.0.1:8000/api/payments/ssl-commerz/fail`
- **Cancel:** `http://127.0.0.1:8000/api/payments/ssl-commerz/cancel`
- **IPN:** `http://127.0.0.1:8000/api/payments/ssl-commerz/ipn`

## Common Issues and Solutions

### Issue: "Payment initiation failed"
**Solution:** 
- Check SSL Commerz credentials in `.env`
- Verify `SSLCZ_TESTMODE=true` for sandbox
- Run `php artisan config:clear`

### Issue: "Callback not working"
**Solution:**
- Ensure your application is accessible from the internet (use ngrok for local testing)
- Update callback URLs in SSL Commerz merchant panel if needed

### Issue: "Payment status not updating"
**Solution:**
- Check if IPN endpoint received the callback (check logs)
- Verify transaction ID matches between payment and callback
- Check `storage/logs/laravel.log` for errors

## Using ngrok for Local Testing

If testing locally and need SSL Commerz to reach your IPN endpoint:

```bash
# Install ngrok
# Then run:
ngrok http 8000

# Update APP_URL in .env with the ngrok URL
APP_URL=https://your-ngrok-url.ngrok.io
```

## Monitoring Logs

```bash
# Watch Laravel logs in real-time
tail -f storage/logs/laravel.log

# Filter for payment-related logs
tail -f storage/logs/laravel.log | grep -i "ssl\|payment"
```

## Database Verification

```bash
# Check orders
php artisan tinker
>>> \App\Models\Order::latest()->first();

# Check payments
>>> \App\Models\Payment::latest()->first();

# Check payment status for an order
>>> \App\Models\Order::where('order_number', 'SS20240309ABCD')->with('payments')->first();
```

## Production Deployment Checklist

Before deploying to production:

1. **Update Environment Variables:**
   ```env
   SSLCZ_TESTMODE=false
   SSLCZ_STORE_ID=your_production_store_id
   SSLCZ_STORE_PASSWORD=your_production_password
   APP_URL=https://your-production-domain.com
   FRONTEND_URL=https://your-frontend-domain.com
   ```

2. **Clear Caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

3. **Test with Real Cards:**
   - Use actual payment methods
   - Verify all callback URLs work
   - Test refund functionality

4. **Monitor:**
   - Set up error monitoring (Sentry, Bugsnag, etc.)
   - Monitor payment success rates
   - Track failed payments

## Support

For SSL Commerz specific issues:
- Documentation: https://developer.sslcommerz.com/
- Support: support@sslcommerz.com
- Merchant Panel: https://merchant.sslcommerz.com/
