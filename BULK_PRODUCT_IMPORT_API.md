# Bulk Product Import API Documentation

## Overview

The Bulk Product Import system allows administrators to upload thousands of products via CSV files. The system processes imports in the background using Laravel queues, providing real-time progress tracking and detailed error reporting.

---

## API Endpoints

### Base URL
```
/api/admin/products/import
```

All endpoints require authentication and admin role.

---

## 1. Download CSV Template

**Endpoint:** `GET /api/admin/products/import/template`

**Description:** Downloads a CSV template with all supported columns and sample data.

**Authentication:** Required (Admin)

**Response:** CSV file download

**Example:**
```bash
curl -X GET "https://your-domain.com/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output product_import_template.csv
```

---

## 2. Upload CSV File

**Endpoint:** `POST /api/admin/products/import/upload`

**Description:** Uploads a CSV file and starts the import process in the background.

**Authentication:** Required (Admin)

**Request:**
- **Content-Type:** `multipart/form-data`
- **Body:**
  - `file` (required): CSV file (max 10MB)

**Response:**
```json
{
  "success": true,
  "message": "Import started successfully. Processing in background.",
  "data": {
    "import_id": 1,
    "filename": "products.csv",
    "total_rows": 2500,
    "status": "pending"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "The file must be a file of type: csv, txt."
}
```

**Example:**
```bash
curl -X POST "https://your-domain.com/api/admin/products/import/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@products.csv"
```

---

## 3. Check Import Status

**Endpoint:** `GET /api/admin/products/import/{id}`

**Description:** Get the current status and progress of an import.

**Authentication:** Required (Admin or Import Owner)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "filename": "products.csv",
    "status": "processing",
    "total_rows": 2500,
    "processed_rows": 1250,
    "successful_rows": 1200,
    "failed_rows": 50,
    "progress_percentage": 50.0,
    "error_message": null,
    "started_at": "2026-04-20T10:30:00.000000Z",
    "completed_at": null,
    "created_at": "2026-04-20T10:29:45.000000Z"
  }
}
```

**Status Values:**
- `pending` - Import queued, not started yet
- `processing` - Currently processing rows
- `completed` - Import finished successfully
- `failed` - Import failed with critical error
- `cancelled` - Import was cancelled by user

**Example:**
```bash
curl -X GET "https://your-domain.com/api/admin/products/import/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 4. Get Import Errors

**Endpoint:** `GET /api/admin/products/import/{id}/errors`

**Description:** Get detailed error information for failed rows.

**Authentication:** Required (Admin or Import Owner)

**Response:**
```json
{
  "success": true,
  "data": {
    "import_id": 1,
    "filename": "products.csv",
    "failed_rows": 50,
    "errors": [
      {
        "row": 15,
        "errors": [
          "Category ID is required",
          "Price must be a positive number"
        ]
      },
      {
        "row": 23,
        "errors": [
          "Database error: Duplicate entry 'SKU-123' for key 'products.sku'"
        ]
      }
    ]
  }
}
```

**Example:**
```bash
curl -X GET "https://your-domain.com/api/admin/products/import/1/errors" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 5. Export Error Report

**Endpoint:** `GET /api/admin/products/import/{id}/export-errors`

**Description:** Download a CSV file containing all failed rows and their errors.

**Authentication:** Required (Admin or Import Owner)

**Response:** CSV file download

**CSV Format:**
```csv
Row Number,Errors
15,"Category ID is required; Price must be a positive number"
23,"Database error: Duplicate entry 'SKU-123' for key 'products.sku'"
```

**Example:**
```bash
curl -X GET "https://your-domain.com/api/admin/products/import/1/export-errors" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output import_errors.csv
```

---

## 6. List All Imports

**Endpoint:** `GET /api/admin/products/import`

**Description:** Get a paginated list of all imports for the current user.

**Authentication:** Required (Admin)

**Query Parameters:**
- `status` (optional): Filter by status (pending, processing, completed, failed, cancelled)
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 3,
        "filename": "products_batch_3.csv",
        "status": "completed",
        "total_rows": 500,
        "processed_rows": 500,
        "successful_rows": 495,
        "failed_rows": 5,
        "created_at": "2026-04-20T14:30:00.000000Z",
        "completed_at": "2026-04-20T14:45:00.000000Z"
      },
      {
        "id": 2,
        "filename": "products_batch_2.csv",
        "status": "processing",
        "total_rows": 1000,
        "processed_rows": 650,
        "successful_rows": 640,
        "failed_rows": 10,
        "created_at": "2026-04-20T13:00:00.000000Z",
        "completed_at": null
      }
    ],
    "per_page": 15,
    "total": 2
  }
}
```

