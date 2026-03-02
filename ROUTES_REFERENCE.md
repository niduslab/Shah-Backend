# API Routes Reference - New User Features

## 🎯 Quick Reference

All routes require authentication: `Authorization: Bearer {token}`

---

## 📊 User Dashboard

### Get Dashboard Statistics
```
GET /api/dashboard
```
**Returns:** Order stats, spending, wishlist count, recent orders

### Get User Profile
```
GET /api/profile
```
**Returns:** User details with all addresses

---

## 📍 Address Management

### List All Addresses
```
GET /api/addresses
```

### Create Address
```
POST /api/addresses
Body: {
  "address_line_1": "string",
  "address_line_2": "string|optional",
  "contact_no": "string",
  "city": "string",
  "state": "string",
  "zip_code": "string",
  "address_type": "user_address|shipping_address|billing_address",
  "is_default": boolean|optional
}
```

### Get Single Address
```
GET /api/addresses/{id}
```

### Update Address
```
PUT /api/addresses/{id}
Body: {
  "address_line_1": "string|optional",
  "city": "string|optional",
  "is_default": boolean|optional
  // ... other fields optional
}
```

### Delete Address
```
DELETE /api/addresses/{id}
```
**Note:** Cannot delete addresses used in orders

### Set Default Address
```
POST /api/addresses/{id}/set-default
```
**Effect:** Sets this address as default for its type, unsets others

---

## ❤️ Wishlist

### Get Wishlist
```
GET /api/wishlist
```
**Returns:** All wishlist items with product details

### Add to Wishlist
```
POST /api/wishlist
Body: {
  "product_id": number
}
```

### Remove from Wishlist (by wishlist ID)
```
DELETE /api/wishlist/{id}
```

### Remove from Wishlist (by product ID)
```
DELETE /api/wishlist/product/{productId}
```

### Check if Product in Wishlist
```
GET /api/wishlist/check/{productId}
```
**Returns:** `{ "in_wishlist": boolean }`

### Clear Entire Wishlist
```
POST /api/wishlist/clear
```

---

## 🔔 Notifications

### List All Notifications
```
GET /api/notifications
```
**Returns:** Paginated list (20 per page)

### Get Unread Count
```
GET /api/notifications/unread-count
```
**Returns:** `{ "unread_count": number }`

### Mark Notification as Read
```
POST /api/notifications/{id}/mark-as-read
```

### Mark All as Read
```
POST /api/notifications/mark-all-as-read
```

### Delete Notification
```
DELETE /api/notifications/{id}
```

### Clear All Notifications
```
POST /api/notifications/clear
```

---

## 📋 Complete Route List

### User Dashboard (2 routes)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dashboard` | Dashboard statistics |
| GET | `/api/profile` | User profile with addresses |

### Address Management (6 routes)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/addresses` | List all addresses |
| POST | `/api/addresses` | Create new address |
| GET | `/api/addresses/{id}` | Get single address |
| PUT | `/api/addresses/{id}` | Update address |
| DELETE | `/api/addresses/{id}` | Delete address |
| POST | `/api/addresses/{id}/set-default` | Set as default |

### Wishlist (6 routes)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/wishlist` | Get wishlist |
| POST | `/api/wishlist` | Add to wishlist |
| DELETE | `/api/wishlist/{id}` | Remove from wishlist |
| DELETE | `/api/wishlist/product/{productId}` | Remove by product ID |
| GET | `/api/wishlist/check/{productId}` | Check if in wishlist |
| POST | `/api/wishlist/clear` | Clear wishlist |

### Notifications (6 routes)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notifications` | List notifications |
| GET | `/api/notifications/unread-count` | Get unread count |
| POST | `/api/notifications/{id}/mark-as-read` | Mark as read |
| POST | `/api/notifications/mark-all-as-read` | Mark all as read |
| DELETE | `/api/notifications/{id}` | Delete notification |
| POST | `/api/notifications/clear` | Clear all |

---

## 🔐 Authentication

All endpoints require Bearer token authentication:

