# Product Import API - Quick Reference

## Base URL
```
http://localhost:8000/api/admin/products/import
```

**Important:** All routes are under `/api/admin/products/import` (not `/admin/products/import`)

---

## Authentication
All endpoints require:
- **Header:** `Authorization: Bearer {token}`
- **Middleware:** `auth:sanctum` + `admin`

---

## API Endpoints

### 1. Download CSV Template

**GET** `/api/admin/products/import/template`

**Response:** CSV file download

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output template.csv
```

---

### 2. Upload CSV File

**POST** `/api/admin/products/import/upload`

**Content-Type:** `multipart/form-data`

**Request Body:**
```
file: [CSV file]
```

**Success Response (201):**
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
curl -X POST "http://localhost:8000/api/admin/products/import/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@products.csv"
```

**JavaScript Example:**
```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);

fetch('http://localhost:8000/api/admin/products/import/upload', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

---

### 3. Get Import Status

**GET** `/api/admin/products/import/{id}`

**Success Response (200):**
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
    "started_at": "2026-04-21T10:30:00.000000Z",
    "completed_at": null,
    "created_at": "2026-04-21T10:29:45.000000Z"
  }
}
```

**Status Values:**
- `pending` - Queued, not started
- `processing` - Currently importing
- `completed` - Finished successfully
- `failed` - Critical error occurred
- `cancelled` - User cancelled

**Error Response (404):**
```json
{
  "success": false,
  "message": "Import not found."
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 4. Get Import Errors

**GET** `/api/admin/products/import/{id}/errors`

**Success Response (200):**
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
          "SKU already exists in database"
        ]
      },
      {
        "row": 45,
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
curl -X GET "http://localhost:8000/api/admin/products/import/1/errors" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 5. Export Error Report (CSV)

**GET** `/api/admin/products/import/{id}/export-errors`

**Response:** CSV file download

**CSV Format:**
```csv
Row Number,Errors
15,"Category ID is required; Price must be a positive number"
23,"SKU already exists in database"
45,"Database error: Duplicate entry 'SKU-123' for key 'products.sku'"
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/1/export-errors" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output errors.csv
```

---

### 6. List All Imports

**GET** `/api/admin/products/import`

**Query Parameters:**
- `status` (optional): Filter by status (pending, processing, completed, failed, cancelled)
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 3,
        "user_id": 1,
        "filename": "products_batch_3.csv",
        "file_path": "product_imports/1713700000_products_batch_3.csv",
        "status": "completed",
        "total_rows": 500,
        "processed_rows": 500,
        "successful_rows": 495,
        "failed_rows": 5,
        "errors": [...],
        "error_message": null,
        "started_at": "2026-04-21T14:30:00.000000Z",
        "completed_at": "2026-04-21T14:45:00.000000Z",
        "created_at": "2026-04-21T14:29:00.000000Z",
        "updated_at": "2026-04-21T14:45:00.000000Z"
      },
      {
        "id": 2,
        "user_id": 1,
        "filename": "products_batch_2.csv",
        "file_path": "product_imports/1713696000_products_batch_2.csv",
        "status": "processing",
        "total_rows": 1000,
        "processed_rows": 650,
        "successful_rows": 640,
        "failed_rows": 10,
        "errors": [...],
        "error_message": null,
        "started_at": "2026-04-21T13:00:00.000000Z",
        "completed_at": null,
        "created_at": "2026-04-21T12:59:00.000000Z",
        "updated_at": "2026-04-21T13:15:00.000000Z"
      }
    ],
    "first_page_url": "http://localhost:8000/api/admin/products/import?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/admin/products/import?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost:8000/api/admin/products/import",
    "per_page": 15,
    "prev_page_url": null,
    "to": 2,
    "total": 2
  }
}
```

**Example:**
```bash
# All imports
curl -X GET "http://localhost:8000/api/admin/products/import" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by status
curl -X GET "http://localhost:8000/api/admin/products/import?status=completed" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Pagination
curl -X GET "http://localhost:8000/api/admin/products/import?per_page=20&page=2" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 7. Cancel Import

**POST** `/api/admin/products/import/{id}/cancel`

**Success Response (200):**
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
curl -X POST "http://localhost:8000/api/admin/products/import/1/cancel" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 8. Delete Import

**DELETE** `/api/admin/products/import/{id}`

**Success Response (200):**
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
curl -X DELETE "http://localhost:8000/api/admin/products/import/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Complete Workflow Example

### Step 1: Download Template
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output template.csv
```

