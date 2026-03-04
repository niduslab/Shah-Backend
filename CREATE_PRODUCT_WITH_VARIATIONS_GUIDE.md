# Create Product with Variations - Complete Guide

## Overview
You can now create products with images AND variations in a single API call, then update them later as needed.

## API Structure

### Create Product with Everything
```http
POST /api/admin/products
Content-Type: multipart/form-data
```

### Update Product with Everything
```http
PUT /api/admin/products/{id}
Content-Type: multipart/form-data
```

## Complete Example: Create T-Shirt with Variations

### JavaScript/FormData Example

```javascript
const formData = new FormData();

// ===== PRODUCT BASIC INFO =====
formData.append('name', 'Premium Cotton T-Shirt');
formData.append('category_id', '5');
formData.append('brand_id', '3');
formData.append('sku', 'TSHIRT-PREMIUM-001');
formData.append('price', '29.99'); // Base price
formData.append('quantity', '0'); // 0 when using variations
formData.append('description', 'High-quality 100% cotton t-shirt');
formData.append('short_description', 'Premium cotton t-shirt');
formData.append('is_featured', 'true');
formData.append('is_trending', 'false');
formData.append('status', 'active');

// ===== PRODUCT IMAGES =====
formData.append('images[0][file]', imageFile1); // File object
formData.append('images[0][alt_text]', 'T-Shirt Front View');
formData.append('images[0][is_primary]', '1');
formData.append('images[0][sort_order]', '0');

formData.append('images[1][file]', imageFile2);
formData.append('images[1][alt_text]', 'T-Shirt Back View');
formData.append('images[1][is_primary]', '0');
formData.append('images[1][sort_order]', '1');

// ===== PRODUCT VARIATIONS =====
// Variation 1: Small + Red
formData.append('variations[0][sku]', 'TSHIRT-PREMIUM-001-S-RED');
formData.append('variations[0][price]', '29.99');
formData.append('variations[0][quantity]', '50');
formData.append('variations[0][is_default]', '1');
formData.append('variations[0][variation_values][0]', '1'); // Size: Small (option ID: 1)
formData.append('variations[0][variation_values][1]', '4'); // Color: Red (option ID: 4)

// Variation 2: Small + Blue
formData.append('variations[1][sku]', 'TSHIRT-PREMIUM-001-S-BLUE');
formData.append('variations[1][price]', '29.99');
formData.append('variations[1][quantity]', '45');
formData.append('variations[1][is_default]', '0');
formData.append('variations[1][variation_values][0]', '1'); // Size: Small
formData.append('variations[1][variation_values][1]', '5'); // Color: Blue (option ID: 5)

// Variation 3: Medium + Red
formData.append('variations[2][sku]', 'TSHIRT-PREMIUM-001-M-RED');
formData.append('variations[2][price]', '29.99');
formData.append('variations[2][quantity]', '75');
formData.append('variations[2][is_default]', '0');
formData.append('variations[2][variation_values][0]', '2'); // Size: Medium (option ID: 2)
formData.append('variations[2][variation_values][1]', '4'); // Color: Red

// Variation 4: Large + Black (Premium price)
formData.append('variations[3][sku]', 'TSHIRT-PREMIUM-001-L-BLACK');
formData.append('variations[3][price]', '32.99'); // Premium price
formData.append('variations[3][quantity]', '30');
formData.append('variations[3][is_default]', '0');
formData.append('variations[3][variation_values][0]', '3'); // Size: Large (option ID: 3)
formData.append('variations[3][variation_values][1]', '6'); // Color: Black (option ID: 6)

// Send request
const response = await axios.post('/api/admin/products', formData, {
  headers: {
    'Content-Type': 'multipart/form-data',
    'Authorization': `Bearer ${token}`
  }
});

console.log('Product created:', response.data);
```

### JSON Example (Pre-uploaded Images)

