# Professional Shipping System Guide

## Overview

Your Laravel e-commerce platform now has a comprehensive, professional shipping system with:

- Multiple shipping methods (Shah Sports Team, Pathao Courier)
- Weight-based cost calculation
- Area/location-based pricing rules
- Shipping classes for product categorization
- Free shipping thresholds
- Automatic product weight/dimension handling

## System Architecture

### Database Structure

```
shipping_classes
├── id
├── name (e.g., "Heavy Equipment", "Standard Items")
├── slug
└── description

shipping_rates
├── id
├── name
├── shipping_class_id (nullable)
├── method (shah_sports_team | pathao_courier)
├── country
├── delivery_time
├── free_shipping_min_order
├── base_cost
└── is_active

weight_cost_rules
├── id
├── shipping_rate_id
├── state (nullable - for state-specific pricing)
├── city (nullable - for city-specific pricing)
├── shipping_calculation_method (per_unit | rules)
├── per_unit_cost (cost per kg)
└── default_rule_cost

weight_cost_rule_items
├── id
├── weight_cost_rule_id
├── weight (threshold in kg)
└── cost (shipping cost for this weight)
```

### Models

1. **ShippingClass** - Categorizes products (e.g., Heavy, Standard, Fragile)
2. **ShippingRate** - Base shipping method configuration
3. **WeightCostRule** - Location and calculation method rules
4. **WeightCostRuleItem** - Specific weight thresholds and costs
5. **Product** - Has `shipping_class_id`, `weight`, dimensions

## How It Works

### 1. Product Setup

Each product has:
```php
- shipping_class_id (optional)
- weight (decimal)
- weight_unit (kg, g, lb)
- length, width, height (for large item detection)
```

### 2. Shipping Calculation Flow

```
Cart Items → Enrich with Product Data → Calculate Total Weight
    ↓
Get Available Methods (based on weight/size)
    ↓
For Each Method:
    ↓
Get Applicable Shipping Rate (by shipping class)
    ↓
Check Free Shipping Threshold
    ↓
Find Weight Cost Rule (by location: city > state > default)
    ↓
Calculate Cost:
    - Per Unit: base_cost + (weight × per_unit_cost)
    - Rules: base_cost + cost from weight_cost_rule_items
    - Fallback: base_cost + (additional_kg × default_rate)
```

### 3. Location-Based Pricing

Priority order:
1. **City-specific** - Most specific (e.g., "Dhaka")
2. **State-specific** - Regional (e.g., "Dhaka Division")
3. **Default** - No location specified (nationwide)

## API Endpoints

### Get Shipping Methods

```http
POST /api/checkout/shipping-methods
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variation_id": 5,
      "quantity": 2
    }
  ],
  "address_id": 10,
  "subtotal": 5000
}
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service",
      "cost": 120.00,
      "delivery_time": "2-3 business days",
      "free_shipping_min_order": 3000.00,
      "is_free": false,
      "recommended": true
    },
    {
      "code": "shah_sports_team",
      "name": "Shah Sports Team Delivery",
      "description": "Our own delivery team for heavy items",
      "cost": 250.00,
      "delivery_time": "3-5 business days",
      "free_shipping_min_order": 5000.00,
      "is_free": false,
      "recommended": false
    }
  ]
}
```

### Checkout Preview

```http
POST /api/checkout/preview
Content-Type: application/json

{
  "items": [...],
  "shipping_address_id": 10,
  "shipping_method": "pathao_courier",
  "coupon_code": "SAVE10",
  "subtotal": 5000
}
```

## Configuration Examples

### Example 1: Simple Per-Unit Pricing

**Scenario:** Charge ৳15 per kg for Pathao Courier

```sql
-- Create shipping rate
INSERT INTO shipping_rates (name, method, base_cost, free_shipping_min_order, delivery_time, is_active)
VALUES ('Pathao Standard', 'pathao_courier', 60, 3000, '2-3 business days', 1);

-- Create weight cost rule (per unit)
INSERT INTO weight_cost_rules (shipping_rate_id, shipping_calculation_method, per_unit_cost)
VALUES (1, 'per_unit', 15);
```

**Calculation:**
- Product weight: 3.5 kg
- Cost = 60 (base) + (3.5 × 15) = ৳112.50

