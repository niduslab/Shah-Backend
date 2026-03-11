# API Endpoints Quick Reference

## Base URL
```
http://localhost:8000/api/admin
```

All admin endpoints require authentication with `auth:sanctum` middleware.

---

## Page Management Endpoints

### 1. Create Page
```http
POST /api/admin/pages
Content-Type: application/json
Authorization: Bearer {token}
```

**Request Body**:
```json
{
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Welcome to Our Store",
  "meta_description": "Discover premium fitness equipment",
  "is_active": true,
  "sort_order": 1
}
```

**Response** (201 Created):
```json
{
  "id": 1,
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Welcome to Our Store",
  "meta_description": "Discover premium fitness equipment",
  "is_active": true,
  "sort_order": 1,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

**cURL Example**:
```bash
curl -X POST http://localhost:8000/api/admin/pages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Home",
    "slug": "home",
    "type": "landing",
    "is_active": true
  }'
```

---

### 2. Add Section to Page
```http
POST /api/admin/pages/{pageId}/sections
Content-Type: application/json
Authorization: Bearer {token}
```

**Request Body Example (Landing Hero Grid)**:
```json
{
  "section_type": "landing_hero_grid",
  "title": "Hero Section",
  "content": {
    "use_video": false,
    "main_card_image": "/images/bike-promo.jpg",
    "main_card_heading": "Elevate Your",
    "main_card_subheading": "Fitness Journey",
    "main_card_badge": "Up to",
    "main_card_badge_text": "40% Discounts",
    "main_card_cta_text": "Shop Now",
    "main_card_cta_link": "/products",
    "top_right_image": "/images/gear-promo.jpg",
    "top_right_title": "Perfect Gear Awaits",
    "top_right_cta_text": "Shop Now",
    "bottom_left_image": "/images/weights-promo.jpg",
    "bottom_left_title": "Shine Bright with Weights",
    "bottom_left_cta_text": "Shop Now",
    "bottom_right_image": "/images/top-picks.jpg",
    "bottom_right_title": "TOP PICKS",
    "bottom_right_cta_text": "Shop Now"
  },
  "settings": {
    "main_card_bg_overlay": true,
    "text_color": "#ffffff"
  },
  "sort_order": 1,
  "is_active": true
}
```

**Response** (201 Created):
```json
{
  "id": 1,
  "page_id": 1,
  "section_type": "landing_hero_grid",
  "title": "Hero Section",
  "content": { ... },
  "settings": { ... },
  "sort_order": 1,
  "is_active": true,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

**cURL Example**:
```bash
curl -X POST http://localhost:8000/api/admin/pages/1/sections \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "section_type": "landing_hero_grid",
    "content": {
      "main_card_heading": "Elevate Your Fitness Journey"
    },
    "sort_order": 1
  }'
```

---

## All Available Endpoints

### Template Management
```http
GET  /api/admin/page-templates
GET  /api/admin/page-templates/category/{category}
GET  /api/admin/page-templates/page-type/{pageType}
GET  /api/admin/page-templates/{templateType}/schema
```

### Page CRUD (via apiResource)
```http
GET    /api/admin/pages              # List all pages
POST   /api/admin/pages              # Create page
GET    /api/admin/pages/{id}         # Get single page
PUT    /api/admin/pages/{id}         # Update page
PATCH  /api/admin/pages/{id}         # Partial update
DELETE /api/admin/pages/{id}         # Delete page
```

### Page Actions
```http
POST /api/admin/pages/{id}/duplicate  # Duplicate page
```

### Section Management
```http
GET    /api/admin/pages/{pageId}/sections              # List sections
POST   /api/admin/pages/{pageId}/sections              # Create section
GET    /api/admin/pages/{pageId}/sections/{sectionId}  # Get section
PUT    /api/admin/pages/{pageId}/sections/{sectionId}  # Update section
DELETE /api/admin/pages/{pageId}/sections/{sectionId}  # Delete section
POST   /api/admin/pages/{pageId}/sections/reorder      # Reorder sections
```

### Public Endpoints (No Auth Required)
```http
GET /api/pages/{slug}           # Get page by slug
GET /api/pages/type/{type}      # Get pages by type
```

---

## Complete Example: Create Landing Page

### Step 1: Create Page
```bash
curl -X POST http://localhost:8000/api/admin/pages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Home",
    "slug": "home",
    "type": "landing",
    "meta_title": "Premium Fitness Equipment",
    "is_active": true
  }'
```

**Response**:
```json
{
  "id": 1,
  "title": "Home",
  "slug": "home",
  "type": "landing",
  ...
}
```

### Step 2: Add Hero Section
```bash
curl -X POST http://localhost:8000/api/admin/pages/1/sections \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "section_type": "landing_hero_grid",
    "content": {
      "use_video": false,
      "main_card_image": "/images/bike.jpg",
      "main_card_heading": "Elevate Your Fitness Journey",
      "main_card_badge": "Up to",
      "main_card_badge_text": "40% Discounts",
      "main_card_cta_text": "Shop Now",
      "top_right_image": "/images/gear.jpg",
      "top_right_title": "Perfect Gear Awaits",
      "top_right_cta_text": "Shop Now",
      "bottom_left_image": "/images/weights.jpg",
      "bottom_left_title": "Shine Bright with Weights",
      "bottom_left_cta_text": "Shop Now",
      "bottom_right_image": "/images/top-picks.jpg",
      "bottom_right_title": "TOP PICKS",
      "bottom_right_cta_text": "Shop Now"
    },
    "sort_order": 1
  }'
```

### Step 3: Add Category Cards
```bash
curl -X POST http://localhost:8000/api/admin/pages/1/sections \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "section_type": "category_cards_two_column",
    "content": {
      "left_card_image": "/images/cardio.jpg",
      "left_card_title": "Cardio Equipment",
      "left_card_description": "Burn calories and boost endurance",
      "left_card_badge": "Save all 45%",
      "left_card_cta_text": "Shop Now",
      "right_card_image": "/images/weights.jpg",
      "right_card_title": "Free Weight Equipment",
      "right_card_description": "Build strength and muscle",
      "right_card_badge": "Up to 30%",
      "right_card_cta_text": "Shop Now"
    },
    "sort_order": 2
  }'
```

### Step 4: Add Pre-Order Showcase
```bash
curl -X POST http://localhost:8000/api/admin/pages/1/sections \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "section_type": "preorder_showcase",
    "content": {
      "section_heading": "Pre-Order Now & Save Big",
      "view_all_text": "View All Preorder Products",
      "view_all_link": "/products/preorder",
      "products": [
        {
          "image": "/images/treadmill.jpg",
          "title": "T Series 10 Treadmill",
          "badge": "Save 30%",
          "cta_text": "Preorder Now",
          "has_carousel": true
        },
        {
          "image": "/images/elliptical.jpg",
          "title": "Step Climber XL",
          "cta_text": "Preorder Now",
          "has_carousel": false
        }
      ]
    },
    "sort_order": 3
  }'
```

### Step 5: View the Page
```bash
curl http://localhost:8000/api/pages/home
```

---

## JavaScript/Fetch Examples

### Create Page
```javascript
const createPage = async () => {
  const response = await fetch('http://localhost:8000/api/admin/pages', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      title: 'Home',
      slug: 'home',
      type: 'landing',
      is_active: true
    })
  });
  
  const page = await response.json();
  console.log('Created page:', page);
  return page;
};
```

### Add Section
```javascript
const addSection = async (pageId) => {
  const response = await fetch(`http://localhost:8000/api/admin/pages/${pageId}/sections`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      section_type: 'landing_hero_grid',
      content: {
        main_card_heading: 'Elevate Your Fitness Journey',
        main_card_cta_text: 'Shop Now'
      },
      sort_order: 1
    })
  });
  
  const section = await response.json();
  console.log('Created section:', section);
  return section;
};
```

---

## Axios Examples

### Create Page
```javascript
import axios from 'axios';

