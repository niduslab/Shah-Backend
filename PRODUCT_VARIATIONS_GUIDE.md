# Product Variations System Guide

## Overview
Your e-commerce system has a professional, flexible product variation system that handles different attributes like size, color, material, etc., with individual pricing and inventory for each combination.

## Database Structure

### 1. Variations Table (Master Attributes)
Stores the types of variations (e.g., "Size", "Color", "Material")

```sql
variations
├── id
├── name (e.g., "Size", "Color", "Grip Size")
├── slug (unique, auto-generated)
├── description
├── is_active
└── timestamps
```

### 2. Variation Options Table (Attribute Values)
Stores the specific values for each variation type

```sql
variation_options
├── id
├── variation_id (FK to variations)
├── value (e.g., "42", "Red", "Large")
├── label (optional display label)
├── sort_order
├── is_active
└── timestamps
```

### 3. Product Variations Table (Product SKUs)
Stores each unique combination of a product with specific attributes

```sql
product_variations
├── id
├── product_id (FK to products)
├── sku (unique)
├── price (nullable, uses product price if null)
├── quantity (stock for this specific variation)
├── is_default (one default per product)
└── timestamps
```

### 4. Variation Values Table (Links)
Links product variations to their specific attribute values

```sql
variation_values
├── id
├── product_variation_id (FK to product_variations)
├── variation_option_id (FK to variation_options)
└── timestamps
```

## How It Works

### Example: T-Shirt with Size and Color

#### Step 1: Create Master Variations (One-time setup)
```http
POST /api/admin/variations
{
  "name": "Size",
  "description": "Clothing sizes"
}

POST /api/admin/variations
{
  "name": "Color",
  "description": "Product colors"
}
```

#### Step 2: Create Variation Options
```http
POST /api/admin/variations/1/options
{
  "value": "S",
  "label": "Small",
  "sort_order": 1
}

POST /api/admin/variations/1/options
{
  "value": "M",
  "label": "Medium",
  "sort_order": 2
}

POST /api/admin/variations/1/options
{
  "value": "L",
  "label": "Large",
  "sort_order": 3
}

POST /api/admin/variations/2/options
{
  "value": "red",
  "label": "Red",
  "sort_order": 1
}

POST /api/admin/variations/2/options
{
  "value": "blue",
  "label": "Blue",
  "sort_order": 2
}
```

#### Step 3: Create Base Product
```http
POST /api/admin/products
{
  "name": "Premium T-Shirt",
  "category_id": 5,
  "brand_id": 3,
  "sku": "TSHIRT-001",
  "price": 29.99,
  "quantity": 0,
  "description": "High-quality cotton t-shirt",
  "images": [
    {
      "path": "products/tshirt-main.jpg",
      "alt_text": "Premium T-Shirt",
      "is_primary": true
    }
  ]
}
```

#### Step 4: Add Product Variations
```http
POST /api/admin/products/1/variations
{
  "sku": "TSHIRT-001-S-RED",
  "price": 29.99,
  "quantity": 50,
  "is_default": true,
  "variation_values": [1, 4]  // Small (ID: 1) + Red (ID: 4)
}

POST /api/admin/products/1/variations
{
  "sku": "TSHIRT-001-M-RED",
  "price": 29.99,
  "quantity": 75,
  "variation_values": [2, 4]  // Medium (ID: 2) + Red (ID: 4)
}

POST /api/admin/products/1/variations
{
  "sku": "TSHIRT-001-L-BLUE",
  "price": 32.99,
  "quantity": 30,
  "variation_values": [3, 5]  // Large (ID: 3) + Blue (ID: 5)
}
```

## API Endpoints

### Product Variation Management

#### 1. Add Variation to Product
```http
POST /api/admin/products/{productId}/variations
Content-Type: application/json

{
  "sku": "PRODUCT-SKU-VAR1",
  "price": 99.99,
  "quantity": 50,
  "is_default": true,
  "variation_values": [1, 5, 8]
}
```

**Fields:**
- `sku`: Optional, auto-generated if not provided (format: `{product_sku}-V{number}`)
- `price`: Optional, uses product price if null
- `quantity`: Stock quantity for this variation
- `is_default`: Boolean, only one default per product
- `variation_values`: Array of variation_option IDs