**Example:**
```bash
curl -X GET "https://your-domain.com/api/admin/products/import?status=completed&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 7. Cancel Import

**Endpoint:** `POST /api/admin/products/import/{id}/cancel`

**Description:** Cancel a pending or in-progress import.

**Authentication:** Required (Admin or Import Owner)

**Response:**
```json
{
  "success": true,
  "message": "Import cancelled successfully."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Can only cancel imports that are pending or in progress."
}
```

**Example:**
```bash
curl -X POST "https://your-domain.com/api/admin/products/import/1/cancel" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 8. Delete Import

**Endpoint:** `DELETE /api/admin/products/import/{id}`

**Description:** Delete an import record and its associated file.

**Authentication:** Required (Admin or Import Owner)

**Response:**
```json
{
  "success": true,
  "message": "Import deleted successfully."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Cannot delete an import that is in progress. Cancel it first."
}
```

**Example:**
```bash
curl -X DELETE "https://your-domain.com/api/admin/products/import/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## CSV File Format

### Required Columns

| Column | Type | Description | Example |
|--------|------|-------------|---------|
| `name` | string | Product name (max 255 chars) | "Treadmill Pro 3000" |
| `category_id` | integer | Category ID (must exist) | 5 |
| `price` | decimal | Product price | 1299.99 |

### Optional Columns

| Column | Type | Description | Example |
|--------|------|-------------|---------|
| `sku` | string | Product SKU (auto-generated if empty) | "TM-3000" |
| `brand_id` | integer | Brand ID | 3 |
| `model_id` | integer | Product model ID | 12 |
| `shipping_class_id` | integer | Shipping class ID | 2 |
| `short_description` | text | Short description | "High-performance treadmill" |
| `description` | text | Full description | "Professional grade..." |
| `compare_price` | decimal | Original price for comparison | 1599.99 |
| `cost_price` | decimal | Cost price | 899.99 |
| `quantity` | integer | Stock quantity | 25 |
| `low_stock_threshold` | integer | Low stock alert threshold | 5 |
| `weight` | decimal | Product weight | 85.5 |
| `weight_unit` | enum | Weight unit (g, kg, lb) | "kg" |
| `length` | decimal | Length dimension | 180 |
| `width` | decimal | Width dimension | 80 |
| `height` | decimal | Height dimension | 150 |
| `shipping_type` | enum | Shipping type (default, free, fixed, per_item) | "default" |
| `shipping_cost` | decimal | Custom shipping cost | 50.00 |
| `requires_shipping` | boolean | Requires shipping (1/0) | 1 |
| `separate_shipping` | boolean | Ships separately (1/0) | 0 |
| `shipping_notes` | text | Shipping notes | "Fragile item" |
| `is_featured` | boolean | Featured product (1/0) | 1 |
| `is_trending` | boolean | Trending product (1/0) | 0 |
| `kinomap` | boolean | Kinomap compatible (1/0) | 0 |
| `status` | enum | Status (active, inactive, draft) | "active" |
| `meta_title` | string | SEO title | "Best Treadmill 2026" |
| `meta_description` | text | SEO description | "Buy the best..." |
| `meta_keywords` | string | SEO keywords | "treadmill,fitness,gym" |
| `is_preorder` | boolean | Preorder product (1/0) | 0 |
| `preorder_release_date` | date | Preorder release date | "2026-05-01" |
| `preorder_limit` | integer | Preorder quantity limit | 100 |
| `preorder_deposit_amount` | decimal | Preorder deposit | 200.00 |
| `preorder_deposit_type` | enum | Deposit type (percentage, fixed) | "fixed" |

### Image Columns (1-10)

| Column | Type | Description | Example |
|--------|------|-------------|---------|
| `image_1` to `image_10` | string | Image URL or path | "https://cdn.example.com/img1.jpg" |
| `image_1_alt` to `image_10_alt` | string | Image alt text | "Product front view" |

**Note:** First image (`image_1`) is automatically set as primary.

### Variation Columns (1-10)

| Column | Type | Description | Example |
|--------|------|-------------|---------|
| `variation_1_sku` to `variation_10_sku` | string | Variation SKU | "TM-3000-V1" |
| `variation_1_attributes` to `variation_10_attributes` | string | Attributes (format: `key:value\|key:value`) | "color:Black\|warranty:2 Years" |
| `variation_1_price` to `variation_10_price` | decimal | Variation price | 1299.99 |
| `variation_1_quantity` to `variation_10_quantity` | integer | Variation stock | 15 |

**Note:** First variation is automatically set as default.

---

## Sample CSV

```csv
name,sku,category_id,brand_id,price,compare_price,quantity,description,short_description,status,is_featured,weight,weight_unit,image_1,image_2,variation_1_sku,variation_1_attributes,variation_1_price,variation_1_quantity
"Treadmill Pro 3000","TM-3000",5,3,1299.99,1599.99,25,"Professional grade treadmill","High-performance treadmill","active",1,85.5,"kg","https://cdn.example.com/tm3000-1.jpg","https://cdn.example.com/tm3000-2.jpg","TM-3000-V1","color:Black|warranty:2 Years",1299.99,15
"Yoga Mat Premium","YM-PREM",8,7,49.99,69.99,150,"Eco-friendly premium yoga mat","Premium yoga mat","active",0,1.2,"kg","https://cdn.example.com/yoga-mat.jpg","","","","",""
```

---

## Error Handling

### Validation Errors

Rows with validation errors are skipped and logged. The import continues processing remaining rows.

**Common Validation Errors:**
- Missing required fields (name, category_id, price)
- Invalid data types (non-numeric price, invalid category_id)
- Invalid enum values (status, weight_unit)
- Foreign key violations (non-existent category_id, brand_id)

### Database Errors

Database errors (duplicate SKU, constraint violations) are caught and logged per row.

**Common Database Errors:**
- Duplicate SKU
- Foreign key constraint failures
- Data too long for column

### Import-Level Errors

Critical errors that stop the entire import:
- File not found
- Invalid CSV format
- File too large (>10MB)
- Memory exhaustion

---

## Best Practices

### 1. File Preparation
- ✅ Use UTF-8 encoding
- ✅ Validate data before upload
- ✅ Test with small batch first (10-50 rows)
- ✅ Ensure category_id and brand_id exist
- ✅ Use unique SKUs

### 2. Image Handling
- ✅ Use publicly accessible URLs
- ✅ Verify images are accessible before import
- ✅ Use CDN URLs for better performance
- ✅ Optimize images before upload (recommended: <500KB per image)

### 3. Large Imports
- ✅ Split into multiple files (500-1000 rows each)
- ✅ Import during off-peak hours
- ✅ Monitor queue workers
- ✅ Ensure sufficient disk space

### 4. Error Recovery
- ✅ Download error report
- ✅ Fix errors in original CSV
- ✅ Re-import only failed rows
- ✅ Verify successful imports

---

## Queue Configuration

The import system uses Laravel queues. Ensure queue workers are running:

```bash
# Start queue worker
php artisan queue:work --queue=default --tries=3 --timeout=3600

