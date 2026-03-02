# Quick Start Guide - New User Features

## Step 1: Run Migrations

```bash
php artisan migrate
```

This will create:
- `wishlists` table
- `notifications` table  
- Add `is_default` column to `addresses` table

---

## Step 2: Test the Endpoints

### Get User Dashboard
```bash
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Create an Address
```bash
curl -X POST http://localhost:8000/api/addresses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "address_line_1": "123 Main Street",
    "contact_no": "+1234567890",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001",
    "address_type": "shipping_address",
    "is_default": true
  }'
```

### Add Product to Wishlist
```bash
curl -X POST http://localhost:8000/api/wishlist \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1}'
```

### Get Wishlist
```bash
curl -X GET http://localhost:8000/api/wishlist \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Step 3: Available Routes

### User Dashboard
- `GET /api/dashboard` - Dashboard statistics
- `GET /api/profile` - User profile with addresses

### Address Management
- `GET /api/addresses` - List all addresses
- `POST /api/addresses` - Create new address
- `GET /api/addresses/{id}` - Get single address
- `PUT /api/addresses/{id}` - Update address
- `DELETE /api/addresses/{id}` - Delete address
- `POST /api/addresses/{id}/set-default` - Set as default

### Wishlist
- `GET /api/wishlist` - Get wishlist
- `POST /api/wishlist` - Add to wishlist
- `DELETE /api/wishlist/{id}` - Remove from wishlist
- `DELETE /api/wishlist/product/{productId}` - Remove by product ID
- `GET /api/wishlist/check/{productId}` - Check if in wishlist
- `POST /api/wishlist/clear` - Clear entire wishlist

### Notifications
- `GET /api/notifications` - List notifications
- `GET /api/notifications/unread-count` - Get unread count
- `POST /api/notifications/{id}/mark-as-read` - Mark as read
- `POST /api/notifications/mark-all-as-read` - Mark all as read
- `DELETE /api/notifications/{id}` - Delete notification
- `POST /api/notifications/clear` - Clear all notifications

---

## Step 4: Frontend Integration Examples

### React/Vue - Dashboard Component
```javascript
// Fetch dashboard data
const fetchDashboard = async () => {
  const response = await fetch('/api/dashboard', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  const data = await response.json();
  return data.data;
};
```

### React/Vue - Wishlist Toggle
```javascript
// Add to wishlist
const addToWishlist = async (productId) => {
  const response = await fetch('/api/wishlist', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ product_id: productId })
  });
  return await response.json();
};

// Check if in wishlist
const checkWishlist = async (productId) => {
  const response = await fetch(`/api/wishlist/check/${productId}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  const data = await response.json();
  return data.data.in_wishlist;
};
```

### React/Vue - Address Management
```javascript
// Get all addresses
const fetchAddresses = async () => {
  const response = await fetch('/api/addresses', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  const data = await response.json();
  return data.data;
};

// Set default address
const setDefaultAddress = async (addressId) => {
  const response = await fetch(`/api/addresses/${addressId}/set-default`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return await response.json();
};
```

---

## Step 5: Common Use Cases

### Display User Dashboard Stats
```javascript
const DashboardStats = () => {
  const [stats, setStats] = useState(null);
  
  useEffect(() => {
    fetchDashboard().then(data => {
      setStats(data.statistics);
    });
  }, []);
  
  return (
    <div className="dashboard-stats">
      <StatCard title="Total Orders" value={stats?.total_orders} />
      <StatCard title="Total Spent" value={`$${stats?.total_spent}`} />
      <StatCard title="Wishlist Items" value={stats?.wishlist_count} />
      <StatCard title="Pending Reviews" value={stats?.pending_reviews} />
    </div>
  );
};
```

### Wishlist Heart Icon
```javascript
const WishlistButton = ({ productId }) => {
  const [inWishlist, setInWishlist] = useState(false);
  
  useEffect(() => {
    checkWishlist(productId).then(setInWishlist);
  }, [productId]);
  
  const toggleWishlist = async () => {
    if (inWishlist) {
      await removeFromWishlist(productId);
      setInWishlist(false);
    } else {
      await addToWishlist(productId);
      setInWishlist(true);
    }
  };
  
  return (
    <button onClick={toggleWishlist}>
      {inWishlist ? '❤️' : '🤍'}
    </button>
  );
};
```

### Address Selector
```javascript
const AddressSelector = ({ onSelect }) => {
  const [addresses, setAddresses] = useState([]);
  
  useEffect(() => {
    fetchAddresses().then(setAddresses);
  }, []);
  
  return (
    <select onChange={(e) => onSelect(e.target.value)}>
      {addresses.map(addr => (
        <option key={addr.id} value={addr.id}>
          {addr.address_line_1}, {addr.city} {addr.is_default && '(Default)'}
        </option>
      ))}
    </select>
  );
};
```

---

## Step 6: Error Handling

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description"
}
```

Handle errors in your frontend:

```javascript
const handleApiCall = async (apiFunction) => {
  try {
    const response = await apiFunction();
    if (!response.success) {
      showError(response.message);
      return null;
    }
    return response.data;
  } catch (error) {
    showError('Network error occurred');
    return null;
  }
};
```

---

## Step 7: Testing Checklist

- [ ] User can view dashboard statistics
- [ ] User can create shipping address
- [ ] User can create billing address
- [ ] User can set default address
- [ ] User can update address
- [ ] User can delete unused address
- [ ] User can add product to wishlist
- [ ] User can remove product from wishlist
- [ ] User can view wishlist with product details
- [ ] User can clear entire wishlist
- [ ] User can view notifications
- [ ] User can mark notifications as read
- [ ] User can delete notifications
- [ ] Dashboard shows correct order counts
- [ ] Dashboard shows correct total spent
- [ ] Recent orders display correctly

---

## Troubleshooting

### Issue: Routes not found
**Solution:** Clear route cache
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Migrations fail
**Solution:** Check database connection and rollback if needed
```bash
php artisan migrate:rollback
php artisan migrate
```

### Issue: 401 Unauthorized
**Solution:** Ensure Bearer token is valid and included in headers
```javascript
headers: {
  'Authorization': `Bearer ${token}`
}
```

### Issue: Cannot delete address
**Solution:** Address is used in orders. This is by design for data integrity.

### Issue: Duplicate wishlist entry
**Solution:** Check if product already exists before adding. Use the check endpoint.

---

## Next Steps

1. ✅ Run migrations
2. ✅ Test endpoints with Postman
3. ⬜ Build frontend components
4. ⬜ Add notification types
5. ⬜ Implement real-time notifications
6. ⬜ Add wishlist sharing
7. ⬜ Add address validation

---

## Support

For detailed API documentation, see `API_DOCUMENTATION.md`
For implementation details, see `IMPLEMENTATION_SUMMARY.md`