#### 2. Update Product Variation
```http
PUT /api/admin/products/{productId}/variations/{variationId}
Content-Type: application/json

{
  "sku": "UPDATED-SKU",
  "price": 109.99,
  "quantity": 75,
  "is_default": false,
  "reason": "Stock adjustment"
}
```

#### 3. Delete Product Variation
```http
DELETE /api/admin/products/{productId}/variations/{variationId}
```

#### 4. Get Product with Variations
```http
GET /api/admin/products/{productId}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Premium T-Shirt",
    "sku": "TSHIRT-001",
    "price": "29.99",
    "quantity": 0,
    "variations": [
      {
        "id": 1,
        "product_id": 1,
        "sku": "TSHIRT-001-S-RED",
        "price": "29.99",
        "quantity": 50,
        "is_default": true,
        "name": "Small / Red",
        "stock_status": "in_stock",
        "variation_values": [
          {
            "id": 1,
            "product_variation_id": 1,
            "variation_option_id": 1,
            "variation_option": {
              "id": 1,
              "variation_id": 1,
              "value": "S",
              "label": "Small",
              "variation": {
                "id": 1,
                "name": "Size"
              }
            }
          },
          {
            "id": 2,
            "product_variation_id": 1,
            "variation_option_id": 4,
            "variation_option": {
              "id": 4,
              "variation_id": 2,
              "value": "red",
              "label": "Red",
              "variation": {
                "id": 2,
                "name": "Color"
              }
            }
          }
        ]
      }
    ]
  }
}
```

## Frontend Implementation Examples

### React Product Variation Selector

```jsx
import { useState, useEffect } from 'react';

function ProductVariationSelector({ product }) {
  const [selectedOptions, setSelectedOptions] = useState({});
  const [selectedVariation, setSelectedVariation] = useState(null);
  const [availableOptions, setAvailableOptions] = useState({});

  useEffect(() => {
    // Group variations by attribute type
    const grouped = {};
    
    product.variations.forEach(variation => {
      variation.variation_values.forEach(value => {
        const variationName = value.variation_option.variation.name;
        const optionId = value.variation_option.id;
        
        if (!grouped[variationName]) {
          grouped[variationName] = new Set();
        }
        grouped[variationName].add(optionId);
      });
    });
    
    setAvailableOptions(grouped);
  }, [product]);

  const handleOptionSelect = (variationType, optionId) => {
    const newSelected = {
      ...selectedOptions,
      [variationType]: optionId
    };
    setSelectedOptions(newSelected);
    
    // Find matching variation
    const selectedIds = Object.values(newSelected).sort();
    const matchingVariation = product.variations.find(variation => {
      const variationIds = variation.variation_values
        .map(v => v.variation_option_id)
        .sort();
      return JSON.stringify(variationIds) === JSON.stringify(selectedIds);
    });
    
    setSelectedVariation(matchingVariation);
  };

  return (
    <div className="variation-selector">
      {Object.entries(availableOptions).map(([variationType, options]) => (
        <div key={variationType} className="variation-group">
          <h4>{variationType}</h4>
          <div className="options">
            {Array.from(options).map(optionId => {
              const option = findOptionById(product, optionId);
              return (
                <button
                  key={optionId}
                  className={selectedOptions[variationType] === optionId ? 'selected' : ''}
                  onClick={() => handleOptionSelect(variationType, optionId)}
                >
                  {option.label || option.value}
                </button>
              );
            })}
          </div>
        </div>
      ))}
      
      {selectedVariation && (
        <div className="selected-variation">
          <p>Price: ${selectedVariation.price || product.price}</p>
          <p>Stock: {selectedVariation.quantity} available</p>
          <p>SKU: {selectedVariation.sku}</p>
        </div>
      )}
    </div>
  );
}

function findOptionById(product, optionId) {
  for (const variation of product.variations) {
    for (const value of variation.variation_values) {
      if (value.variation_option.id === optionId) {
        return value.variation_option;
      }
    }
  }
  return null;
}
```

### JavaScript Add to Cart with Variation

```javascript
async function addToCart(productId, variationId, quantity) {
  try {
    const response = await axios.post('/api/cart/add', {
      product_id: productId,
      product_variation_id: variationId,
      quantity: quantity
    });
    
    console.log('Added to cart:', response.data);
  } catch (error) {
    if (error.response.data.message === 'Insufficient stock') {
      alert('Sorry, not enough stock available');
    }
  }
}
```

