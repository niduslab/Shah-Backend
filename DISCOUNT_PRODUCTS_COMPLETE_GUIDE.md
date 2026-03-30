# Complete Guide: Discount Products in Catalog

## Overview
This guide covers all discount-related filtering in the catalog API, including Flash Deals, Promotions, and Coupons.

## Quick Reference

### All Available Discount Filters

```http
GET /api/catalog/products?{filter}
```

| Filter | Description | Example |
|--------|-------------|---------|
| `flash_deal_id={id}` | Products in specific flash deal | `?flash_deal_id=1` |
| `has_flash_deal=true` | Products with any active flash deal | `?has_flash_deal=true` |
| `promotion_id={id}` | Products in specific promotion | `?promotion_id=1` |
| `has_promotion=true` | Products with any active promotion | `?has_promotion=true` |
| `coupon_id={id}` | Products eligible for specific coupon | `?coupon_id=1` |
| `has_coupon=true` | Products with any active coupon | `?has_coupon=true` |
| `has_discount=true` | Products with ANY discount type | `?has_discount=true` |

## Discount Types Comparison

### 1. Flash Deals
- **Purpose**: Time-limited special pricing
- **Duration**: Short-term (hours to days)
- **Application**: Automatic at product level
- **Pricing**: Fixed flash price in pivot table
- **Stock**: Can have quantity limits
- **Best For**: Urgent sales, clearance, limited-time offers

**Example:**
```json
{
  "flash_deals": [{
    "id": 1,
    "title": "24-Hour Flash Sale",
    "pivot": {
      "flash_price": 79.99,
      "quantity_limit": 100,
      "quantity_sold": 25
    }
  }]
}
```

### 2. Promotions
- **Purpose**: Marketing campaigns and seasonal sales
- **Duration**: Medium to long-term (days to months)
- **Application**: Automatic at product/brand/category level
- **Pricing**: Percentage or fixed discount
- **Scope**: Can apply to products, brands, or categories
- **Best For**: Seasonal sales, brand promotions, category discounts

**Types:**
- `percentage` - Percentage off (e.g., 20% off)
- `fixed_amount` - Fixed amount off (e.g., $10 off)
- `flash_sale` - Time-limited percentage
- `combo_offer` - Bundle pricing
- `free_delivery` - Free shipping

**Example:**
```json
{
  "promotions": [{
    "id": 1,
    "name": "Summer Sale",
    "promotion_type": "percentage",
    "discount_value": 20.00,
    "applies_to": "specific_categories"
  }]
}
```

### 3. Coupons
- **Purpose**: Customer incentives and loyalty rewards
- **Duration**: Variable (can be long-term)
- **Application**: Manual at checkout with code
- **Pricing**: Percentage or fixed discount
- **Restrictions**: Usage limits, minimum order amounts
- **Best For**: Customer acquisition, retention, special offers

**Types:**
- `percentage` - Percentage off with optional max discount
- `fixed_amount` - Fixed amount off
- `free_shipping` - Free shipping

**Example:**
```json
{
  "coupons": [{
    "id": 1,
    "code": "SAVE20",
    "discount_type": "percentage",
    "discount_value": 20.00,
    "min_order_amount": 50.00,
    "max_discount_amount": 100.00
  }]
}
```

## Common Use Cases

### 1. Show All Discounted Products
Display any product with any type of discount:

```javascript
fetch('/api/catalog/products?has_discount=true&per_page=20')
```

**Use for:**
- "Sale" or "Deals" page
- Homepage discount section
- Email campaigns

### 2. Flash Deal Landing Page
Show products from a specific flash deal:

```javascript
fetch('/api/catalog/products?flash_deal_id=1')
```

**Use for:**
- Flash deal campaign pages
- Time-limited offer banners
- Countdown sale pages

### 3. Promotion Campaign Page
Show products in a specific promotion:

```javascript
fetch('/api/catalog/products?promotion_id=1')
```

**Use for:**
- Seasonal sale pages (Summer Sale, Black Friday)
- Brand promotion pages
- Category discount pages

### 4. Coupon Landing Page
Show products eligible for a coupon:

```javascript
fetch('/api/catalog/products?coupon_id=1')
```

**Use for:**
- Coupon campaign pages
- Email marketing with coupon codes
- Loyalty program pages

