# Promotions & Coupons Products Feature

## Overview
The catalog now supports filtering products by promotions and coupons, allowing you to display products with active discounts and special offers.

## Implementation Details

### Database Structure

#### Promotions
- Stored in `promotions` table
- Products linked via `promotion_products` pivot table
- Brands linked via `promotion_brands` pivot table
- Categories linked via `promotion_categories` pivot table
- Promotion types: `percentage`, `fixed_amount`, `flash_sale`, `combo_offer`, `free_delivery`
- Applies to: `all_products`, `specific_products`, `specific_brands`, `specific_categories`

#### Coupons
- Stored in `coupons` table
- Products linked via `coupon_products` pivot table
- Brands linked via `coupon_brands` pivot table
- Categories linked via `coupon_categories` pivot table
- Discount types: `percentage`, `fixed_amount`, `free_shipping`
- Applies to: `all_products`, `specific_products`, `specific_brands`, `specific_categories`

### New Filter Parameters

The `GET /api/catalog/products` endpoint now accepts these new parameters:

1. **promotion_id** - Filter products by a specific promotion ID
2. **has_promotion** - Filter products that have any active promotion
3. **coupon_id** - Filter products by a specific coupon ID
4. **has_coupon** - Filter products that have any active coupon
5. **has_discount** - Filter products with any active discount (flash deal, promotion, or coupon)

## API Usage

### Get Products from a Specific Promotion

```http
GET /api/catalog/products?promotion_id=1
```

Returns all active products that belong to promotion #1, including promotion details.

### Get All Products with Active Promotions

```http
GET /api/catalog/products?has_promotion=true
```

Returns all products that are part of any currently active promotion.

### Get Products from a Specific Coupon

```http
GET /api/catalog/products?coupon_id=1
```

Returns all active products that the coupon applies to.

### Get All Products with Active Coupons

```http
GET /api/catalog/products?has_coupon=true
```

Returns all products that have any active coupon available.

### Get All Products with Any Discount

```http
GET /api/catalog/products?has_discount=true
```

Returns all products that have any active discount (flash deals, promotions, or coupons).

### Combined Filters

You can combine discount filters with other existing filters:

```http
GET /api/catalog/products?has_promotion=true&category_id=5&min_price=50&max_price=500&sort_by=price_low
```

```http
GET /api/catalog/products?has_discount=true&brand_id=3&in_stock=true
```

## Response Format

### Products with Promotions

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Product Name",
        "slug": "product-name",
        "sku": "SKU123",
        "price": 99.99,
        "quantity": 50,
        "images": [...],
        "category": {...},
        "brand": {...},
        "promotions": [
          {
            "id": 1,
            "name": "Summer Sale",
            "promotion_type": "percentage",
            "discount_value": 20.00,
            "starts_at": "2026-03-30T00:00:00.000000Z",
            "ends_at": "2026-04-30T23:59:59.000000Z",
            "is_active": true
          }
        ]
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

### Products with Coupons

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Product Name",
        "price": 99.99,
        "coupons": [
          {
            "id": 1,
            "code": "SAVE20",
            "name": "20% Off Electronics",
            "discount_type": "percentage",
            "discount_value": 20.00,
            "min_order_amount": 50.00,
            "max_discount_amount": 100.00,
            "expires_at": "2026-04-30T23:59:59.000000Z",
            "is_active": true
          }
        ]
      }
    ]
  }
}
```

## Validation Rules

### Promotions
The system automatically validates:
- Promotion must have `is_active = true`
- Current time must be between `starts_at` and `ends_at`
- Only active products are shown

### Coupons
The system automatically validates:
- Coupon must have `is_active = true`
- Current time must be after `starts_at` (if set)
- Current time must be before `expires_at` (if set)
- Only active products are shown

## Use Cases

### 1. Promotion Landing Page
Display all products from a specific promotion campaign:
```javascript
fetch('/api/catalog/products?promotion_id=1&per_page=20')
```

### 2. Coupon-Eligible Products
Show products that can use a specific coupon:
```javascript
fetch('/api/catalog/products?coupon_id=1')
```

### 3. All Discounted Products
Show all products with any type of discount:
```javascript
fetch('/api/catalog/products?has_discount=true')
```

### 4. Category + Promotion
Show promotional products within a specific category:
```javascript
fetch('/api/catalog/products?has_promotion=true&category_id=5')
```

### 5. Brand + Coupon
Show products from a brand that have active coupons:
```javascript
fetch('/api/catalog/products?has_coupon=true&brand_id=3')
```

### 6. Search + Discount
Search for discounted products:
```javascript
fetch('/api/catalog/products?has_discount=true&search=laptop')
```

## Frontend Implementation Examples

### Calculate Promotion Discount

```javascript
async function getPromotionProducts(promotionId, page = 1) {
  const response = await fetch(
    `/api/catalog/products?promotion_id=${promotionId}&page=${page}&per_page=20`
  );
  const data = await response.json();
  
  if (data.success) {
    return data.data.data.map(product => {
      const promotion = product.promotions[0];
      const discountedPrice = calculatePromotionPrice(
        product.price, 
        promotion
      );
      
      return {
        ...product,
        originalPrice: product.price,
        discountedPrice,
        discount: promotion.discount_value,
        discountType: promotion.promotion_type,
        promotionEndsAt: promotion.ends_at
      };
    });
  }
}

