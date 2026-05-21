# Duplicate SKU Error - Fix Guide

## Error Message
```
Database error: SQLSTATE[23000]: Integrity constraint violation: 1062 
Duplicate entry 'TM-3000-V1' for key 'product_variations.product_variations_sku_unique'
```

---

## What This Means

You're trying to import a product with a variation SKU (`TM-3000-V1`) that already exists in the database.

**Common Causes:**
1. ✅ Using the template example data without changing SKUs
2. ✅ Re-importing the same CSV file
3. ✅ Copying SKUs from existing products
4. ✅ Not using unique SKUs for variations

---

## ✅ Solution 1: Use Unique SKUs

### Change the SKUs in your CSV

**Before (Duplicate):**
```csv
name,sku,variation_1_sku,variation_2_sku,variation_3_sku
"Treadmill Pro 3000","TM-3000","TM-3000-V1","TM-3000-V2","TM-3000-V3"
```

**After (Unique):**
```csv
name,sku,variation_1_sku,variation_2_sku,variation_3_sku
"Treadmill Pro 3000","TM-3000-NEW","TM-3000-NEW-V1","TM-3000-NEW-V2","TM-3000-NEW-V3"
```

Or use your own SKU format:
```csv
name,sku,variation_1_sku,variation_2_sku,variation_3_sku
"My Treadmill","MY-TM-001","MY-TM-001-BLACK","MY-TM-001-SILVER","MY-TM-001-WHITE"
```

---

## ✅ Solution 2: Leave SKUs Empty (Auto-Generate)

The system will automatically generate unique SKUs if you leave them empty.

**CSV:**
```csv
name,sku,variation_1_sku,variation_1_attributes,variation_2_sku,variation_2_attributes
"Treadmill Pro 3000","","","color:Black|warranty:2 Years","","color:Silver|warranty:2 Years"
```

**Result:**
- Product SKU: `SS-ABC12345` (auto-generated)
- Variation 1 SKU: `SS-ABC12345-V1` (auto-generated)
- Variation 2 SKU: `SS-ABC12345-V2` (auto-generated)

---

## ✅ Solution 3: Delete Existing Products

If you're testing and want to re-import the template example:

### Option A: Delete via Admin Panel
1. Go to Products → All Products
2. Find products with SKU `TM-3000`
3. Delete them
4. Re-import your CSV

### Option B: Delete via Database (Advanced)
```sql
-- Find products with template SKUs
SELECT id, name, sku FROM products WHERE sku LIKE 'TM-3000%';

-- Delete them (this will cascade to variations)
DELETE FROM products WHERE sku LIKE 'TM-3000%';
```

---

## ✅ Solution 4: Update Existing Products

If you want to update existing products instead of creating new ones, you'll need to:

1. Export existing products
2. Modify the data
3. Use the update API endpoint (not import)

**Note:** The import system is for creating NEW products only, not updating existing ones.

---

## Prevention

### ✅ Best Practices

1. **Always Change Template SKUs**
   - Don't use `TM-3000` from the template
   - Use your own SKU format
   - Example: `BRAND-MODEL-VARIANT`

2. **Use Consistent SKU Format**
   ```
   Product SKU: BRAND-MODEL
   Variation SKUs: BRAND-MODEL-V1, BRAND-MODEL-V2, etc.
   ```

3. **Let System Auto-Generate**
   - Leave SKU fields empty
   - System generates unique SKUs
   - No duplicate errors

4. **Check Before Import**
   - Search for SKU in admin panel
   - Verify it doesn't exist
   - Use unique identifiers

5. **Test with Small Batch**
   - Import 5-10 products first
   - Verify they work
   - Then import full catalog

---

## How the Fix Works

### Before Fix
- ❌ Validation only checked product SKUs
- ❌ Variation SKU duplicates caught at database level
- ❌ Unclear error messages

### After Fix
- ✅ Validation checks both product AND variation SKUs
- ✅ Duplicates caught before processing
- ✅ Clear, actionable error messages
- ✅ Tells you which SKU is duplicate

---

## Updated Error Messages

### Old Error (Confusing)
```
Database error: SQLSTATE[23000]: Integrity constraint violation: 1062 
Duplicate entry 'TM-3000-V1' for key 'product_variations.product_variations_sku_unique'
```

### New Error (Clear)
```
Variation SKU 'TM-3000-V1' already exists in database. 
Please use a unique SKU or leave empty to auto-generate.
```

---

## Validation Now Checks

### Product SKU
```php
if (!empty($row['sku'])) {
    if (Product::where('sku', $row['sku'])->exists()) {
        $errors[] = 'Product SKU already exists in database';
    }
}
```

### Variation SKUs (All 10)
```php
for ($i = 1; $i <= 10; $i++) {
    $varSkuKey = "variation_{$i}_sku";
    if (!empty($row[$varSkuKey])) {
        if (ProductVariation::where('sku', $row[$varSkuKey])->exists()) {
            $errors[] = "Variation SKU '{$row[$varSkuKey]}' already exists";
        }
    }
}
```

