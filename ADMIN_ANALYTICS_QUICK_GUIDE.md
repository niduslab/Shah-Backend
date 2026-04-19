# Admin Analytics Quick Reference Guide

## Quick Access URLs

All analytics endpoints are under `/api/admin/analytics/` and require admin authentication.

## Main Dashboard

**Endpoint**: `GET /api/admin/analytics/dashboard`

**Parameters**:
- `date_from` - Start date (default: 30 days ago)
- `date_to` - End date (default: today)

**What You Get**:
- Total visitors and session statistics
- Page view metrics
- Product view analytics
- Checkout funnel performance
- Cart event summaries
- Search statistics

**Example**:
```
GET /api/admin/analytics/dashboard?date_from=2024-01-01&date_to=2024-12-31
```

---

## 1. Visitor Analytics

### View All Visitors
**Endpoint**: `GET /api/admin/analytics/visitors`

**Filters**:
- `per_page` - Results per page (default: 15)
- `date_from` / `date_to` - Date range
- `device_type` - Filter by mobile, tablet, or desktop
- `authenticated` - true/false for logged-in users

**Example**:
```
GET /api/admin/analytics/visitors?device_type=mobile&authenticated=true
```

### View Visitor Details
**Endpoint**: `GET /api/admin/analytics/visitors/{session_id}`

**Shows**:
- Complete session information
- All pages viewed
- Products viewed
- Cart activities
- Checkout progress
- Search queries

---

## 2. Product Analytics

### Product View Statistics
**Endpoint**: `GET /api/admin/analytics/product-views`

**Parameters**:
- `per_page` - Results per page
- `date_from` / `date_to` - Date range
- `sort_by` - Sort by: `views`, `conversions`, or `purchases`

**Metrics Shown**:
- Total views per product
- Unique sessions viewing product
- Time spent on product page
- Cart conversion rate (views → cart adds)
- Purchase conversion rate (views → purchases)

**Example**:
```
GET /api/admin/analytics/product-views?sort_by=conversions&per_page=20
```

**Use This To**:
- Identify best-performing products
- Find products with high views but low conversions
- Optimize product pages with low engagement

---

## 3. Checkout Funnel Analytics

### View Checkout Funnel
**Endpoint**: `GET /api/admin/analytics/checkout-funnel`

**Parameters**:
- `status` - Filter by stage: `cart_viewed`, `checkout_initiated`, `shipping_info_entered`, `payment_info_entered`, `order_completed`, `abandoned`
- `date_from` / `date_to` - Date range

**Funnel Stages**:
1. **Cart Viewed** - Customer viewed their cart
2. **Checkout Initiated** - Customer clicked checkout
3. **Shipping Info Entered** - Shipping details provided
4. **Payment Info Entered** - Payment details provided
5. **Order Completed** - Purchase successful
6. **Abandoned** - Customer left without completing

**Example**:
```
GET /api/admin/analytics/checkout-funnel?status=abandoned
```

### View Abandoned Carts
**Endpoint**: `GET /api/admin/analytics/abandoned-carts`

**Parameters**:
- `min_value` - Minimum cart value to show
- `date_from` / `date_to` - Date range

**Shows**:
- Customer information (if logged in)
- Cart contents
- Cart total value
- When abandoned
- Session details

**Example**:
```
GET /api/admin/analytics/abandoned-carts?min_value=100
```

**Use This To**:
- Identify high-value abandoned carts
- Send recovery emails
- Understand abandonment patterns
- Calculate lost revenue

---

## 4. Cart Event Analytics

### View Cart Events
**Endpoint**: `GET /api/admin/analytics/cart-events`

**Parameters**:
- `event_type` - Filter by: `added`, `updated`, `removed`
- `date_from` / `date_to` - Date range

**Shows**:
- Which products are added most
- Which products are removed most
- Quantity changes
- Price at time of event

**Example**:
```
GET /api/admin/analytics/cart-events?event_type=removed
```

**Use This To**:
- Identify products frequently removed (pricing issues?)
- See most popular products
- Understand cart behavior patterns

---

