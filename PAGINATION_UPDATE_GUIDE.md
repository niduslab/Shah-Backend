# Pagination Update Guide

## Changes Made

### Backend Updates

#### 1. Brand Controller
**File:** `app/Http/Controllers/Api/Admin/BrandController.php`

**Changed from:**
```php
$brands = $query->orderBy('sort_order')->orderBy('name')->get();
```

**Changed to:**
```php
$query->orderBy('sort_order')->orderBy('name');
$perPage = $request->get('per_page', 15);
$brands = $query->paginate($perPage);
```

#### 2. Category Controller
**File:** `app/Http/Controllers/Api/Admin/CategoryController.php`

**Changed from:**
```php
$categories = $query->orderBy('sort_order')->orderBy('name')->get();
```

**Changed to:**
```php
$query->orderBy('sort_order')->orderBy('name');
$perPage = $request->get('per_page', 15);
$categories = $query->paginate($perPage);
```

---

## New Response Format

### Before (Simple Array)
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Brand 1" },
    { "id": 2, "name": "Brand 2" }
  ]
}
```

### After (Paginated Object)
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      { "id": 1, "name": "Brand 1" },
      { "id": 2, "name": "Brand 2" }
    ],
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

## API Usage

### Brands Endpoint
```
GET /api/admin/brands
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `active_only` - Filter active brands (boolean)
- `search` - Search by brand name

**Examples:**
```bash
# Get first page (15 items)
GET /api/admin/brands

# Get page 2 with 20 items
GET /api/admin/brands?page=2&per_page=20

# Search with pagination
GET /api/admin/brands?search=Nike&per_page=10

# Active brands only
GET /api/admin/brands?active_only=1&page=1&per_page=25
```

### Categories Endpoint
```
GET /api/admin/categories
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `parent_only` - Only parent categories (boolean)
- `active_only` - Filter active categories (boolean)

**Examples:**
```bash
# Get first page (15 items)
GET /api/admin/categories

# Get page 2 with 20 items
GET /api/admin/categories?page=2&per_page=20

# Parent categories only
GET /api/admin/categories?parent_only=1&per_page=10

# Active categories only
GET /api/admin/categories?active_only=1&page=1
```

---

## Frontend Integration

### React/JavaScript Example

#### Before (Simple Array)
```javascript
const fetchBrands = async () => {
  const response = await fetch('/api/admin/brands');
  const result = await response.json();
  
  if (result.success) {
    setBrands(result.data); // Direct array
  }
};
```

#### After (Paginated Data)
```javascript
const fetchBrands = async (page = 1, perPage = 15) => {
  const response = await fetch(`/api/admin/brands?page=${page}&per_page=${perPage}`);
  const result = await response.json();
  
  if (result.success) {
    setBrands(result.data.data); // Access nested data array
    setPagination({
      currentPage: result.data.current_page,
      lastPage: result.data.last_page,
      total: result.data.total,
      perPage: result.data.per_page,
      from: result.data.from,
      to: result.data.to
    });
  }
};
```

### Complete React Component Example

```javascript
import React, { useState, useEffect } from 'react';

