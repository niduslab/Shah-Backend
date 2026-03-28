# Visitor Popup - Quick Reference

## What Was Created

✅ **Migration**: `database/migrations/2026_03_24_042545_create_visitor_popups_table.php`
✅ **Model**: `app/Models/VisitorPopup.php`
✅ **Public Controller**: `app/Http/Controllers/Api/VisitorPopupController.php`
✅ **Admin Controller**: `app/Http/Controllers/Api/Admin/VisitorPopupController.php`
✅ **Routes**: Added to `routes/api.php`
✅ **Database**: Table created successfully

## Quick API Reference

### Public Endpoint (No Auth Required)
```
POST /api/visitor-popup
Body: { "name": "John Doe", "email": "john@example.com", "phone": "+123456" }
```

### Admin Endpoints (Auth + Admin Role Required)
```
GET    /api/admin/visitor-popups              # List all submissions
GET    /api/admin/visitor-popups/statistics   # Get statistics
GET    /api/admin/visitor-popups/export       # Export to CSV
GET    /api/admin/visitor-popups/{id}         # Get single submission
DELETE /api/admin/visitor-popups/{id}         # Delete submission
```

## Frontend Integration

### Check if popup should show:
```javascript
const shouldShow = !localStorage.getItem('visitor_popup_submitted');
```

### After successful submission:
```javascript
localStorage.setItem('visitor_popup_submitted', 'true');
```

## Admin Features

- Pagination support
- Search by name, email, or phone
- Filter by email/phone presence
- Date range filtering
- Statistics dashboard
- CSV export
- Individual record viewing
- Delete functionality

## Database Fields

- `name` (required) - Visitor's name
- `email` (optional) - Visitor's email
- `phone` (optional) - Visitor's phone
- `ip_address` (auto) - Captured IP
- `user_agent` (auto) - Browser/device info
- `submitted_at` (auto) - Submission timestamp

## Testing

### Test Public Endpoint:
```bash
curl -X POST http://your-domain.com/api/visitor-popup \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","phone":"+1234567890"}'
```

### Test Admin Endpoint:
```bash
curl -X GET http://your-domain.com/api/admin/visitor-popups \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

## Next Steps

1. Implement the popup UI in your frontend
2. Add popup trigger logic (on load, exit intent, etc.)
3. Style the popup to match your design
4. Test the submission flow
5. Set up admin dashboard to view submissions
6. Configure export functionality if needed