## Business Logic

### Stock Management
- Each variation has its own stock quantity
- Base product `quantity` is typically 0 when using variations
- Total stock = sum of all variation quantities
- Stock is tracked per variation in `inventory_logs` table

### Pricing
- Each variation can have its own price
- If variation price is `null`, uses base product price
- Allows for premium pricing (e.g., XL size costs more)

### Default Variation
- One variation can be marked as default
- Used for initial display/selection
- Setting a new default automatically unsets others

### SKU Generation
- Auto-generated format: `{product_sku}-V{number}`
- Example: `TSHIRT-001-V1`, `TSHIRT-001-V2`
- Can be manually overridden

## Common Use Cases

### 1. Simple Size Variations (Same Price)
```json
{
  "variations": [
    {"sku": "SHIRT-S", "price": null, "quantity": 50, "variation_values": [1]},
    {"sku": "SHIRT-M", "price": null, "quantity": 75, "variation_values": [2]},
    {"sku": "SHIRT-L", "price": null, "quantity": 30, "variation_values": [3]}
  ]
}
```

### 2. Size + Color Combinations
```json
{
  "variations": [
    {"sku": "SHIRT-S-RED", "quantity": 20, "variation_values": [1, 10]},
    {"sku": "SHIRT-S-BLUE", "quantity": 25, "variation_values": [1, 11]},
    {"sku": "SHIRT-M-RED", "quantity": 30, "variation_values": [2, 10]},
    {"sku": "SHIRT-M-BLUE", "quantity": 35, "variation_values": [2, 11]}
  ]
}
```

### 3. Premium Variations (Different Prices)
```json
{
  "variations": [
    {"sku": "PHONE-64GB", "price": 699.99, "quantity": 100, "variation_values": [20]},
    {"sku": "PHONE-128GB", "price": 799.99, "quantity": 75, "variation_values": [21]},
    {"sku": "PHONE-256GB", "price": 899.99, "quantity": 50, "variation_values": [22]}
  ]
}
```

### 4. Multiple Attributes
```json
{
  "variations": [
    {
      "sku": "LAPTOP-I5-8GB-256GB",
      "price": 899.99,
      "quantity": 20,
      "variation_values": [30, 40, 50]  // Processor, RAM, Storage
    }
  ]
}
```

## Inventory Tracking

Every stock change is logged in `inventory_logs`:
```sql
inventory_logs
├── product_id
├── product_variation_id
├── quantity_before
├── quantity_change
├── quantity_after
├── reason (sale, restock, adjustment, return, etc.)
├── reference_type (Order, Return, etc.)
├── reference_id
├── notes
├── created_by (user_id)
└── timestamps
```

## Best Practices

1. **Create variations first** - Set up variation types and options before creating products
2. **Use meaningful SKUs** - Include variation attributes in SKU for easy identification
3. **Set one default** - Always have a default variation for initial display
4. **Track inventory** - Use the built-in inventory logging system
5. **Price strategically** - Use null prices for same-price variations, specific prices for premium options
6. **Limit combinations** - Don't create every possible combination, only what you actually stock
7. **Update stock regularly** - Keep variation quantities accurate
8. **Use labels** - Provide user-friendly labels for variation options

## Migration from Simple Products

If you have existing products without variations:

1. Keep the product as-is if no variations needed
2. Set product `quantity` to 0 when adding variations
3. Create variations with the actual stock
4. Variations inherit product price if not specified

## Testing

### Test Variation Creation
```bash
curl -X POST http://localhost/api/admin/products/1/variations \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "TEST-VAR-1",
    "price": 99.99,
    "quantity": 50,
    "is_default": true,
    "variation_values": [1, 5]
  }'
```

### Test Variation Update
```bash
curl -X PUT http://localhost/api/admin/products/1/variations/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 75,
    "reason": "Restock"
  }'
```

## Summary

Your variation system is:
- ✅ Flexible - Supports any number of attributes
- ✅ Scalable - Handles complex combinations
- ✅ Professional - Individual pricing and inventory
- ✅ Tracked - Complete inventory logging
- ✅ User-friendly - Clear API and data structure