### Step 2: Fill Template with Data
Edit `template.csv` with your product data

### Step 3: Upload CSV
```bash
curl -X POST "http://localhost:8000/api/admin/products/import/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@products.csv"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "import_id": 1,
    "total_rows": 2500
  }
}
```

### Step 4: Monitor Progress (Poll every 5 seconds)
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "processing",
    "progress_percentage": 45.5,
    "processed_rows": 1137,
    "successful_rows": 1100,
    "failed_rows": 37
  }
}
```

### Step 5: Check Errors (if any)
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/1/errors" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Step 6: Export Error Report
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/1/export-errors" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output errors.csv
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized access."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Import not found."
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The file must be a file of type: csv, txt.",
  "errors": {
    "file": [
      "The file must be a file of type: csv, txt."
    ]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to start import: [error details]"
}
```

---

## Frontend Integration Example

### React/Vue Component

```javascript
import axios from 'axios';

const API_BASE = 'http://localhost:8000/api/admin/products/import';
const token = localStorage.getItem('auth_token');

// Upload CSV
async function uploadCSV(file) {
  const formData = new FormData();
  formData.append('file', file);
  
  try {
    const response = await axios.post(`${API_BASE}/upload`, formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });
    
    return response.data;
  } catch (error) {
    console.error('Upload failed:', error.response.data);
    throw error;
  }
}

// Monitor progress
async function checkProgress(importId) {
  try {
    const response = await axios.get(`${API_BASE}/${importId}`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    return response.data.data;
  } catch (error) {
    console.error('Status check failed:', error);
    throw error;
  }
}

// Poll progress every 5 seconds
function pollProgress(importId, callback) {
  const interval = setInterval(async () => {
    const status = await checkProgress(importId);
    callback(status);
    
    if (['completed', 'failed', 'cancelled'].includes(status.status)) {
      clearInterval(interval);
    }
  }, 5000);
  
  return interval;
}

// Usage
const fileInput = document.getElementById('csvFile');
fileInput.addEventListener('change', async (e) => {
  const file = e.target.files[0];
  
  // Upload
  const result = await uploadCSV(file);
  console.log('Import started:', result.data.import_id);
  
  // Monitor
  pollProgress(result.data.import_id, (status) => {
    console.log(`Progress: ${status.progress_percentage}%`);
    console.log(`Success: ${status.successful_rows}, Failed: ${status.failed_rows}`);
  });
});
```

---

## Testing with Postman

### 1. Set Environment Variables
- `base_url`: `http://localhost:8000`
- `token`: Your auth token

### 2. Upload CSV
- **Method:** POST
- **URL:** `{{base_url}}/api/admin/products/import/upload`
- **Headers:**
  - `Authorization`: `Bearer {{token}}`
  - `Accept`: `application/json`
- **Body:** form-data
  - Key: `file`
  - Type: File
  - Value: Select your CSV file

### 3. Check Status
- **Method:** GET
- **URL:** `{{base_url}}/api/admin/products/import/1`
- **Headers:**
  - `Authorization`: `Bearer {{token}}`

---

## Common Issues

### Issue: 404 Not Found on OPTIONS request
**Cause:** CORS preflight failing or wrong URL

**Solution:**
1. Ensure URL is `/api/admin/products/import` (not `/admin/products/import`)
2. Check CORS configuration (see below)

### Issue: 401 Unauthenticated
**Cause:** Missing or invalid token

**Solution:**
```bash
# Get token first
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### Issue: File upload fails
**Cause:** Wrong Content-Type or file size

**Solution:**
- Use `multipart/form-data`
- Check file size < 10MB
- Ensure file is CSV format

---

## Rate Limiting

No rate limiting applied to import endpoints (admin only).

---

## Notes

- All timestamps are in ISO 8601 format (UTC)
- File uploads limited to 10MB
- CSV must have header row
- Maximum 1000 errors stored per import
- Import files stored in `storage/app/product_imports/`
- Queue must be running for background processing
