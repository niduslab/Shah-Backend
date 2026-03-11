# Category Image Upload Guide

## Overview

Categories now support image uploads with Laravel Storage facade, including:
- ✅ Image validation (JPEG, PNG, WebP only)
- ✅ Automatic file storage using Storage facade in `storage/app/public/storage/categories/`
- ✅ Database transactions with automatic rollback
- ✅ Old image deletion on update using Storage facade
- ✅ Image deletion when category is deleted
- ✅ Meta title and meta description storage
- ✅ Full URL generation in API responses
- ✅ Automatic cleanup on transaction failure

---

## Storage Setup

### Create Symbolic Link

Before uploading images, create the storage symbolic link:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

### Storage Location

- **Physical Path**: `storage/app/public/storage/categories/`
- **Public URL**: `/storage/storage/categories/{filename}`
- **Storage Disk**: `public`
- **Naming**: Laravel auto-generates unique filenames

---

## Database Structure

The `categories` table includes:

```sql
- id
- parent_id (nullable)
- name
- slug (unique)
- description (nullable)
- image (nullable) - Stores relative path from storage disk
- sort_order (default: 0)
- is_active (default: true)
- meta_title (nullable)
- meta_description (nullable)
- created_at
- updated_at
```

---

## API Endpoints

### 1. Create Category with Image

**Endpoint**: `POST /api/admin/categories`

**Content-Type**: `multipart/form-data`

**Form Fields**:
```
name: string (required, max: 255)
parent_id: integer (nullable, must exist in categories)
description: string (nullable)
image: file (nullable, image, max: 2MB, types: jpeg,png,webp)
sort_order: integer (nullable, min: 0)
is_active: boolean (nullable)
meta_title: string (nullable, max: 255)
meta_description: string (nullable)
```

**cURL Example**:
```bash
curl -X POST http://localhost:8000/api/admin/categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Cardio Equipment" \
  -F "description=Premium cardio machines for home and gym" \
  -F "image=@/path/to/cardio-image.jpg" \
  -F "meta_title=Cardio Equipment - Premium Fitness Machines" \
  -F "meta_description=Shop our collection of premium cardio equipment including treadmills, bikes, and ellipticals" \
  -F "is_active=true" \
  -F "sort_order=1"
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Category created successfully.",
  "data": {
    "id": 1,
    "parent_id": null,
    "name": "Cardio Equipment",
    "slug": "cardio-equipment",
    "description": "Premium cardio machines for home and gym",
    "image": "/storage/storage/categories/abc123def456.jpg",
    "sort_order": 1,
    "is_active": true,
    "meta_title": "Cardio Equipment - Premium Fitness Machines",
    "meta_description": "Shop our collection of premium cardio equipment...",
    "created_at": "2024-03-08T14:09:03.000000Z",
    "updated_at": "2024-03-08T14:09:03.000000Z"
  }
}
```

**Note**: The `image` field returns the full URL path, not the storage path.

---

### 2. Update Category with Image

**Endpoint**: `PUT /api/admin/categories/{id}` or `POST /api/admin/categories/{id}` (with `_method=PUT`)

**Content-Type**: `multipart/form-data`

**Note**: When updating with file upload, use POST with `_method=PUT` field.

**cURL Example**:
```bash
curl -X POST http://localhost:8000/api/admin/categories/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "_method=PUT" \
  -F "name=Cardio Equipment Updated" \
  -F "image=@/path/to/new-image.jpg" \
  -F "meta_title=Updated Cardio Equipment Title" \
  -F "meta_description=Updated description for SEO"
```

**Behavior**:
- If new image is uploaded, old image is automatically deleted using Storage facade
- If no image is provided, existing image remains unchanged
- Meta fields are updated if provided
- All operations wrapped in database transaction
- Automatic cleanup if transaction fails

---

### 3. Delete Category

**Endpoint**: `DELETE /api/admin/categories/{id}`

