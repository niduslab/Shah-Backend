# Queue Worker Setup Guide for Live Server

## Prerequisites
- SSH access to your server
- Sudo/root privileges
- Laravel application deployed

---

## Option 1: Database Queue (Recommended - Simple & Reliable)

### Step 1: Configure Laravel

1. **Update `.env` file:**
```bash
QUEUE_CONNECTION=database
```

2. **Create jobs table:**
```bash
cd /path/to/your/project
php artisan queue:table
php artisan migrate
```

3. **Create failed jobs table (optional but recommended):**
```bash
php artisan queue:failed-table
php artisan migrate
```

### Step 2: Install Supervisor

**For Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install supervisor
```

**For CentOS/RHEL:**
```bash
sudo yum install supervisor
sudo systemctl enable supervisord
```

### Step 3: Configure Supervisor

1. **Create supervisor config file:**
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

2. **Add this configuration** (replace paths with your actual paths):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/ecommerce_backend_with-admin/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/ecommerce_backend_with-admin/storage/logs/worker.log
stopwaitsecs=3600
```

**Configuration Explanation:**
- `command`: The actual command to run the queue worker
- `autostart=true`: Start automatically when supervisor starts
- `autorestart=true`: Restart if the worker crashes
- `user=www-data`: Run as web server user (change if different)
- `numprocs=2`: Run 2 worker processes (adjust based on load)
- `max-time=3600`: Restart worker every hour (prevents memory leaks)
- `timeout=120`: Job timeout in seconds

3. **Update Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Step 4: Verify Workers are Running

```bash
# Check supervisor status
sudo supervisorctl status

# You should see:
# laravel-worker:laravel-worker_00   RUNNING   pid 12345, uptime 0:00:10
# laravel-worker:laravel-worker_01   RUNNING   pid 12346, uptime 0:00:10
```

### Step 5: Managing Queue Workers

```bash
# Start workers
sudo supervisorctl start laravel-worker:*

# Stop workers
sudo supervisorctl stop laravel-worker:*

# Restart workers (do this after code deployment)
sudo supervisorctl restart laravel-worker:*

# View worker logs
tail -f /var/www/html/ecommerce_backend_with-admin/storage/logs/worker.log
```

---

## Option 2: Redis Queue (Best Performance)

### Step 1: Install Redis

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

**CentOS/RHEL:**
```bash
sudo yum install redis
sudo systemctl enable redis
sudo systemctl start redis
```

### Step 2: Install PHP Redis Extension

```bash
sudo apt-get install php-redis
# or
sudo pecl install redis
```

### Step 3: Configure Laravel

1. **Update `.env`:**
```bash
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

2. **Update supervisor config:**
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Change the command line to:
```ini
command=php /var/www/html/ecommerce_backend_with-admin/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=120
```

3. **Restart supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-worker:*
```

---

## Deployment Workflow

### After Each Code Deployment:

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php artisan migrate --force

# 4. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Restart queue workers (IMPORTANT!)
sudo supervisorctl restart laravel-worker:*
```

**Why restart workers?** Queue workers are long-lived processes. They won't pick up code changes until restarted.

---

## Monitoring & Maintenance

### Check Queue Status

```bash
# View queued jobs count
php artisan queue:monitor

# View failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Retry specific failed job
php artisan queue:retry 5

# Delete all failed jobs
php artisan queue:flush
```

### Monitor Worker Logs

```bash
# Real-time log monitoring
tail -f storage/logs/worker.log

# Check Laravel logs
tail -f storage/logs/laravel.log
```

### Performance Monitoring

```bash
# Check Redis queue size (if using Redis)
redis-cli LLEN queues:default

# Check database queue size (if using database)
mysql -u username -p database_name -e "SELECT COUNT(*) FROM jobs;"
```

---

## Troubleshooting

### Workers Not Starting

```bash
# Check supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log

# Check worker logs
tail -f storage/logs/worker.log

# Verify permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

### Jobs Not Processing

```bash
# Check if workers are running
sudo supervisorctl status

# Check queue connection in .env
cat .env | grep QUEUE_CONNECTION

# Test queue manually
php artisan queue:work --once

# Check for failed jobs
php artisan queue:failed
```

### High Memory Usage

```bash
# Reduce max-time in supervisor config
# Change from 3600 to 1800 (30 minutes)
command=php artisan queue:work database --sleep=3 --tries=3 --max-time=1800

# Restart supervisor
sudo supervisorctl restart laravel-worker:*
```

---

## Cron Job for Scheduled Tasks

Don't forget to set up Laravel's scheduler:

```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /var/www/html/ecommerce_backend_with-admin && php artisan schedule:run >> /dev/null 2>&1
```

---

## Production Best Practices

1. **Use Database Queue** for simplicity and reliability
2. **Run at least 2 workers** for redundancy
3. **Monitor failed jobs** regularly
4. **Restart workers** after every deployment
5. **Set up alerts** for worker failures
6. **Keep logs** for debugging
7. **Use max-time** to prevent memory leaks
8. **Test queue** in staging before production

---

## Quick Reference Commands

```bash
# Supervisor
sudo supervisorctl status                    # Check status
sudo supervisorctl restart laravel-worker:*  # Restart workers
sudo supervisorctl tail laravel-worker       # View logs

# Laravel Queue
php artisan queue:work                       # Start worker manually
php artisan queue:failed                     # List failed jobs
php artisan queue:retry all                  # Retry failed jobs
php artisan queue:flush                      # Clear failed jobs

# Monitoring
tail -f storage/logs/worker.log              # Worker logs
tail -f storage/logs/laravel.log             # Laravel logs
```

---

## Testing Before Going Live

```bash
# 1. Test queue locally
php artisan queue:work --once

# 2. Dispatch a test job
php artisan tinker
>>> App\Jobs\GenerateInvoiceJob::dispatch(App\Models\Order::first());

# 3. Check if job processed
tail -f storage/logs/laravel.log
```

---

## Support

If you encounter issues:
1. Check supervisor logs: `/var/log/supervisor/supervisord.log`
2. Check worker logs: `storage/logs/worker.log`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify queue connection in `.env`
5. Ensure database/Redis is running
