# Flash Deal Products Feature

## Overview
The catalog now supports filtering products by flash deals, allowing you to display products that are part of active flash deal campaigns.

## Implementation Details

### Database Structure
- Flash deals are stored in `flash_deals` table
- Products are linked via `flash_deal_products` pivot table
- Pivot table includes: `flash_price`, `quantity_limit`, `quantity_sold`

### New Filter Parameters

The `GET /api/catalog/products` endpoint now accepts two new parameters:

1. **flash_deal_id** - Filter products by a specific flash deal ID
2. **has_flash_deal** - Filter products that have any active flash deal

## API Usage

### Get Products from a Specific Flash Deal

```http
GET /api/catalog/products?flash_deal_id=1
```

This returns all active products that belong to flash deal #1, including:
- Product details (name, price, images, etc.)
- Flash deal information (flash_price, time_remaining, etc.)
- Only shows products from currently active flash deals

### Get All Products with Active Flash Deals

```http
GET /api/catalog/products?has_flash_deal=true
```

This returns all products that are part of any currently active flash deal.

### Combined Filters

You can combine flash deal filters with other existing filters:

```http
GET /api/catalog/products?flash_deal_id=1&category_id=5&min_price=50&max_price=500&sort_by=price_low
```

## Response Format

When filtering by flash deals, the response includes flash deal information:

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
        "flash_deals": [
          {
            "id": 1,
            "title": "Summer Sale",
            "starts_at": "2026-03-30T00:00:00.000000Z",
            "ends_at": "2026-04-05T23:59:59.000000Z",
            "is_active": true,
            "pivot": {
              "flash_price": 79.99,
              "quantity_limit": 100,
              "quantity_sold": 25
            }
          }
        ]
      }
    ],
    "per_page": 15,
    "total": 50
  }
}
```

## Flash Deal Validation

The system automatically validates:
- Flash deal must have `is_active = true`
- Current time must be between `starts_at` and `ends_at`
- Only active products are shown

## Use Cases

### 1. Flash Deal Landing Page
Display all products from a specific flash deal campaign:
```javascript
fetch('/api/catalog/products?flash_deal_id=1&per_page=20')
```

### 2. Flash Deal Badge/Tag
Show products with active flash deals across the site:
```javascript
fetch('/api/catalog/products?has_flash_deal=true')
```

### 3. Category + Flash Deal
Show flash deal products within a specific category:
```javascript
fetch('/api/catalog/products?flash_deal_id=1&category_id=5')
```

### 4. Search + Flash Deal
Search for products within a flash deal:
```javascript
fetch('/api/catalog/products?flash_deal_id=1&search=laptop')
```

## Frontend Implementation Example

```javascript
// Get flash deal products
async function getFlashDealProducts(flashDealId, page = 1) {
  const response = await fetch(
    `/api/catalog/products?flash_deal_id=${flashDealId}&page=${page}&per_page=20`
  );
  const data = await response.json();
  
  if (data.success) {
    return data.data.data.map(product => ({
      ...product,
      flashPrice: product.flash_deals[0]?.pivot.flash_price,
      originalPrice: product.price,
      discount: calculateDiscount(
        product.price, 
        product.flash_deals[0]?.pivot.flash_price
      ),
      flashDealEndsAt: product.flash_deals[0]?.ends_at
    }));
  }
}

function calculateDiscount(originalPrice, flashPrice) {
  return Math.round(((originalPrice - flashPrice) / originalPrice) * 100);
}
```

## Related Endpoints

- `GET /api/flash-deals` - List all active flash deals
- `GET /api/flash-deals/{id}` - Get specific flash deal with products
- `GET /api/flash-deals/upcoming` - List upcoming flash deals

## Notes

- Flash deal products are automatically filtered to show only active deals
- The `flash_price` from the pivot table should be displayed instead of the regular price
- Products can belong to multiple flash deals simultaneously
- When multiple flash deals apply, all are included in the response