```javascript
const response = await axios.post('/api/admin/products', {
  // Product info
  name: 'Premium Cotton T-Shirt',
  category_id: 5,
  brand_id: 3,
  sku: 'TSHIRT-PREMIUM-001',
  price: 29.99,
  quantity: 0,
  description: 'High-quality 100% cotton t-shirt',
  short_description: 'Premium cotton t-shirt',
  is_featured: true,
  is_trending: false,
  status: 'active',
  
  // Images (already uploaded)
  images: [
    {
      path: 'products/tshirt-front.jpg',
      alt_text: 'T-Shirt Front View',
      is_primary: true,
      sort_order: 0
    },
    {
      path: 'products/tshirt-back.jpg',
      alt_text: 'T-Shirt Back View',
      is_primary: false,
      sort_order: 1
    }
  ],
  
  // Variations
  variations: [
    {
      sku: 'TSHIRT-PREMIUM-001-S-RED',
      price: 29.99,
      quantity: 50,
      is_default: true,
      variation_values: [1, 4] // Size: Small, Color: Red
    },
    {
      sku: 'TSHIRT-PREMIUM-001-S-BLUE',
      price: 29.99,
      quantity: 45,
      is_default: false,
      variation_values: [1, 5] // Size: Small, Color: Blue
    },
    {
      sku: 'TSHIRT-PREMIUM-001-M-RED',
      price: 29.99,
      quantity: 75,
      is_default: false,
      variation_values: [2, 4] // Size: Medium, Color: Red
    },
    {
      sku: 'TSHIRT-PREMIUM-001-L-BLACK',
      price: 32.99, // Premium price
      quantity: 30,
      is_default: false,
      variation_values: [3, 6] // Size: Large, Color: Black
    }
  ]
}, {
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});
```

## Update Product with Variations

### Update Existing Variations
```javascript
const formData = new FormData();

// Update product info
formData.append('name', 'Updated T-Shirt Name');
formData.append('price', '34.99');

// Update existing variation (include ID)
formData.append('variations[0][id]', '1'); // Existing variation ID
formData.append('variations[0][sku]', 'TSHIRT-PREMIUM-001-S-RED');
formData.append('variations[0][price]', '31.99'); // Updated price
formData.append('variations[0][quantity]', '60'); // Updated quantity

// Add new variation (no ID)
formData.append('variations[1][sku]', 'TSHIRT-PREMIUM-001-XL-RED');
formData.append('variations[1][price]', '34.99');
formData.append('variations[1][quantity]', '25');
formData.append('variations[1][variation_values][0]', '4'); // Size: XL
formData.append('variations[1][variation_values][1]', '4'); // Color: Red

// Delete variation (mark with _delete)
formData.append('variations[2][id]', '3'); // Variation to delete
formData.append('variations[2][_delete]', 'true');

await axios.put(`/api/admin/products/${productId}`, formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
```

### JSON Update Example
```javascript
await axios.put(`/api/admin/products/${productId}`, {
  name: 'Updated T-Shirt Name',
  price: 34.99,
  
  variations: [
    // Update existing
    {
      id: 1,
      price: 31.99,
      quantity: 60
    },
    // Add new
    {
      sku: 'TSHIRT-PREMIUM-001-XL-RED',
      price: 34.99,
      quantity: 25,
      variation_values: [4, 4]
    },
    // Delete
    {
      id: 3,
      _delete: true
    }
  ]
}, {
  headers: { 'Content-Type': 'application/json' }
});
```

## React Component Example

