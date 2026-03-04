# Product Image Management System

## Overview
Professional e-commerce product image management system supporting multiple images per product with primary image designation, ordering, and full CRUD operations.

## Database Structure

### product_images Table
```sql
- id: Primary key
- product_id: Foreign key to products table (cascade on delete)
- image_path: String (500 chars) - stores image path or URL
- alt_text: String (255 chars, nullable) - for SEO and accessibility
- is_primary: Boolean (default: false) - designates the main product image
- sort_order: Integer (default: 0) - controls display order
- timestamps: created_at, updated_at
```

### Indexes
- `product_id` + `is_primary` - Fast primary image lookup
- `product_id` + `sort_order` - Efficient ordered retrieval

## Features

### 1. Multiple Images Support
- Up to 10 images per product
- Automatic sort ordering
- Primary image designation
- Full URL generation with Storage facade

### 2. Image Operations

#### Create Product with Images
```http
POST /api/admin/products
Content-Type: application/json

{
  "name": "Product Name",
  "category_id": 1,
  "price": 99.99,
  "images": [
    {
      "path": "products/image1.jpg",
      "alt_text": "Product front view",
      "is_primary": true,
      "sort_order": 0
    },
    {
      "path": "products/image2.jpg",
      "alt_text": "Product side view",
      "sort_order": 1
    }
  ]
}
```

#### Update Product with Images
```http
PUT /api/admin/products/{id}
Content-Type: application/json

{
  "name": "Updated Product Name",
  "images": [
    {
      "path": "products/new-image.jpg",
      "alt_text": "New image",
      "is_primary": true
    }
  ]
}
```
**Note:** This replaces all existing images.

#### Add Images to Existing Product
```http
POST /api/admin/products/{id}/images
Content-Type: application/json

{
  "images": [
    {
      "path": "products/additional-image.jpg",
      "alt_text": "Additional view"
    }
  ]
}
```
**Note:** Adds images without removing existing ones. Max 10 total images.

#### Update Single Image
```http
PUT /api/admin/products/{productId}/images/{imageId}
Content-Type: application/json

{
  "path": "products/updated-image.jpg",
  "alt_text": "Updated alt text",
  "is_primary": false,
  "sort_order": 2
}
```

#### Delete Single Image
```http
DELETE /api/admin/products/{productId}/images/{imageId}
```
**Note:** If primary image is deleted, the first remaining image becomes primary.

#### Set Primary Image
```http
POST /api/admin/products/{productId}/images/{imageId}/set-primary
```
**Note:** Automatically unsets other primary images.

#### Reorder Images
```http
POST /api/admin/products/{productId}/images/reorder
Content-Type: application/json

{
  "image_ids": [5, 3, 7, 2]
}
```
**Note:** Array order determines new sort_order (0-indexed).

## Model Usage

### ProductImage Model

#### Attributes
- `full_url` - Computed attribute returning full image URL
- `is_primary` - Boolean
- `sort_order` - Integer

#### Relationships
```php
$image->product; // Get parent product
```

#### Scopes
```php
ProductImage::primary()->get(); // Get primary images only
ProductImage::ordered()->get(); // Order by sort_order
```

### Product Model

#### Image Relationships
```php
$product->images; // All images ordered by sort_order
$product->primaryImage; // Primary image only
```

#### Computed Attributes
```php
$product->primary_image_url; // URL of primary image
$product->image_urls; // Array of all image URLs
```

## Business Rules

1. **Primary Image**
   - Only one primary image per product
   - First image is automatically primary if none specified
   - Deleting primary image promotes next image

2. **Image Limits**
   - Maximum 10 images per product
   - Enforced on create and add operations

3. **Sort Order**
   - Auto-assigned if not provided
   - 0-indexed sequential ordering
   - Can be manually reordered

4. **Cascade Deletion**
   - Deleting product removes all images
   - Database constraint ensures data integrity

## Response Format

### Single Image Response
```json
{
  "id": 1,
  "product_id": 5,
  "image_path": "products/image.jpg",
  "alt_text": "Product view",
  "is_primary": true,
  "sort_order": 0,
  "full_url": "https://example.com/storage/products/image.jpg",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Product with Images
```json
{
  "id": 5,
  "name": "Product Name",
  "price": 99.99,
  "primary_image_url": "https://example.com/storage/products/image1.jpg",
  "image_urls": [
    "https://example.com/storage/products/image1.jpg",
    "https://example.com/storage/products/image2.jpg"
  ],
  "images": [
    {
      "id": 1,
      "image_path": "products/image1.jpg",
      "alt_text": "Front view",
      "is_primary": true,
      "sort_order": 0,
      "full_url": "https://example.com/storage/products/image1.jpg"
    },
    {
      "id": 2,
      "image_path": "products/image2.jpg",
      "alt_text": "Side view",
      "is_primary": false,
      "sort_order": 1,
      "full_url": "https://example.com/storage/products/image2.jpg"
    }
  ]
}
```

## Validation Rules

### Create/Update Product
- `images`: nullable, array, max 10 items
- `images.*.path`: required with images, string, max 500 chars
- `images.*.alt_text`: nullable, string, max 255 chars
- `images.*.is_primary`: nullable, boolean
- `images.*.sort_order`: nullable, integer, min 0

### Add Images
- `images`: required, array, min 1, max 10 items
- Same field rules as above
- Total images (existing + new) cannot exceed 10

### Update Image
- `path`: sometimes, string, max 500 chars
- `alt_text`: nullable, string, max 255 chars
- `is_primary`: nullable, boolean
- `sort_order`: nullable, integer, min 0

### Reorder Images
- `image_ids`: required, array
- `image_ids.*`: required, integer, exists in product_images

## Error Responses

### Product Not Found
```json
{
  "success": false,
  "message": "Product not found."
}
```
**Status:** 404

### Image Not Found
```json
{
  "success": false,
  "message": "Image not found."
}
```
**Status:** 404

### Image Limit Exceeded
```json
{
  "success": false,
  "message": "Cannot add images. Maximum 10 images allowed per product."
}
```
**Status:** 422

### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "images.0.path": ["The images.0.path field is required."]
  }
}
```
**Status:** 422

## Best Practices

1. **Image Storage**
   - Store images in `storage/app/public/products/`
   - Use descriptive filenames
   - Consider image optimization before upload

2. **Alt Text**
   - Always provide alt text for accessibility
   - Describe the image content clearly
   - Important for SEO

3. **Primary Image**
   - Ensure every product has a primary image
   - Use best quality image as primary
   - Primary image used in listings and thumbnails

4. **Performance**
   - Images are eager loaded with `ordered()` scope
   - Use pagination for product lists
   - Consider CDN for image delivery

5. **Image URLs**
   - Use `full_url` attribute for frontend display
   - Supports both storage paths and external URLs
   - Automatically handles Storage facade URL generation

## Migration

If updating from old system, run:
```bash
php artisan migrate:refresh --path=/database/migrations/2024_07_09_070935_create_product_images_table.php
```

**Warning:** This will drop and recreate the table. Backup data first.
