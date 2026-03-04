# Complete Product Creation Workflow

## Overview
This guide shows the complete workflow for creating products with images and variations in your e-commerce system.

## Workflow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    SETUP PHASE (One-time)                    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │ Create Variations│
                    │  (Size, Color)   │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ Create Variation │
                    │     Options      │
                    │ (S, M, L, Red)   │
                    └────────┬─────────┘
                             │
┌────────────────────────────┴────────────────────────────────┐
│                    PRODUCT CREATION                          │
└──────────────────────────────────────────────────────────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │  Create Product  │
                    │  (Base Info)     │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │  Upload Images   │
                    │ (Multiple files) │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │ Add Variations   │
                    │ (SKU, Price, Qty)│
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │  Product Ready   │
                    │   for Sale!      │
                    └──────────────────┘
```

## Step-by-Step Guide

### Phase 1: One-Time Setup (Admin)

#### 1.1 Create Variation Types
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

#### 1.2 Create Variation Options
```http
POST /api/admin/variations/1/options
[
  {"value": "S", "label": "Small", "sort_order": 1},
  {"value": "M", "label": "Medium", "sort_order": 2},
  {"value": "L", "label": "Large", "sort_order": 3},
  {"value": "XL", "label": "Extra Large", "sort_order": 4}
]

POST /api/admin/variations/2/options
[
  {"value": "red", "label": "Red", "sort_order": 1},
  {"value": "blue", "label": "Blue", "sort_order": 2},
  {"value": "black", "label": "Black", "sort_order": 3}
]
```

### Phase 2: Create Product with Images

#### 2.1 Create Base Product with Images (Form Data)
```javascript
const formData = new FormData();

// Basic product info
formData.append('name', 'Premium Cotton T-Shirt');
formData.append('category_id', '5');
formData.append('brand_id', '3');
formData.append('sku', 'TSHIRT-PREMIUM-001');
formData.append('price', '29.99');
formData.append('quantity', '0'); // 0 because we'll use variations
formData.append('description', 'High-quality 100% cotton t-shirt');
formData.append('short_description', 'Premium cotton t-shirt');
formData.append('is_featured', 'true');
formData.append('is_trending', 'false');
formData.append('status', 'active');

// Upload multiple images
formData.append('images[0][file]', imageFile1);
formData.append('images[0][alt_text]', 'T-Shirt Front View');
formData.append('images[0][is_primary]', '1');
formData.append('images[0][sort_order]', '0');

formData.append('images[1][file]', imageFile2);
formData.append('images[1][alt_text]', 'T-Shirt Back View');
formData.append('images[1][is_primary]', '0');
formData.append('images[1][sort_order]', '1');

formData.append('images[2][file]', imageFile3);
formData.append('images[2][alt_text]', 'T-Shirt Detail');
formData.append('images[2][is_primary]', '0');
formData.append('images[2][sort_order]', '2');

// Send request
const response = await axios.post('/api/admin/products', formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});

const productId = response.data.data.id;
```

### Phase 3: Add Product Variations

#### 3.1 Add Variations for Each Combination
```javascript
// Small + Red
await axios.post(`/api/admin/products/${productId}/variations`, {
  sku: 'TSHIRT-PREMIUM-001-S-RED',
  price: 29.99,
  quantity: 50,
  is_default: true,
  variation_values: [1, 4] // Size S (ID: 1) + Color Red (ID: 4)
});

// Small + Blue
await axios.post(`/api/admin/products/${productId}/variations`, {
  sku: 'TSHIRT-PREMIUM-001-S-BLUE',
  price: 29.99,
  quantity: 45,
  variation_values: [1, 5] // Size S (ID: 1) + Color Blue (ID: 5)
});

// Medium + Red
await axios.post(`/api/admin/products/${productId}/variations`, {
  sku: 'TSHIRT-PREMIUM-001-M-RED',
  price: 29.99,
  quantity: 75,
  variation_values: [2, 4] // Size M (ID: 2) + Color Red (ID: 4)
});

// Medium + Blue
await axios.post(`/api/admin/products/${productId}/variations`, {
  sku: 'TSHIRT-PREMIUM-001-M-BLUE',
  price: 29.99,
  quantity: 80,
  variation_values: [2, 5] // Size M (ID: 2) + Color Blue (ID: 5)
});

// Large + Black (Premium price)
await axios.post(`/api/admin/products/${productId}/variations`, {
  sku: 'TSHIRT-PREMIUM-001-L-BLACK',
  price: 32.99, // Premium price for large black
  quantity: 30,
  variation_values: [3, 6] // Size L (ID: 3) + Color Black (ID: 6)
});
```

## Complete Example: React Component

```jsx
import { useState } from 'react';
import axios from 'axios';

