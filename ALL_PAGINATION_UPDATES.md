# All Admin Controllers - Pagination Updates

## Summary
All admin controller `index()` methods have been updated to support pagination.

---

## ✅ Controllers Updated

### 1. BrandController
**File:** `app/Http/Controllers/Api/Admin/BrandController.php`
- ✅ Added pagination (15 per page default)
- Supports: `per_page`, `active_only`, `search`

### 2. CategoryController
**File:** `app/Http/Controllers/Api/Admin/CategoryController.php`
- ✅ Added pagination (15 per page default)
- Supports: `per_page`, `parent_only`, `active_only`

### 3. ProductModelController
**File:** `app/Http/Controllers/Api/Admin/ProductModelController.php`
- ✅ Added pagination (15 per page default)
- Supports: `per_page`, `brand_id`

### 4. ShippingController
**File:** `app/Http/Controllers/Api/Admin/ShippingController.php`
- ✅ Added pagination (15 per page default)
- Supports: `per_page`, `method`, `is_active`

### 5. ContentController - Pages
**File:** `app/Http/Controllers/Api/Admin/ContentController.php`
- ✅ Added pagination to `pages()` method (15 per page default)
- Supports: `per_page`, `is_active`

### 6. ContentController - Banners
**File:** `app/Http/Controllers/Api/Admin/ContentController.php`
- ✅ Added pagination to `banners()` method (15 per page default)
- Supports: `per_page`, `position`, `is_active`

---

## ✅ Controllers Already Had Pagination

### 7. UserController
- Already paginated via `UserService`
- Supports: `per_page`, `status`, `search`, `verified`, `sort_by`, `sort_order`

### 8. ProductController
- Already paginated via `CatalogService`
- Supports: `per_page`, `search`, `category_id`, `brand_id`, `status`, `min_price`, `max_price`, `in_stock`, `is_featured`, `is_trending`, `sort_by`, `sort_order`

### 9. OrderController
- Already paginated
- Supports: `per_page`, `status`, `payment_status`, `order_type`, `shipping_method`, `search`, `date_from`, `date_to`, `sort_by`, `sort_order`

### 10. InventoryController
- Already paginated
- Supports: `per_page`, `search`, `category_id`, `stock_status`, `sort_by`, `sort_order`

### 11. ReviewController
- Already paginated
- Supports: `per_page`, `status`, `rating`, `product_id`, `search`, `sort_by`, `sort_order`

### 12. ReturnController
- Already paginated
- Supports: `per_page`, `status`, `search`, `date_from`, `date_to`, `sort_by`, `sort_order`

### 13. RefundController
- Already paginated
- Supports: `per_page`, `status`, `refund_method`, `search`, `sort_by`, `sort_order`

### 14. PromotionController
- Already paginated
- Supports: `per_page`, `status`, `promotion_type`, `sort_by`, `sort_order`

### 15. CouponController
- Already paginated
- Supports: `per_page`, `status`, `search`, `sort_by`, `sort_order`

### 16. CampaignController
- Already paginated
- Supports: `per_page`, `status`, `campaign_type`, `search`, `sort_by`, `sort_order`

### 17. GalleryController
- Already paginated
- Supports: `per_page`, `type`, `active_only`

### 18. FlashDealController
- Already paginated
- Supports: `per_page`, `status`

---

## 📊 Pagination Response Format

