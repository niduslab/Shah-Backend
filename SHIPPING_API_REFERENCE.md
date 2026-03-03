# Shipping API Quick Reference

## Customer Endpoints

### 1. Get Available Shipping Methods

Calculate shipping costs for cart items.

```http
POST /api/checkout/shipping-methods
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
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

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service for standard deliveries",
      "cost": 120.00,
      "delivery_time": "2-3 business days",
      "free_shipping_min_order": 3000.00,
      "is_free": false,
      "recommended": true
    }
  ]
}
```

### 2. Checkout Preview

Preview order totals including shipping.

```http
POST /api/checkout/preview
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "variation_id": null,
      "quantity": 2,
      "price": 1500.00
    }
  ],
  "shipping_address_id": 10,
  "shipping_method": "pathao_courier",
  "coupon_code": "SAVE10",
  "is_preorder": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 3000.00,
    "shipping_cost": 120.00,
    "coupon_discount": 300.00,
    "total": 2820.00,
    "is_preorder": false,
    "deposit_amount": null,
    "remaining_amount": null,
    "payable_now": 2820.00
  }
}
```

### 3. Process Checkout

Create order with selected shipping method.

```http
POST /api/checkout/process
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "items": [...],
  "shipping_address_id": 10,
  "billing_address_id": 10,
  "shipping_method": "pathao_courier",
  "payment_method": "ssl_commerz",
  "coupon_code": "SAVE10",
  "notes": "Please call before delivery"
}
```

## Admin Endpoints

### 1. List Shipping Rates

```http
GET /api/admin/shipping-rates
Authorization: Bearer {token}
```

### 2. Create Shipping Rate

```http
POST /api/admin/shipping-rates
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Pathao Standard",
  "shipping_class_id": null,
  "method": "pathao_courier",
  "country": "Bangladesh",
  "delivery_time": "2-3 business days",
  "free_shipping_min_order": 3000.00,
  "base_cost": 60.00,
  "is_active": true
}
```

### 3. Update Shipping Rate

```http
PUT /api/admin/shipping-rates/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

### 4. Delete Shipping Rate

```http
DELETE /api/admin/shipping-rates/{id}
Authorization: Bearer {token}
```

### 5. List Shipping Classes

```http
GET /api/admin/shipping-classes
Authorization: Bearer {token}
```

### 6. Create Shipping Class

```http
POST /api/admin/shipping-classes
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Heavy Equipment",
  "description": "Large and heavy sports equipment"
}
```

## Shipping Service Methods (Backend)

### Calculate Shipping Cost

```php
use App\Services\ShippingService;

$shippingService = app(ShippingService::class);

$items = [
    ['product_id' => 1, 'variation_id' => null, 'quantity' => 2],
];

$address = Address::find(10);
$subtotal = 5000;

$costs = $shippingService->calculateShippingCost($items, $address, $subtotal);
// Returns array of available methods with costs
```

### Get Shipping Cost for Specific Method

```php
$cost = $shippingService->getShippingCostForMethod(
    'pathao_courier',
    $items,
    $address,
    $subtotal
);
// Returns float
```

### Get Detailed Shipping Quote

```php
$quote = $shippingService->getShippingQuote(
    'pathao_courier',
    $items,
    $address,
    $subtotal
);

// Returns:
// [
//     'success' => true,
//     'method' => 'pathao_courier',
//     'name' => 'Pathao Courier',
//     'base_cost' => 60.00,
//     'total_cost' => 120.00,
//     'total_weight' => 5.5,
//     'delivery_time' => '2-3 business days',
//     'is_free_shipping' => false,
//     'free_shipping_threshold' => 3000.00,
//     'amount_to_free_shipping' => 0,
// ]
```

### Get Available Methods

```php
$methods = $shippingService->getAvailableMethods($items, $address);

