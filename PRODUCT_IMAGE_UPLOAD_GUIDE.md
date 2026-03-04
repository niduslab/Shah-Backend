# Product Image Upload Guide

## Overview
The product API now supports both file uploads and pre-uploaded image paths, making it flexible for different frontend implementations.

## Two Ways to Handle Images

### Method 1: Direct File Upload (Recommended)
Upload image files directly when creating/updating products. The backend handles storage automatically.

### Method 2: Pre-Upload Then Reference
Upload images first to a separate endpoint, then reference the paths when creating products.

## API Usage

### Creating Product with File Uploads

#### Using Form Data (multipart/form-data)

```javascript
const formData = new FormData();

// Product fields
formData.append('name', 'Premium Laptop');
formData.append('category_id', '5');
formData.append('brand_id', '3');
formData.append('price', '1299.99');
formData.append('quantity', '50');
formData.append('description', 'High-performance laptop');
formData.append('short_description', 'Premium laptop for professionals');
formData.append('is_featured', 'true');  // String 'true' or '1' works
formData.append('is_trending', 'true');
formData.append('status', 'active');

// Image files
formData.append('images[0][file]', imageFile1); // File object
formData.append('images[0][alt_text]', 'Laptop front view');
formData.append('images[0][is_primary]', '1');
formData.append('images[0][sort_order]', '0');

formData.append('images[1][file]', imageFile2); // File object
formData.append('images[1][alt_text]', 'Laptop side view');
formData.append('images[1][is_primary]', '0');
formData.append('images[1][sort_order]', '1');

// Send request
const response = await axios.post('/api/admin/products', formData, {
  headers: {
    'Content-Type': 'multipart/form-data',
    'Authorization': `Bearer ${token}`
  }
});
```

#### Using HTML Form

```html
<form action="/api/admin/products" method="POST" enctype="multipart/form-data">
  <input type="text" name="name" value="Premium Laptop" required>
  <input type="number" name="category_id" value="5" required>
  <input type="number" name="brand_id" value="3">
  <input type="number" name="price" value="1299.99" required>
  <input type="number" name="quantity" value="50">
  <textarea name="description">High-performance laptop</textarea>
  <input type="text" name="short_description" value="Premium laptop">
  
  <!-- Boolean fields: use 'true'/'false' or '1'/'0' -->
  <select name="is_featured">
    <option value="false">No</option>
    <option value="true" selected>Yes</option>
  </select>
  
  <select name="is_trending">
    <option value="0">No</option>
    <option value="1" selected>Yes</option>
  </select>
  
  <select name="status">
    <option value="draft">Draft</option>
    <option value="active" selected>Active</option>
    <option value="inactive">Inactive</option>
  </select>
  
  <!-- Image 1 -->
  <input type="file" name="images[0][file]" accept="image/*" required>
  <input type="text" name="images[0][alt_text]" value="Front view">
  <input type="hidden" name="images[0][is_primary]" value="1">
  <input type="hidden" name="images[0][sort_order]" value="0">
  
  <!-- Image 2 -->
  <input type="file" name="images[1][file]" accept="image/*">
  <input type="text" name="images[1][alt_text]" value="Side view">
  <input type="hidden" name="images[1][is_primary]" value="0">
  <input type="hidden" name="images[1][sort_order]" value="1">
  
  <button type="submit">Create Product</button>
</form>
```

#### Using React with File Input

```jsx
import { useState } from 'react';
import axios from 'axios';

function CreateProduct() {
  const [formData, setFormData] = useState({
    name: '',
    category_id: '',
    price: '',
    is_featured: false,
    is_trending: false,
    status: 'draft'
  });
  
  const [images, setImages] = useState([]);
  
  const handleImageChange = (e) => {
    const files = Array.from(e.target.files);
    setImages(files.map((file, index) => ({
      file,
      alt_text: '',
      is_primary: index === 0,
      sort_order: index
    })));
  };
  
  const handleSubmit = async (e) => {
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
      data.append(`images[${index}][is_primary]`, image.is_primary ? '1' : '0');
      data.append(`images[${index}][sort_order]`, image.sort_order);
    });
    
    try {
      const response = await axios.post('/api/admin/products', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      console.log('Product created:', response.data);
    } catch (error) {
      console.error('Error:', error.response.data);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
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
      
      <label>
        <input
          type="checkbox"
          checked={formData.is_featured}
          onChange={(e) => setFormData({...formData, is_featured: e.target.checked})}
        />
        Featured
      </label>
      
      <label>
        <input
          type="checkbox"
          checked={formData.is_trending}
          onChange={(e) => setFormData({...formData, is_trending: e.target.checked})}
        />
        Trending
      </label>
      
      <input
        type="file"
        multiple
        accept="image/*"
        onChange={handleImageChange}
      />
      
      <button type="submit">Create Product</button>
    </form>
  );
}
```

### Creating Product with Pre-Uploaded Paths

If you've already uploaded images and have their paths:

```javascript
const response = await axios.post('/api/admin/products', {
  name: 'Premium Laptop',
  category_id: 5,
  brand_id: 3,
  price: 1299.99,
  quantity: 50,
  is_featured: true,  // Boolean works in JSON
  is_trending: true,
  status: 'active',
  images: [
    {
      path: 'products/laptop-front.jpg',
      alt_text: 'Laptop front view',
      is_primary: true,
      sort_order: 0
    },
    {
      path: 'products/laptop-side.jpg',
      alt_text: 'Laptop side view',
      is_primary: false,
      sort_order: 1
    }
  ]
}, {
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});
```

## Important Notes

### Boolean Fields
When using form data (multipart/form-data), boolean fields accept:
- `'true'` or `'false'` (strings)
- `'1'` or `'0'` (strings)
- `1` or `0` (numbers)

When using JSON, use actual booleans:
- `true` or `false`

### Image Validation
- **Accepted formats:** JPEG, JPG, PNG, GIF, WebP
- **Max file size:** 5MB (5120 KB)
- **Max images per product:** 10
- **Required fields:** Either `file` or `path` must be provided

### Image Storage
- Images are stored in `storage/app/public/products/`
- Filenames are auto-generated: `timestamp_index_uniqueid.extension`
- Example: `1709567890_0_65f1a2b3c4d5e.jpg`

### Primary Image
- Only one image can be primary
- If multiple images have `is_primary: true`, only the first one will be primary
- If no image is marked as primary, the first image becomes primary automatically

### Sort Order
- If not provided, images are ordered by their array index (0, 1, 2, ...)
- You can manually set sort order for custom ordering

## Error Handling

### Common Errors

#### 1. File Too Large
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "images.0.file": ["The images.0.file must not be greater than 5120 kilobytes."]
  }
}
```
**Solution:** Compress image before upload or increase max size in validation.

#### 2. Invalid File Type
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "images.0.file": ["The images.0.file must be an image.", "The images.0.file must be a file of type: jpeg, jpg, png, gif, webp."]
  }
}
```
**Solution:** Ensure file is a valid image format.

#### 3. Too Many Images
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "images": ["The images must not have more than 10 items."]
  }
}
```
**Solution:** Limit to 10 images per product.

#### 4. Boolean Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "is_featured": ["The is featured field must be true or false."]
  }
}
```
**Solution:** Use 'true'/'false' or '1'/'0' for form data, or actual booleans for JSON.

#### 5. Missing Image Data
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "images.0.path": ["The images.0.path field is required when images is present."]
  }
}
```
**Solution:** Provide either `file` or `path` for each image.

## Testing with cURL

### Upload Files
```bash
curl -X POST http://localhost/api/admin/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Premium Laptop" \
  -F "category_id=5" \
  -F "brand_id=3" \
  -F "price=1299.99" \
  -F "quantity=50" \
  -F "is_featured=true" \
  -F "is_trending=true" \
  -F "status=active" \
  -F "images[0][file]=@/path/to/image1.jpg" \
  -F "images[0][alt_text]=Front view" \
  -F "images[0][is_primary]=1" \
  -F "images[0][sort_order]=0" \
  -F "images[1][file]=@/path/to/image2.jpg" \
  -F "images[1][alt_text]=Side view" \
  -F "images[1][is_primary]=0" \
  -F "images[1][sort_order]=1"
```

### Use Paths
```bash
curl -X POST http://localhost/api/admin/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Premium Laptop",
    "category_id": 5,
    "price": 1299.99,
    "is_featured": true,
    "is_trending": true,
    "status": "active",
    "images": [
      {
        "path": "products/laptop-front.jpg",
        "alt_text": "Front view",
        "is_primary": true
      }
    ]
  }'
```

## Success Response

```json
{
  "success": true,
  "message": "Product created successfully.",
  "data": {
    "id": 5,
    "name": "Premium Laptop",
    "price": "1299.99",
    "is_featured": true,
    "is_trending": true,
    "status": "active",
    "primary_image_url": "https://example.com/storage/products/1709567890_0_65f1a2b3c4d5e.jpg",
    "images": [
      {
        "id": 12,
        "product_id": 5,
        "image_path": "products/1709567890_0_65f1a2b3c4d5e.jpg",
        "alt_text": "Front view",
        "is_primary": true,
        "sort_order": 0,
        "full_url": "https://example.com/storage/products/1709567890_0_65f1a2b3c4d5e.jpg"
      },
      {
        "id": 13,
        "product_id": 5,
        "image_path": "products/1709567890_1_65f1a2b3c4d5f.jpg",
        "alt_text": "Side view",
        "is_primary": false,
        "sort_order": 1,
        "full_url": "https://example.com/storage/products/1709567890_1_65f1a2b3c4d5f.jpg"
      }
    ]
  }
}
```

## Best Practices

1. **Always provide alt text** for SEO and accessibility
2. **Compress images** before upload to reduce file size
3. **Use descriptive filenames** for better organization
4. **Set primary image** to the best product photo
5. **Order images logically** (front, side, back, details)
6. **Handle errors gracefully** in your frontend
7. **Show upload progress** for better UX
8. **Validate files client-side** before upload
9. **Use WebP format** for better compression
10. **Implement image preview** before submission
