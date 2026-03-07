# Shipping API Documentation

## Overview

Your e-commerce platform handles shipping through a comprehensive flexible system that supports:
- Multiple shipping methods (Shah Sports Team, Pathao Courier)
- Weight-based calculations with location-based pricing
- Free shipping thresholds
- **Per-product custom shipping (NEW)**
- **Per-variation shipping overrides (NEW)**
- Shipping classes for product categorization
- Separate shipping for oversized items

## Shipping Methods

You support two shipping methods:

1. **Shah Sports Team** (`shah_sports_team`) - Your own delivery team for heavy and large items
2. **Pathao Courier** (`pathao_courier`) - Fast courier service for standard deliveries

## API Endpoints

### Customer-Facing Endpoints

#### 1. Get Available Shipping Methods
```
POST /api/checkout/shipping-methods
```

**Purpose**: Calculate shipping costs for all available methods based on cart items and delivery address.

**Request Body**:
```json
{
  "items": [
    {
      "product_id": 1,
      "variation_id": 5,
      "quantity": 2
    }
  ],
  "address_id": 10,
  "subtotal": 5000.00
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "shah_sports_team": {
      "code": "shah_sports_team",
      "name": "Shah Sports Team Delivery",
      "description": "Our own delivery team for heavy and large items",
      "cost": 150.00,
      "base_shipping_cost": 140.00,
      "custom_shipping_cost": 10.00,
      "delivery_time": "2-3 business days",
      "free_shipping_min_order": 10000.00,
      "is_free": false
    },
    "pathao_courier": {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service",
      "cost": 80.00,
      "base_shipping_cost": 70.00,
      "custom_shipping_cost": 10.00,
      "delivery_time": "1-2 business days",
      "free_shipping_min_order": 5000.00,
      "is_free": false
    }
  }
}
```

**Note**: If cart contains only custom shipping items (no default shipping), response will show only "custom" method:
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

**Logic**:
- Enriches items with product weight, dimensions, and shipping class
- Calculates total weight
- Determines available methods based on item characteristics
- Applies weight-based pricing rules
- Checks free shipping eligibility
- Recommends method based on weight/size

#### 2. Preview Order (includes shipping)
```
POST /api/checkout/preview
```

**Purpose**: Calculate order totals including shipping before final checkout.

**Request Body**:
```json
{
  "items": [...],
  "shipping_address_id": 10,
  "shipping_method": "pathao_courier",
  "coupon_code": "SAVE10"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "subtotal": 5000.00,
    "shipping_cost": 80.00,
    "coupon_discount": 500.00,
    "total": 4580.00,
    "deposit_amount": null,
    "payable_now": 4580.00
  }
}
```

#### 3. Process Checkout
```
POST /api/checkout/process
```

**Purpose**: Create order with selected shipping method.

**Request Body**:
```json
{
  "items": [...],
  "shipping_address_id": 10,
  "billing_address_id": 11,
  "shipping_method": "pathao_courier",
  "payment_method": "ssl_commerz",
  "coupon_code": "SAVE10",
  "notes": "Please call before delivery"
}
```

**Validation**:
- `shipping_method` must be one of: `shah_sports_team`, `pathao_courier`
- `shipping_address_id` must exist in addresses table

#### 4. Track Order
```
GET /api/orders/{orderNumber}/track
```

**Response**:
```json
{
  "success": true,
  "data": {
    "order_number": "ORD-2024-001",
    "status": "shipped",
    "shipping_method": "pathao_courier",
    "tracking_number": "PATHAO123456",
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-16T14:30:00Z"
  }
}
```

### Admin Endpoints

All admin endpoints require authentication and admin role.

#### 1. List Shipping Rates
```
GET /api/admin/shipping-rates
```

**Query Parameters**:
- `method` - Filter by shipping method (shah_sports_team, pathao_courier)
- `is_active` - Filter by active status (true/false)
- `per_page` - Items per page (default: 15)

**Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Standard Pathao",
        "method": "pathao_courier",
        "shipping_class_id": null,
        "zone": "Dhaka",
        "base_cost": 60.00,
        "per_kg_cost": 15.00,
        "min_weight": null,
        "max_weight": 20.00,
        "free_shipping_threshold": 5000.00,
        "delivery_time": "1-2 business days",
        "is_active": true,
        "shipping_class": null
      }
    ],
    "per_page": 15,
    "total": 5
  }
}
```

#### 2. Create Shipping Rate
```
POST /api/admin/shipping-rates
```

**Request Body**:
```json
{
  "name": "Express Delivery",
  "method": "pathao_courier",
  "shipping_class_id": 2,
  "zone": "Dhaka",
  "base_cost": 100.00,
  "per_kg_cost": 20.00,
  "min_weight": 0,
  "max_weight": 10.00,
  "free_shipping_threshold": 8000.00,
  "is_active": true
}
```

**Validation**:
- `name` - required, string, max 255 chars
- `method` - required, must be: shah_sports_team or pathao_courier
- `shipping_class_id` - optional, must exist in shipping_classes table
- `zone` - optional, string, max 100 chars
- `base_cost` - required, numeric, min 0
- `per_kg_cost` - optional, numeric, min 0
- `min_weight` - optional, numeric, min 0
- `max_weight` - optional, numeric, min 0
- `free_shipping_threshold` - optional, numeric, min 0
- `is_active` - optional, boolean

#### 3. Get Shipping Rate
```
GET /api/admin/shipping-rates/{id}
```

#### 4. Update Shipping Rate
```
PUT /api/admin/shipping-rates/{id}
```

**Request Body**: Same as create, all fields optional

#### 5. Delete Shipping Rate
```
DELETE /api/admin/shipping-rates/{id}
```

#### 6. List Shipping Classes
```
GET /api/admin/shipping-classes
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Heavy Equipment",
      "slug": "heavy-equipment",
      "description": "Large sports equipment requiring special handling",
      "products_count": 15
    }
  ]
}
```

#### 7. Create Shipping Class
```
POST /api/admin/shipping-classes
```

**Request Body**:
```json
{
  "name": "Fragile Items",
  "description": "Items requiring careful handling"
}
```

**Note**: Slug is auto-generated from name

#### 8. Update Shipping Class
```
PUT /api/admin/shipping-classes/{id}
```

#### 9. Delete Shipping Class
```
DELETE /api/admin/shipping-classes/{id}
```

**Note**: Cannot delete if products are assigned to this class

## Shipping Calculation Logic

### 1. Custom Product Shipping (NEW)

Products can override default shipping with custom configurations:

**Shipping Types:**
- `default`: Use standard weight/location-based calculation
- `free`: Product ships for free
- `fixed`: Fixed shipping cost regardless of quantity
- `per_item`: Shipping cost multiplied by quantity

**Additional Options:**
- `requires_shipping`: Whether product needs shipping (false for digital products)
- `separate_shipping`: Product must ship separately (can't combine with others)
- `shipping_notes`: Special handling instructions

**Variation Overrides:**
Variations can override product shipping with their own `shipping_type` and `shipping_cost`.

See [CUSTOM_PRODUCT_SHIPPING.md](CUSTOM_PRODUCT_SHIPPING.md) for detailed documentation.

### 2. Weight Calculation

The system calculates total weight by:
1. Getting product/variation weight for each item
2. Converting all weights to kg (supports g, lb, oz)
3. Multiplying by quantity
4. Summing all items

### 3. Method Recommendation

**Shah Sports Team** is recommended when:
- Total weight > 20 kg
- Any item dimension > 100 cm
- Item volume > 0.5 cubic meters

**Pathao Courier** is recommended for:
- Standard weight items (≤ 20 kg)
- Regular dimensions

### 4. Cost Calculation Priority

The system uses this priority for calculating shipping cost:

0. **Custom Shipping Check**: If product has custom shipping (free/fixed/per_item), use that
1. **Free Shipping Check**: If order subtotal ≥ `free_shipping_min_order`, cost = 0 (for default items only)
2. **Weight Cost Rules**: Location-specific rules (City > State > Default)
   - Per-unit calculation: `base_cost + (weight × per_unit_cost)`
   - Rules-based: `base_cost + rule_item_cost` (based on weight brackets)
3. **Fallback**: Simple calculation: `base_cost + (additional_kg × cost_per_kg)`
4. **Combine**: `custom_shipping_cost + default_shipping_cost`

### 5. Shipping Class Priority

When multiple items have different shipping classes:
1. Try to find rate matching any shipping class
2. Fall back to default rate (no shipping class)

### 6. Location-Based Pricing

Weight cost rules can be defined for:
- Specific city (highest priority)
- Specific state (medium priority)
- Default/nationwide (lowest priority)

## Database Schema

### shipping_rates
- `id`
- `name` - Display name
- `method` - shah_sports_team | pathao_courier
- `shipping_class_id` - Optional class restriction
- `zone` - Geographic zone
- `base_cost` - Base shipping fee
- `per_kg_cost` - Cost per additional kg (fallback)
- `min_weight` - Minimum weight limit
- `max_weight` - Maximum weight limit
- `free_shipping_threshold` - Order amount for free shipping
- `delivery_time` - Estimated delivery time
- `is_active` - Enable/disable rate

### shipping_classes
- `id`
- `name` - Class name
- `slug` - URL-friendly identifier
- `description` - Class description

### weight_cost_rules
- `id`
- `shipping_rate_id` - Parent rate
- `state` - Optional state filter
- `city` - Optional city filter
- `shipping_calculation_method` - per_unit | rules
- `per_unit_cost` - Cost per kg (if per_unit method)
- `default_rule_cost` - Fallback cost (if rules method)

### weight_cost_rule_items
- `id`
- `weight_cost_rule_id` - Parent rule
- `weight` - Weight threshold (kg)
- `cost` - Cost for this weight bracket

## Model Relationships

```
Product
  └─ belongsTo ShippingClass
  