// Returns:
// [
//     [
//         'code' => 'pathao_courier',
//         'name' => 'Pathao Courier',
//         'description' => '...',
//         'recommended' => true,
//     ],
//     ...
// ]
```

### Recommend Shipping Method

```php
$recommendedMethod = $shippingService->recommendShippingMethod($items);
// Returns: 'pathao_courier' or 'shah_sports_team'
```

### Check Free Shipping

```php
$isFree = $shippingService->checkFreeShipping($subtotal, 'pathao_courier');
// Returns: boolean
```

## Shipping Methods

### Available Methods

1. **shah_sports_team** - Shah Sports Team Delivery
   - For heavy and large items
   - Own delivery team
   - Recommended for items > 20kg or large dimensions

2. **pathao_courier** - Pathao Courier
   - Standard courier service
   - Fast delivery
   - Recommended for regular items

## Weight Units

Supported weight units (automatically converted to kg):
- `kg` - Kilograms (default)
- `g` - Grams
- `lb` - Pounds
- `oz` - Ounces

## Calculation Methods

### Per Unit Method

Cost = base_cost + (total_weight × per_unit_cost)

Example:
- Base cost: ৳60
- Per unit cost: ৳15/kg
- Weight: 3.5 kg
- Total: ৳60 + (3.5 × ৳15) = ৳112.50

### Rules Method

Cost = base_cost + cost_from_weight_tier

Example:
- Base cost: ৳100
- Weight: 12 kg
- Tiers:
  - 0-5kg: ৳50
  - 5-10kg: ৳100
  - 10-20kg: ৳200
  - 20-50kg: ৳400
- Total: ৳100 + ৳200 = ৳300

## Location Priority

When multiple weight cost rules exist:

1. **City-specific** (highest priority)
   - Example: city = "Dhaka"
   
2. **State-specific**
   - Example: state = "Dhaka Division", city = null
   
3. **Default** (lowest priority)
   - Example: state = null, city = null

## Free Shipping

Free shipping applies when:
```
subtotal >= free_shipping_min_order
```

Set `free_shipping_min_order = 0` to disable free shipping.

## Error Responses

### Invalid Items

```json
{
  "success": false,
  "message": "The items field is required.",
  "errors": {
    "items": ["The items field is required."]
  }
}
```

### Shipping Method Not Available

```json
{
  "success": false,
  "message": "Shipping method not available"
}
```

### Product Not Found

```json
{
  "success": false,
  "message": "The selected items.0.product_id is invalid.",
  "errors": {
    "items.0.product_id": ["The selected items.0.product_id is invalid."]
  }
}
```

## Testing Examples

### cURL: Get Shipping Methods

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/shipping-methods \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2}
    ],
    "address_id": 10,
    "subtotal": 5000
  }'
```

### JavaScript: Get Shipping Methods

```javascript
const getShippingMethods = async (items, addressId, subtotal) => {
  const response = await fetch('http://127.0.0.1:8000/api/checkout/shipping-methods', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    credentials: 'include',
    body: JSON.stringify({
      items,
      address_id: addressId,
      subtotal,
    }),
  });
  
  return await response.json();
};
```

### React Hook: useShipping

```javascript
import { useState, useEffect } from 'react';
import api from '../api/axios';

export const useShipping = (cartItems, addressId, subtotal) => {
  const [methods, setMethods] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!cartItems.length || !addressId) return;

    const fetchMethods = async () => {
      setLoading(true);
      try {
        const response = await api.post('/api/checkout/shipping-methods', {
          items: cartItems.map(item => ({
            product_id: item.product_id,
            variation_id: item.variation_id,
            quantity: item.quantity,
          })),
          address_id: addressId,
          subtotal,
        });
        setMethods(response.data.data);
      } catch (err) {
        setError(err.response?.data?.message || 'Failed to fetch shipping methods');
      } finally {
        setLoading(false);
      }
    };

    fetchMethods();
  }, [cartItems, addressId, subtotal]);

  return { methods, loading, error };
};
```

## Database Seeding

### Seed Basic Shipping Configuration

```php
// database/seeders/ShippingSeeder.php

use App\Models\ShippingClass;
use App\Models\ShippingRate;
use App\Models\WeightCostRule;
use App\Models\WeightCostRuleItem;

// Create shipping classes
$standard = ShippingClass::create([
    'name' => 'Standard Items',
    'slug' => 'standard',
    'description' => 'Regular sized items',
]);

$heavy = ShippingClass::create([
    'name' => 'Heavy Equipment',
    'slug' => 'heavy',
    'description' => 'Large and heavy items',
]);

// Create Pathao rate
$pathaoRate = ShippingRate::create([
    'name' => 'Pathao Standard',
    'method' => 'pathao_courier',
    'base_cost' => 60,
    'free_shipping_min_order' => 3000,
    'delivery_time' => '2-3 business days',
    'is_active' => true,
]);

// Create weight rule for Pathao
$pathaoRule = WeightCostRule::create([
    'shipping_rate_id' => $pathaoRate->id,
    'shipping_calculation_method' => 'per_unit',
    'per_unit_cost' => 15,
]);

// Create Shah Sports rate
$shahRate = ShippingRate::create([
    'name' => 'Shah Sports Team',
    'shipping_class_id' => $heavy->id,
    'method' => 'shah_sports_team',
    'base_cost' => 150,
    'free_shipping_min_order' => 5000,
    'delivery_time' => '3-5 business days',
    'is_active' => true,
]);

// Create tiered weight rule for Shah Sports
$shahRule = WeightCostRule::create([
    'shipping_rate_id' => $shahRate->id,
    'shipping_calculation_method' => 'rules',
    'default_rule_cost' => 500,
]);

WeightCostRuleItem::insert([
    ['weight_cost_rule_id' => $shahRule->id, 'weight' => 10, 'cost' => 100],
    ['weight_cost_rule_id' => $shahRule->id, 'weight' => 20, 'cost' => 200],
    ['weight_cost_rule_id' => $shahRule->id, 'weight' => 50, 'cost' => 400],
]);
```

Run seeder:
```bash
php artisan db:seed --class=ShippingSeeder
```
