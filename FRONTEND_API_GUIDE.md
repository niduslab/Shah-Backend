# Frontend API Integration Guide

## 🚨 Important Changes - Pagination Update

All admin list endpoints now return **paginated data**. You must update your code to access data correctly.

---

## Quick Reference

### Before Pagination (Old)
```javascript
const response = await fetch('/api/admin/brands');
const result = await response.json();
const brands = result.data; // ❌ This is now wrong!
```

### After Pagination (New)
```javascript
const response = await fetch('/api/admin/brands?page=1&per_page=15');
const result = await response.json();
const brands = result.data.data; // ✅ Note the nested .data
const pagination = result.data; // Contains pagination info
```

---

## Response Structure

### Paginated Response Format
```javascript
{
  success: true,
  data: {
    current_page: 1,
    data: [...],              // ← Your actual items are here!
    first_page_url: "...",
    from: 1,
    last_page: 5,
    last_page_url: "...",
    links: [...],
    next_page_url: "...",
    path: "...",
    per_page: 15,
    prev_page_url: null,
    to: 15,
    total: 67
  }
}
```

---

## Affected Endpoints

### ✅ All These Endpoints Are Now Paginated:

```
GET /api/admin/brands
GET /api/admin/categories
GET /api/admin/products
GET /api/admin/product-models
GET /api/admin/orders
GET /api/admin/users
GET /api/admin/inventory
GET /api/admin/reviews
GET /api/admin/returns
GET /api/admin/refunds
GET /api/admin/promotions
GET /api/admin/coupons
GET /api/admin/campaigns
GET /api/admin/flash-deals
GET /api/admin/galleries
GET /api/admin/shipping-rates
GET /api/admin/content/pages
GET /api/admin/content/banners
```

---

## React/TypeScript Examples

### 1. Basic Fetch with Pagination

```typescript
interface PaginationData<T> {
  current_page: number;
  data: T[];
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  next_page_url: string | null;
  prev_page_url: string | null;
}

interface ApiResponse<T> {
  success: boolean;
  data: PaginationData<T>;
}

// Example: Fetch brands
const fetchBrands = async (page = 1, perPage = 15) => {
  const response = await fetch(
    `/api/admin/brands?page=${page}&per_page=${perPage}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }
  );

  const result: ApiResponse<Brand> = await response.json();
  
  return {
    brands: result.data.data,        // The actual array
    pagination: {
      currentPage: result.data.current_page,
      lastPage: result.data.last_page,
      total: result.data.total,
      perPage: result.data.per_page,
      from: result.data.from,
      to: result.data.to
    }
  };
};
```

### 2. Custom Hook for Pagination

```typescript
import { useState, useEffect } from 'react';

interface UsePaginationOptions {
  endpoint: string;
  initialPage?: number;
  initialPerPage?: number;
}

export const usePagination = <T,>({ 
  endpoint, 
  initialPage = 1, 
  initialPerPage = 15 
}: UsePaginationOptions) => {
  const [data, setData] = useState<T[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pagination, setPagination] = useState({
    currentPage: initialPage,
    lastPage: 1,
    total: 0,
    perPage: initialPerPage,
    from: 0,
    to: 0
  });

  const fetchData = async (page: number, perPage: number) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch(
        `${endpoint}?page=${page}&per_page=${perPage}`,
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
          }
        }
      );

      const result = await response.json();

      if (result.success) {
        setData(result.data.data);
        setPagination({
          currentPage: result.data.current_page,
          lastPage: result.data.last_page,
          total: result.data.total,
          perPage: result.data.per_page,
          from: result.data.from,
          to: result.data.to
        });
      } else {
        setError(result.message || 'Failed to fetch data');
      }
    } catch (err) {
      setError('Network error occurred');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData(pagination.currentPage, pagination.perPage);
  }, []);

  const goToPage = (page: number) => {
    fetchData(page, pagination.perPage);
  };

  const changePerPage = (perPage: number) => {
    fetchData(1, perPage);
  };

  const refresh = () => {
    fetchData(pagination.currentPage, pagination.perPage);
  };

  return {
    data,
    loading,
    error,
    pagination,
    goToPage,
    changePerPage,
    refresh
  };
};
```

### 3. Usage in Component

```typescript
import React from 'react';
import { usePagination } from './hooks/usePagination';