function CreateProductForm() {
  const [formData, setFormData] = useState({
    name: '',
    category_id: '',
    brand_id: '',
    sku: '',
    price: '',
    description: '',
    short_description: '',
    is_featured: false,
    is_trending: false,
    status: 'draft'
  });
  
  const [images, setImages] = useState([]);
  const [variations, setVariations] = useState([]);
  const [createdProductId, setCreatedProductId] = useState(null);

  // Step 1: Create product with images
  const handleCreateProduct = async (e) => {
    e.preventDefault();
    
    const data = new FormData();
    
    // Add product fields
    Object.keys(formData).forEach(key => {
      data.append(key, formData[key]);
    });
    
    // Add images
    images.forEach((image, index) => {
      data.append(`images[${index}][file]`, image.file);
      data.append(`images[${index}][alt_text]`, image.alt_text);
      data.append(`images[${index}][is_primary]`, index === 0 ? '1' : '0');
      data.append(`images[${index}][sort_order]`, index);
    });
    
    try {
      const response = await axios.post('/api/admin/products', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      setCreatedProductId(response.data.data.id);
      alert('Product created! Now add variations.');
    } catch (error) {
      console.error('Error:', error.response.data);
    }
  };

  // Step 2: Add variations
  const handleAddVariations = async () => {
    if (!createdProductId) {
      alert('Create product first!');
      return;
    }
    
    try {
      for (const variation of variations) {
        await axios.post(
          `/api/admin/products/${createdProductId}/variations`,
          variation
        );
      }
      alert('All variations added successfully!');
    } catch (error) {
      console.error('Error:', error.response.data);
    }
  };

  const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    setImages(files.map(file => ({
      file,
      alt_text: file.name
    })));
  };

  const addVariation = () => {
    setVariations([...variations, {
      sku: '',
      price: formData.price,
      quantity: 0,
      is_default: variations.length === 0,
      variation_values: []
    }]);
  };

  return (
    <div className="create-product-form">
      <h2>Create Product</h2>
      
      {/* Step 1: Product Form */}
      <form onSubmit={handleCreateProduct}>
        <input
          type="text"
          placeholder="Product Name"
          value={formData.name}
          onChange={(e) => setFormData({...formData, name: e.target.value})}
          required
        />
        
        <input
          type="number"
          placeholder="Category ID"
          value={formData.category_id}
          onChange={(e) => setFormData({...formData, category_id: e.target.value})}
          required
        />
        
        <input
          type="number"
          placeholder="Price"
          value={formData.price}
          onChange={(e) => setFormData({...formData, price: e.target.value})}
          required
        />
        
        <textarea
          placeholder="Description"
          value={formData.description}
          onChange={(e) => setFormData({...formData, description: e.target.value})}
        />
        
        <label>
          <input
            type="checkbox"
            checked={formData.is_featured}
            onChange={(e) => setFormData({...formData, is_featured: e.target.checked})}
          />
          Featured
        </label>
        
        <input
          type="file"
          multiple
          accept="image/*"
          onChange={handleImageChange}
        />
        
        <button type="submit">Create Product</button>
      </form>

      {/* Step 2: Add Variations */}
      {createdProductId && (
        <div className="variations-section">
          <h3>Add Variations</h3>
          <button onClick={addVariation}>Add Variation</button>
          
          {variations.map((variation, index) => (
            <div key={index} className="variation-form">
              <input
                type="text"
                placeholder="SKU"
                value={variation.sku}
                onChange={(e) => {
                  const newVariations = [...variations];
                  newVariations[index].sku = e.target.value;
                  setVariations(newVariations);
                }}
              />
              
              <input
                type="number"
                placeholder="Price"
                value={variation.price}
                onChange={(e) => {
                  const newVariations = [...variations];
                  newVariations[index].price = e.target.value;
                  setVariations(newVariations);
                }}
              />
              
              <input
                type="number"
                placeholder="Quantity"
                value={variation.quantity}
                onChange={(e) => {
                  const newVariations = [...variations];
                  newVariations[index].quantity = e.target.value;
                  setVariations(newVariations);
                }}
              />
              
              {/* Add variation option selectors here */}
            </div>
          ))}
          
          <button onClick={handleAddVariations}>Save All Variations</button>
        </div>
      )}
    </div>
  );
}
```

## Database State After Creation

### Products Table
```
id | name                    | sku                  | price | quantity
1  | Premium Cotton T-Shirt  | TSHIRT-PREMIUM-001  | 29.99 | 0
```

### Product Images Table
```
id | product_id | image_path                  | alt_text           | is_primary | sort_order
1  | 1          | products/1234567890_0.jpg  | T-Shirt Front View | 1          | 0
2  | 1          | products/1234567890_1.jpg  | T-Shirt Back View  | 0          | 1
3  | 1          | products/1234567890_2.jpg  | T-Shirt Detail     | 0          | 2
```

### Product Variations Table
```
id | product_id | sku                        | price | quantity | is_default
1  | 1          | TSHIRT-PREMIUM-001-S-RED   | 29.99 | 50       | 1
2  | 1          | TSHIRT-PREMIUM-001-S-BLUE  | 29.99 | 45       | 0
3  | 1          | TSHIRT-PREMIUM-001-M-RED   | 29.99 | 75       | 0
4  | 1          | TSHIRT-PREMIUM-001-M-BLUE  | 29.99 | 80       | 0
5  | 1          | TSHIRT-PREMIUM-001-L-BLACK | 32.99 | 30       | 0
```

### Variation Values Table
```
id | product_variation_id | variation_option_id
1  | 1                    | 1  (Size: S)
2  | 1                    | 4  (Color: Red)
3  | 2                    | 1  (Size: S)
4  | 2                    | 5  (Color: Blue)
5  | 3                    | 2  (Size: M)
6  | 3                    | 4  (Color: Red)
...
```

## Key Points

1. **Product quantity = 0** when using variations (stock is in variations)
2. **Images belong to product**, not individual variations
3. **Each variation** has its own SKU, price, and quantity
4. **One default variation** for initial display
5. **Variation values** link to pre-defined options
6. **Inventory tracking** happens at variation level

## Summary

Your system handles:
- ✅ Multiple product images with file upload
- ✅ Flexible variation system (size, color, etc.)
- ✅ Individual pricing per variation
- ✅ Separate inventory per variation
- ✅ Complete inventory logging
- ✅ Professional e-commerce structure
