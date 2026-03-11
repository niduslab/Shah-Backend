# SSL Commerz Payment Gateway Setup

## Configuration

The SSL Commerz payment gateway has been configured with the following credentials:

### Environment Variables (.env)
```env
SSLCZ_STORE_ID=ntime692c10b55069c
SSLCZ_STORE_PASSWORD=ntime692c10b55069c@ssl
SSLCZ_TESTMODE=true
```

### Services Configuration (config/services.php)
```php
'sslcommerz' => [
    'store_id' => env('SSLCZ_STORE_ID'),
    'store_password' => env('SSLCZ_STORE_PASSWORD'),
    'sandbox' => env('SSLCZ_TESTMODE', true),
],
```

## Payment Flow

### 1. Checkout Process
When a customer proceeds to checkout:

**Endpoint:** `POST /api/checkout/process`

**Request:**
```json
{
  "items": [
    {
      "product_id": 1,
      "variation_id": 1,
      "quantity": 2,
      "price": 1500.00
    }
  ],
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method": "shah_sports_team",
  "payment_method": "ssl_commerz",
  "notes": "Please deliver before 5 PM"
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
      "order_number": "ORD-20240309-001",
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

### 2. Payment Gateway Redirect
The frontend should redirect the customer to the `redirect_url` provided in the response. This will take them to the SSL Commerz payment page.

### 3. Payment Callbacks

#### Success Callback
**URL:** `GET /api/payments/ssl-commerz/success`
- Redirects to: `{FRONTEND_URL}/order/success/{order_number}`

#### Failure Callback
**URL:** `GET /api/payments/ssl-commerz/fail`
- Redirects to: `{FRONTEND_URL}/payment/failed?order={order_number}`

#### Cancel Callback
**URL:** `GET /api/payments/ssl-commerz/cancel`
- Redirects to: `{FRONTEND_URL}/payment/cancelled?order={order_number}`

#### IPN (Instant Payment Notification)
**URL:** `POST /api/payments/ssl-commerz/ipn`
- This is called by SSL Commerz to notify the backend of payment status
- Updates order and payment status automatically

## Testing the Integration

### Test Cards (Sandbox Mode)
SSL Commerz provides test cards for sandbox testing:

**Visa:**
- Card Number: 4111 1111 1111 1111
- Expiry: Any future date
- CVV: Any 3 digits

**MasterCard:**
- Card Number: 5555 5555 5555 4444
- Expiry: Any future date
- CVV: Any 3 digits

### Testing Steps

1. **Create a test order:**
```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 1, "quantity": 1, "price": 1000}],
    "shipping_address_id": 1,
    "payment_method": "ssl_commerz",
    "shipping_method": "shah_sports_team"
  }'
```

2. **Redirect to payment gateway:**
   - Use the `redirect_url` from the response
   - Complete payment using test card details

3. **Verify payment status:**
```bash
curl http://127.0.0.1:8000/api/payments/{order_number}/status
```

## Payment Status Tracking

**Endpoint:** `GET /api/payments/{orderNumber}/status`

**Response:**
```json
{
  "success": true,
  "data": {
    "order_number": "ORD-20240309-001",
    "payment_status": "paid",
    "payments": [
      {
        "id": 1,
        "amount": 1650.00,
        "status": "completed",
        "payment_method": "ssl_commerz",
        "transaction_id": "TXN-20240309123456-ABC123",
        "paid_at": "2024-03-09 12:35:00"
      }
    ]
  }
}
```

## Retry Failed Payments

**Endpoint:** `POST /api/payments/{orderNumber}/retry`

**Request:**
```json
{
  "payment_method": "ssl_commerz"
}
```

## Important Notes

1. **Sandbox Mode:** Currently configured for testing. Set `SSLCZ_TESTMODE=false` for production.

2. **Frontend URLs:** Make sure `FRONTEND_URL` in `.env` matches your frontend application URL.

3. **IPN Endpoint:** The IPN endpoint must be publicly accessible for SSL Commerz to send payment notifications.

4. **Security:** 
   - Never commit `.env` file to version control
   - Keep store credentials secure
   - Validate all payment callbacks

5. **Order Status Flow:**
   - Order created → `payment_status: pending`
   - Payment successful → `payment_status: paid`, `status: confirmed`
   - Payment failed → `payment_status: failed`

## Troubleshooting

### Payment initiation fails
- Check SSL Commerz credentials in `.env`
- Verify `SSLCZ_TESTMODE` is set to `true` for sandbox
- Check logs in `storage/logs/laravel.log`

### Callback not working
- Ensure routes are publicly accessible (no auth middleware)
- Check if IPN URL is reachable from external networks
- Verify SSL Commerz has correct callback URLs configured

### Payment status not updating
- Check IPN endpoint is receiving callbacks
- Verify transaction ID matches between payment and callback
- Check database for payment records

## Production Checklist

Before going live:

- [ ] Set `SSLCZ_TESTMODE=false`
- [ ] Update to production SSL Commerz credentials
- [ ] Test with real payment methods
- [ ] Verify IPN endpoint is publicly accessible
- [ ] Set up proper error monitoring
- [ ] Configure proper frontend URLs
- [ ] Test all payment scenarios (success, fail, cancel)
- [ ] Verify order status updates correctly
- [ ] Test refund functionality
