# Import API Troubleshooting Guide

## ❌ Error: 404 Not Found on OPTIONS Request

### Your Error:
```
Request URL: http://localhost:8000/admin/products/import
Request Method: OPTIONS
Status Code: 404 Not Found
```

### Problem:
You're using the **wrong URL**. The routes are under `/api/admin/`, not `/admin/`.

### ✅ Solution:

**Wrong URL:**
```
❌ http://localhost:8000/admin/products/import
```

**Correct URL:**
```
✅ http://localhost:8000/api/admin/products/import/upload
```

---

## Complete URL Reference

| Endpoint | Correct URL |
|----------|-------------|
| Download Template | `http://localhost:8000/api/admin/products/import/template` |
| Upload CSV | `http://localhost:8000/api/admin/products/import/upload` |
| Get Status | `http://localhost:8000/api/admin/products/import/{id}` |
| Get Errors | `http://localhost:8000/api/admin/products/import/{id}/errors` |
| Export Errors | `http://localhost:8000/api/admin/products/import/{id}/export-errors` |
| List Imports | `http://localhost:8000/api/admin/products/import` |
| Cancel Import | `http://localhost:8000/api/admin/products/import/{id}/cancel` |
| Delete Import | `http://localhost:8000/api/admin/products/import/{id}/delete` |

---

## Quick Test

### 1. Test Route Exists
```bash
php artisan route:list | grep "products/import"
```

**Expected Output:**
```
GET|HEAD   api/admin/products/import ..................... admin
POST       api/admin/products/import/upload .............. admin
GET|HEAD   api/admin/products/import/template ............ admin
GET|HEAD   api/admin/products/import/{id} ................ admin
GET|HEAD   api/admin/products/import/{id}/errors ......... admin
GET|HEAD   api/admin/products/import/{id}/export-errors .. admin
POST       api/admin/products/import/{id}/cancel ......... admin
DELETE     api/admin/products/import/{id} ................ admin
```

### 2. Test Upload Endpoint
```bash
# Create test CSV
echo "name,category_id,price
Test Product,1,99.99" > test.csv

# Upload (replace YOUR_TOKEN)
curl -X POST "http://localhost:8000/api/admin/products/import/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test.csv"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Import started successfully. Processing in background.",
  "data": {
    "import_id": 1,
    "filename": "test.csv",
    "total_rows": 1,
    "status": "pending"
  }
}
```

---

## Common Issues & Solutions

### Issue 1: 404 Not Found
**Symptoms:**
- OPTIONS request returns 404
- POST request returns 404

**Causes:**
1. Wrong URL (missing `/api/`)
2. Routes not registered
3. Route cache outdated

**Solutions:**
```bash
# Check URL is correct
# ✅ http://localhost:8000/api/admin/products/import/upload
# ❌ http://localhost:8000/admin/products/import/upload

# Clear route cache
php artisan route:clear
php artisan route:cache

# Verify routes
php artisan route:list | grep import
```

---

### Issue 2: 401 Unauthenticated
**Symptoms:**
```json
{
  "message": "Unauthenticated."
}
```

**Cause:** Missing or invalid auth token

**Solution:**
```bash
# 1. Login to get token
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# Response:
{
  "token": "1|abc123...",
  "user": {...}
}

# 2. Use token in requests
curl -X POST "http://localhost:8000/api/admin/products/import/upload" \
  -H "Authorization: Bearer 1|abc123..." \
  -F "file=@products.csv"
```

---

### Issue 3: 403 Forbidden
**Symptoms:**
```json
{
  "success": false,
  "message": "Unauthorized access."
}
```

**Cause:** User is not admin

**Solution:**
```sql
-- Check user role
SELECT id, email, role FROM users WHERE email = 'your@email.com';

-- Update to admin
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
```

---

### Issue 4: CORS Error
**Symptoms:**
- Browser console shows CORS error
- OPTIONS request fails
- "Access-Control-Allow-Origin" missing

**Solution:**

**1. Check `.env` file:**
```env
FRONTEND_URL=http://localhost:3000,http://localhost:5173
```

**2. Update `config/cors.php`:**
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // For development only
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

**3. Clear config cache:**
```bash
php artisan config:clear
php artisan config:cache
```

**4. For production, use specific origins:**
```php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://admin.yourdomain.com'
],
```

---

### Issue 5: File Upload Fails
**Symptoms:**
```json
{
  "success": false,
  "message": "The file must be a file of type: csv, txt."
}
```

**Causes:**
1. Wrong file type
2. File too large
3. Wrong form field name

**Solutions:**

**1. Check file type:**
```bash
file products.csv
# Should show: CSV text
```

**2. Check file size:**
```bash
ls -lh products.csv
# Must be < 10MB
```

**3. Use correct field name:**
```javascript
// ✅ Correct
formData.append('file', fileInput.files[0]);

// ❌ Wrong
formData.append('csv', fileInput.files[0]);
```