# For production (with supervisor)
php artisan queue:work --queue=default --tries=3 --timeout=3600 --sleep=3 --max-jobs=1000
```

### Supervisor Configuration

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=default --tries=3 --timeout=3600
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

---

## Performance Metrics

### Expected Processing Speed

| Products | Estimated Time | Notes |
|----------|---------------|-------|
| 100 | 1-2 minutes | Without variations |
| 500 | 5-10 minutes | Without variations |
| 1000 | 10-20 minutes | Without variations |
| 2500 | 25-50 minutes | Without variations |
| 5000 | 50-100 minutes | Without variations |

**Factors affecting speed:**
- Number of variations per product
- Number of images per product
- Server resources
- Database performance
- Number of queue workers

---

## Troubleshooting

### Import Stuck in "Pending"
- Check if queue workers are running
- Check queue connection in `.env`
- Restart queue workers

### Import Failed
- Check error message in import status
- Review Laravel logs: `storage/logs/laravel.log`
- Check database connection
- Verify disk space

### Slow Processing
- Increase queue workers
- Optimize database indexes
- Increase PHP memory limit
- Use Redis for queue driver

### Memory Issues
- Reduce chunk size in job (default: 100)
- Increase PHP memory limit
- Split large files into smaller batches

---

## Security Considerations

- ✅ Admin-only access enforced
- ✅ File type validation (CSV only)
- ✅ File size limits (10MB max)
- ✅ Input sanitization
- ✅ Foreign key validation
- ✅ Transaction-based processing
- ✅ Error logging without sensitive data

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review import errors via API
3. Contact system administrator

---

## Changelog

### Version 1.0.0 (2026-04-20)
- Initial release
- CSV import with background processing
- Progress tracking
- Error reporting
- Support for products, images, and variations
