# Bulk Product Import - Setup Instructions

## Quick Setup Guide

Follow these steps to set up the bulk product import system.

---

## 1. Install Dependencies

The system uses `league/csv` for CSV parsing. Install it via Composer:

```bash
composer require league/csv
```

---

## 2. Run Migrations

Create the `product_imports` table:

```bash
php artisan migrate
```

This will create the migration:
- `2026_04_20_000001_create_product_imports_table.php`

---

## 3. Configure Queue

### Update `.env` file

For development (sync queue):
```env
QUEUE_CONNECTION=sync
```

For production (database queue - recommended):
```env
QUEUE_CONNECTION=database
```

For production (Redis queue - best performance):
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Create Queue Tables (if using database queue)

```bash
php artisan queue:table
php artisan migrate
```

---

## 4. Start Queue Workers

### Development
```bash
php artisan queue:work
```

### Production (with Supervisor)

Create supervisor config: `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --queue=default --tries=3 --timeout=3600 --sleep=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 5. Storage Configuration

Ensure storage directory is writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Create product_imports directory:
```bash
mkdir -p storage/app/product_imports
chmod 775 storage/app/product_imports
```

---

## 6. Test the System

### Download Template
```bash
curl -X GET "http://your-domain.com/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output template.csv
```

### Upload Test File
```bash
curl -X POST "http://your-domain.com/api/admin/products/import/upload" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@template.csv"
```

### Check Status
```bash
curl -X GET "http://your-domain.com/api/admin/products/import/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 7. Verify Installation

### Check Files Created

✅ Migration: `database/migrations/2026_04_20_000001_create_product_imports_table.php`
✅ Model: `app/Models/ProductImport.php`
✅ Service: `app/Services/ProductImportService.php`
✅ Job: `app/Jobs/ProcessProductImport.php`
✅ Controller: `app/Http/Controllers/Api/Admin/ProductImportController.php`
✅ Routes: Added to `routes/api.php`

### Check Database

```sql
-- Verify table exists
SHOW TABLES LIKE 'product_imports';

-- Check table structure
DESCRIBE product_imports;
```

### Check Queue

```bash
# Check queue jobs
php artisan queue:work --once

# Monitor queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

---

## 8. Production Optimization

### PHP Configuration

Update `php.ini`:
```ini
memory_limit = 512M
max_execution_time = 3600
upload_max_filesize = 10M
post_max_size = 10M
```

### Database Indexes

Ensure indexes exist:
```sql
-- Check indexes on products table
SHOW INDEX FROM products;

-- Add indexes if missing
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_slug ON products(slug);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
```

### Laravel Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 9. Monitoring

### Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue worker logs (if using supervisor)
tail -f storage/logs/worker.log
```

### Monitor Queue

```bash
# Check queue size
php artisan queue:monitor

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## 10. Troubleshooting

### Issue: Queue not processing

**Solution:**
```bash
# Restart queue workers
php artisan queue:restart

# Or with supervisor
sudo supervisorctl restart laravel-worker:*
```

### Issue: Import stuck in "pending"

**Solution:**
```bash
# Check if queue workers are running
ps aux | grep "queue:work"

# Start queue worker
php artisan queue:work
```

### Issue: Memory exhausted

**Solution:**
1. Increase PHP memory limit in `php.ini`
2. Reduce chunk size in `ProcessProductImport` job
3. Split large CSV files into smaller batches

### Issue: File upload fails

**Solution:**
```bash
# Check storage permissions
ls -la storage/app/product_imports

# Fix permissions
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Issue: Database errors

**Solution:**
1. Check database connection in `.env`
2. Verify foreign keys exist (categories, brands)
3. Check for duplicate SKUs
4. Review Laravel logs

---

## 11. Testing Checklist

- [ ] Migration runs successfully
- [ ] Queue workers start without errors
- [ ] Template downloads correctly
- [ ] CSV file uploads successfully
- [ ] Import status updates in real-time
- [ ] Products are created in database
- [ ] Images are linked correctly
- [ ] Variations are created properly
- [ ] Errors are logged correctly
- [ ] Error export works
- [ ] Import can be cancelled
- [ ] Import can be deleted

---

## 12. Sample Test Data

Create a test CSV file `test_products.csv`:

```csv
name,sku,category_id,brand_id,price,quantity,status,image_1
"Test Product 1","TEST-001",1,1,99.99,10,"active","https://via.placeholder.com/400"
"Test Product 2","TEST-002",1,1,149.99,20,"active","https://via.placeholder.com/400"
"Test Product 3","TEST-003",1,1,199.99,30,"active","https://via.placeholder.com/400"
```

**Note:** Replace `category_id` and `brand_id` with actual IDs from your database.

---

## 13. Performance Tuning

### For Large Imports (5000+ products)

1. **Increase Queue Workers:**
```ini
# In supervisor config
numprocs=4  # Run 4 workers in parallel
```

2. **Use Redis Queue:**
```env
QUEUE_CONNECTION=redis
```

3. **Optimize Database:**
```sql
-- Add composite indexes
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_products_brand_status ON products(brand_id, status);
```

4. **Adjust Chunk Size:**
```php
// In ProcessProductImport job
ProcessProductImport::dispatch($import, 200); // Increase to 200
```

---

## 14. Security Checklist

- [ ] Admin middleware applied to all routes
- [ ] File type validation enforced
- [ ] File size limits configured
- [ ] Input sanitization enabled
- [ ] Foreign key validation active
- [ ] Error messages don't expose sensitive data
- [ ] File storage is outside public directory
- [ ] Queue jobs have timeout limits

---

## 15. Backup Strategy

Before running large imports:

```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup product images
tar -czf images_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/public/products
```

---

## 16. Next Steps

After setup is complete:

1. ✅ Test with small CSV file (10-20 products)
2. ✅ Verify products appear in database
3. ✅ Check images are accessible
4. ✅ Test error handling with invalid data
5. ✅ Monitor queue performance
6. ✅ Set up monitoring/alerting
7. ✅ Document any custom configurations
8. ✅ Train admin users on the system

---

## Support Resources

- **Documentation:** `BULK_PRODUCT_IMPORT_API.md`
- **Guide:** `BULK_PRODUCT_UPLOAD_GUIDE.md`
- **Laravel Queue Docs:** https://laravel.com/docs/queues
- **League CSV Docs:** https://csv.thephpleague.com/

---

## Quick Commands Reference

```bash
# Setup
composer require league/csv
php artisan migrate
php artisan queue:table
php artisan migrate

# Start queue
php artisan queue:work

# Monitor
php artisan queue:monitor
tail -f storage/logs/laravel.log

# Maintenance
php artisan queue:restart
php artisan queue:retry all
php artisan queue:flush

# Optimization
php artisan config:cache
php artisan route:cache
composer dump-autoload --optimize
```

---

## Completion Checklist

- [ ] Dependencies installed
- [ ] Migrations run
- [ ] Queue configured
- [ ] Workers running
- [ ] Storage configured
- [ ] Routes registered
- [ ] System tested
- [ ] Documentation reviewed
- [ ] Production optimized
- [ ] Monitoring setup

---

**Setup Complete!** 🎉

You can now upload CSV files with thousands of products through the admin panel.