**cURL Example**:
```bash
curl -X DELETE http://localhost:8000/api/admin/categories/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Behavior**:
- Category image is automatically deleted using Storage facade
- Child categories are moved to parent
- Cannot delete if category has products

---

## JavaScript/Fetch Examples

### Create Category with Image

```javascript
const createCategory = async (formData) => {
  const data = new FormData();
  data.append('name', 'Cardio Equipment');
  data.append('description', 'Premium cardio machines');
  data.append('meta_title', 'Cardio Equipment - Premium Fitness');
  data.append('meta_description', 'Shop our collection of cardio equipment');
  data.append('is_active', true);
  data.append('sort_order', 1);
  
  // Add image file
  const fileInput = document.querySelector('#categoryImage');
  if (fileInput.files[0]) {
    data.append('image', fileInput.files[0]);
  }

  const response = await fetch('http://localhost:8000/api/admin/categories', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: data
  });

  const result = await response.json();
  console.log('Category created:', result);
  // result.data.image will be full URL like "/storage/storage/categories/abc123.jpg"
  return result;
};
```

### Update Category with Image

```javascript
const updateCategory = async (categoryId, formData) => {
  const data = new FormData();
  data.append('_method', 'PUT');
  data.append('name', 'Updated Category Name');
  data.append('meta_title', 'Updated Meta Title');
  data.append('meta_description', 'Updated meta description');
  
  // Add image file if selected
  const fileInput = document.querySelector('#categoryImage');
  if (fileInput.files[0]) {
    data.append('image', fileInput.files[0]);
  }

  const response = await fetch(`http://localhost:8000/api/admin/categories/${categoryId}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: data
  });

  const result = await response.json();
  console.log('Category updated:', result);
  return result;
};
```

---

## React Example

### Category Form Component

```jsx
import { useState } from 'react';
import axios from 'axios';

export default function CategoryForm({ category = null, onSuccess }) {
  const [formData, setFormData] = useState({
    name: category?.name || '',
    description: category?.description || '',
    meta_title: category?.meta_title || '',
    meta_description: category?.meta_description || '',
    is_active: category?.is_active ?? true,
    sort_order: category?.sort_order || 0
  });
  const [image, setImage] = useState(null);
  const [preview, setPreview] = useState(category?.image || null);

  const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setImage(file);
      setPreview(URL.createObjectURL(file));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const data = new FormData();
    Object.keys(formData).forEach(key => {
      data.append(key, formData[key]);
    });

    if (image) {
      data.append('image', image);
    }

    if (category) {
      data.append('_method', 'PUT');
    }

    try {
      const url = category 
        ? `/api/admin/categories/${category.id}`
        : '/api/admin/categories';
      
      const response = await axios.post(url, data, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      });

      console.log('Success:', response.data);
      onSuccess(response.data);
    } catch (error) {
      console.error('Error:', error.response?.data);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <div className="form-group">
        <label>Category Name *</label>
        <input
          type="text"
          value={formData.name}
          onChange={(e) => setFormData({...formData, name: e.target.value})}
          required
        />
      </div>

      <div className="form-group">
        <label>Description</label>
        <textarea
          value={formData.description}
          onChange={(e) => setFormData({...formData, description: e.target.value})}
          rows={4}
        />
      </div>

      <div className="form-group">
        <label>Category Image</label>
        <input
          type="file"
          accept="image/jpeg,image/png,image/webp"
          onChange={handleImageChange}
        />
        {preview && (
          <div className="image-preview">
            <img src={preview} alt="Preview" style={{maxWidth: '200px'}} />
          </div>
        )}
      </div>

      <div className="form-group">
        <label>Meta Title (SEO)</label>
        <input
          type="text"
          value={formData.meta_title}
          onChange={(e) => setFormData({...formData, meta_title: e.target.value})}
          maxLength={255}
        />
      </div>

      <div className="form-group">
        <label>Meta Description (SEO)</label>
        <textarea
          value={formData.meta_description}
          onChange={(e) => setFormData({...formData, meta_description: e.target.value})}
          rows={3}
        />
      </div>

      <div className="form-group">
        <label>
          <input
            type="checkbox"
            checked={formData.is_active}
            onChange={(e) => setFormData({...formData, is_active: e.target.checked})}
          />
          Active
        </label>
      </div>

      <div className="form-group">
        <label>Sort Order</label>
        <input
          type="number"
          value={formData.sort_order}
          onChange={(e) => setFormData({...formData, sort_order: parseInt(e.target.value)})}
          min={0}
        />
      </div>

      <button type="submit">
        {category ? 'Update Category' : 'Create Category'}
      </button>
    </form>
  );
}
```

---

## Image Specifications

### Accepted Formats
- JPEG (.jpeg)
- PNG (.png)
- WebP (.webp)

**Note**: JPG and GIF are no longer supported to match BrandController pattern.

### File Size
- Maximum: 2MB (2048 KB)

### Recommended Dimensions
- Width: 800px - 1200px
- Height: 600px - 900px
- Aspect Ratio: 4:3 or 16:9

### Storage Location
- **Physical Path**: `storage/app/public/storage/categories/`
- **Public URL**: `/storage/storage/categories/{filename}`
- **Naming**: Laravel auto-generates unique filenames
- **Example**: `storage/categories/abc123def456.jpg` (stored in DB)
- **Example URL**: `/storage/storage/categories/abc123def456.jpg` (returned by API)

---

## Validation Rules

### Image Field
```php
'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048'
```

- **nullable**: Image is optional
- **image**: Must be a valid image file
- **mimes**: Only JPEG, PNG, WebP allowed
- **max:2048**: Maximum 2MB file size

### Meta Fields
```php
'meta_title' => 'nullable|string|max:255'
'meta_description' => 'nullable|string'
```

---

## Transaction Safety

All image operations are wrapped in database transactions:

```php
DB::beginTransaction();
try {
    // Upload image using Storage facade
    // Save to database
    DB::commit();
    // Delete old image (if updating)
} catch (\Exception $e) {
    DB::rollBack();
    // Clean up uploaded file
    throw $e;
}
```

This ensures:
- ✅ No orphaned files if database save fails
- ✅ No database records without files
- ✅ Automatic cleanup on errors
- ✅ Old images only deleted after successful update

---

## Error Handling

### Validation Errors

**Request**:
```bash
curl -X POST http://localhost:8000/api/admin/categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=" \
  -F "image=@large-file.jpg"
```

**Response** (422 Unprocessable Entity):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "image": ["The image must not be greater than 2048 kilobytes."]
  }
}
```

### Invalid Image Type

**Response** (422):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "image": ["The image must be a file of type: jpeg, png, webp."]
  }
}
```