All endpoints now return data in this format:

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "first_page_url": "http://localhost:8000/api/admin/brands?page=1",
    "from": 1,
    "last_page": 2,
    "last_page_url": "http://localhost:8000/api/admin/brands?page=2",
    "links": [
      { "url": null, "label": "&laquo; Previous", "active": false },
      { "url": "http://localhost:8000/api/admin/brands?page=1", "label": "1", "active": true },
      { "url": "http://localhost:8000/api/admin/brands?page=2", "label": "2", "active": false },
      { "url": "http://localhost:8000/api/admin/brands?page=2", "label": "Next &raquo;", "active": false }
    ],
    "next_page_url": "http://localhost:8000/api/admin/brands?page=2",
    "path": "http://localhost:8000/api/admin/brands",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 24
  }
}
```

---

## 🔗 All Admin Endpoints with Pagination

| Endpoint | Controller | Default Per Page |
|----------|------------|------------------|
| `GET /api/admin/brands` | BrandController | 15 |
| `GET /api/admin/categories` | CategoryController | 15 |
| `GET /api/admin/product-models` | ProductModelController | 15 |
| `GET /api/admin/shipping-rates` | ShippingController | 15 |
| `GET /api/admin/content/pages` | ContentController | 15 |
| `GET /api/admin/content/banners` | ContentController | 15 |
| `GET /api/admin/users` | UserController | 15 |
| `GET /api/admin/products` | ProductController | 15 |
| `GET /api/admin/orders` | OrderController | 15 |
| `GET /api/admin/inventory` | InventoryController | 15 |
| `GET /api/admin/reviews` | ReviewController | 15 |
| `GET /api/admin/returns` | ReturnController | 15 |
| `GET /api/admin/refunds` | RefundController | 15 |
| `GET /api/admin/promotions` | PromotionController | 15 |
| `GET /api/admin/coupons` | CouponController | 15 |
| `GET /api/admin/campaigns` | CampaignController | 15 |
| `GET /api/admin/galleries` | GalleryController | 15 |
| `GET /api/admin/flash-deals` | FlashDealController | 15 |

---

## 🎯 Common Query Parameters

All paginated endpoints support:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)

Additional filters vary by endpoint (see individual controller documentation).

---

## 📝 Frontend Update Checklist

For each admin page, you need to:

- [ ] Update API hook to handle paginated response
- [ ] Access data via `response.data.data` instead of `response.data`
- [ ] Add pagination state management
- [ ] Implement pagination controls (Previous, Next, Page numbers)
- [ ] Add per-page selector (10, 15, 25, 50, 100)
- [ ] Show pagination info (Showing X to Y of Z entries)
- [ ] Handle page changes
- [ ] Handle per-page changes

---

## 💻 Frontend Implementation Example

```javascript
const usePaginatedData = (endpoint) => {
  const [data, setData] = useState([]);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 15,
    from: 0,
    to: 0
  });
  const [loading, setLoading] = useState(false);

  const fetchData = async (page = 1, perPage = 15, filters = {}) => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page,
        per_page: perPage,
        ...filters
      });

      const response = await fetch(`${endpoint}?${params}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      const result = await response.json();

      if (result.success) {
        setData(result.data.data); // Note the nested .data
        setPagination({
          currentPage: result.data.current_page,
          lastPage: result.data.last_page,
          total: result.data.total,
          perPage: result.data.per_page,
          from: result.data.from,
          to: result.data.to
        });
      }
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  return { data, pagination, loading, fetchData };
};

// Usage
const BrandList = () => {
  const { data, pagination, loading, fetchData } = usePaginatedData('/api/admin/brands');

  useEffect(() => {
    fetchData();
  }, []);

  const handlePageChange = (newPage) => {
    fetchData(newPage, pagination.perPage);
  };

  const handlePerPageChange = (newPerPage) => {
    fetchData(1, newPerPage);
  };

  return (
    <div>
      {/* Data table */}
      {/* Pagination controls */}
    </div>
  );
};
```

---

## ✅ Testing Commands

Test all paginated endpoints:

```bash
# Brands
curl "http://localhost:8000/api/admin/brands?page=1&per_page=10"

# Categories
curl "http://localhost:8000/api/admin/categories?page=1&per_page=10"

# Product Models
curl "http://localhost:8000/api/admin/product-models?page=1&per_page=10"

# Shipping Rates
curl "http://localhost:8000/api/admin/shipping-rates?page=1&per_page=10"

# CMS Pages
curl "http://localhost:8000/api/admin/content/pages?page=1&per_page=10"

# Banners
curl "http://localhost:8000/api/admin/content/banners?page=1&per_page=10"

# Products
curl "http://localhost:8000/api/admin/products?page=1&per_page=10"

# Orders
curl "http://localhost:8000/api/admin/orders?page=1&per_page=10"

# And so on...
```

---

## 📋 Summary

### Total Controllers: 18
- ✅ **6 Updated** (Brand, Category, ProductModel, Shipping, Pages, Banners)
- ✅ **12 Already Had Pagination** (User, Product, Order, Inventory, Review, Return, Refund, Promotion, Coupon, Campaign, Gallery, FlashDeal)

### All admin index endpoints now support pagination! 🎉

---

**Status:** ✅ Complete  
**Default Per Page:** 15 items  
**Customizable:** Yes, via `per_page` query parameter  
**Response Format:** Laravel paginated response with metadata
