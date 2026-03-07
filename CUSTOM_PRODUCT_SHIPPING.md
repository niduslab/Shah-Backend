# Custom Product Shipping Documentation

## Overview

Your e-commerce platform now supports advanced per-product and per-variation custom shipping configurations. This allows you to override default shipping calculations for specific products with custom rules.

## Shipping Types

### Product-Level Shipping Types

Products can have one of four shipping types:

1. **default** - Use standard shipping calculation (weight-based, location-based)
2. **free** - Product ships for free
3. **fixed** - Fixed shipping cost regardless of quantity
4. **per_item** - Shipping cost multiplied by quantity

### Variation-Level Shipping Types

Product variations can override product shipping with:

1. **inherit** (default) - Use product's shipping configuration
2. **free** - Variation ships for free
3. **fixed** - Fixed shipping cost for this variation
4. **per_item** - Shipping cost per variation quantity

## Database Schema

### Products Table (New Fields)

```sql
shipping_type ENUM('default', 'free', 'fixed', 'per_item') DEFAULT 'default'
shipping_cost DECIMAL(10,2) NULL
requires_shipping BOOLEAN DEFAULT true
separate_shipping BOOLEAN DEFAULT false
shipping_notes TEXT NULL
```

### Product Variations Table (New Fields)

```sql
shipping_type ENUM('inherit', 'free', 'fixed', 'per_item') DEFAULT 'inherit'
shipping_cost DECIMAL(10,2) NULL
```

## Field Descriptions

### shipping_type
Determines how shipping is calculated for this product:
- `default`: Use standard shipping rates and calculations
- `free`: No shipping charge for this product
- `fixed`: Charge a fixed amount (from `shipping_cost`)
- `per_item`: Charge per quantity (from `shipping_cost`)

### shipping_cost
The custom shipping amount (required when `shipping_type` is `fixed` or `per_item`)

### requires_shipping
Whether this product needs to be shipped:
- `true`: Product requires shipping (default)
- `false`: Digital/virtual product (no shipping needed)

### separate_shipping
Whether this product must ship separately:
- `true`: Cannot combine with other items, ships alone
- `false`: Can combine with other items (default)

When `separate_shipping` is true and `shipping_type` is `fixed`, the fixed cost is multiplied by quantity.

### shipping_notes
Optional notes about shipping requirements (e.g., "Fragile - Handle with care", "Requires signature")

## API Usage

### Creating a Product with Custom Shipping

```http
POST /api/admin/products
```

**Example 1: Free Shipping Product**
```json
{
  "name": "Digital Download - Training Video",
  "category_id": 5,
  "price": 29.99,
  "shipping_type": "free",
  "requires_shipping": false
}
```

**Example 2: Fixed Shipping Cost**
```json
{
  "name": "Heavy Dumbbell Set",
  "category_id": 3,
  "price": 199.99,
  "weight": 25,
  "weight_unit": "kg",
  "shipping_type": "fixed",
  "shipping_cost": 50.00,
  "shipping_notes": "Requires special handling"
}
```

**Example 3: Per-Item Shipping**
```json
{
  "name": "Yoga Mat",
  "category_id": 2,
  "price": 39.99,
  "shipping_type": "per_item",
  "shipping_cost": 5.00
}
```

**Example 4: Separate Shipping**
```json
{
  "name": "Treadmill",
  "category_id": 1,
  "price": 1299.99,
  "shipping_type": "fixed",
  "shipping_cost": 150.00,
  "separate_shipping": true,
  "shipping_notes": "White glove delivery required"
}
```

### Creating Product with Variation-Specific Shipping

```json
{
  "name": "Resistance Bands",
  "category_id": 4,
  "price": 19.99,
  "shipping_type": "default",
  "variations": [
    {
      "sku": "RB-LIGHT",
      "price": 19.99,
      "quantity": 100,
      "shipping_type": "per_item",
      "shipping_cost": 3.00,
      "attributes": {
        "Resistance": "Light"
      }
    },
    {
      "sku": "RB-HEAVY",
      "price": 29.99,
      "quantity": 50,
      "shipping_type": "per_item",
      "shipping_cost": 5.00,
      "attributes": {
        "Resistance": "Heavy"
      }
    }
  ]
}
```

### Updating Product Shipping

```http
PUT /api/admin/products/{id}
```

```json
{
  "shipping_type": "fixed",
  "shipping_cost": 25.00,
  "separate_shipping": false
}
```

### Updating Variation Shipping

```http
PUT /api/admin/products/{productId}/variations/{variationId}
```

```json
{
  "shipping_type": "free",
  "shipping_cost": null
}
```

## Shipping Calculation Logic

### How Custom Shipping Works

When calculating shipping for a cart:

1. **Group Items by Shipping Type**
   - Items that don't require shipping (excluded from shipping)
   - Items with custom shipping (free, fixed, per_item)
   - Items with default shipping (use standard rates)

2. **Calculate Custom Shipping**
   - Free: $0
   - Fixed: `shipping_cost` (or `shipping_cost × quantity` if `separate_shipping`)
   - Per-item: `shipping_cost × quantity`

3. **Calculate Default Shipping**
   - Use weight-based calculation
   - Apply location-based rules
   - Check free shipping thresholds

4. **Combine Costs**
   - Total = Custom Shipping + Default Shipping

### Example Calculations

**Cart with Mixed Items:**
```
Item 1: Yoga Mat (per_item: $5) × 2 = $10
Item 2: Water Bottle (default shipping, 0.5kg) × 1
Item 3: Digital Guide (free shipping) × 1 = $0

Custom Shipping: $10
Default Shipping: $8 (for water bottle)
Total Shipping: $18
```