## 5. Search Analytics

### View Search Statistics
**Endpoint**: `GET /api/admin/analytics/search`

**Parameters**:
- `no_results` - Set to `true` to see only searches with no results
- `date_from` / `date_to` - Date range

**Metrics**:
- Most searched terms
- Average results per search
- Click-through rate
- Searches with zero results

**Example**:
```
GET /api/admin/analytics/search?no_results=true
```

**Use This To**:
- Identify missing products (searches with no results)
- Understand customer intent
- Improve search functionality
- Add new product categories

---

## 6. Page View Analytics

### View Page Statistics
**Endpoint**: `GET /api/admin/analytics/page-views`

**Parameters**:
- `page_type` - Filter by: `home`, `product`, `category`, `cart`, `checkout`, etc.
- `date_from` / `date_to` - Date range

**Shows**:
- Most visited pages
- Average time on page
- Unique visitors per page

**Example**:
```
GET /api/admin/analytics/page-views?page_type=product
```

---

## 7. Export Data

### Export Analytics to CSV
**Endpoint**: `GET /api/admin/analytics/export`

**Parameters**:
- `type` - Export type: `visitors`, `products`, `checkouts`, `searches`
- `date_from` / `date_to` - Date range

**Example**:
```
GET /api/admin/analytics/export?type=abandoned-carts&date_from=2024-01-01
```

**Downloads**: CSV file with selected data

---

## Key Metrics Explained

### Conversion Rate
```
Conversion Rate = (Completed Orders / Total Checkouts) × 100
```

### Abandonment Rate
```
Abandonment Rate = (Abandoned Carts / Total Checkouts) × 100
```

### View-to-Cart Rate
```
View-to-Cart Rate = (Products Added to Cart / Product Views) × 100
```

### View-to-Purchase Rate
```
View-to-Purchase Rate = (Products Purchased / Product Views) × 100
```

### Click-Through Rate (Search)
```
CTR = (Searches with Clicks / Total Searches) × 100
```

---

## Common Use Cases

### 1. Daily Morning Check
```
GET /api/admin/analytics/dashboard?date_from=yesterday&date_to=today
```
Quick overview of yesterday's performance.

### 2. Find High-Value Abandoned Carts
```
GET /api/admin/analytics/abandoned-carts?min_value=200&date_from=last-week
```
Identify customers who abandoned expensive carts for recovery campaigns.

### 3. Identify Problem Products
```
GET /api/admin/analytics/product-views?sort_by=views
```
Find products with high views but low conversions - may need better descriptions or pricing.

### 4. Improve Search
```
GET /api/admin/analytics/search?no_results=true
```
See what customers are searching for but not finding.

### 5. Mobile Experience Check
```
GET /api/admin/analytics/visitors?device_type=mobile
```
Analyze mobile user behavior separately.

### 6. Weekly Performance Report
```
GET /api/admin/analytics/export?type=checkouts&date_from=last-week&date_to=today
```
Export weekly data for reporting.

---

## Tips for Better Insights

1. **Compare Time Periods**: Always compare current period with previous period to identify trends

2. **Segment by Device**: Mobile and desktop users behave differently

3. **Track Conversion Funnel**: Monitor where most drop-offs occur

4. **Monitor Abandoned Carts**: High abandonment? Check shipping costs, checkout complexity

5. **Use Search Data**: Customer searches reveal what they want

6. **Product Performance**: High views + low conversions = optimization opportunity

7. **Session Duration**: Longer sessions usually mean more engagement

8. **Return Visitors**: Track authenticated vs guest to measure loyalty

---

## Automated Insights

The system automatically:
- Marks checkouts as abandoned after 30 minutes of inactivity
- Tracks conversion from product view → cart → purchase
- Calculates all conversion rates
- Aggregates data for quick reporting

---

## Need Help?

- Check the full documentation: `ANALYTICS_SYSTEM_DOCUMENTATION.md`
- Review API responses for detailed data structures
- Contact development team for custom reports

---

**Quick Tip**: Bookmark the dashboard endpoint with your preferred date range for daily checks!