interface Brand {
  id: number;
  name: string;
  logo: string;
  is_active: boolean;
  products_count: number;
}

const BrandList: React.FC = () => {
  const { 
    data: brands, 
    loading, 
    error, 
    pagination, 
    goToPage, 
    changePerPage 
  } = usePagination<Brand>({ 
    endpoint: '/api/admin/brands' 
  });

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <h1>Brands</h1>

      {/* Per Page Selector */}
      <select 
        value={pagination.perPage}
        onChange={(e) => changePerPage(Number(e.target.value))}
      >
        <option value={10}>10 per page</option>
        <option value={15}>15 per page</option>
        <option value={25}>25 per page</option>
        <option value={50}>50 per page</option>
      </select>

      {/* Data Table */}
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Products</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {brands.map(brand => (
            <tr key={brand.id}>
              <td>{brand.id}</td>
              <td>{brand.name}</td>
              <td>{brand.products_count}</td>
              <td>{brand.is_active ? 'Active' : 'Inactive'}</td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Pagination Info */}
      <div>
        Showing {pagination.from} to {pagination.to} of {pagination.total} entries
      </div>

      {/* Pagination Controls */}
      <div>
        <button 
          onClick={() => goToPage(pagination.currentPage - 1)}
          disabled={pagination.currentPage === 1}
        >
          Previous
        </button>

        {Array.from({ length: pagination.lastPage }, (_, i) => i + 1).map(page => (
          <button
            key={page}
            onClick={() => goToPage(page)}
            className={pagination.currentPage === page ? 'active' : ''}
          >
            {page}
          </button>
        ))}

        <button 
          onClick={() => goToPage(pagination.currentPage + 1)}
          disabled={pagination.currentPage === pagination.lastPage}
        >
          Next
        </button>
      </div>
    </div>
  );
};

export default BrandList;
```

---

## React Query Example

```typescript
import { useQuery } from '@tanstack/react-query';

interface FetchBrandsParams {
  page: number;
  perPage: number;
}

const fetchBrands = async ({ page, perPage }: FetchBrandsParams) => {
  const response = await fetch(
    `/api/admin/brands?page=${page}&per_page=${perPage}`,
    {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    }
  );

  if (!response.ok) {
    throw new Error('Failed to fetch brands');
  }

  const result = await response.json();
  return result.data; // Returns the paginated data object
};

const BrandList = () => {
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);

  const { data, isLoading, error } = useQuery({
    queryKey: ['brands', page, perPage],
    queryFn: () => fetchBrands({ page, perPage }),
    keepPreviousData: true
  });

  if (isLoading) return <div>Loading...</div>;
  if (error) return <div>Error loading brands</div>;

  return (
    <div>
      {/* Render data.data (the brands array) */}
      {data.data.map(brand => (
        <div key={brand.id}>{brand.name}</div>
      ))}

      {/* Pagination using data.current_page, data.last_page, etc. */}
      <Pagination
        currentPage={data.current_page}
        lastPage={data.last_page}
        onPageChange={setPage}
      />
    </div>
  );
};
```

---

## Axios Example

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Accept': 'application/json'
  }
});

// Add token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Fetch brands with pagination
const getBrands = async (page = 1, perPage = 15) => {
  const response = await api.get('/admin/brands', {
    params: { page, per_page: perPage }
  });

  return {
    brands: response.data.data.data,
    pagination: {
      currentPage: response.data.data.current_page,
      lastPage: response.data.data.last_page,
      total: response.data.data.total,
      perPage: response.data.data.per_page,
      from: response.data.data.from,
      to: response.data.data.to
    }
  };
};
```

---

## Reusable Pagination Component

