# Quick Start - Queue Workers on Live Server

## TL;DR - Fastest Setup

### Option 1: Automated Setup (Recommended)

```bash
# 1. Make script executable
chmod +x setup-queue-worker.sh

# 2. Run setup script
sudo ./setup-queue-worker.sh

# 3. Done! Workers are running
```

### Option 2: Manual Setup (5 minutes)

```bash
# 1. Install Supervisor
sudo apt-get update && sudo apt-get install -y supervisor

# 2. Update .env
nano .env
# Change: QUEUE_CONNECTION=sync
# To:     QUEUE_CONNECTION=database

# 3. Create queue tables
php artisan queue:table
php artisan queue:failed-table
php artisan migrate

# 4. Create supervisor config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Paste this (update paths):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/ecommerce_backend_with-admin/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/ecommerce_backend_with-admin/storage/logs/worker.log
```

```bash
# 5. Start workers
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# 6. Verify
sudo supervisorctl status
```

---

## Daily Commands

```bash
# Check if workers are running
sudo supervisorctl status

# Restart workers (after code deployment)
sudo supervisorctl restart laravel-worker:*

# View logs
tail -f storage/logs/worker.log

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## Deployment

Use the deployment script:

```bash
# Make executable
chmod +x deploy-with-queue.sh

# Run deployment
./deploy-with-queue.sh
```

Or manually:
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
sudo supervisorctl restart laravel-worker:*
```

---

## Troubleshooting

### Workers not running?
```bash
sudo supervisorctl status
sudo supervisorctl start laravel-worker:*
```

### Jobs not processing?
```bash
# Check .env
cat .env | grep QUEUE_CONNECTION

# Should show: QUEUE_CONNECTION=database
```

### Need to see what's happening?
```bash
tail -f storage/logs/worker.log
tail -f storage/logs/laravel.log
```

---

## That's It!

Your queue workers are now running and will:
- ✅ Generate invoices automatically after payment
- ✅ Send invoice emails asynchronously  
- ✅ Restart automatically if they crash
- ✅ Process jobs in the background
- ✅ Retry failed jobs automatically

For detailed documentation, see `QUEUE_SETUP_GUIDE.md`
