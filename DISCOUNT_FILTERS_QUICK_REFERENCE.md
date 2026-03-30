# Discount Filters Quick Reference

## Single Line Summary
Filter catalog products by flash deals, promotions, coupons, or any discount type.

## API Endpoint
```
GET /api/catalog/products
```

## All Discount Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `flash_deal_id` | integer | Specific flash deal | `?flash_deal_id=1` |
| `has_flash_deal` | boolean | Any active flash deal | `?has_flash_deal=true` |
| `promotion_id` | integer | Specific promotion | `?promotion_id=1` |
| `has_promotion` | boolean | Any active promotion | `?has_promotion=true` |
| `coupon_id` | integer | Specific coupon | `?coupon_id=1` |
| `has_coupon` | boolean | Any active coupon | `?has_coupon=true` |
| `has_discount` | boolean | ANY discount type | `?has_discount=true` |

## Quick Examples

### Show all discounted products
```http
GET /api/catalog/products?has_discount=true
```

### Show flash deal products
```http
GET /api/catalog/products?flash_deal_id=1
```

### Show promotion products
```http
GET /api/catalog/products?promotion_id=1
```

### Show coupon-eligible products
```http
GET /api/catalog/products?coupon_id=1
```

### Combine with other filters
```http
GET /api/catalog/products?has_discount=true&category_id=5&brand_id=3&sort_by=price_low
```

## Response Includes

Products will include related discount data:

```json
{
  "id": 1,
  "name": "Product Name",
  "price": 99.99,
  "flash_deals": [...],      // If has_flash_deal or flash_deal_id
  "promotions": [...],       // If has_promotion or promotion_id
  "coupons": [...]          // If has_coupon or coupon_id
}
```

## Discount Types

| Type | Application | Duration | Best For |
|------|-------------|----------|----------|
| Flash Deal | Automatic | Hours-Days | Urgent sales |
| Promotion | Automatic | Days-Months | Campaigns |
| Coupon | Manual (code) | Variable | Incentives |

## Common Patterns

```javascript
// All discounts
fetch('/api/catalog/products?has_discount=true')

// Category discounts
fetch('/api/catalog/products?has_discount=true&category_id=5')

// Brand promotions
fetch('/api/catalog/products?has_promotion=true&brand_id=3')

// In-stock discounts
fetch('/api/catalog/products?has_discount=true&in_stock=true')

// Search discounted
fetch('/api/catalog/products?has_discount=true&search=laptop')
```

## Validation

All filters automatically check:
- ✓ Active status
- ✓ Valid time ranges
- ✓ Product is active
- ✓ Stock availability (if in_stock=true)

## Related Endpoints

```
GET /api/flash-deals              - List active flash deals
GET /api/flash-deals/{id}         - Flash deal details
GET /api/admin/promotions         - List promotions (admin)
GET /api/admin/coupons            - List coupons (admin)
POST /api/cart/validate-coupon    - Validate coupon code
```

## Full Documentation

- `DISCOUNT_PRODUCTS_COMPLETE_GUIDE.md` - Complete implementation guide
- `FLASH_DEAL_PRODUCTS_FEATURE.md` - Flash deals details
- `PROMOTIONS_COUPONS_PRODUCTS_FEATURE.md` - Promotions & coupons details