### 5. Category Discounts
Show discounted products in a category:

```javascript
fetch('/api/catalog/products?category_id=5&has_discount=true')
```

**Use for:**
- Category pages with discount filter
- "Electronics on Sale" pages

### 6. Brand Promotions
Show discounted products from a brand:

```javascript
fetch('/api/catalog/products?brand_id=3&has_promotion=true')
```

**Use for:**
- Brand partnership campaigns
- Vendor-specific sales

## Frontend Implementation

### Complete Discount Display Component

```javascript
class DiscountProductDisplay {
  async getProducts(filters) {
    const params = new URLSearchParams(filters);
    const response = await fetch(`/api/catalog/products?${params}`);
    const data = await response.json();
    
    if (data.success) {
      return data.data.data.map(product => this.enrichProduct(product));
    }
    return [];
  }
  
  enrichProduct(product) {
    const discounts = this.getDiscounts(product);
    const bestDiscount = this.getBestDiscount(discounts);
    
    return {
      ...product,
      discounts,
      bestDiscount,
      finalPrice: this.calculateFinalPrice(product, bestDiscount),
      savingsAmount: this.calculateSavings(product, bestDiscount),
      savingsPercent: this.calculateSavingsPercent(product, bestDiscount)
    };
  }
  
  getDiscounts(product) {
    const discounts = [];
    
    // Flash Deals
    if (product.flash_deals?.length > 0) {
      product.flash_deals.forEach(deal => {
        discounts.push({
          type: 'flash_deal',
          priority: 1,
          label: 'Flash Deal',
          price: deal.pivot.flash_price,
          endsAt: deal.ends_at,
          badge: 'FLASH DEAL'
        });
      });
    }
    
    // Promotions
    if (product.promotions?.length > 0) {
      product.promotions.forEach(promo => {
        const discountedPrice = this.calculatePromotionPrice(product.price, promo);
        discounts.push({
          type: 'promotion',
          priority: 2,
          label: promo.name,
          price: discountedPrice,
          discount: promo.discount_value,
          discountType: promo.promotion_type,
          endsAt: promo.ends_at,
          badge: `${promo.discount_value}% OFF`
        });
      });
    }
    
    // Coupons
    if (product.coupons?.length > 0) {
      product.coupons.forEach(coupon => {
        discounts.push({
          type: 'coupon',
          priority: 3,
          label: coupon.name,
          code: coupon.code,
          discount: coupon.discount_value,
          discountType: coupon.discount_type,
          minOrder: coupon.min_order_amount,
          maxDiscount: coupon.max_discount_amount,
          badge: `CODE: ${coupon.code}`
        });
      });
    }
    
    return discounts;
  }
  
  getBestDiscount(discounts) {
    if (discounts.length === 0) return null;
    
    // Sort by priority (flash deals first) and then by best price
    return discounts.sort((a, b) => {
      if (a.priority !== b.priority) {
        return a.priority - b.priority;
      }
      return (a.price || 0) - (b.price || 0);
    })[0];
  }
  
  calculatePromotionPrice(price, promotion) {
    let discount = 0;
    
    switch (promotion.promotion_type) {
      case 'percentage':
      case 'flash_sale':
        discount = price * (promotion.discount_value / 100);
        break;
      case 'fixed_amount':
        discount = promotion.discount_value;
        break;
    }
    
    if (promotion.max_discount_amount && discount > promotion.max_discount_amount) {
      discount = promotion.max_discount_amount;
    }
    
    return Math.max(0, price - discount);
  }
  
  calculateFinalPrice(product, discount) {
    if (!discount) return product.price;
    return discount.price || product.price;
  }
  
  calculateSavings(product, discount) {
    if (!discount) return 0;
    const finalPrice = this.calculateFinalPrice(product, discount);
    return product.price - finalPrice;
  }
  
  calculateSavingsPercent(product, discount) {
    const savings = this.calculateSavings(product, discount);
    return Math.round((savings / product.price) * 100);
  }
}

// Usage
const display = new DiscountProductDisplay();

// Get all discounted products
const products = await display.getProducts({ has_discount: true });

// Display product
products.forEach(product => {
  console.log(`${product.name}`);
  console.log(`Original: $${product.price}`);
  console.log(`Final: $${product.finalPrice}`);
  console.log(`Save: $${product.savingsAmount} (${product.savingsPercent}%)`);
  console.log(`Badge: ${product.bestDiscount.badge}`);
});
```