```jsx
import { useState } from 'react';
import axios from 'axios';

function CreateProductWithVariations() {
  const [product, setProduct] = useState({
    name: '',
    category_id: '',
    price: '',
    sku: '',
    description: '',
    is_featured: false,
    status: 'draft'
  });
  
  const [images, setImages] = useState([]);
  const [variations, setVariations] = useState([
    {
      sku: '',
      price: '',
      quantity: 0,
      is_default: true,
      variation_values: []
    }
  ]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    
    // Add product fields
    Object.keys(product).forEach(key => {
      formData.append(key, product[key]);
    });
    
    // Add images
    images.forEach((image, index) => {
      formData.append(`images[${index}][file]`, image.file);
      formData.append(`images[${index}][alt_text]`, image.alt_text);
      formData.append(`images[${index}][is_primary]`, index === 0 ? '1' : '0');
      formData.append(`images[${index}][sort_order]`, index);
    });
    
    // Add variations
    variations.forEach((variation, index) => {
      formData.append(`variations[${index}][sku]`, variation.sku);
      formData.append(`variations[${index}][price]`, variation.price);
      formData.append(`variations[${index}][quantity]`, variation.quantity);
      formData.append(`variations[${index}][is_default]`, variation.is_default ? '1' : '0');
      
      variation.variation_values.forEach((valueId, valueIndex) => {
        formData.append(`variations[${index}][variation_values][${valueIndex}]`, valueId);
      });
    });
    
    try {
      const response = await axios.post('/api/admin/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      alert('Product created successfully!');
      console.log(response.data);
    } catch (error) {
      console.error('Error:', error.response.data);
      alert('Error creating product');
    }
  };

  const addVariation = () => {
    setVariations([...variations, {
      sku: '',
      price: product.price,
      quantity: 0,
      is_default: false,
      variation_values: []
    }]);
  };

  const removeVariation = (index) => {
    setVariations(variations.filter((_, i) => i !== index));
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Create Product with Variations</h2>
      
      {/* Product Fields */}
      <input
        type="text"
        placeholder="Product Name"
        value={product.name}
        onChange={(e) => setProduct({...product, name: e.target.value})}
        required
      />
      
      <input
        type="number"
        placeholder="Base Price"
        value={product.price}
        onChange={(e) => setProduct({...product, price: e.target.value})}
        required
      />
      
      {/* Image Upload */}
      <input
        type="file"
        multiple
        accept="image/*"
        onChange={(e) => {
          const files = Array.from(e.target.files);
          setImages(files.map(file => ({ file, alt_text: file.name })));
        }}
      />
      
      {/* Variations */}
      <h3>Variations</h3>
      {variations.map((variation, index) => (
        <div key={index} className="variation-item">
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
          
          <label>
            <input
              type="checkbox"
              checked={variation.is_default}
              onChange={(e) => {
                const newVariations = variations.map((v, i) => ({
                  ...v,
                  is_default: i === index ? e.target.checked : false
                }));
                setVariations(newVariations);
              }}
            />
            Default
          </label>
          
          <button type="button" onClick={() => removeVariation(index)}>
            Remove
          </button>
        </div>
      ))}
      
      <button type="button" onClick={addVariation}>
        Add Variation
      </button>
      
      <button type="submit">Create Product</button>
    </form>
  );
}
```

## Response Format

```json
{
  "success": true,
  "message": "Product created successfully.",
  "data": {
    "id": 1,
    "name": "Premium Cotton T-Shirt",
    "sku": "TSHIRT-PREMIUM-001",
    "price": "29.99",
    "quantity": 0,
    "status": "active",
    "images": [
      {
        "id": 1,
        "product_id": 1,
        "image_path": "products/1234567890_0.jpg",
        "alt_text": "T-Shirt Front View",
        "is_primary": true,
        "sort_order": 0,
        "full_url": "https://example.com/storage/products/1234567890_0.jpg"
      }
    ],
    "variations": [
      {
        "id": 1,
        "product_id": 1,
        "sku": "TSHIRT-PREMIUM-001-S-RED",
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

## Key Features

### Create
- ✅ Create product with images and variations in one call
- ✅ Auto-generate SKUs if not provided
- ✅ Auto-set first variation as default
- ✅ Upload images directly or use pre-uploaded paths

### Update
- ✅ Update existing variations by including their ID
- ✅ Add new variations by omitting ID
- ✅ Delete variations by setting `_delete: true`
- ✅ Update images separately or together

## Validation Rules

### Variations
- `variations`: nullable, array
- `variations.*.id`: nullable, exists (for updates)
- `variations.*.sku`: nullable, string, max 100, unique
- `variations.*.price`: nullable, numeric, min 0
- `variations.*.quantity`: nullable, integer, min 0
- `variations.*.is_default`: nullable, boolean
- `variations.*.variation_values`: nullable, array
- `variations.*.variation_values.*`: exists in variation_options
- `variations.*._delete`: nullable, boolean (for deletion)

## Best Practices

1. **Set product quantity to 0** when using variations
2. **Always have one default variation** for initial display
3. **Use meaningful SKUs** that include variation attributes
4. **Provide alt text** for all images
5. **Set appropriate prices** - use null to inherit product price
6. **Track inventory** at variation level
7. **Test with small datasets** before bulk operations

## Summary

You can now:
- ✅ Create products with images AND variations in one API call
- ✅ Update products and their variations together
- ✅ Add new variations during update
- ✅ Delete variations during update
- ✅ Professional e-commerce workflow
