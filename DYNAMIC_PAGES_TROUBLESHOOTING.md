# Dynamic Pages API - Troubleshooting Guide

## Common Errors & Solutions

### Error: "NaN" in URL
**Problem:** `POST /api/admin/pages/NaN/sections`

**Cause:** Frontend is trying to add a section before the page is created, or the page ID is not being stored/passed correctly.

**Solution:**
```javascript
// ❌ WRONG - Don't do this
const pageId = undefined; // or NaN
fetch(`/api/admin/pages/${pageId}/sections`, {...});

// ✅ CORRECT - Always create page first, then use returned ID
const createPageResponse = await fetch('/api/admin/pages', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    title: 'My Page',
    slug: 'my-page',
    type: 'landing'
  })
});

const page = await createPageResponse.json();
console.log('Created page ID:', page.id); // Use this ID!

// Now add sections using the page.id
const sectionResponse = await fetch(`/api/admin/pages/${page.id}/sections`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    section_type: 'hero_slider',
    title: 'Main Hero',
    content: {
      slides: []
    },
    settings: {}
  })
});
```

---

### Error: "Server Error" (500)
**Problem:** Generic server error

**Common Causes:**
1. Missing required fields
2. Invalid JSON structure
3. Database connection issues
4. Page doesn't exist

**Debug Steps:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Or on Windows
Get-Content storage/logs/laravel.log -Tail 50 -Wait
```

---

### Error: "Unauthenticated" (401)
**Problem:** Missing or invalid authentication token

**Solution:**
```javascript
// Make sure to include the Bearer token
fetch('/api/admin/pages', {
  headers: {
    'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
    'Content-Type': 'application/json'
  }
});
```

---

### Error: "Page not found" (404)
**Problem:** Invalid page ID

**Solution:**
```javascript
// Verify page exists first
const checkPage = await fetch(`/api/admin/pages/${pageId}`);
if (!checkPage.ok) {
  console.error('Page does not exist');
  return;
}

// Then add section
await fetch(`/api/admin/pages/${pageId}/sections`, {...});
```

---

## Complete Working Example

### React Admin Panel Example

```jsx
import React, { useState } from 'react';

function PageBuilder() {
  const [pageId, setPageId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const createPage = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('/api/admin/pages', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          title: 'New Landing Page',
          slug: 'new-landing-page',
          type: 'landing',
          meta_title: 'New Landing Page',
          is_active: true
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const page = await response.json();
      setPageId(page.id);
      console.log('Page created:', page);
      return page;
    } catch (err) {
      setError(err.message);
      console.error('Error creating page:', err);
    } finally {
      setLoading(false);
    }
  };

  const addSection = async (sectionData) => {
    if (!pageId) {
      setError('Please create a page first');
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch(`/api/admin/pages/${pageId}/sections`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(sectionData)
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }

      const section = await response.json();
      console.log('Section added:', section);
      return section;
    } catch (err) {
      setError(err.message);
      console.error('Error adding section:', err);
    } finally {
      setLoading(false);
    }
  };

  const addHeroSection = async () => {
    await addSection({
      section_type: 'hero_slider',
      title: 'Main Hero',
      content: {
        slides: [
          {
            type: 'image',
            media_url: '/storage/media/hero.jpg',
            title: 'Welcome',
            subtitle: 'To our store',
            cta_text: 'Shop Now',
            cta_link: '/shop'
          }
        ]
      },
      settings: {
        autoplay: true,
        interval: 5000
      }
    });
  };

  return (
    <div>
      <h1>Page Builder</h1>
      
      {error && (
        <div className="alert alert-danger">{error}</div>
      )}

      {!pageId ? (
        <button onClick={createPage} disabled={loading}>
          {loading ? 'Creating...' : 'Create New Page'}
        </button>
      ) : (
        <div>
          <p>Page ID: {pageId}</p>
          <button onClick={addHeroSection} disabled={loading}>
            {loading ? 'Adding...' : 'Add Hero Section'}
          </button>
        </div>
      )}
    </div>
  );
}

export default PageBuilder;
```

---

## Validation Rules

### Page Creation
```json
{
  "title": "required|string|max:255",
  "slug": "nullable|string|unique",
  "type": "required|in:landing,brand,flash_deal,gallery,custom",
  "meta_title": "nullable|string",
  "meta_description": "nullable|string",
  "is_active": "boolean (default: true)"
}
```

### Section Creation
```json
{
  "section_type": "required|string",
  "title": "nullable|string",
  "content": "required|object",
  "settings": "nullable|object",
  "sort_order": "nullable|integer (auto-calculated if not provided)",
  "is_active": "nullable|boolean (default: true)"
}
```

---

## API Response Formats

### Success Response (201 Created)
```json
{
  "id": 1,
  "page_id": 5,
  "section_type": "hero_slider",
  "title": "Main Hero",
  "content": {
    "slides": []
  },
  "settings": {},
  "sort_order": 1,
  "is_active": true,
  "created_at": "2026-03-07T10:00:00.000000Z",
  "updated_at": "2026-03-07T10:00:00.000000Z"
}
```

### Error Response (422 Validation Error)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "section_type": [
      "The section type field is required."
    ],
    "content": [
      "The content field is required."
    ]
  }
}
```

### Error Response (404 Not Found)
```json
{
  "message": "No query results for model [App\\Models\\Page] 999"
}
```

---

## Testing with cURL

### Create Page
```bash
curl -X POST http://localhost:8000/api/admin/pages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Test Page",
    "slug": "test-page",
    "type": "landing"
  }'
```

### Add Section (replace {PAGE_ID} with actual ID)
```bash
curl -X POST http://localhost:8000/api/admin/pages/{PAGE_ID}/sections \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "section_type": "hero_slider",
    "title": "Hero",
    "content": {
      "slides": []
    },
    "settings": {}
  }'
```

---

## Best Practices

1. **Always validate page ID before adding sections**
   ```javascript
   if (!pageId || isNaN(pageId)) {
     console.error('Invalid page ID');
     return;
   }
   ```

2. **Store page ID after creation**
   ```javascript
   const page = await createPage();
   localStorage.setItem('currentPageId', page.id);
   ```

3. **Handle errors gracefully**
   ```javascript
   try {
     await addSection(data);
   } catch (error) {
     console.error('Failed to add section:', error);
     alert('Failed to add section. Please try again.');
   }
   ```

4. **Use TypeScript for type safety**
   ```typescript
   interface Page {
     id: number;
     title: string;
     slug: string;
     type: 'landing' | 'brand' | 'flash_deal' | 'gallery' | 'custom';
   }

   interface Section {
     section_type: string;
     title?: string;
     content: Record<string, any>;
     settings?: Record<string, any>;
   }
   ```

5. **Validate data before sending**
   ```javascript
   const validateSection = (section) => {
     if (!section.section_type) {
       throw new Error('section_type is required');
     }
     if (!section.content) {
       throw new Error('content is required');
     }
     return true;
   };
   ```

---

## Database Check

If you're still having issues, verify the tables exist:

```bash
php artisan migrate:status
```

Run migrations if needed:
```bash
php artisan migrate
```

Seed sample data:
```bash
php artisan db:seed --class=PageSeeder
```

---

## Need Help?

Check the logs:
- Laravel: `storage/logs/laravel.log`
- Browser Console: F12 → Console tab
- Network Tab: F12 → Network tab (check request/response)