### Example 2: Tiered Weight Pricing

**Scenario:** Different rates for weight ranges

```sql
-- Create shipping rate
INSERT INTO shipping_rates (name, method, base_cost, is_active)
VALUES ('Shah Sports Heavy', 'shah_sports_team', 100, 1);

-- Create weight cost rule (rules-based)
INSERT INTO weight_cost_rules (shipping_rate_id, shipping_calculation_method, default_rule_cost)
VALUES (2, 'rules', 500);

-- Create weight tiers
INSERT INTO weight_cost_rule_items (weight_cost_rule_id, weight, cost) VALUES
(1, 5, 50),    -- Up to 5kg: ৳50
(1, 10, 100),  -- Up to 10kg: ৳100
(1, 20, 200),  -- Up to 20kg: ৳200
(1, 50, 400);  -- Up to 50kg: ৳400
```

**Calculation:**
- Product weight: 12 kg
- Finds first rule where weight >= 12 → 20kg tier
- Cost = 100 (base) + 200 (tier) = ৳300

### Example 3: Location-Based Pricing

**Scenario:** Different rates for Dhaka vs other areas

```sql
-- Dhaka city pricing
INSERT INTO weight_cost_rules (shipping_rate_id, city, shipping_calculation_method, per_unit_cost)
VALUES (1, 'Dhaka', 'per_unit', 10);

-- Other areas pricing
INSERT INTO weight_cost_rules (shipping_rate_id, shipping_calculation_method, per_unit_cost)
VALUES (1, 'per_unit', 20);
```

**Calculation:**
- Dhaka address: 60 + (weight × 10)
- Other areas: 60 + (weight × 20)

### Example 4: Shipping Classes

**Scenario:** Different rates for heavy equipment vs standard items

```sql
-- Create shipping classes
INSERT INTO shipping_classes (name, slug, description) VALUES
('standard', 'standard', 'Standard items'),
('heavy', 'heavy', 'Heavy equipment and large items');

-- Standard items rate
INSERT INTO shipping_rates (name, shipping_class_id, method, base_cost, is_active)
VALUES ('Pathao Standard', 1, 'pathao_courier', 60, 1);

-- Heavy items rate
INSERT INTO shipping_rates (name, shipping_class_id, method, base_cost, is_active)
VALUES ('Shah Sports Heavy', 2, 'shah_sports_team', 150, 1);

-- Assign products to shipping classes
UPDATE products SET shipping_class_id = 2 WHERE weight > 20;
```

## React Frontend Integration

### Fetch Shipping Methods

```javascript
import api from '../api/axios';

const getShippingMethods = async (cartItems, addressId, subtotal) => {
  try {
    const response = await api.post('/api/checkout/shipping-methods', {
      items: cartItems.map(item => ({
        product_id: item.product_id,
        variation_id: item.variation_id,
        quantity: item.quantity,
      })),
      address_id: addressId,
      subtotal: subtotal,
    });
    
    return response.data.data;
  } catch (error) {
    console.error('Error fetching shipping methods:', error);
    throw error;
  }
};
```

### Display Shipping Options

```javascript
const ShippingMethodSelector = ({ methods, selected, onSelect }) => {
  return (
    <div className="shipping-methods">
      {methods.map(method => (
        <div 
          key={method.code}
          className={`shipping-method ${selected === method.code ? 'selected' : ''}`}
          onClick={() => onSelect(method.code)}
        >
          <div className="method-header">
            <h4>{method.name}</h4>
            {method.recommended && <span className="badge">Recommended</span>}
          </div>
          <p className="description">{method.description}</p>
          <div className="method-footer">
            <span className="delivery-time">{method.delivery_time}</span>
            <span className="cost">
              {method.is_free ? (
                <span className="free">FREE</span>
              ) : (
                `৳${method.cost.toFixed(2)}`
              )}
            </span>
          </div>
          {!method.is_free && method.free_shipping_min_order > 0 && (
            <p className="free-shipping-hint">
              Add ৳{(method.free_shipping_min_order - subtotal).toFixed(2)} more for free shipping
            </p>
          )}
        </div>
      ))}
    </div>
  );
};
```

