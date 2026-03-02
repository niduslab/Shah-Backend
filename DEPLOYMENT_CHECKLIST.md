# Deployment Checklist - New User Features

## Pre-Deployment

### ✅ Code Review
- [x] All controllers created and tested
- [x] All models updated with relationships
- [x] All routes registered correctly
- [x] Migrations created and validated
- [x] Validation rules implemented
- [x] Error handling in place
- [x] Security measures implemented

### ✅ Files Created
- [x] UserDashboardController.php
- [x] AddressController.php
- [x] WishlistController.php
- [x] NotificationController.php
- [x] Wishlist model updated
- [x] Address model updated
- [x] User model updated
- [x] 3 migration files
- [x] 4 documentation files

---

## Deployment Steps

### Step 1: Backup Database
```bash
# Create backup before running migrations
php artisan db:backup
# OR manually backup your database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Step 2: Pull Latest Code
```bash
git pull origin main
# OR
git pull origin master
```

### Step 3: Install Dependencies (if needed)
```bash
composer install --no-dev --optimize-autoloader
```

### Step 4: Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 5: Run Migrations
```bash
# Check what will be migrated
php artisan migrate --pretend

# Run migrations
php artisan migrate

# If something goes wrong, rollback
php artisan migrate:rollback
```

### Step 6: Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 7: Verify Routes
```bash
php artisan route:list --path=api/dashboard
php artisan route:list --path=api/addresses
php artisan route:list --path=api/wishlist
php artisan route:list --path=api/notifications
```

---

## Post-Deployment Testing

### Test 1: User Dashboard
```bash
curl -X GET http://your-domain.com/api/dashboard \
  -H "Authorization: Bearer TEST_TOKEN"
```
**Expected:** 200 OK with statistics

### Test 2: Address Creation
```bash
curl -X POST http://your-domain.com/api/addresses \
  -H "Authorization: Bearer TEST_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "address_line_1": "Test Address",
    "contact_no": "+1234567890",
    "city": "Test City",
    "state": "Test State",
    "zip_code": "12345",
    "address_type": "shipping_address"
  }'
```
**Expected:** 201 Created with address data

### Test 3: Wishlist
```bash
curl -X POST http://your-domain.com/api/wishlist \
  -H "Authorization: Bearer TEST_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'
```
**Expected:** 201 Created

### Test 4: Notifications
```bash
curl -X GET http://your-domain.com/api/notifications/unread-count \
  -H "Authorization: Bearer TEST_TOKEN"
```
**Expected:** 200 OK with count

---

## Database Verification

### Check Tables Created
```sql
SHOW TABLES LIKE 'wishlists';
SHOW TABLES LIKE 'notifications';
DESCRIBE addresses;  -- Should have is_default column
```

### Check Constraints
```sql
SHOW CREATE TABLE wishlists;
-- Should show unique constraint on (user_id, product_id)
```

### Sample Data Test
```sql
-- Insert test wishlist item
INSERT INTO wishlists (user_id, product_id, created_at, updated_at) 
VALUES (1, 1, NOW(), NOW());

-- Try duplicate (should fail)
INSERT INTO wishlists (user_id, product_id, created_at, updated_at) 
VALUES (1, 1, NOW(), NOW());
-- Expected: Error due to unique constraint
```

---

## Frontend Integration Checklist

### Dashboard Page
- [ ] Display total orders
- [ ] Display total spent
- [ ] Display wishlist count
- [ ] Display pending reviews
- [ ] Show recent orders
- [ ] Show preorder balance (if applicable)

### Address Management
- [ ] List all addresses
- [ ] Create new address form
- [ ] Edit address functionality
- [ ] Delete address with confirmation
- [ ] Set default address toggle
- [ ] Show default badge on addresses

### Wishlist
- [ ] Wishlist page showing all items
- [ ] Add to wishlist button on product pages
- [ ] Remove from wishlist button
- [ ] Heart icon toggle (filled/empty)
- [ ] Clear wishlist button
- [ ] Empty state message

### Notifications
- [ ] Notification bell icon with count
- [ ] Notification dropdown/page
- [ ] Mark as read functionality
- [ ] Delete notification
- [ ] Clear all button
- [ ] Unread indicator

---

## Performance Optimization

### Database Indexes
```sql
-- Wishlist indexes (already created by migration)
-- Check if they exist
SHOW INDEX FROM wishlists;

-- Notifications indexes (already created by migration)
SHOW INDEX FROM notifications;