function calculatePromotionPrice(price, promotion) {
  let discount = 0;
  
  switch (promotion.promotion_type) {
    case 'percentage':
      discount = price * (promotion.discount_value / 100);
      break;
    case 'fixed_amount':
      discount = promotion.discount_value;
      break;
  }
  
  // Apply max discount limit if set
  if (promotion.max_discount_amount && discount > promotion.max_discount_amount) {
    discount = promotion.max_discount_amount;
  }
  
  return Math.max(0, price - discount);
}
```

### Display Coupon Information

```javascript
async function getCouponProducts(couponId) {
  const response = await fetch(
    `/api/catalog/products?coupon_id=${couponId}`
  );
  const data = await response.json();
  
  if (data.success) {
    return data.data.data.map(product => {
      const coupon = product.coupons[0];
      
      return {
        ...product,
        couponCode: coupon.code,
        couponDiscount: coupon.discount_value,
        couponType: coupon.discount_type,
        minOrderAmount: coupon.min_order_amount,
        maxDiscount: coupon.max_discount_amount
      };
    });
  }
}
```

### Show All Discounted Products

```javascript
async function getAllDiscountedProducts(filters = {}) {
  const params = new URLSearchParams({
    has_discount: true,
    ...filters
  });
  
  const response = await fetch(`/api/catalog/products?${params}`);
  const data = await response.json();
  
  if (data.success) {
    return data.data.data.map(product => {
      // Check which discount type applies
      const hasFlashDeal = product.flash_deals?.length > 0;
      const hasPromotion = product.promotions?.length > 0;
      const hasCoupon = product.coupons?.length > 0;
      
      return {
        ...product,
        discountType: hasFlashDeal ? 'flash_deal' : 
                      hasPromotion ? 'promotion' : 
                      hasCoupon ? 'coupon' : null,
        discountBadge: getDiscountBadge(product)
      };
    });
  }
}

function getDiscountBadge(product) {
  if (product.flash_deals?.length > 0) {
    return 'Flash Deal';
  }
  if (product.promotions?.length > 0) {
    const promo = product.promotions[0];
    return `${promo.discount_value}% OFF`;
  }
  if (product.coupons?.length > 0) {
    const coupon = product.coupons[0];
    return `Use Code: ${coupon.code}`;
  }
  return null;
}
```

## Promotion Types

### 1. Percentage Discount
```json
{
  "promotion_type": "percentage",
  "discount_value": 20.00
}
```
Reduces price by 20%

### 2. Fixed Amount Discount
```json
{
  "promotion_type": "fixed_amount",
  "discount_value": 10.00
}
```
Reduces price by $10

### 3. Flash Sale
```json
{
  "promotion_type": "flash_sale",
  "discount_value": 30.00
}
```
Time-limited percentage discount

### 4. Free Delivery
```json
{
  "promotion_type": "free_delivery"
}
```
Waives shipping costs

### 5. Combo Offer
```json
{
  "promotion_type": "combo_offer",
  "discount_value": 15.00
}
```
Special bundle pricing

## Coupon Types

### 1. Percentage Discount
```json
{
  "discount_type": "percentage",
  "discount_value": 15.00,
  "max_discount_amount": 50.00
}
```

### 2. Fixed Amount Discount
```json
{
  "discount_type": "fixed_amount",
  "discount_value": 25.00
}
```

### 3. Free Shipping
```json
{
  "discount_type": "free_shipping"
}
```

## Related Endpoints

### Admin Endpoints
- `GET /api/admin/promotions` - List all promotions
- `POST /api/admin/promotions` - Create promotion
- `GET /api/admin/promotions/{id}` - Get promotion details
- `PUT /api/admin/promotions/{id}` - Update promotion
- `DELETE /api/admin/promotions/{id}` - Delete promotion
- `POST /api/admin/promotions/{id}/toggle` - Toggle promotion status

- `GET /api/admin/coupons` - List all coupons
- `POST /api/admin/coupons` - Create coupon
- `GET /api/admin/coupons/{id}` - Get coupon details
- `PUT /api/admin/coupons/{id}` - Update coupon
- `DELETE /api/admin/coupons/{id}` - Delete coupon
- `GET /api/admin/coupons/{id}/usage` - Get coupon usage history

### Public Endpoints
- `POST /api/cart/validate-coupon` - Validate coupon code

## Priority and Stacking

### Discount Priority
When multiple discounts apply to a product:
1. Flash Deals (highest priority)
2. Promotions (by priority field)
3. Coupons (applied at checkout)

### Notes
- Products can have multiple promotions simultaneously
- Products can have multiple coupons available
- Flash deals, promotions, and coupons can coexist
- The frontend should determine which discount to display prominently
- Coupons are typically applied at checkout, not at product level
- Promotions are automatically applied to eligible products