**Cart with Separate Shipping:**
```
Item 1: Treadmill (fixed: $150, separate: true) × 1 = $150
Item 2: Yoga Mat (per_item: $5) × 2 = $10

Total Shipping: $160
```

**Cart with All Free Shipping:**
```
Item 1: Digital Download (free) × 1 = $0
Item 2: Promotional T-Shirt (free) × 1 = $0

Total Shipping: $0
```

## Checkout API Response

When calling `POST /api/checkout/shipping-methods`, the response now includes custom shipping breakdown:

```json
{
  "success": true,
  "data": {
    "pathao_courier": {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service",
      "cost": 95.00,
      "base_shipping_cost": 80.00,
      "custom_shipping_cost": 15.00,
      "delivery_time": "1-2 business days",
      "free_shipping_min_order": 5000.00,
      "is_free": false
    }
  }
}
```

If all items have custom shipping (no default shipping items):

```json
{
  "success": true,
  "data": {
    "custom": {
      "code": "custom",
      "name": "Custom Shipping",
      "description": "Product-specific shipping rates",
      "cost": 25.00,
      "delivery_time": null,
      "free_shipping_min_order": 0,
      "is_free": false
    }
  }
}
```

## Model Helper Methods

### Product Model

```php
// Check if product requires shipping
$product->requiresShipping(); // bool

// Check if product has free shipping
$product->hasFreeShipping(); // bool

// Check if product has custom shipping
$product->hasCustomShipping(); // bool

// Get custom shipping cost
$product->getCustomShippingCost($quantity = 1); // float|null

// Check if ships separately
$product->shipsSeparately(); // bool
```

### ProductVariation Model

```php
// Get shipping cost (considers inheritance)
$variation->getCustomShippingCost($quantity = 1); // float|null

// Check if has free shipping
$variation->hasFreeShipping(); // bool

// Check if requires shipping
$variation->requiresShipping(); // bool
```

## Use Cases

### 1. Digital Products
```json
{
  "shipping_type": "free",
  "requires_shipping": false
}
```

### 2. Oversized Items
```json
{
  "shipping_type": "fixed",
  "shipping_cost": 200.00,
  "separate_shipping": true,
  "shipping_notes": "Freight shipping required"
}
```

### 3. Promotional Free Shipping
```json
{
  "shipping_type": "free",
  "requires_shipping": true
}
```

### 4. Flat Rate Per Item
```json
{
  "shipping_type": "per_item",
  "shipping_cost": 7.50
}
```

### 5. Mixed Shipping (Product + Variations)
```json
{
  "shipping_type": "default",
  "variations": [
    {
      "shipping_type": "free",
      "attributes": {"Size": "Small"}
    },
    {
      "shipping_type": "per_item",
      "shipping_cost": 10.00,
      "attributes": {"Size": "Large"}
    }
  ]
}
```

## Best Practices

1. **Digital Products**: Always set `requires_shipping: false` and `shipping_type: free`

2. **Heavy Items**: Use `fixed` shipping with appropriate cost based on actual carrier rates

3. **Separate Shipping**: Enable for items that can't be combined (oversized, fragile, hazardous)

4. **Per-Item Shipping**: Good for lightweight items where shipping scales linearly

5. **Variation Shipping**: Use when different sizes/colors have different shipping costs

6. **Shipping Notes**: Add handling instructions for warehouse/fulfillment team

## Migration

Run the migration to add custom shipping fields:

```bash
php artisan migrate
```

This will add the new fields to both `products` and `product_variations` tables.

## Validation Rules

### Product Shipping Fields
- `shipping_type`: optional, must be one of: default, free, fixed, per_item
- `shipping_cost`: optional, numeric, min 0 (required if shipping_type is fixed or per_item)
- `requires_shipping`: optional, boolean
- `separate_shipping`: optional, boolean
- `shipping_notes`: optional, string, max 500 characters

### Variation Shipping Fields
- `shipping_type`: optional, must be one of: inherit, free, fixed, per_item
- `shipping_cost`: optional, numeric, min 0 (required if shipping_type is fixed or per_item)

## Backward Compatibility

All existing products will default to:
- `shipping_type`: 'default'
- `requires_shipping`: true
- `separate_shipping`: false

This means existing products continue to use the standard shipping calculation without any changes needed.

## Advanced Scenarios

### Scenario 1: Free Shipping Threshold Still Applies

Even with custom shipping, if the order meets the free shipping threshold for standard items, those items ship free, but custom shipping items still charge their custom rates.

### Scenario 2: Multiple Separate Shipping Items

If cart has multiple items with `separate_shipping: true`, each is calculated independently and costs are summed.

### Scenario 3: Variation Overrides

When a variation has `shipping_type: inherit`, it uses the product's shipping configuration. Any other value overrides the product setting.

## Testing Examples

### Test 1: All Custom Shipping
```
Cart: 
- Digital Download (free) × 1
- Promotional Item (free) × 1

Expected: Total shipping = $0
```

### Test 2: Mixed Shipping
```
Cart:
- Yoga Mat (per_item: $5) × 2 = $10
- Dumbbell (default, 5kg) = ~$15

Expected: Total shipping = $25
```

### Test 3: Separate Shipping
```
Cart:
- Treadmill (fixed: $150, separate: true) × 2 = $300

Expected: Total shipping = $300
```

### Test 4: Free Shipping Threshold
```
Cart subtotal: $6000
- Standard Item (default, 2kg) = $0 (free shipping threshold met)
- Custom Item (fixed: $20) = $20

Expected: Total shipping = $20
```
