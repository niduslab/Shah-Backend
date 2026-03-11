# Shipping Methods API Guide

## Overview
Get available shipping methods with calculated costs for checkout page.

---

## API Endpoint

### Get Shipping Methods with Prices
```
POST /api/checkout/shipping-methods
```

**Authentication:** Not required (public endpoint)

---

## Request Format

```json
{
  "items": [
    {
      "product_id": 25,
      "variation_id": null,
      "quantity": 3
    },
    {
      "product_id": 24,
      "quantity": 1
    }
  ],
  "address_id": 1,
  "subtotal": 264.00
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `items` | array | Yes | Cart items |
| `items[].product_id` | integer | Yes | Product ID |
| `items[].variation_id` | integer | No | Product variation ID |
| `items[].quantity` | integer | Yes | Quantity |
| `address_id` | integer | No | Shipping address ID (for location-based rates) |
| `subtotal` | number | Yes | Cart subtotal for free shipping calculation |

---

## Response Format

```json
{
  "success": true,
  "data": [
    {
      "code": "standard",
      "name": "Standard Shipping",
      "description": "Free shipping on orders over 1000 BDT",
      "cost": 100.00,
      "base_shipping_cost": 100.00,
      "custom_shipping_cost": 0.00,
      "delivery_time": "3-5 business days",
      "free_shipping_min_order": 1000,
      "is_free": false
    },
    {
      "code": "shah_sports_team",
      "name": "Shah Sports Team Delivery",
      "description": "Our own delivery team for heavy and large items",
      "cost": 150.00,
      "base_shipping_cost": 150.00,
      "custom_shipping_cost": 0.00,
      "delivery_time": "1-2 business days",
      "free_shipping_min_order": 0,
      "is_free": false
    },
    {
      "code": "pathao_courier",
      "name": "Pathao Courier",
      "description": "Fast and reliable courier service for standard deliveries",
      "cost": 120.00,
      "base_shipping_cost": 120.00,
      "custom_shipping_cost": 0.00,
      "delivery_time": "1-3 business days",
      "free_shipping_min_order": 0,
      "is_free": false
    }
  ]
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `code` | string | Shipping method code (use this in checkout) |
| `name` | string | Display name |
| `description` | string | Method description |
| `cost` | number | Total shipping cost |
| `base_shipping_cost` | number | Base shipping cost |
| `custom_shipping_cost` | number | Additional product-specific shipping |
| `delivery_time` | string | Estimated delivery time |
| `free_shipping_min_order` | number | Minimum order for free shipping |
| `is_free` | boolean | Whether shipping is free |

---

## Shipping Methods

### 1. Standard Shipping
- **Code:** `standard`
- **Pricing:** Weight-based
  - Up to 1kg: 60 BDT
  - 1-5kg: 100 BDT
  - 5-10kg: 150 BDT
  - Above 10kg: 150 BDT + 15 BDT per additional kg
- **Free Shipping:** Orders ≥ 1000 BDT
- **Delivery:** 3-5 business days

### 2. Shah Sports Team
- **Code:** `shah_sports_team`
- **Pricing:** Database configured rates
- **Best For:** Heavy/large items
- **Delivery:** 1-2 business days

### 3. Pathao Courier
- **Code:** `pathao_courier`
- **Pricing:** Database configured rates
- **Best For:** Standard items
- **Delivery:** 1-3 business days

---

## Frontend Implementation

### React Example

```javascript
import { useState, useEffect } from 'react';

const ShippingMethodSelector = ({ cartItems, subtotal, selectedAddress }) => {
  const [shippingMethods, setShippingMethods] = useState([]);
  const [selectedMethod, setSelectedMethod] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchShippingMethods();
  }, [cartItems, subtotal, selectedAddress]);

  const fetchShippingMethods = async () => {
    setLoading(true);
    
    try {
      const response = await fetch('http://127.0.0.1:8000/api/checkout/shipping-methods', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          items: cartItems.map(item => ({
            product_id: item.product_id,
            variation_id: item.variation_id,
            quantity: item.quantity
          })),
          address_id: selectedAddress?.id,
          subtotal: subtotal
        })
      });

      const result = await response.json();
      
      if (result.success) {
        setShippingMethods(result.data);
        // Auto-select cheapest or first method
        if (result.data.length > 0) {
          const cheapest = result.data.reduce((min, method) => 
            method.cost < min.cost ? method : min
          );
          setSelectedMethod(cheapest.code);
        }
      }
    } catch (error) {
      console.error('Failed to fetch shipping methods:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div>Loading shipping options...</div>;
  }

  return (
    <div className="shipping-methods">
      <h3>Select Shipping Method</h3>
      
      {shippingMethods.map(method => (
        <div
          key={method.code}
          className={`shipping-method ${selectedMethod === method.code ? 'selected' : ''}`}
          onClick={() => setSelectedMethod(method.code)}
        >
          <div className="method-info">
            <div className="method-header">
              <h4>{method.name}</h4>
              <span className="method-cost">
                {method.is_free ? 'FREE' : `৳${method.cost}`}
              </span>
            </div>
            <p className="method-description">{method.description}</p>
            <p className="method-delivery">{method.delivery_time}</p>
            
            {method.free_shipping_min_order > 0 && !method.is_free && (
              <p className="free-shipping-notice">
                Add ৳{(method.free_shipping_min_order - subtotal).toFixed(2)} more for free shipping
              </p>
            )}
          </div>
          
          {selectedMethod === method.code && (
            <div className="checkmark">✓</div>
          )}
        </div>
      ))}
    </div>
  );
};

export default ShippingMethodSelector;
```

### Vue.js Example

```vue
<template>
  <div class="shipping-methods">
    <h3>Select Shipping Method</h3>
    
    <div v-if="loading">Loading shipping options...</div>
    
    <div
      v-for="method in shippingMethods"
      :key="method.code"
      :class="['shipping-method', { selected: selectedMethod === method.code }]"
      @click="selectedMethod = method.code"
    >
      <div class="method-info">
        <div class="method-header">
          <h4>{{ method.name }}</h4>
          <span class="method-cost">
            {{ method.is_free ? 'FREE' : `৳${method.cost}` }}
          </span>
        </div>
        <p class="method-description">{{ method.description }}</p>
        <p class="method-delivery">{{ method.delivery_time }}</p>
        
        <p v-if="method.free_shipping_min_order > 0 && !method.is_free" class="free-shipping-notice">
          Add ৳{{ (method.free_shipping_min_order - subtotal).toFixed(2) }} more for free shipping
        </p>
      </div>
      
      <div v-if="selectedMethod === method.code" class="checkmark">✓</div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    cartItems: Array,
    subtotal: Number,
    selectedAddress: Object
  },
  data() {
    return {
      shippingMethods: [],
      selectedMethod: null,
      loading: false
    };
  },
  watch: {
    cartItems: 'fetchShippingMethods',
    subtotal: 'fetchShippingMethods',
    selectedAddress: 'fetchShippingMethods'
  },
  mounted() {
    this.fetchShippingMethods();
  },
  methods: {
    async fetchShippingMethods() {
      this.loading = true;
      
      try {
        const response = await fetch('http://127.0.0.1:8000/api/checkout/shipping-methods', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            items: this.cartItems.map(item => ({
              product_id: item.product_id,
              variation_id: item.variation_id,
              quantity: item.quantity
            })),
            address_id: this.selectedAddress?.id,
            subtotal: this.subtotal
          })
        });

        const result = await response.json();
        
        if (result.success) {
          this.shippingMethods = result.data;
          // Auto-select cheapest
          if (result.data.length > 0) {
            const cheapest = result.data.reduce((min, method) => 
              method.cost < min.cost ? method : min
            );
            this.selectedMethod = cheapest.code;
          }
        }
      } catch (error) {
        console.error('Failed to fetch shipping methods:', error);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
```

---

## CSS Styling

```css
.shipping-methods {
  margin: 20px 0;
}

.shipping-method {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.shipping-method:hover {
  border-color: #4CAF50;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.shipping-method.selected {
  border-color: #4CAF50;
  background-color: #f1f8f4;
}

.method-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.method-header h4 {
  margin: 0;
  font-size: 16px;
}

.method-cost {
  font-size: 18px;
  font-weight: bold;
  color: #4CAF50;
}

.method-description {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.method-delivery {
  margin: 5px 0;
  font-size: 13px;
  color: #999;
}

.free-shipping-notice {
  margin: 10px 0 0 0;
  padding: 8px;
  background: #fff3cd;
  border-radius: 4px;
  font-size: 13px;
  color: #856404;
}

.checkmark {
  width: 30px;
  height: 30px;
  background: #4CAF50;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: bold;
}
```

---

## Testing

### Test Request

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/shipping-methods \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 25, "quantity": 3},
      {"product_id": 24, "quantity": 1}
    ],
    "subtotal": 264.00
  }'
