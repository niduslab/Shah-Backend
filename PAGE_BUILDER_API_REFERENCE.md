# Page Builder API Reference

Quick reference for all page builder endpoints.

---

## Template Management

### Get All Templates
```http
GET /api/admin/page-templates
```

**Response**:
```json
{
  "hero_banner": {
    "name": "Hero Banner",
    "description": "Full-width hero section...",
    "category": "hero",
    "schema": { ... },
    "settings_schema": { ... }
  },
  ...
}
```

---

### Get Templates by Category
```http
GET /api/admin/page-templates/category/{category}
```

**Categories**: `hero`, `content`, `banner`, `products`, `cta`

**Example**: `GET /api/admin/page-templates/category/hero`

---

### Get Template Schema
```http
GET /api/admin/page-templates/{templateType}/schema
```

**Example**: `GET /api/admin/page-templates/product_hero/schema`

**Response**:
```json
{
  "name": "Product Hero with Image",
  "description": "Hero section showcasing a single product",
  "category": "hero",
  "schema": {
    "product_image": {
      "type": "image",
      "required": true
    },
    "heading": {
      "type": "text",
      "required": true
    },
    ...
  },
  "settings_schema": {
    "layout": {
      "type": "select",
      "options": ["image_left", "image_right"],
      "default": "image_left"
    }
  }
}
```

---

## Page Management

### List All Pages
```http
GET /api/admin/pages
```

**Response**:
```json
[
  {
    "id": 1,
    "title": "Home",
    "slug": "home",
    "type": "landing",
    "is_active": true,
    "sections": [...]
  }
]
```

---

### Create Page
```http
POST /api/admin/pages
```

**Body**:
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

**Fields**:
- `title` (required): Page title
- `slug` (optional): Auto-generated from title if not provided
- `type` (required): `landing`, `brand`, `flash_deal`, `gallery`, `custom`
- `meta_title` (optional): SEO title
- `meta_description` (optional): SEO description
- `is_active` (optional): Default `true`
- `sort_order` (optional): Default `0`

---

### Get Page
```http
GET /api/admin/pages/{id}
```

**Response**:
```json
{
  "id": 1,
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Welcome",
  "meta_description": "...",
  "is_active": true,
  "sort_order": 1,
  "sections": [
    {
      "id": 1,
      "section_type": "product_hero",
      "title": "Hero Section",
      "content": { ... },
      "settings": { ... },
      "sort_order": 1,
      "is_active": true
    }
  ]
}
```

---

### Update Page
```http
PUT /api/admin/pages/{id}
```

**Body**:
```json
{
  "title": "Updated Title",
  "meta_title": "New SEO Title",
  "is_active": false
}
```

---

### Delete Page
```http
DELETE /api/admin/pages/{id}
```

**Response**:
```json
{
  "message": "Page deleted successfully"
}
```

---

### Duplicate Page
```http
POST /api/admin/pages/{id}/duplicate
```

Creates a copy of the page with all sections. The duplicate is set to inactive by default.

**Response**:
```json
{
  "id": 2,
  "title": "Home (Copy)",
  "slug": "home-copy-1234567890",
  "is_active": false,
  "sections": [...]
}
```

---

## Section Management

### Get Page Sections
```http
GET /api/admin/pages/{pageId}/sections
```

**Response**:
```json
[
  {
    "id": 1,
    "page_id": 1,
    "section_type": "product_hero",
    "title": "Hero Section",
    "content": {
      "product_image": "/images/rowing.jpg",
      "heading": "Smart Rowing",
      "description": "..."
    },
    "settings": {
      "layout": "image_left"
    },
    "sort_order": 1,
    "is_active": true
  }
]
```

---

### Add Section
```http
POST /api/admin/pages/{pageId}/sections
```

**Body**:
```json
{
  "section_type": "product_hero",
  "title": "Hero Section",
  "content": {
    "product_image": "/images/rowing-machine.jpg",
    "heading": "Smart Rowing. Full-Body Results.",
    "subheading": "Real Progress.",
    "description": "Experience a powerful workout...",
    "cta_text": "Shop Rowers",
    "cta_link": "/products/rowers",
    "background_color": "#e8f4f8"
  },
  "settings": {
    "layout": "image_left",
    "image_width": "50%"
  },
  "sort_order": 1,
  "is_active": true
}
```

**Fields**:
- `section_type` (required): Template type (e.g., `product_hero`)
- `title` (optional): Admin label for the section
- `content` (required): Section content matching template schema
- `settings` (optional): Layout/styling options
- `sort_order` (optional): Auto-calculated if not provided
- `is_active` (optional): Default `true`

---

### Get Single Section
```http
GET /api/admin/pages/{pageId}/sections/{sectionId}
```

---

### Update Section
```http
PUT /api/admin/pages/{pageId}/sections/{sectionId}
```

**Body**:
```json
{
  "content": {
    "heading": "Updated Heading",
    "description": "New description"
  },
  "settings": {
    "layout": "image_right"
  }
}
```

---

### Delete Section
```http
DELETE /api/admin/pages/{pageId}/sections/{sectionId}
```

**Response**:
```json
{
  "message": "Section deleted successfully"
}
```

---

### Reorder Sections
```http
POST /api/admin/pages/{pageId}/sections/reorder
```

