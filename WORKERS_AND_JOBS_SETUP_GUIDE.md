# Workers and Jobs Setup Guide

## 📋 Overview

Your e-commerce system uses **Laravel Scheduler** and **Queue Workers** for background tasks. Here's what you need to run.

---

## 🎯 Quick Answer

### For Analytics System:
**✅ NO QUEUE WORKER NEEDED** - Analytics tracking works synchronously (real-time)

### For Other Features:
**⚠️ QUEUE WORKER RECOMMENDED** - For emails, notifications, and invoice generation

---

## 📊 Analytics System

### Current Configuration
```env
QUEUE_CONNECTION=sync
```

**What this means:**
- Analytics tracking happens **immediately** (synchronous)
- No queue worker needed for analytics
- All tracking calls return instantly

### Scheduled Task (Laravel Scheduler)

There's **ONE** scheduled command for analytics:

```php
// Runs every 10 minutes
$schedule->command('analytics:mark-abandoned-checkouts')->everyTenMinutes();
```

**What it does:**
- Marks checkouts as "abandoned" if no activity for 30 minutes
- Runs automatically via Laravel Scheduler

---

## 🚀 Required: Laravel Scheduler

### What Needs to Run

You have **3 scheduled commands** in `app/Console/Kernel.php`:

1. **Flash Deal Notifications** - Every 15 minutes
   ```php
   $schedule->command('notifications:flash-deals')->everyFifteenMinutes();
   ```

2. **OTP Cleanup** - Daily
   ```php
   $schedule->command('otp:cleanup')->daily();
   ```

3. **Abandoned Checkouts** - Every 10 minutes ⭐ (Analytics)
   ```php
   $schedule->command('analytics:mark-abandoned-checkouts')->everyTenMinutes();
   ```

### How to Run the Scheduler

#### Option 1: Add Cron Job (Production - Recommended)

Add this to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

**To edit crontab:**
```bash
crontab -e
```

**Example:**
```bash
* * * * * cd /var/www/ecommerce && php artisan schedule:run >> /dev/null 2>&1
```

#### Option 2: Run Manually (Development/Testing)

Run this command in a separate terminal:

```bash
php artisan schedule:work
```

This will check for scheduled tasks every minute.

#### Option 3: Test Individual Commands

```bash
# Test abandoned checkout marking
php artisan analytics:mark-abandoned-checkouts

# Test flash deal notifications
php artisan notifications:flash-deals

# Test OTP cleanup
php artisan otp:cleanup
```

---

## 📬 Queue Workers (Optional but Recommended)

### What Uses Queues

Your system has **queued jobs** for:

1. **Email Sending**
   - Order confirmations
   - Order status updates
   - Invoice emails

2. **Notifications**
   - Flash deal notifications
   - Order notifications
   - Review notifications
   - Low stock alerts

3. **Invoice Generation**
   - PDF invoice creation
   - Invoice email sending

### Current Setup: Sync (No Queue)

```env
QUEUE_CONNECTION=sync
```

**Pros:**
- ✅ No worker needed
- ✅ Simple setup
- ✅ Immediate execution

**Cons:**
- ❌ Slower API responses (waits for emails to send)
- ❌ No retry on failure
- ❌ Can't handle high load

### Recommended: Switch to Database Queue

#### Step 1: Update .env

```env
QUEUE_CONNECTION=database
```

#### Step 2: Create Queue Tables

```bash
php artisan queue:table
php artisan migrate
```

#### Step 3: Start Queue Worker

Run this in a separate terminal:

```bash
php artisan queue:work
```

**For production (with supervisor):**
```bash
php artisan queue:work --tries=3 --timeout=90
```

---

## 🔧 Complete Setup Instructions

### For Development (Local)

**Terminal 1: Laravel Server**
```bash
php artisan serve
```

**Terminal 2: Laravel Scheduler (Optional)**
```bash
php artisan schedule:work
```

**Terminal 3: Queue Worker (Optional - if using database queue)**
```bash
php artisan queue:work
```

### For Production

#### 1. Setup Cron Job

```bash
crontab -e
```

Add:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

#### 2. Setup Supervisor for Queue Worker (Recommended)

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log
stopwaitsecs=3600
```

**Start supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 📊 Analytics-Specific Setup

### Minimum Required (Analytics Only)

**For analytics to work properly, you need:**

1. ✅ **Laravel Scheduler Running** (for abandoned cart detection)
   ```bash
   # Development
   php artisan schedule:work
   
   # Production (cron job)
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

