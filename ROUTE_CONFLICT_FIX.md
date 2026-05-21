# Route Conflict Fix - Import vs Products

## Problem

**Error:**
```
GET http://localhost:8000/api/admin/products/import
Status: 500 Internal Server Error

TypeError: App\Http\Controllers\Api\Admin\ProductController::show(): 
Argument #1 ($id) must be of type int, string given
```

**Root Cause:**
Route conflict! Laravel was matching `GET /api/admin/products/import` to `GET /api/admin/products/{id}` (from apiResource), treating "import" as a product ID.

---

## Solution Applied

### ✅ Fixed: Moved import routes BEFORE apiResource

**Before (Wrong Order):**
```php
// Products apiResource first
Route::apiResource('products', ProductController::class);
// Creates: GET /products/{id} ← This matches /products/import!

// Import routes second
Route::prefix('products/import')->group(function () {
    Route::get('/', ...); // Never reached!
});
```

**After (Correct Order):**
```php
// Import routes FIRST (more specific)
Route::prefix('products/import')->group(function () {
    Route::get('/', ...);
    Route::get('template', ...);
    Route::post('upload', ...);
    // ... other import routes
});

// Products apiResource SECOND (less specific)
Route::apiResource('products', ProductController::class);
// Creates: GET /products/{id}
```

---

## Why This Works

Laravel matches routes in the order they're defined. More specific routes must come before less specific ones.

**Route Matching Order:**
1. ✅ `GET /products/import` → Matches import index (specific)
2. ✅ `GET /products/import/template` → Matches template download (specific)
3. ✅ `GET /products/123` → Matches product show (less specific, uses {id})

**If order was wrong:**
1. ❌ `GET /products/import` → Matches `products/{id}` with id="import"
2. ❌ Controller expects int, gets string → TypeError

---

## Verification

### Check Routes
```bash
php artisan route:list --path=admin/products/import
```

**Output:**
```
GET|HEAD   api/admin/products/import
GET|HEAD   api/admin/products/import/template
POST       api/admin/products/import/upload
GET|HEAD   api/admin/products/import/{id}
DELETE     api/admin/products/import/{id}
POST       api/admin/products/import/{id}/cancel
GET|HEAD   api/admin/products/import/{id}/errors
GET|HEAD   api/admin/products/import/{id}/export-errors
```

### Test Endpoints
```bash
# 1. Test import list (should work now)
curl -X GET "http://localhost:8000/api/admin/products/import" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected: List of imports (not 500 error)

# 2. Test template download
curl -X GET "http://localhost:8000/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected: CSV file download

# 3. Test product show (should still work)
curl -X GET "http://localhost:8000/api/admin/products/1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected: Product details
```

---

## All Import Routes (Now Working)

| Method | URL | Purpose |
|--------|-----|---------|
| GET | `/api/admin/products/import` | List all imports |
| GET | `/api/admin/products/import/template` | Download CSV template |
| POST | `/api/admin/products/import/upload` | Upload CSV file |
| GET | `/api/admin/products/import/{id}` | Get import status |
| GET | `/api/admin/products/import/{id}/errors` | Get error details |
| GET | `/api/admin/products/import/{id}/export-errors` | Download error CSV |
| POST | `/api/admin/products/import/{id}/cancel` | Cancel import |
| DELETE | `/api/admin/products/import/{id}` | Delete import |

---

## Testing After Fix

### 1. List Imports
```bash
curl -X GET "http://localhost:8000/api/admin/products/import" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 15,
    "total": 0
  }
}
```

### 2. Download Template
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output template.csv
```

**Expected:** CSV file downloaded

### 3. Upload CSV
```bash
# Create test CSV
echo "name,category_id,price
Test Product,1,99.99" > test.csv

# Upload
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

## Common Route Conflicts to Avoid

### ❌ Bad Pattern
```php
// Generic route first
Route::get('products/{id}', ...);

// Specific route second (never reached!)
Route::get('products/import', ...);
```

### ✅ Good Pattern
```php
// Specific routes first
Route::get('products/import', ...);
Route::get('products/featured', ...);
Route::get('products/trending', ...);

// Generic route last
Route::get('products/{id}', ...);
```

---

## Laravel Route Matching Rules

1. **First Match Wins** - Routes are matched in order of definition
2. **Specific Before Generic** - Put specific paths before wildcard parameters
3. **Prefix Groups** - Use `Route::prefix()` for related routes
4. **Route Constraints** - Use `where()` to constrain parameters

### Example with Constraints
```php
// Only match numeric IDs
Route::get('products/{id}', ...)->where('id', '[0-9]+');

// Now this works even after
Route::get('products/import', ...);
```

---

## Files Modified

- ✅ `routes/api.php` - Reordered routes (import before apiResource)

**No other changes needed!**

---

## Rollback (If Needed)

If you need to revert:

```php
// Move import routes back after products
// (Not recommended - will cause the same error)
```

---

## Prevention

### Best Practices for Route Organization

```php
// 1. Static routes first
Route::get('products/import', ...);
Route::get('products/export', ...);
Route::get('products/featured', ...);

// 2. Parameterized routes second
Route::get('products/{id}', ...);

// 3. Or use apiResource last
Route::apiResource('products', ProductController::class);
```

### Use Route Groups
```php
// Group related routes
Route::prefix('products')->group(function () {
    // Static routes
    Route::get('import', ...);
    Route::get('export', ...);
    
    // Dynamic routes
    Route::get('{id}', ...);
});
```

---

## Summary

✅ **Problem:** Route conflict causing 500 error  
✅ **Cause:** Import routes defined after apiResource  
✅ **Solution:** Moved import routes before apiResource  
✅ **Result:** All routes now work correctly  

**No code changes needed - just route order!**

---

## Quick Reference

### Correct Route Order
```
1. products/import/* (specific)
2. products/{id} (generic)
```

### Test Commands
```bash
# Clear cache
php artisan route:clear

# List routes
php artisan route:list --path=admin/products

# Test endpoint
curl http://localhost:8000/api/admin/products/import \
  -H "Authorization: Bearer TOKEN"
```

---

**Status:** ✅ FIXED - All import routes now working correctly!