### React Component Example

```jsx
import React, { useState, useEffect } from 'react';

function DiscountProducts({ filters }) {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetchProducts();
  }, [filters]);
  
  const fetchProducts = async () => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`/api/catalog/products?${params}`);
    const data = await response.json();
    
    if (data.success) {
      setProducts(data.data.data);
    }
    setLoading(false);
  };
  
  const getDiscountBadge = (product) => {
    if (product.flash_deals?.length > 0) {
      return <span className="badge flash">FLASH DEAL</span>;
    }
    if (product.promotions?.length > 0) {
      const promo = product.promotions[0];
      return <span className="badge promo">{promo.discount_value}% OFF</span>;
    }
    if (product.coupons?.length > 0) {
      const coupon = product.coupons[0];
      return <span className="badge coupon">USE: {coupon.code}</span>;
    }
    return null;
  };
  
  const calculatePrice = (product) => {
    if (product.flash_deals?.length > 0) {
      return product.flash_deals[0].pivot.flash_price;
    }
    if (product.promotions?.length > 0) {
      const promo = product.promotions[0];
      if (promo.promotion_type === 'percentage') {
        return product.price * (1 - promo.discount_value / 100);
      }
      if (promo.promotion_type === 'fixed_amount') {
        return product.price - promo.discount_value;
      }
    }
    return product.price;
  };
  
  if (loading) return <div>Loading...</div>;
  
  return (
    <div className="discount-products">
      {products.map(product => (
        <div key={product.id} className="product-card">
          {getDiscountBadge(product)}
          <img src={product.images[0]?.url} alt={product.name} />
          <h3>{product.name}</h3>
          <div className="price">
            <span className="original">${product.price}</span>
            <span className="final">${calculatePrice(product).toFixed(2)}</span>
          </div>
        </div>
      ))}
    </div>
  );
}

// Usage examples
<DiscountProducts filters={{ has_discount: true }} />
<DiscountProducts filters={{ flash_deal_id: 1 }} />
<DiscountProducts filters={{ promotion_id: 1, category_id: 5 }} />
```

## Best Practices

### 1. Display Priority
When multiple discounts apply, show them in this order:
1. Flash Deals (most urgent)
2. Promotions (automatic)
3. Coupons (requires action)

### 2. Badge Design
- Flash Deals: Red/urgent colors with countdown
- Promotions: Green/blue with percentage
- Coupons: Yellow/orange with code

### 3. Price Display
```
[Original Price crossed out] [Final Price prominent] [Savings amount]
$99.99                       $79.99                  Save $20 (20%)
```

### 4. Combining Filters
```javascript
// Good: Specific and performant
?has_discount=true&category_id=5&in_stock=true

// Avoid: Too many discount filters at once
?has_flash_deal=true&has_promotion=true&has_coupon=true
// Use has_discount=true instead
```

### 5. Performance
- Use pagination (`per_page` parameter)
- Cache discount product lists
- Update when discounts expire

## Validation & Business Rules

### Automatic Validation
All discount filters automatically validate:
- Active status
- Time ranges (starts_at, ends_at)
- Product status (only active products)

### Discount Stacking
- Flash deals override other discounts
- Promotions can coexist with coupons
- Multiple promotions: highest priority wins
- Coupons applied at checkout separately

## Testing Examples

```bash
# Test flash deal products
curl "http://localhost:8000/api/catalog/products?flash_deal_id=1"

# Test promotion products
curl "http://localhost:8000/api/catalog/products?promotion_id=1"

# Test coupon products
curl "http://localhost:8000/api/catalog/products?coupon_id=1"

# Test all discounted products
curl "http://localhost:8000/api/catalog/products?has_discount=true"

# Test combined filters
curl "http://localhost:8000/api/catalog/products?has_discount=true&category_id=5&sort_by=price_low"
```

## Related Documentation
- `FLASH_DEAL_PRODUCTS_FEATURE.md` - Detailed flash deal documentation
- `PROMOTIONS_COUPONS_PRODUCTS_FEATURE.md` - Detailed promotions & coupons documentation
- `API_QUICK_REFERENCE.md` - Quick API reference
