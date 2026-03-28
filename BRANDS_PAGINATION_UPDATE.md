# Brands Endpoint - Pagination Added

## ✅ Update Summary

Added pagination to the brands endpoint with a default of 50 items per page.

## 📍 Endpoint

```
GET /api/catalog/brands
```

## 📥 Request Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `per_page` | integer | No | 50 | Number of brands per page (max recommended: 100) |
| `page` | integer | No | 1 | Page number |

## 📤 Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Nike",
      "slug": "nike",
      "logo": "brands/nike.png",
      "description": "Just Do It",
      "is_active": true,
      "sort_order": 1,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Adidas",
      "slug": "adidas",
      "logo": "brands/adidas.png",
      "description": "Impossible is Nothing",
      "is_active": true,
      "sort_order": 2,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 50,
    "total": 150,
    "last_page": 3,
    "from": 1,
    "to": 50
  }
}
```

## 📋 Usage Examples

### Default Request (50 items per page)
```bash
curl -X GET http://localhost:8000/api/catalog/brands
```

### Custom Page Size
```bash
curl -X GET "http://localhost:8000/api/catalog/brands?per_page=20"
```

### Specific Page
```bash
curl -X GET "http://localhost:8000/api/catalog/brands?page=2"
```

### Custom Page Size and Page Number
```bash
curl -X GET "http://localhost:8000/api/catalog/brands?per_page=25&page=3"
```

## 🎨 Frontend Integration

### JavaScript/Fetch Example

```javascript
// Fetch brands with default pagination (50 per page)
async function fetchBrands(page = 1, perPage = 50) {
  const response = await fetch(
    `http://localhost:8000/api/catalog/brands?page=${page}&per_page=${perPage}`
  );
  const data = await response.json();
  
  if (data.success) {
    console.log('Brands:', data.data);
    console.log('Pagination:', data.pagination);
    return data;
  }
}

// Usage
fetchBrands(1, 50); // First page, 50 items
```

### React Example

```jsx
import { useState, useEffect } from 'react';

const BrandsList = () => {
  const [brands, setBrands] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchBrands(currentPage);
  }, [currentPage]);

  const fetchBrands = async (page) => {
    setLoading(true);
    try {
      const response = await fetch(
        `http://localhost:8000/api/catalog/brands?page=${page}&per_page=50`
      );
      const data = await response.json();
      
      if (data.success) {
        setBrands(data.data);
        setPagination(data.pagination);
      }
    } catch (error) {
      console.error('Error fetching brands:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h2>Brands</h2>
      
      {loading ? (
        <p>Loading...</p>
      ) : (
        <>
          <div className="brands-grid">
            {brands.map((brand) => (
              <div key={brand.id} className="brand-card">
                <img src={brand.logo} alt={brand.name} />
                <h3>{brand.name}</h3>
                <p>{brand.description}</p>
              </div>
            ))}
          </div>

          {pagination && (
            <div className="pagination">
              <button
                onClick={() => setCurrentPage(currentPage - 1)}
                disabled={currentPage === 1}
              >
                Previous
              </button>
              
              <span>
                Page {pagination.current_page} of {pagination.last_page}
              </span>
              
              <button
                onClick={() => setCurrentPage(currentPage + 1)}
                disabled={currentPage === pagination.last_page}
              >
                Next
              </button>
              
              <p>
                Showing {pagination.from} to {pagination.to} of {pagination.total} brands
              </p>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default BrandsList;
```

### Vue 3 Example

```vue
<template>
  <div class="brands-container">
    <h2>Brands</h2>
    
    <div v-if="loading">Loading...</div>
    
    <div v-else>
      <div class="brands-grid">
        <div v-for="brand in brands" :key="brand.id" class="brand-card">
          <img :src="brand.logo" :alt="brand.name" />
          <h3>{{ brand.name }}</h3>
          <p>{{ brand.description }}</p>
        </div>
      </div>

      <div v-if="pagination" class="pagination">
        <button
          @click="currentPage--"
          :disabled="currentPage === 1"
        >
          Previous
        </button>
        
        <span>
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        
        <button
          @click="currentPage++"
          :disabled="currentPage === pagination.last_page"
        >
          Next
        </button>
        
        <p>
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} brands
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const brands = ref([]);
const pagination = ref(null);
const currentPage = ref(1);
const loading = ref(false);

const fetchBrands = async (page) => {
  loading.value = true;
  try {
    const response = await fetch(
      `http://localhost:8000/api/catalog/brands?page=${page}&per_page=50`
    );
    const data = await response.json();
    
    if (data.success) {
      brands.value = data.data;
      pagination.value = data.pagination;
    }
  } catch (error) {
    console.error('Error fetching brands:', error);
  } finally {
    loading.value = false;
  }
};

watch(currentPage, (newPage) => {
  fetchBrands(newPage);
});

onMounted(() => {
  fetchBrands(currentPage.value);
});
</script>
```

## 📊 Pagination Object Details

| Field | Type | Description |
|-------|------|-------------|
| `current_page` | integer | Current page number |
| `per_page` | integer | Number of items per page |
| `total` | integer | Total number of brands |
| `last_page` | integer | Last page number |
| `from` | integer | Starting item number on current page |
| `to` | integer | Ending item number on current page |

## 🔍 Filtering & Sorting

The brands are automatically:
- ✅ Filtered to show only active brands (`is_active = true`)
- ✅ Sorted by `sort_order` (ascending)

## ⚡ Performance Notes

- Default page size: 50 brands
- Recommended max page size: 100 brands
- Brands are ordered by `sort_order` for consistent results
- Only active brands are returned

## 🎯 Benefits

1. **Better Performance**: Load only what's needed
2. **Improved UX**: Faster page loads
3. **Scalability**: Handles large brand catalogs
4. **Flexibility**: Frontend can control page size
5. **Consistent API**: Matches other paginated endpoints

## 📝 Notes

- The endpoint only returns active brands (`is_active = true`)
- Brands are sorted by `sort_order` in ascending order
- If `per_page` is not specified, defaults to 50
- If `page` is not specified, defaults to 1
- Maximum recommended `per_page` value is 100

---

**Status**: ✅ Implemented and Ready
**Default Behavior**: 50 brands per page
**Backward Compatible**: Yes (returns data in same format with added pagination info)