2. ✅ **No Queue Worker Needed** (analytics is synchronous)

### Optional (Better Performance)

If you want faster API responses, switch to database queue:

```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:work
```

---

## 🧪 Testing

### Test Analytics Tracking (No Worker Needed)

```bash
# Track a page view
curl -X POST http://localhost:8000/api/analytics/track/page-view \
  -H "Content-Type: application/json" \
  -d '{"page_type":"home","page_title":"Home"}'

# Should return immediately: {"success":true,"message":"Page view tracked"}
```

### Test Abandoned Checkout Command

```bash
php artisan analytics:mark-abandoned-checkouts
```

Expected output:
```
Marking abandoned checkouts...
Abandoned checkouts marked successfully!
```

### Test Queue Worker (If Using Database Queue)

```bash
# Dispatch a test job
php artisan queue:work --once

# Check queue status
php artisan queue:failed
```

---

## 📋 Checklist

### For Analytics System:

- [ ] ✅ **Analytics tracking works without any workers** (sync mode)
- [ ] ⚠️ **Setup Laravel Scheduler** (for abandoned cart detection)
  - [ ] Development: Run `php artisan schedule:work`
  - [ ] Production: Add cron job
- [ ] ✅ Test tracking endpoints
- [ ] ✅ Test abandoned checkout command

### For Full E-commerce System:

- [ ] ⚠️ **Setup Laravel Scheduler** (required)
  - [ ] Add cron job (production)
  - [ ] Or run `php artisan schedule:work` (development)
- [ ] 🔄 **Setup Queue Worker** (recommended)
  - [ ] Change `QUEUE_CONNECTION=database` in .env
  - [ ] Run `php artisan queue:table && php artisan migrate`
  - [ ] Start queue worker
  - [ ] Setup supervisor (production)
- [ ] ✅ Test email sending
- [ ] ✅ Test notifications
- [ ] ✅ Test invoice generation

---

## 🎯 Recommendations

### Minimum Setup (Development)

```bash
# Terminal 1: Server
php artisan serve

# Terminal 2: Scheduler (for abandoned carts)
php artisan schedule:work
```

**Analytics will work!** ✅

### Recommended Setup (Development)

```bash
# Terminal 1: Server
php artisan serve

# Terminal 2: Scheduler
php artisan schedule:work

# Terminal 3: Queue Worker (for emails/notifications)
php artisan queue:work
```

**Everything will work optimally!** ✅

### Production Setup

1. **Web Server** (Nginx/Apache)
2. **Cron Job** (for scheduler)
3. **Supervisor** (for queue worker)

---

## ❓ FAQ

### Q: Do I need a queue worker for analytics?
**A:** No! Analytics tracking is synchronous and works immediately.

### Q: What happens if I don't run the scheduler?
**A:** Abandoned carts won't be automatically marked after 30 minutes. You can still manually mark them via the service method.

### Q: Can I use Redis instead of database for queues?
**A:** Yes! Update `.env`:
```env
QUEUE_CONNECTION=redis
```

### Q: How do I monitor queue jobs?
**A:** Use Laravel Horizon (for Redis) or check:
```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## 🚨 Important Notes

1. **Analytics Tracking** = Real-time, no worker needed ✅
2. **Abandoned Cart Detection** = Needs scheduler ⚠️
3. **Emails/Notifications** = Better with queue worker 🔄
4. **Invoice Generation** = Better with queue worker 🔄

---

## 📞 Quick Commands Reference

```bash
# Scheduler
php artisan schedule:work              # Development
php artisan schedule:list              # List scheduled tasks

# Queue
php artisan queue:work                 # Start worker
php artisan queue:work --once          # Process one job
php artisan queue:failed               # List failed jobs
php artisan queue:retry all            # Retry failed jobs
php artisan queue:flush                # Clear failed jobs

# Analytics
php artisan analytics:mark-abandoned-checkouts  # Mark abandoned carts

# Testing
php artisan tinker                     # Laravel REPL
```

---

**Summary:** For analytics to work properly, just run the Laravel Scheduler. Queue workers are optional but recommended for better performance with emails and notifications.