```bash
curl -X GET http://your-domain.com/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📦 Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description"
}
```

---

## 🎯 Common HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## 💡 Usage Examples

### Get Dashboard with cURL
```bash
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Add to Wishlist with cURL
```bash
curl -X POST http://localhost:8000/api/wishlist \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'
```

### Create Address with cURL
```bash
curl -X POST http://localhost:8000/api/addresses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "address_line_1": "123 Main St",
    "contact_no": "+1234567890",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001",
    "address_type": "shipping_address",
    "is_default": true
  }'
```

### Get Unread Notifications with JavaScript
```javascript
fetch('/api/notifications/unread-count', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data.data.unread_count));
```

---

## 🔍 Testing with Postman

1. Create a new collection: "User Features"
2. Add environment variable: `token` with your Bearer token
3. Import these endpoints
4. Set Authorization header: `Bearer {{token}}`
5. Test each endpoint

---

## 📱 Frontend Integration

### React Example
```javascript
const api = {
  baseURL: 'http://localhost:8000/api',
  headers: () => ({
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json'
  }),
  
  getDashboard: () => 
    fetch(`${api.baseURL}/dashboard`, { headers: api.headers() }),
  
  getWishlist: () => 
    fetch(`${api.baseURL}/wishlist`, { headers: api.headers() }),
  
  addToWishlist: (productId) => 
    fetch(`${api.baseURL}/wishlist`, {
      method: 'POST',
      headers: api.headers(),
      body: JSON.stringify({ product_id: productId })
    }),
  
  getAddresses: () => 
    fetch(`${api.baseURL}/addresses`, { headers: api.headers() })
};
```

---

## 🎨 Route Visualization

```
/api
├── /dashboard (GET) - Dashboard stats
├── /profile (GET) - User profile
│
├── /addresses
│   ├── / (GET) - List all
│   ├── / (POST) - Create
│   ├── /{id} (GET) - View single
│   ├── /{id} (PUT) - Update
│   ├── /{id} (DELETE) - Delete
│   └── /{id}/set-default (POST) - Set default
│
├── /wishlist
│   ├── / (GET) - View wishlist
│   ├── / (POST) - Add product
│   ├── /{id} (DELETE) - Remove item
│   ├── /product/{productId} (DELETE) - Remove by product
│   ├── /check/{productId} (GET) - Check status
│   └── /clear (POST) - Clear all
│
└── /notifications
    ├── / (GET) - List all
    ├── /unread-count (GET) - Unread count
    ├── /{id}/mark-as-read (POST) - Mark read
    ├── /mark-all-as-read (POST) - Mark all read
    ├── /{id} (DELETE) - Delete
    └── /clear (POST) - Clear all
```

---

## ✅ Validation Rules

### Address
- `address_line_1`: required, string, max:500
- `address_line_2`: optional, string, max:500
- `contact_no`: required, string, max:20
- `city`: required, string, max:100
- `state`: required, string, max:100
- `zip_code`: required, string, max:20
- `address_type`: required, enum(user_address, shipping_address, billing_address)
- `is_default`: optional, boolean

### Wishlist
- `product_id`: required, exists in products table

---

## 🚀 Quick Test Script

```bash
#!/bin/bash
TOKEN="your_token_here"
BASE_URL="http://localhost:8000/api"

# Test dashboard
curl -X GET "$BASE_URL/dashboard" -H "Authorization: Bearer $TOKEN"

# Test wishlist
curl -X GET "$BASE_URL/wishlist" -H "Authorization: Bearer $TOKEN"

# Test addresses
curl -X GET "$BASE_URL/addresses" -H "Authorization: Bearer $TOKEN"

# Test notifications
curl -X GET "$BASE_URL/notifications/unread-count" -H "Authorization: Bearer $TOKEN"
```

Save as `test_api.sh` and run: `bash test_api.sh`

---

**Total New Routes:** 20  
**Authentication:** Required on all routes  
**Response Format:** JSON  
**Status:** ✅ Production Ready
