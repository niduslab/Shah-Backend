# Product Images Migration Guide

## Overview
This guide helps you migrate from the old product images system to the new professional multi-image management system.

## What Changed

### Database Schema
**Old Structure:**
- Column: `image` (ambiguous naming)
- No indexes for performance
- Basic functionality

**New Structure:**
- Column: `image_path` (clear, descriptive)
- Indexed for fast queries (`product_id` + `is_primary`, `product_id` + `sort_order`)
- Professional e-commerce features

### Model Changes
**Old ProductImage Model:**
```php
protected $fillable = ['product_id', 'image_id']; // Referenced non-existent Image model
```

**New ProductImage Model:**
```php
protected $fillable = [
    'product_id',
    'image_path',
    'alt_text',
    'is_primary',
    'sort_order',
];
```

### New Features
- ✅ Multiple images per product (up to 10)
- ✅ Primary image designation
- ✅ Image ordering/sorting
- ✅ Alt text for SEO and accessibility
- ✅ Full URL generation
- ✅ Comprehensive CRUD operations
- ✅ Automatic primary image management
- ✅ Image reordering API

## Migration Steps

### Step 1: Backup Your Database
```bash
# MySQL
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# PostgreSQL
pg_dump database_name > backup_$(date +%Y%m%d).sql
```

### Step 2: Check Current Data
```sql
-- See current product images
SELECT * FROM product_images LIMIT 10;

-- Count images per product
SELECT product_id, COUNT(*) as image_count 
FROM product_images 
GROUP BY product_id 
ORDER BY image_count DESC;
```

### Step 3: Run Migration

#### Option A: Fresh Migration (Development Only)
```bash
# Drop and recreate table
php artisan migrate:refresh --path=/database/migrations/2024_07_09_070935_create_product_images_table.php
```
⚠️ **Warning:** This deletes all existing data!

#### Option B: Update Existing Table (Production)
Create a new migration file:

```bash
php artisan make:migration update_product_images_table_structure
```

Edit the migration file:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Rename column if it exists as 'image'
            if (Schema::hasColumn('product_images', 'image')) {
                $table->renameColumn('image', 'image_path');
            }
            
            // Add new columns if they don't exist
            if (!Schema::hasColumn('product_images', 'alt_text')) {
                $table->string('alt_text', 255)->nullable()->after('image_path');
            }
            
            if (!Schema::hasColumn('product_images', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('alt_text');
            }
            
            if (!Schema::hasColumn('product_images', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_primary');
            }
            
            // Add indexes for performance
            $table->index(['product_id', 'is_primary'], 'idx_product_primary');
            $table->index(['product_id', 'sort_order'], 'idx_product_sort');
        });
        
        // Set first image of each product as primary
        DB::statement("
            UPDATE product_images pi1
            SET is_primary = 1
            WHERE id = (
                SELECT MIN(id) 
                FROM product_images pi2 
                WHERE pi2.product_id = pi1.product_id
            )
        ");
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('idx_product_primary');
            $table->dropIndex('idx_product_sort');
            
            if (Schema::hasColumn('product_images', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            
            if (Schema::hasColumn('product_images', 'is_primary')) {
                $table->dropColumn('is_primary');
            }
            
            if (Schema::hasColumn('product_images', 'alt_text')) {
                $table->dropColumn('alt_text');
            }
            
            if (Schema::hasColumn('product_images', 'image_path')) {
                $table->renameColumn('image_path', 'image');
            }
        });
    }
};
```

Run the migration:
```bash
php artisan migrate
```

### Step 4: Update Existing Data

#### Set Primary Images
```sql
-- Set first image of each product as primary
UPDATE product_images pi1
SET is_primary = 1
WHERE id = (
    SELECT MIN(id) 
    FROM product_images pi2 
    WHERE pi2.product_id = pi1.product_id
);
```

#### Set Sort Orders
```sql
-- Set sort order based on creation date
SET @row_number = 0;
SET @current_product = 0;

UPDATE product_images
SET sort_order = (
    SELECT @row_number := IF(@current_product = product_id, @row_number + 1, 0) AS rn,
           @current_product := product_id
    FROM (SELECT @row_number := 0, @current_product := 0) AS vars
)
ORDER BY product_id, created_at;
```

Or use PHP script:
```php
use App\Models\Product;

