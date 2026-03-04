# Product Images API Quick Reference

## Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/admin/products` | Create product with images |
| PUT | `/api/admin/products/{id}` | Update product (replaces images) |
| POST | `/api/admin/products/{id}/images` | Add images to product |
| PUT | `/api/admin/products/{productId}/images/{imageId}` | Update single image |
| DELETE | `/api/admin/products/{productId}/images/{imageId}` | Delete single image |
| POST | `/api/admin/products/{productId}/images/{imageId}/set-primary` | Set as primary image |
| POST | `/api/admin/products/{productId}/images/reorder` | Reorder all images |

## Quick Examples

### 1. Create Product with Multiple Images
```bash
curl -X POST https://api.example.com/api/admin/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Premium Laptop",
    "category_id": 5,
    "price": 1299.99,
    "images": [
      {
        "path": "products/laptop-front.jpg",
        "alt_text": "Laptop front view",
        "is_primary": true,
        "sort_order": 0
      },
      {
        "path": "products/laptop-side.jpg",
        "alt_text": "Laptop side view",
        "sort_order": 1
      },
      {
        "path": "products/laptop-keyboard.jpg",
        "alt_text": "Laptop keyboard closeup",
        "sort_order": 2
      }
    ]
  }'
```

### 2. Add More Images to Existing Product
```bash
curl -X POST https://api.example.com/api/admin/products/5/images \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "images": [
      {
        "path": "products/laptop-ports.jpg",
        "alt_text": "Laptop ports view"
      },
      {
        "path": "products/laptop-screen.jpg",
        "alt_text": "Laptop screen display"
      }
    ]
  }'
```

### 3. Update Image Details
```bash
curl -X PUT https://api.example.com/api/admin/products/5/images/12 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "alt_text": "Updated laptop front view with better lighting",
    "sort_order": 0
  }'
```

### 4. Set Primary Image
```bash
curl -X POST https://api.example.com/api/admin/products/5/images/14/set-primary \
  -H "Authorization: Bearer {token}"
```

### 5. Reorder Images
```bash
curl -X POST https://api.example.com/api/admin/products/5/images/reorder \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "image_ids": [14, 12, 15, 13, 16]
  }'
```

### 6. Delete Image
```bash
curl -X DELETE https://api.example.com/api/admin/products/5/images/13 \
  -H "Authorization: Bearer {token}"
```

## JavaScript/Axios Examples

### Create Product with Images
```javascript
const createProduct = async () => {
  try {
    const response = await axios.post('/api/admin/products', {
      name: 'Premium Laptop',
      category_id: 5,
      price: 1299.99,
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
          sort_order: 1
        }
      ]
    });
    console.log('Product created:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
};
```

### Add Images
```javascript
const addImages = async (productId, images) => {
  try {
    const response = await axios.post(`/api/admin/products/${productId}/images`, {
      images: images
    });
    console.log('Images added:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
};

// Usage
addImages(5, [
  { path: 'products/new-image.jpg', alt_text: 'New view' }
]);
```

### Set Primary Image
```javascript
const setPrimaryImage = async (productId, imageId) => {
  try {
    const response = await axios.post(
      `/api/admin/products/${productId}/images/${imageId}/set-primary`
    );
    console.log('Primary image set:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
};
```

### Reorder Images (Drag & Drop)
```javascript
const reorderImages = async (productId, imageIds) => {
  try {
    const response = await axios.post(
      `/api/admin/products/${productId}/images/reorder`,
      { image_ids: imageIds }
    );
    console.log('Images reordered:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
};

// Usage after drag & drop
const newOrder = [14, 12, 15, 13, 16]; // New order of image IDs
reorderImages(5, newOrder);
```

## Response Examples

### Success Response (Create/Update)
```json
{
  "success": true,
  "message": "Product created successfully.",
  "data": {
    "id": 5,
    "name": "Premium Laptop",
    "price": "1299.99",
    "primary_image_url": "https://example.com/storage/products/laptop-front.jpg",
    "images": [
      {
        "id": 12,
        "product_id": 5,
        "image_path": "products/laptop-front.jpg",
        "alt_text": "Laptop front view",
        "is_primary": true,
        "sort_order": 0,
        "full_url": "https://example.com/storage/products/laptop-front.jpg"
      },
      {
        "id": 13,
        "product_id": 5,
        "image_path": "products/laptop-side.jpg",
        "alt_text": "Laptop side view",
        "is_primary": false,
        "sort_order": 1,
        "full_url": "https://example.com/storage/products/laptop-side.jpg"
      }
    ]
  }
}
```

### Error Response (Validation)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "images.0.path": [
      "The images.0.path field is required."
    ],
    "images": [
      "The images must not have more than 10 items."
    ]
  }
}
```

### Error Response (Not Found)
```json
{
  "success": false,
  "message": "Product not found."
}
```

### Error Response (Image Limit)
```json
{
  "success": false,
  "message": "Cannot add images. Maximum 10 images allowed per product."
}
```

## Important Notes

1. **Image Limits**: Maximum 10 images per product
2. **Primary Image**: Only one primary image allowed; automatically managed
3. **Sort Order**: Auto-assigned if not provided; 0-indexed
4. **Cascade Delete**: Deleting product removes all images
5. **Auto-Promotion**: Deleting primary image promotes next image
6. **Full URLs**: Use `full_url` attribute for display
7. **Alt Text**: Always provide for SEO and accessibility

## Common Workflows

### Initial Product Setup
1. Create product with 1-3 main images
2. Set best image as primary
3. Add more images later if needed

### Image Management
1. Add new images as product photos improve
2. Reorder images to show best views first
3. Update alt text for better SEO
4. Delete poor quality images

### Primary Image Update
1. Upload new high-quality image
2. Add to product images
3. Set as primary using set-primary endpoint
4. Optionally delete old primary image

### Bulk Image Upload
1. Upload images to storage first
2. Collect all image paths
3. Use add-images endpoint with array
4. Reorder if needed