const BrandList = () => {
  const [brands, setBrands] = useState([]);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 15,
    from: 0,
    to: 0
  });
  const [loading, setLoading] = useState(false);

  const fetchBrands = async (page = 1, perPage = 15) => {
    setLoading(true);
    try {
      const response = await fetch(
        `/api/admin/brands?page=${page}&per_page=${perPage}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        }
      );
      const result = await response.json();
      
      if (result.success) {
        setBrands(result.data.data);
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
      console.error('Error fetching brands:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBrands();
  }, []);

  const handlePageChange = (newPage) => {
    fetchBrands(newPage, pagination.perPage);
  };

  const handlePerPageChange = (newPerPage) => {
    fetchBrands(1, newPerPage);
  };

  return (
    <div>
      <h1>Brands</h1>
      
      {/* Per Page Selector */}
      <select 
        value={pagination.perPage} 
        onChange={(e) => handlePerPageChange(Number(e.target.value))}
      >
        <option value={10}>10 per page</option>
        <option value={15}>15 per page</option>
        <option value={25}>25 per page</option>
        <option value={50}>50 per page</option>
      </select>

      {/* Brand List */}
      {loading ? (
        <p>Loading...</p>
      ) : (
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
      )}

      {/* Pagination Info */}
      <div className="pagination-info">
        Showing {pagination.from} to {pagination.to} of {pagination.total} entries
      </div>

      {/* Pagination Controls */}
      <div className="pagination">
        <button 
          onClick={() => handlePageChange(pagination.currentPage - 1)}
          disabled={pagination.currentPage === 1}
        >
          Previous
        </button>
        
        {[...Array(pagination.lastPage)].map((_, index) => (
          <button
            key={index + 1}
            onClick={() => handlePageChange(index + 1)}
            className={pagination.currentPage === index + 1 ? 'active' : ''}
          >
            {index + 1}
          </button>
        ))}
        
        <button 
          onClick={() => handlePageChange(pagination.currentPage + 1)}
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

### Using React Query (Recommended)

```javascript
import { useQuery } from '@tanstack/react-query';

const useBrands = (page = 1, perPage = 15) => {
  return useQuery({
    queryKey: ['brands', page, perPage],
    queryFn: async () => {
      const response = await fetch(
        `/api/admin/brands?page=${page}&per_page=${perPage}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        }
      );
      const result = await response.json();
      return result.data; // Returns paginated data
    }
  });
};

// Usage in component
const BrandList = () => {
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(15);
  
  const { data, isLoading, error } = useBrands(page, perPage);

  if (isLoading) return <div>Loading...</div>;
  if (error) return <div>Error loading brands</div>;

  return (
    <div>
      {/* Render data.data (brands array) */}
      {data.data.map(brand => (
        <div key={brand.id}>{brand.name}</div>
      ))}
      
      {/* Pagination controls using data.current_page, data.last_page, etc. */}
    </div>
  );
};
```

---

## Custom Hook Example

```javascript
// hooks/usePagination.js
import { useState, useCallback } from 'react';

export const usePagination = (fetchFunction) => {
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
  const [error, setError] = useState(null);

  const fetchData = useCallback(async (page = 1, perPage = 15, filters = {}) => {
    setLoading(true);
    setError(null);
    
    try {
      const result = await fetchFunction(page, perPage, filters);
      
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
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [fetchFunction]);

  const goToPage = useCallback((page) => {
    fetchData(page, pagination.perPage);
  }, [fetchData, pagination.perPage]);

  const changePerPage = useCallback((perPage) => {
    fetchData(1, perPage);
  }, [fetchData]);

  return {
    data,
    pagination,
    loading,
    error,
    fetchData,
    goToPage,
    changePerPage
  };
};

// Usage
const BrandList = () => {
  const fetchBrands = async (page, perPage) => {
    const response = await fetch(`/api/admin/brands?page=${page}&per_page=${perPage}`);
    return await response.json();
  };

  const { data, pagination, loading, goToPage, changePerPage } = usePagination(fetchBrands);

  useEffect(() => {
    fetchData();
  }, []);

  // Render component...
};
```

---

## Pagination Component (Reusable)

```javascript
// components/Pagination.jsx
const Pagination = ({ pagination, onPageChange }) => {
  const { currentPage, lastPage, from, to, total } = pagination;

  const renderPageNumbers = () => {
    const pages = [];
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(lastPage, startPage + maxVisible - 1);

    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      pages.push(
        <button
          key={i}
          onClick={() => onPageChange(i)}
          className={currentPage === i ? 'active' : ''}
        >
          {i}
        </button>
      );
    }

    return pages;
  };

  return (
    <div className="pagination-wrapper">
      <div className="pagination-info">
        Showing {from} to {to} of {total} entries
      </div>
      
      <div className="pagination-controls">
        <button
          onClick={() => onPageChange(1)}
          disabled={currentPage === 1}
        >
          First
        </button>
        
        <button
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage === 1}
        >
          Previous
        </button>
        
        {renderPageNumbers()}
        
        <button
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage === lastPage}
        >
          Next
        </button>
        
        <button
          onClick={() => onPageChange(lastPage)}
          disabled={currentPage === lastPage}
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

## Summary of Changes

### Backend
- ✅ Added pagination to `BrandController::index()`
- ✅ Added pagination to `CategoryController::index()`
- ✅ Default: 15 items per page
- ✅ Customizable via `per_page` query parameter

### Response Format
- ✅ Changed from simple array to paginated object
- ✅ Includes pagination metadata (current_page, last_page, total, etc.)
- ✅ Includes navigation links

### Frontend Updates Needed
- ⚠️ Update API hooks to handle paginated response
- ⚠️ Access data via `result.data.data` instead of `result.data`
- ⚠️ Implement pagination controls
- ⚠️ Handle page changes and per_page changes
- ⚠️ Update brand management page
- ⚠️ Update category management page

---

## Testing

### Test Pagination
```bash
# Test brands pagination
curl "http://localhost:8000/api/admin/brands?page=1&per_page=10"

# Test categories pagination
curl "http://localhost:8000/api/admin/categories?page=1&per_page=10"

# Test with filters
curl "http://localhost:8000/api/admin/brands?active_only=1&page=1&per_page=15"
```

---

## Migration Checklist

- [x] Update BrandController backend
- [x] Update CategoryController backend
- [ ] Update frontend Brand API hook
- [ ] Update frontend Category API hook
- [ ] Update Brand management page component
- [ ] Update Category management page component
- [ ] Add pagination controls component
- [ ] Test pagination functionality
- [ ] Update any other components using these endpoints

---

**Status:** Backend Complete ✅  
**Next Steps:** Update frontend to handle paginated responses