Product::with('images')->chunk(100, function ($products) {
    foreach ($products as $product) {
        $product->images()->orderBy('id')->get()->each(function ($image, $index) {
            $image->update(['sort_order' => $index]);
        });
    }
});
```

### Step 5: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 6: Test the System

#### Test Product Creation
```bash
curl -X POST http://localhost/api/admin/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "category_id": 1,
    "price": 99.99,
    "images": [
      {"path": "test1.jpg", "alt_text": "Test 1", "is_primary": true},
      {"path": "test2.jpg", "alt_text": "Test 2"}
    ]
  }'
```

#### Test Image Operations
```bash
# Add images
curl -X POST http://localhost/api/admin/products/1/images \
  -H "Content-Type: application/json" \
  -d '{"images": [{"path": "test3.jpg", "alt_text": "Test 3"}]}'

# Set primary
curl -X POST http://localhost/api/admin/products/1/images/2/set-primary

# Reorder
curl -X POST http://localhost/api/admin/products/1/images/reorder \
  -H "Content-Type: application/json" \
  -d '{"image_ids": [2, 1, 3]}'
```

### Step 7: Update Frontend Code

#### Old Code
```javascript
// Old way - single image or unclear structure
product.image
```

#### New Code
```javascript
// New way - clear structure with multiple images
product.primary_image_url  // Primary image URL
product.image_urls         // Array of all image URLs
product.images             // Full image objects with metadata

// Display primary image
<img src={product.primary_image_url} alt={product.name} />

// Display image gallery
{product.images.map(image => (
  <img 
    key={image.id}
    src={image.full_url} 
    alt={image.alt_text || product.name}
    className={image.is_primary ? 'primary' : ''}
  />
))}
```

## Rollback Plan

If you need to rollback:

### Step 1: Restore Database Backup
```bash
# MySQL
mysql -u username -p database_name < backup_YYYYMMDD.sql

# PostgreSQL
psql database_name < backup_YYYYMMDD.sql
```

### Step 2: Revert Code Changes
```bash
git revert <commit-hash>
```

### Step 3: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
```

## Troubleshooting

### Issue: Column 'image_path' not found
**Solution:** Run the migration again or manually rename column:
```sql
ALTER TABLE product_images CHANGE image image_path VARCHAR(500);
```

### Issue: No primary image set
**Solution:** Run the primary image update query:
```sql
UPDATE product_images pi1
SET is_primary = 1
WHERE id = (SELECT MIN(id) FROM product_images pi2 WHERE pi2.product_id = pi1.product_id);
```

### Issue: Images not ordered correctly
**Solution:** Reset sort orders:
```php
Product::with('images')->each(function ($product) {
    $product->images->each(function ($image, $index) {
        $image->update(['sort_order' => $index]);
    });
});
```

### Issue: Full URL not generating
**Solution:** Check storage link:
```bash
php artisan storage:link
```

Verify `.env` configuration:
```env
FILESYSTEM_DISK=public
APP_URL=https://your-domain.com
```

## Verification Checklist

- [ ] Database backup created
- [ ] Migration ran successfully
- [ ] All products have primary images
- [ ] Sort orders are set correctly
- [ ] Indexes created for performance
- [ ] API endpoints working
- [ ] Frontend displaying images correctly
- [ ] Image upload working
- [ ] Image reordering working
- [ ] Image deletion working
- [ ] Alt text displaying properly
- [ ] Full URLs generating correctly

## Performance Optimization

After migration, optimize performance:

```sql
-- Analyze table for query optimization
ANALYZE TABLE product_images;

-- Check index usage
SHOW INDEX FROM product_images;

-- Optimize table
OPTIMIZE TABLE product_images;
```

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable query logging to debug database issues
3. Verify all files are updated (migration, model, controller, service)
4. Ensure routes are registered correctly
5. Clear all caches

## Next Steps

After successful migration:
1. Update API documentation for clients
2. Train team on new image management features
3. Update frontend to use new image structure
4. Consider implementing image optimization
5. Set up CDN for image delivery
6. Implement image upload validation
7. Add image compression on upload