## Admin Management

### Create Shipping Rate

```javascript
const createShippingRate = async (data) => {
  const response = await api.post('/api/admin/shipping-rates', {
    name: 'Pathao Standard',
    shipping_class_id: null, // or specific class ID
    method: 'pathao_courier',
    country: 'Bangladesh',
    delivery_time: '2-3 business days',
    free_shipping_min_order: 3000,
    base_cost: 60,
    is_active: true,
  });
  return response.data;
};
```

### Create Weight Cost Rule

```javascript
const createWeightCostRule = async (shippingRateId, data) => {
  const response = await api.post('/api/admin/weight-cost-rules', {
    shipping_rate_id: shippingRateId,
    state: 'Dhaka Division', // optional
    city: 'Dhaka', // optional
    shipping_calculation_method: 'per_unit', // or 'rules'
    per_unit_cost: 15, // for per_unit method
    default_rule_cost: 500, // for rules method fallback
  });
  return response.data;
};
```

### Create Weight Tier

```javascript
const createWeightTier = async (weightCostRuleId, weight, cost) => {
  const response = await api.post('/api/admin/weight-cost-rule-items', {
    weight_cost_rule_id: weightCostRuleId,
    weight: weight, // in kg
    cost: cost,
  });
  return response.data;
};
```

## Best Practices

### 1. Product Configuration

- Always set product weight and weight_unit
- Assign shipping_class_id for special items
- Set dimensions for large items

### 2. Shipping Rate Setup

- Create default rates (no shipping class) first
- Add specific rates for special shipping classes
- Enable free shipping thresholds to encourage larger orders

### 3. Weight Cost Rules

- Start with simple per_unit rules
- Add location-specific rules only when needed
- Use rules-based method for complex tiered pricing

### 4. Testing

```php
// Test shipping calculation
$items = [
    ['product_id' => 1, 'variation_id' => null, 'quantity' => 2],
    ['product_id' => 5, 'variation_id' => 3, 'quantity' => 1],
];

$address = Address::find(10);
$subtotal = 5000;

$costs = app(ShippingService::class)->calculateShippingCost($items, $address, $subtotal);
dd($costs);
```

## Troubleshooting

### Issue: Shipping cost is 0

**Causes:**
- Free shipping threshold met
- No shipping rate found for method
- No weight cost rule configured

**Solution:**
- Check `free_shipping_min_order` vs `subtotal`
- Verify shipping rate exists and `is_active = true`
- Create weight cost rule or ensure fallback calculation works

### Issue: Wrong shipping cost

**Causes:**
- Product weight not set
- Wrong weight unit
- Location rule not matching

**Solution:**
- Verify product weight and weight_unit
- Check weight cost rule location filters
- Review calculation method (per_unit vs rules)

### Issue: Method not available

**Causes:**
- Shipping rate is inactive
- No rate for product's shipping class

**Solution:**
- Set `is_active = true` on shipping rate
- Create default rate (null shipping_class_id)

## Performance Optimization

### 1. Eager Loading

```php
$products = Product::with(['shippingClass', 'variations'])
    ->whereIn('id', $productIds)
    ->get();
```

### 2. Caching

```php
Cache::remember("shipping_rates_{$method}", 3600, function () use ($method) {
    return ShippingRate::where('method', $method)
        ->where('is_active', true)
        ->with(['weightCostRules.items'])
        ->get();
});
```

### 3. Database Indexing

```sql
CREATE INDEX idx_shipping_rates_method ON shipping_rates(method, is_active);
CREATE INDEX idx_weight_rules_location ON weight_cost_rules(shipping_rate_id, city, state);
CREATE INDEX idx_products_shipping ON products(shipping_class_id, weight);
```

## Future Enhancements

1. **Real-time Carrier APIs** - Integrate with Pathao API for live rates
2. **Shipping Zones** - Define geographic zones with different rates
3. **Dimensional Weight** - Calculate based on volume for large items
4. **Multi-package** - Split orders into multiple packages
5. **Shipping Insurance** - Optional insurance for high-value items
6. **Delivery Slots** - Allow customers to choose delivery time
7. **Pickup Points** - Support for pickup locations

Your shipping system is now production-ready and highly flexible!