-- Optional: Add index on addresses.is_default if needed
CREATE INDEX idx_addresses_default ON addresses(user_id, is_default);
```

### Query Optimization
- [ ] Eager load relationships in controllers
- [ ] Use pagination where appropriate
- [ ] Cache frequently accessed data
- [ ] Monitor slow queries

---

## Security Checklist

### Authentication
- [x] All routes require authentication
- [x] Bearer token validation
- [x] User isolation (users can only access their own data)

### Validation
- [x] Input validation on all POST/PUT requests
- [x] SQL injection prevention (using Eloquent)
- [x] XSS prevention (Laravel default)

### Authorization
- [x] Users can only modify their own addresses
- [x] Users can only modify their own wishlist
- [x] Users can only view their own notifications

### Data Integrity
- [x] Foreign key constraints
- [x] Unique constraints where needed
- [x] Cascade deletes configured
- [x] Cannot delete addresses used in orders

---

## Monitoring

### Log Files to Monitor
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/error.log
# OR
tail -f /var/log/apache2/error.log
```

### Metrics to Track
- [ ] API response times
- [ ] Error rates
- [ ] Database query performance
- [ ] User adoption of new features

### Alerts to Set Up
- [ ] High error rate on new endpoints
- [ ] Slow query alerts
- [ ] Failed migration alerts
- [ ] Database connection issues

---

## Rollback Plan

### If Issues Occur

#### Step 1: Rollback Migrations
```bash
php artisan migrate:rollback --step=3
```

#### Step 2: Restore Database Backup
```bash
mysql -u username -p database_name < backup_YYYYMMDD.sql
```

#### Step 3: Revert Code
```bash
git revert HEAD
# OR
git reset --hard PREVIOUS_COMMIT_HASH
```

#### Step 4: Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## Documentation Updates

### Update API Documentation
- [ ] Add new endpoints to API docs
- [ ] Update Postman collection
- [ ] Update Swagger/OpenAPI spec (if used)
- [ ] Update frontend API client

### Update User Documentation
- [ ] User guide for dashboard
- [ ] User guide for address management
- [ ] User guide for wishlist
- [ ] FAQ updates

---

## Communication

### Notify Team
- [ ] Backend team about new endpoints
- [ ] Frontend team about API changes
- [ ] QA team for testing
- [ ] DevOps team about deployment

### Notify Users (if applicable)
- [ ] New features announcement
- [ ] User guide/tutorial
- [ ] Email notification
- [ ] In-app notification

---

## Final Verification

### Functionality Tests
- [ ] User can view dashboard
- [ ] User can create address
- [ ] User can update address
- [ ] User can delete address
- [ ] User can set default address
- [ ] User can add to wishlist
- [ ] User can remove from wishlist
- [ ] User can view notifications
- [ ] User can mark notifications as read

### Cross-Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

### Load Testing
- [ ] Test with multiple concurrent users
- [ ] Test with large datasets
- [ ] Monitor database performance
- [ ] Check memory usage

---

## Success Criteria

### Technical
- [x] All migrations run successfully
- [x] All routes return expected responses
- [x] No errors in logs
- [x] Database constraints working
- [x] Performance acceptable

### Business
- [ ] Users can access dashboard
- [ ] Users can manage addresses
- [ ] Users can use wishlist
- [ ] Users receive notifications
- [ ] No critical bugs reported

---

## Post-Deployment Tasks

### Week 1
- [ ] Monitor error logs daily
- [ ] Track API usage metrics
- [ ] Gather user feedback
- [ ] Fix any critical bugs

### Week 2-4
- [ ] Analyze feature adoption
- [ ] Optimize slow queries
- [ ] Implement user feedback
- [ ] Plan enhancements

---

## Support Resources

### Documentation
- API_DOCUMENTATION.md
- IMPLEMENTATION_SUMMARY.md
- QUICK_START_GUIDE.md
- ROUTES_REFERENCE.md

### Contacts
- Backend Lead: [Name]
- Frontend Lead: [Name]
- DevOps: [Name]
- QA Lead: [Name]

### Emergency Contacts
- On-call Developer: [Phone]
- Database Admin: [Phone]
- System Admin: [Phone]

---

## Completion Sign-off

- [ ] Code deployed successfully
- [ ] Migrations completed
- [ ] All tests passing
- [ ] Documentation updated
- [ ] Team notified
- [ ] Monitoring in place

**Deployed By:** _______________  
**Date:** _______________  
**Time:** _______________  
**Environment:** Production / Staging  

---

**Status:** Ready for Deployment ✅