const createPage = async () => {
  try {
    const { data } = await axios.post('http://localhost:8000/api/admin/pages', {
      title: 'Home',
      slug: 'home',
      type: 'landing',
      is_active: true
    }, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    console.log('Created page:', data);
    return data;
  } catch (error) {
    console.error('Error creating page:', error.response.data);
  }
};
```

### Add Section
```javascript
const addSection = async (pageId) => {
  try {
    const { data } = await axios.post(
      `http://localhost:8000/api/admin/pages/${pageId}/sections`,
      {
        section_type: 'landing_hero_grid',
        content: {
          main_card_heading: 'Elevate Your Fitness Journey'
        },
        sort_order: 1
      },
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );
    
    console.log('Created section:', data);
    return data;
  } catch (error) {
    console.error('Error creating section:', error.response.data);
  }
};
```

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required"],
    "type": ["The type field is required"]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "message": "Page not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid",
  "errors": {
    "slug": ["The slug has already been taken"]
  }
}
```

---

## Testing Endpoints

### Using Postman
1. Import the collection
2. Set base URL: `http://localhost:8000/api/admin`
3. Add Authorization header: `Bearer YOUR_TOKEN`
4. Test each endpoint

### Using Thunder Client (VS Code)
1. Create new request
2. Set method and URL
3. Add headers
4. Add body (JSON)
5. Send request

### Using Laravel Artisan
```bash
# List all routes
php artisan route:list --path=admin/pages

# Test with Tinker
php artisan tinker
>>> $page = App\Models\Page::create(['title' => 'Test', 'slug' => 'test', 'type' => 'landing']);
>>> $section = App\Models\PageSection::create(['page_id' => $page->id, 'section_type' => 'landing_hero_grid', 'content' => [], 'sort_order' => 1]);
```

---

## Authentication

### Get Token (Login)
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Response**:
```json
{
  "token": "1|abcdef123456...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

Use the token in subsequent requests:
```
Authorization: Bearer 1|abcdef123456...
```

---

## Summary

✅ **POST /api/admin/pages** - Create page (already exists via apiResource)
✅ **POST /api/admin/pages/{pageId}/sections** - Create section (already exists)
✅ All CRUD operations available
✅ Template browsing endpoints
✅ Public access endpoints

All endpoints are ready to use! Just ensure you have a valid authentication token.