**4. Check PHP upload limits:**
```ini
; php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

---

### Issue 6: Import Stuck in "Pending"
**Symptoms:**
- Status stays "pending"
- Never changes to "processing"
- No products created

**Cause:** Queue workers not running

**Solution:**
```bash
# Check if workers running
ps aux | grep "queue:work"

# Start queue worker
php artisan queue:work

# Check queue connection
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

### Issue 7: Import Fails Immediately
**Symptoms:**
- Status changes to "failed"
- Error message in response

**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Common errors:

# 1. File not found
# Solution: Check storage permissions
chmod -R 775 storage

# 2. Class not found (League\Csv\Reader)
# Solution: Install dependencies
composer update

# 3. Database error
# Solution: Check database connection in .env
```

---

## Frontend Integration Examples

### JavaScript/Fetch
```javascript
const API_BASE = 'http://localhost:8000/api/admin/products/import';
const token = 'YOUR_TOKEN';

// Upload CSV
async function uploadCSV(file) {
  const formData = new FormData();
  formData.append('file', file);
  
  const response = await fetch(`${API_BASE}/upload`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    },
    body: formData
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }
  
  return await response.json();
}

// Check status
async function checkStatus(importId) {
  const response = await fetch(`${API_BASE}/${importId}`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  return await response.json();
}

// Usage
document.getElementById('uploadBtn').addEventListener('click', async () => {
  const file = document.getElementById('csvFile').files[0];
  
  try {
    const result = await uploadCSV(file);
    console.log('Import started:', result.data.import_id);
    
    // Poll status
    const interval = setInterval(async () => {
      const status = await checkStatus(result.data.import_id);
      console.log('Progress:', status.data.progress_percentage + '%');
      
      if (status.data.status === 'completed') {
        clearInterval(interval);
        console.log('Import completed!');
      }
    }, 5000);
  } catch (error) {
    console.error('Upload failed:', error);
  }
});
```

### Axios
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/admin/products/import',
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

// Upload
const uploadCSV = async (file) => {
  const formData = new FormData();
  formData.append('file', file);
  
  const { data } = await api.post('/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  });
  
  return data;
};

// Check status
const checkStatus = async (importId) => {
  const { data } = await api.get(`/${importId}`);
  return data;
};
```

### React Hook
```javascript
import { useState } from 'react';
import axios from 'axios';

function useProductImport() {
  const [importing, setImporting] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState(null);
  
  const uploadCSV = async (file) => {
    setImporting(true);
    setError(null);
    
    try {
      const formData = new FormData();
      formData.append('file', file);
      
      const { data } = await axios.post(
        'http://localhost:8000/api/admin/products/import/upload',
        formData,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        }
      );
      
      // Poll progress
      const importId = data.data.import_id;
      const interval = setInterval(async () => {
        const status = await axios.get(
          `http://localhost:8000/api/admin/products/import/${importId}`,
          {
            headers: { 'Authorization': `Bearer ${token}` }
          }
        );
        
        setProgress(status.data.data.progress_percentage);
        
        if (['completed', 'failed', 'cancelled'].includes(status.data.data.status)) {
          clearInterval(interval);
          setImporting(false);
        }
      }, 5000);
      
    } catch (err) {
      setError(err.response?.data?.message || 'Upload failed');
      setImporting(false);
    }
  };
  
  return { uploadCSV, importing, progress, error };
}
```

---

## Debugging Checklist

- [ ] URL starts with `/api/admin/` (not `/admin/`)
- [ ] Auth token is valid and included
- [ ] User has admin role
- [ ] CORS is configured correctly
- [ ] File is CSV format and < 10MB
- [ ] Form field name is `file`
- [ ] Queue workers are running
- [ ] `league/csv` package is installed
- [ ] Routes are registered (`php artisan route:list`)
- [ ] Storage directory is writable

---

## Get Help

### Check System Status
```bash
# 1. Check routes
php artisan route:list | grep import

# 2. Check queue
php artisan queue:monitor

# 3. Check logs
tail -f storage/logs/laravel.log

# 4. Check permissions
ls -la storage/app/product_imports

# 5. Check dependencies
composer show league/csv
```

### Test Endpoints
```bash
# Test template download
curl -I "http://localhost:8000/api/admin/products/import/template"
# Should return: 200 OK

# Test upload (with valid token)
curl -X POST "http://localhost:8000/api/admin/products/import/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test.csv"
# Should return: 201 Created
```

---

## Quick Fix Summary

**Your Issue:** 404 on `/admin/products/import`

**Solution:** Change URL to `/api/admin/products/import/upload`

```javascript
// ❌ Wrong
fetch('http://localhost:8000/admin/products/import', ...)

// ✅ Correct
fetch('http://localhost:8000/api/admin/products/import/upload', ...)
```

That's it! The `/api/` prefix is required for all API routes.