---

## Testing

### Using Postman

1. **Create Request**
   - Method: POST
   - URL: `http://localhost:8000/api/admin/categories`
   - Headers: `Authorization: Bearer YOUR_TOKEN`

2. **Body**
   - Select: form-data
   - Add fields:
     - name: Cardio Equipment
     - description: Premium cardio machines
     - meta_title: Cardio Equipment SEO Title
     - meta_description: SEO description here
     - image: [Select File - JPEG, PNG, or WebP only]
     - is_active: true

3. **Send Request**

### Using cURL

```bash
# Create category with image
curl -X POST http://localhost:8000/api/admin/categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Cardio Equipment" \
  -F "description=Premium cardio machines" \
  -F "image=@./cardio-image.jpg" \
  -F "meta_title=Cardio Equipment - Premium Fitness" \
  -F "meta_description=Shop our collection of cardio equipment" \
  -F "is_active=true"
```

---

## File Management

### Automatic Cleanup

The system automatically handles:

1. **On Create**: 
   - Uploads image using Storage facade
   - Wraps in transaction
   - Cleans up if transaction fails

2. **On Update**: 
   - Uploads new image
   - Updates database in transaction
   - Deletes old image only after successful commit
   - Cleans up new upload if transaction fails

3. **On Delete**: 
   - Deletes image using Storage facade
   - Removes category record

### Storage Disk Configuration

Ensure `config/filesystems.php` has the public disk configured:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

---

## Security Considerations

✅ **File Type Validation**: Only JPEG, PNG, WebP allowed
✅ **File Size Limit**: Maximum 2MB prevents abuse
✅ **Unique Naming**: Laravel generates unique filenames automatically
✅ **Path Sanitization**: Storage facade handles secure file storage
✅ **Authentication Required**: Admin access only
✅ **Transaction Safety**: Database transactions prevent orphaned files
✅ **Automatic Cleanup**: Failed uploads are automatically deleted

---

## Troubleshooting

### Image Not Displaying

1. Run `php artisan storage:link` to create symbolic link
2. Check if file exists in `storage/app/public/storage/categories/`
3. Verify file permissions (644 for files, 755 for directories)
4. Check image path in database
5. Ensure symbolic link exists at `public/storage`
6. Verify `APP_URL` in `.env` is correct

### Upload Fails

1. Check PHP upload limits (`upload_max_filesize`, `post_max_size` in php.ini)
2. Verify storage directory exists and is writable
3. Check disk space
4. Review Laravel logs in `storage/logs/laravel.log`
5. Ensure `storage/app/public` directory has proper permissions (755)

### Old Images Not Deleted

1. Verify file path matches database record
2. Check storage disk configuration in `config/filesystems.php`
3. Ensure Storage facade has proper permissions
4. Check Laravel logs for deletion errors
5. Verify symbolic link is working

### Transaction Rollback Issues

1. Check database supports transactions (InnoDB for MySQL)
2. Review Laravel logs for specific error messages
3. Ensure Storage facade is working correctly
4. Verify file permissions allow deletion

---

## Comparison with BrandController

CategoryController now uses the exact same pattern as BrandController:

| Feature | CategoryController | BrandController |
|---------|-------------------|-----------------|
| Storage Method | ✅ Storage facade | ✅ Storage facade |
| Storage Path | `storage/categories` | `storage/brands` |
| Transactions | ✅ DB transactions | ✅ DB transactions |
| Cleanup on Failure | ✅ Automatic | ✅ Automatic |
| URL Generation | ✅ Full URL | ✅ Full URL |
| Validation | jpeg, png, webp | jpeg, png, webp |
| Max Size | 2MB | 2MB |
| Old File Deletion | ✅ After commit | ✅ After commit |

---

## Summary

✅ **Storage Facade**: Uses Laravel Storage for file operations
✅ **Transaction Safety**: Database transactions with automatic rollback
✅ **Automatic Cleanup**: Failed uploads are automatically deleted
✅ **Full URLs**: API returns complete URLs for images
✅ **Image Upload**: Supported with validation
✅ **Meta Fields**: meta_title and meta_description stored
✅ **Auto Cleanup**: Old images deleted automatically after successful update
✅ **File Validation**: Type and size checks
✅ **Easy Integration**: Works with FormData
✅ **SEO Ready**: Meta fields for search optimization
✅ **Consistent Pattern**: Matches BrandController implementation

All category operations now support image uploads with Laravel Storage facade and transaction safety!
