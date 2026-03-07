# Shipping System - Quick Start Guide

## 🚀 Getting Started

### Step 1: Run Migration
```bash
php artisan migrate
```

### Step 2: Set Up Shipping Rates (Admin)

**Create a basic rate:**
```http
POST /api/admin/shipping-rates
```
```json
{
  "name": "Standard Pathao",
  "method": "pathao_courier",
  "base_cost": 60.00,
  "free_shipping_threshold": 5000.00,
  "delivery_time": "1-2 business days",
  "is_active": true
}
```

### Step 3: Create Products with Custom Shipping

**Digital Product (No Shipping):**
```json
{
  "name": "Training Video",
  "price": 29.99,
  "shipping_type": "free",
  "requires_shipping": false
}
```

**Physical Product (Free Shipping):**
```json
{
  "name": "Promotional T-Shirt",
  "price": 19.99,
  "shipping_type": "free"
}
```

**Flat Rate Product:**
```json
{
  "name": "Yoga Mat",
  "price": 39.99,
  "shipping_type": "fixed",
  "shipping_cost": 10.00
}
```

**Per-Item Shipping:**
```json
{
  "name": "Water Bottle",
  "price": 15.99,
  "shipping_type": "per_item",
  "shipping_cost": 5.00
}
```

**Oversized Item:**
```json
{
  "name": "Treadmill",
  "price": 1299.99,
  "weight": 80,
  "shipping_type": "fixed",
  "shipping_cost": 150.00,
  "separate_shipping": true
}
```

## 📋 Shipping Type Cheat Sheet

| Type | When to Use | Cost Calculation | Example |
|------|-------------|------------------|---------|
| `default` | Standard items | Weight + location based | Regular products |
| `free` | Promotions, digital | $0 | Downloads, promos |
| `fixed` | Flat rate items | Fixed amount | Yoga mat: $10 |
| `per_item` | Scales with qty | Cost × quantity | Bottle: $5 × 3 = $15 |

## 🔧 Common Scenarios

### Scenario 1: Digital Product
```json
{
  "shipping_type": "free",
  "requires_shipping": false
}
```

### Scenario 2: Free Shipping Promo
```json
{
  "shipping_type": "free",
  "requires_shipping": true
}
```

### Scenario 3: Heavy Item
```json
{
  "weight": 25,
  "weight_unit": "kg",
  "shipping_type": "fixed",
  "shipping_cost": 50.00
}
```

### Scenario 4: Variable by Size
```json
{
  "variations": [
    {
      "attributes": {"Size": "S"},
      "shipping_type": "per_item",
      "shipping_cost": 5.00
    },
    {
      "attributes": {"Size": "XL"},
      "shipping_type": "per_item",
      "shipping_cost": 10.00
    }
  ]
}
```

## 🛒 Checkout Flow

1. Customer adds items to cart
2. Call `POST /api/checkout/shipping-methods` with cart items
3. System returns available methods with calculated costs
4. Customer selects method
5. Call `POST /api/checkout/process` to create order

## 📊 Example Cart Calculations

### Cart 1: Mixed Items
```
- Digital Download (free) × 1 = $0
- Yoga Mat (fixed: $10) × 1 = $10
- Water Bottle (per_item: $5) × 2 = $10
- Dumbbell (default, 5kg) = ~$15

Total Shipping: $35
```

### Cart 2: All Free
```
- Digital Guide (free) × 1 = $0
- Promo T-Shirt (free) × 1 = $0

Total Shipping: $0
```

### Cart 3: Separate Shipping
```
- Treadmill (fixed: $150, separate) × 1 = $150
- Yoga Mat (fixed: $10) × 1 = $10

Total Shipping: $160
```

## 🎯 Best Practices

1. **Digital Products**: Always set `requires_shipping: false`
2. **Heavy Items**: Use `fixed` with realistic carrier costs
3. **Promotions**: Use `free` shipping type
4. **Scalable Items**: Use `per_item` for lightweight items
5. **Oversized**: Enable `separate_shipping`
6. **Instructions**: Add `shipping_notes` for warehouse

## 🔍 Testing Checklist

- [ ] Create product with each shipping type
- [ ] Test cart with mixed shipping types
- [ ] Verify free shipping threshold works
- [ ] Test separate shipping items
- [ ] Check variation shipping overrides
- [ ] Test digital products (no shipping)
- [ ] Verify shipping cost calculations

## 📚 Documentation

- **SHIPPING_API_DOCUMENTATION.md** - Complete API reference
- **CUSTOM_PRODUCT_SHIPPING.md** - Detailed custom shipping guide
- **SHIPPING_FEATURES_SUMMARY.md** - Feature comparison

## 🆘 Quick Troubleshooting

**Problem**: Shipping not calculated
- Check `requires_shipping` is true
- Verify shipping rates exist and are active

**Problem**: Custom shipping not applied
- Check `shipping_type` is set correctly
- Verify `shipping_cost` is provided for fixed/per_item

**Problem**: Free shipping not working
- Check order subtotal meets threshold
- Verify threshold is set on shipping rate

**Problem**: Variation shipping not working
- Ensure variation `shipping_type` is not 'inherit'
- Check variation has `shipping_cost` set

## 🎉 You're Ready!

Your shipping system now supports:
✅ Multiple shipping methods
✅ Custom per-product shipping
✅ Variation-level overrides
✅ Digital products
✅ Free shipping
✅ Separate shipping
✅ Weight-based calculation
✅ Location-based pricing

Start creating products and testing the checkout flow!
