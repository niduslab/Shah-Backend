# Analytics System - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Install (2 minutes)

```bash
# Install dependencies
composer install

# Run migrations
php artisan migrate

# Clear cache
php artisan config:clear && php artisan route:clear
```

### Step 2: Setup Cron (1 minute)

Add to your crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually:
```bash
php artisan analytics:mark-abandoned-checkouts
```

### Step 3: Test Tracking (2 minutes)

Use Postman or curl to test:

```bash
# Test product view tracking
curl -X POST http://your-domain.com/api/analytics/track/product-view \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'

# Test cart event
curl -X POST http://your-domain.com/api/analytics/track/cart-event \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "added",
    "product_id": 1,
    "quantity": 2,
    "price": 99.99
  }'

# Test checkout tracking
curl -X POST http://your-domain.com/api/analytics/track/checkout \
  -H "Content-Type: application/json" \
  -d '{
    "status": "cart_viewed",
    "cart_total": 199.98,
    "items_count": 2
  }'
```

### Step 4: View Dashboard

Access admin dashboard (requires admin authentication):
```
GET /api/admin/analytics/dashboard
```

### Step 5: Integrate Frontend

Add to your product page:
```javascript
// Track product view
fetch('/api/analytics/track/product-view', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ product_id: productId })
});
```

Add to your cart:
```javascript
// Track add to cart
fetch('/api/analytics/track/cart-event', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    event_type: 'added',
    product_id: productId,
    quantity: quantity,
    price: price
  })
});
```

Add to your checkout:
```javascript
// Track checkout stages
fetch('/api/analytics/track/checkout', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    status: 'checkout_initiated',
    cart_items: cartItems,
    cart_total: total,
    items_count: cartItems.length
  })
});
```

## ✅ Done!

Your analytics system is now tracking:
- ✅ Visitor sessions
- ✅ Page views
- ✅ Product views
- ✅ Cart events
- ✅ Checkout funnel
- ✅ Search queries

## 📊 View Your Data

### Check Database
```sql
-- View recent sessions
SELECT * FROM visitor_sessions ORDER BY created_at DESC LIMIT 10;

-- View product views
SELECT * FROM product_views ORDER BY created_at DESC LIMIT 10;

-- View abandoned carts
SELECT * FROM checkout_funnels WHERE status = 'abandoned' ORDER BY abandoned_at DESC;
```

### Use Admin API
```bash
# Get dashboard overview
GET /api/admin/analytics/dashboard

# Get abandoned carts
GET /api/admin/analytics/abandoned-carts?min_value=100

# Get product performance
GET /api/admin/analytics/product-views?sort_by=conversions

# Export data
GET /api/admin/analytics/export?type=checkouts
```

## 🎯 Common Tasks

### Find High-Value Abandoned Carts
```
GET /api/admin/analytics/abandoned-carts?min_value=200&date_from=2024-01-01
```

### See Most Viewed Products
```
GET /api/admin/analytics/product-views?sort_by=views&per_page=20
```

### Check Conversion Rates
```
GET /api/admin/analytics/dashboard
```
Look for `checkout_funnel.conversion_rate`

### Find Problem Products
```
GET /api/admin/analytics/product-views?sort_by=views
```
Look for high views + low conversions

### See What Customers Search For
```
GET /api/admin/analytics/search
```

### Identify Missing Products
```
GET /api/admin/analytics/search?no_results=true
```

## 📚 Need More Help?

- **Getting Started**: Read `ANALYTICS_SYSTEM_README.md`
- **Admin Guide**: Read `ADMIN_ANALYTICS_QUICK_GUIDE.md`
- **Integration**: Read `ANALYTICS_INTEGRATION_EXAMPLES.md`
- **Visual Guide**: Read `ANALYTICS_VISUAL_SUMMARY.md`
- **Full Docs**: Read `ANALYTICS_SYSTEM_DOCUMENTATION.md`

## 🐛 Troubleshooting

### Not tracking?
1. Check migrations ran: `php artisan migrate:status`
2. Check routes exist: `php artisan route:list | grep analytics`
3. Check database tables exist
4. Check browser console for errors

### No abandoned carts?
1. Check cron is running: `php artisan schedule:list`
2. Manually run: `php artisan analytics:mark-abandoned-checkouts`
3. Check visitor_sessions table has data

### Empty dashboard?
1. Generate some test data using the curl commands above
2. Check date range in query parameters
3. Verify admin authentication is working

## 🎉 You're All Set!

Start tracking your e-commerce success today!

---

**Quick Links:**
- Main README: `ANALYTICS_SYSTEM_README.md`
- Admin Guide: `ADMIN_ANALYTICS_QUICK_GUIDE.md`
- Integration Examples: `ANALYTICS_INTEGRATION_EXAMPLES.md`