**Body**:
```json
{
  "sections": [
    { "id": 1, "sort_order": 1 },
    { "id": 3, "sort_order": 2 },
    { "id": 2, "sort_order": 3 }
  ]
}
```

**Response**:
```json
{
  "message": "Sections reordered successfully"
}
```

---

## Public Endpoints (Frontend)

### Get Page by Slug
```http
GET /api/pages/{slug}
```

**Example**: `GET /api/pages/home`

**Response**:
```json
{
  "id": 1,
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Welcome",
  "meta_description": "...",
  "sections": [
    {
      "id": 1,
      "section_type": "product_hero",
      "content": { ... },
      "settings": { ... }
    }
  ]
}
```

Only returns active sections in order.

---

### Get Pages by Type
```http
GET /api/pages/type/{type}
```

**Types**: `landing`, `brand`, `flash_deal`, `gallery`, `custom`

**Example**: `GET /api/pages/type/landing`

---

## Complete Examples

### Example 1: Create Homepage with Hero

```javascript
// 1. Create page
const response = await fetch('/api/admin/pages', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    title: 'Home',
    slug: 'home',
    type: 'landing',
    meta_title: 'NordicTrack - Premium Fitness Equipment',
    is_active: true
  })
});
const page = await response.json();

// 2. Add hero section
await fetch(`/api/admin/pages/${page.id}/sections`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    section_type: 'product_hero',
    title: 'Hero Section',
    content: {
      product_image: '/images/rowing-machine.jpg',
      heading: 'Smart Rowing. Full-Body Results.',
      subheading: 'Real Progress.',
      description: 'Experience a powerful, low-impact workout...',
      cta_text: 'Shop Rowers',
      cta_link: '/products/rowers',
      background_color: '#e8f4f8'
    },
    settings: {
      layout: 'image_left',
      image_width: '50%'
    }
  })
});
```

---

### Example 2: Add Two-Column Cards

```javascript
await fetch(`/api/admin/pages/${pageId}/sections`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    section_type: 'two_column_cards',
    title: 'Featured Categories',
    content: {
      card_1_image: '/images/treadmill.jpg',
      card_1_title: 'T Series 16 Treadmill',
      card_1_cta_text: 'Shop Treadmill',
      card_1_cta_link: '/products/treadmills',
      
      card_2_image: '/images/elliptical.jpg',
      card_2_title: 'Step Climber XL',
      card_2_cta_text: 'Shop Ellipticals',
      card_2_cta_link: '/products/ellipticals'
    },
    settings: {
      card_height: 'large',
      gap: 'medium'
    }
  })
});
```

---

### Example 3: Add Stats Section

```javascript
await fetch(`/api/admin/pages/${pageId}/sections`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    section_type: 'stats_section',
    title: 'Our Legacy',
    content: {
      section_title: 'Thinking Behind the Work',
      section_description: 'NordicTrack delivers a premium personal training experience...',
      stats: [
        { value: '51 +', label: 'Years of Experiences' },
        { value: '1M +', label: 'Happy Customers' },
        { value: '50 +', label: 'Available in Countries' }
      ]
    },
    settings: {
      layout: 'horizontal',
      background_color: '#ffffff'
    }
  })
});
```

---

### Example 4: Add Promo Grid

```javascript
await fetch(`/api/admin/pages/${pageId}/sections`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    section_type: 'promo_grid',
    title: 'Special Offers',
    content: {
      main_card_image: '/images/bike-promo.jpg',
      main_card_title: 'Elevate Your Fitness Journey',
      main_card_subtitle: '',
      main_card_badge: 'Up to 40% Discounts',
      main_card_cta_text: 'Shop Now',
      main_card_cta_link: '/promotions/fitness-sale',
      
      side_card_1_image: '/images/gear-promo.jpg',
      side_card_1_title: 'Perfect Gear Awaits',
      side_card_1_cta_text: 'Shop Now',
      side_card_1_cta_link: '/products/accessories',
      
      side_card_2_image: '/images/weights-promo.jpg',
      side_card_2_title: 'Shine Bright with Weights',
      side_card_2_cta_text: 'Shop Now',
      side_card_2_cta_link: '/products/weights'
    },
    settings: {
      layout: 'main_left',
      background_color: '#ffffff'
    }
  })
});
```

---

### Example 5: Add Category Grid

```javascript
await fetch(`/api/admin/pages/${pageId}/sections`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    section_type: 'category_grid',
    title: 'Equipment Categories',
    content: {
      section_title: 'Explore The Nordictrack Categories',
      categories: [
        { image: '/images/bikes-category.jpg', title: 'Bikes', link: '/products/bikes' },
        { image: '/images/treadmills-category.jpg', title: 'Treadmills', link: '/products/treadmills' },
        { image: '/images/ellipticals-category.jpg', title: 'Ellipticals', link: '/products/ellipticals' },
        { image: '/images/rowers-category.jpg', title: 'Rowers', link: '/products/rowers' }
      ]
    },
    settings: {
      columns: 4,
      card_style: 'bordered'
    }
  })
});
```

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required"],
    "content.heading": ["The heading field is required"]
  }
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

## Notes

- All admin endpoints require authentication (`auth:sanctum` middleware)
- Admin endpoints require admin role (`admin` middleware)
- Public endpoints (`/api/pages/*`) don't require authentication
- Sections are automatically ordered by `sort_order`
- Inactive sections are excluded from public API responses
- Images should be uploaded to media library first
- Template validation happens server-side