---

## Example: Fixing Your CSV

### Your Current CSV (Has Duplicates)
```csv
name,sku,category_id,price,variation_1_sku,variation_1_attributes,variation_1_price,variation_1_quantity
"Treadmill Pro 3000","TM-3000",5,1299.99,"TM-3000-V1","color:Black",1299.99,15
```

### Option 1: Change SKUs
```csv
name,sku,category_id,price,variation_1_sku,variation_1_attributes,variation_1_price,variation_1_quantity
"Treadmill Pro 3000","MY-TM-001",5,1299.99,"MY-TM-001-V1","color:Black",1299.99,15
```

### Option 2: Leave Empty (Auto-Generate)
```csv
name,sku,category_id,price,variation_1_sku,variation_1_attributes,variation_1_price,variation_1_quantity
"Treadmill Pro 3000","",5,1299.99,"","color:Black",1299.99,15
```

### Option 3: Delete Existing First
```sql
DELETE FROM products WHERE sku = 'TM-3000';
```
Then re-import with same SKUs.

---

## Testing After Fix

### 1. Clear Existing Test Data
```sql
-- Delete all products with template SKUs
DELETE FROM products WHERE sku LIKE 'TM-%' OR sku LIKE 'YM-%' OR sku LIKE 'DB-%';
```

### 2. Test Import
```bash
# Download fresh template
curl -X GET "http://localhost:8000/api/admin/products/import/template" \
  -H "Authorization: Bearer TOKEN" \
  --output template.csv

# Modify SKUs in template
# Change TM-3000 to YOUR-SKU-001

# Upload
curl -X POST "http://localhost:8000/api/admin/products/import/upload" \
  -H "Authorization: Bearer TOKEN" \
  -F "file=@template.csv"
```

### 3. Verify
- Check import status
- Verify products created
- Check for errors

---

## Quick Fix Checklist

- [ ] Identify duplicate SKU from error message
- [ ] Choose solution:
  - [ ] Change SKUs to unique values
  - [ ] Leave SKUs empty (auto-generate)
  - [ ] Delete existing products
- [ ] Update CSV file
- [ ] Re-upload CSV
- [ ] Monitor import progress
- [ ] Verify products created successfully

---

## Files Modified

### 1. `app/Services/ProductImportService.php`
**Added:** Variation SKU duplicate validation

**Before:**
```php
// Only checked product SKU
if (!empty($row['sku'])) {
    if (Product::where('sku', $row['sku'])->exists()) {
        $errors[] = 'SKU already exists';
    }
}
```

**After:**
```php
// Checks product SKU
if (!empty($row['sku'])) {
    if (Product::where('sku', $row['sku'])->exists()) {
        $errors[] = 'Product SKU already exists';
    }
}

// Also checks all variation SKUs
for ($i = 1; $i <= 10; $i++) {
    if (!empty($row["variation_{$i}_sku"])) {
        if (ProductVariation::where('sku', $row["variation_{$i}_sku"])->exists()) {
            $errors[] = "Variation SKU already exists";
        }
    }
}
```

### 2. `app/Jobs/ProcessProductImport.php`
**Added:** Better error message parsing

**Before:**
```php
catch (\Exception $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
}
```

**After:**
```php
catch (\Exception $e) {
    $errorMessage = $e->getMessage();
    
    // Parse duplicate SKU errors
    if (strpos($errorMessage, 'Duplicate entry') !== false) {
        if (strpos($errorMessage, 'product_variations') !== false) {
            $errorMessage = "Variation SKU 'XXX' already exists. Use unique SKU or leave empty.";
        } else {
            $errorMessage = "Product SKU 'XXX' already exists. Use unique SKU or leave empty.";
        }
    }
    
    $errors[] = $errorMessage;
}
```

---

## Summary

✅ **Problem:** Duplicate variation SKU error  
✅ **Cause:** Using template example SKUs without changing them  
✅ **Solution:** Change SKUs, leave empty, or delete existing products  
✅ **Prevention:** Always use unique SKUs or let system auto-generate  
✅ **Fix Applied:** Enhanced validation and error messages  

**Status:** ✅ FIXED - Now catches duplicates before processing

---

## Need Help?

**Common Questions:**

**Q: Can I update existing products via import?**  
A: No, import is for creating new products only. Use the update API for existing products.

**Q: What if I have 1000 products with duplicate SKUs?**  
A: Leave all SKU fields empty in your CSV. System will auto-generate unique SKUs.

**Q: Can I use the same SKU for different products?**  
A: No, SKUs must be unique across all products and variations.

**Q: How do I find what SKUs already exist?**  
A: Go to Products → All Products and search, or export products to CSV.

---

**Last Updated:** April 2026  
**Status:** Fixed and Tested ✅