```

### Test with Address

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/shipping-methods \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 25, "quantity": 3}
    ],
    "address_id": 1,
    "subtotal": 99.00
  }'
```

### Test Free Shipping

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/shipping-methods \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 25, "quantity": 10}
    ],
    "subtotal": 1500.00
  }'
```

Expected: Standard shipping should show `"is_free": true`

---

## Integration with Checkout

### Step 1: Fetch Shipping Methods
Call the API when user reaches checkout page or when cart changes.

### Step 2: Display Options
Show all available methods with prices and delivery times.

### Step 3: User Selection
Let user select their preferred method.

### Step 4: Submit with Checkout
Include selected method code in checkout request:

```json
{
  "items": [...],
  "shipping_method": "standard",  // Selected method code
  "payment_method": "cod",
  ...
}
```

---

## Important Notes

1. **Dynamic Pricing:** Shipping costs are calculated based on:
   - Product weights
   - Cart subtotal
   - Delivery address (if provided)
   - Custom product shipping rules

2. **Free Shipping:** Standard shipping is free for orders ≥ 1000 BDT

3. **Real-time Updates:** Fetch shipping methods whenever:
   - Cart items change
   - Quantities change
   - Address changes
   - Subtotal changes

4. **No Authentication:** This endpoint is public for guest checkout

5. **Method Availability:** Methods are shown based on:
   - Database configuration (shah_sports_team, pathao_courier)
   - Always available (standard)

---

## Summary

✅ **Endpoint:** `POST /api/checkout/shipping-methods`  
✅ **Returns:** All available shipping methods with calculated costs  
✅ **Dynamic:** Prices calculated based on cart and address  
✅ **Free Shipping:** Automatic for orders ≥ 1000 BDT  
✅ **Guest Friendly:** No authentication required  

Use this API to display shipping options with prices on your checkout page!