ShippingClass
  ├─ hasMany Product
  └─ hasMany ShippingRate

ShippingRate
  ├─ belongsTo ShippingClass
  └─ hasMany WeightCostRule

WeightCostRule
  ├─ belongsTo ShippingRate
  └─ hasMany WeightCostRuleItem

WeightCostRuleItem
  └─ belongsTo WeightCostRule

Order
  └─ belongsTo Address (shippingAddress)
```

## Service Methods

### ShippingService

**Public Methods**:
- `calculateShippingCost(items, address, subtotal)` - Get costs for all methods
- `getAvailableMethods(items, address)` - List available methods
- `assignTrackingNumber(order, trackingNumber)` - Update order with tracking
- `getShippingRates(city, state)` - Get rates for location
- `checkFreeShipping(subtotal, method)` - Check if free shipping applies
- `recommendShippingMethod(items)` - Get recommended method
- `getShippingCostForMethod(method, items, address, subtotal)` - Get cost for specific method
- `getShippingQuote(method, items, address, subtotal)` - Get detailed quote with breakdown

## Constants

```php
ShippingService::METHOD_SHAH_SPORTS_TEAM = 'shah_sports_team'
ShippingService::METHOD_PATHAO_COURIER = 'pathao_courier'
HEAVY_WEIGHT_THRESHOLD = 20 // kg
LARGE_DIMENSION_THRESHOLD = 100 // cm
LARGE_VOLUME_THRESHOLD = 0.5 // cubic meters
```

## Example Workflows

### Customer Checkout Flow

1. Customer adds items to cart
2. Customer enters shipping address
3. Frontend calls `POST /api/checkout/shipping-methods` with items and address
4. System returns available methods with costs
5. Customer selects method
6. Frontend calls `POST /api/checkout/preview` to show final totals
7. Customer confirms and calls `POST /api/checkout/process`
8. Order created with shipping details

### Admin Setup Flow

1. Create shipping classes (e.g., "Heavy Equipment", "Standard Items")
2. Assign products to shipping classes
3. Create shipping rates for each method
4. Optionally create weight cost rules for specific locations
5. Set free shipping thresholds
6. Activate/deactivate rates as needed

## Notes

- All costs are stored and returned as decimal(10,2)
- Weights are normalized to kg internally
- Shipping method is validated against allowed values
- Free shipping is automatically applied when threshold is met
- Tracking numbers update order status to "shipped"
- Admin endpoints require authentication + admin middleware