```typescript
interface PaginationProps {
  currentPage: number;
  lastPage: number;
  total: number;
  from: number;
  to: number;
  onPageChange: (page: number) => void;
}

const Pagination: React.FC<PaginationProps> = ({
  currentPage,
  lastPage,
  total,
  from,
  to,
  onPageChange
}) => {
  const getPageNumbers = () => {
    const pages: number[] = [];
    const maxVisible = 5;
    
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(lastPage, startPage + maxVisible - 1);

    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      pages.push(i);
    }

    return pages;
  };

  return (
    <div className="pagination">
      <div className="pagination-info">
        Showing {from} to {to} of {total} entries
      </div>

      <div className="pagination-controls">
        <button
          onClick={() => onPageChange(1)}
          disabled={currentPage === 1}
          className="btn-first"
        >
          First
        </button>

        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className="btn-prev"
        >
          Previous
        </button>

        {getPageNumbers().map(page => (
          <button
            key={page}
            onClick={() => onPageChange(page)}
            className={currentPage === page ? 'active' : ''}
          >
            {page}
          </button>
        ))}

        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage === lastPage}
          className="btn-next"
        >
          Next
        </button>

        <button
          onClick={() => onPageChange(lastPage)}
          disabled={currentPage === lastPage}
          className="btn-last"
        >
          Last
        </button>
      </div>
    </div>
  );
};

export default Pagination;
```

---

## Common Mistakes to Avoid

### ❌ Wrong
```javascript
// Trying to access data directly
const brands = response.data;
brands.map(brand => ...) // Error: brands is not an array!
```

### ✅ Correct
```javascript
// Access nested data property
const brands = response.data.data;
brands.map(brand => ...) // Works!
```

### ❌ Wrong
```javascript
// Not handling pagination
const [brands, setBrands] = useState([]);
setBrands(response.data); // Wrong structure!
```

### ✅ Correct
```javascript
// Handle both data and pagination
const [brands, setBrands] = useState([]);
const [pagination, setPagination] = useState({});

setBrands(response.data.data);
setPagination({
  currentPage: response.data.current_page,
  lastPage: response.data.last_page,
  total: response.data.total
});
```

---

## Query Parameters Reference

### Common Parameters (All Paginated Endpoints)
```
?page=1                    // Page number
&per_page=15              // Items per page
```

### Endpoint-Specific Parameters

#### Brands
```
?active_only=1            // Only active brands
&search=Nike              // Search by name
```

#### Categories
```
?parent_only=1            // Only parent categories
&active_only=1            // Only active categories
```

#### Products
```
?search=cricket           // Search term
&category_id=1            // Filter by category
&brand_id=1               // Filter by brand
&status=active            // Filter by status
&min_price=1000           // Minimum price
&max_price=5000           // Maximum price
&in_stock=1               // Only in stock
&is_featured=1            // Only featured
&sort_by=price            // Sort field
&sort_order=asc           // Sort direction
```

#### Orders
```
?status=pending           // Filter by status
&payment_status=paid      // Filter by payment status
&search=SS20260304001     // Search order number
&date_from=2026-03-01     // From date
&date_to=2026-03-31       // To date
```

---

## Testing Checklist

- [ ] Update all API calls to use `response.data.data`
- [ ] Add pagination state management
- [ ] Implement page change handlers
- [ ] Add per-page selector
- [ ] Show pagination info (X to Y of Z)
- [ ] Add Previous/Next buttons
- [ ] Add page number buttons
- [ ] Test with different per_page values
- [ ] Test navigation between pages
- [ ] Handle loading states
- [ ] Handle error states
- [ ] Test with filters/search

---

## Quick Migration Checklist

For each admin list page:

1. **Update API Call**
   ```javascript
   // Add page and per_page parameters
   `/api/admin/brands?page=${page}&per_page=${perPage}`
   ```

2. **Update Data Access**
   ```javascript
   // Change from: result.data
   // Change to: result.data.data
   ```

3. **Add Pagination State**
   ```javascript
   const [pagination, setPagination] = useState({
     currentPage: 1,
     lastPage: 1,
     total: 0,
     perPage: 15
   });
   ```

4. **Add Pagination Controls**
   ```jsx
   <Pagination
     currentPage={pagination.currentPage}
     lastPage={pagination.lastPage}
     onPageChange={handlePageChange}
   />
   ```

---

## Support

If you encounter issues:
1. Check the response structure in browser DevTools
2. Verify you're accessing `response.data.data` not `response.data`
3. Ensure pagination state is properly initialized
4. Check that page/per_page parameters are being sent

---

**Last Updated:** March 4, 2026  
**Breaking Change:** Yes - All admin list endpoints now paginated  
**Action Required:** Update frontend code to handle new response format
