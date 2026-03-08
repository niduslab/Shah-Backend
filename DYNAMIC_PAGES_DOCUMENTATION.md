# Dynamic Pages System Documentation

## Overview
A flexible content management system for building dynamic pages with reusable sections. Perfect for landing pages, brand showcases, flash deals, galleries, and more.

## Architecture

### Database Tables
1. **pages** - Main page records
2. **page_sections** - Modular content blocks
3. **media_library** - Reusable media assets

### Section Types
- `hero_slider` - Hero banners with images/videos
- `product_grid` - Product showcase grids
- `category_grid` - Category navigation
- `brand_showcase` - Brand feature sections
- `banner` - Promotional banners
- `video_section` - Video content
- `text_content` - Rich text blocks
- `custom` - Custom HTML/components

## API Endpoints

### Public Routes

#### Get Page by Slug
```http
GET /api/pages/{slug}
```

Response:
```json
{
  "id": 1,
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "sections": [
    {
      "id": 1,
      "section_type": "hero_slider",
      "title": "Hero Slider",
      "content": {
        "slides": [
          {
            "type": "image",
            "media_url": "/images/hero-1.jpg",
            "title": "Elevate Your Fitness Journey",
            "subtitle": "Up to 40% Discounts",
            "cta_text": "Shop Now",
            "cta_link": "/shop"
          }
        ]
      },
      "settings": {
        "autoplay": true,
        "interval": 5000
      }
    }
  ]
}
```

#### Get Pages by Type
```http
GET /api/pages/type/{type}
```
Types: `landing`, `brand`, `flash_deal`, `gallery`, `custom`

### Admin Routes (Requires Authentication)

#### List All Pages
```http
GET /api/admin/pages
```

#### Create Page
```http
POST /api/admin/pages
Content-Type: application/json

{
  "title": "Summer Sale",
  "slug": "summer-sale",
  "type": "landing",
  "meta_title": "Summer Sale 2026",
  "meta_description": "Best deals of the summer",
  "is_active": true
}
```

#### Update Page
```http
PUT /api/admin/pages/{id}
```

#### Delete Page
```http
DELETE /api/admin/pages/{id}
```

#### Add Section to Page
```http
POST /api/admin/pages/{pageId}/sections
Content-Type: application/json

{
  "section_type": "hero_slider",
  "title": "Main Hero",
  "content": {
    "slides": [
      {
        "type": "image",
        "media_url": "/storage/media/hero.jpg",
        "title": "Welcome",
        "cta_text": "Shop Now",
        "cta_link": "/shop"
      }
    ]
  },
  "settings": {
    "autoplay": true,
    "interval": 5000
  },
  "sort_order": 1
}
```

#### Update Section
```http
PUT /api/admin/pages/{pageId}/sections/{sectionId}
```

#### Delete Section
```http
DELETE /api/admin/pages/{pageId}/sections/{sectionId}
```

#### Reorder Sections
```http
POST /api/admin/pages/{pageId}/sections/reorder
Content-Type: application/json

{
  "sections": [
    {"id": 1, "sort_order": 1},
    {"id": 2, "sort_order": 2},
    {"id": 3, "sort_order": 3}
  ]
}
```

### Media Library Routes

#### List Media
```http
GET /api/admin/media?file_type=image
```

#### Upload Media
```http
POST /api/admin/media
Content-Type: multipart/form-data

file: [binary]
title: "Hero Image"
alt_text: "Fitness equipment"
```

#### Update Media
```http
PUT /api/admin/media/{id}
```

#### Delete Media
```http
DELETE /api/admin/media/{id}
```

## Section Content Examples

### Hero Slider Section
```json
{
  "section_type": "hero_slider",
  "content": {
    "slides": [
      {
        "type": "image",
        "media_url": "/storage/media/hero-1.jpg",
        "title": "Elevate Your Fitness Journey",
        "subtitle": "Up to 40% Discounts",
        "cta_text": "Shop Now",
        "cta_link": "/shop",
        "text_position": "left"
      },
      {
        "type": "video",
        "media_url": "/storage/media/promo.mp4",
        "poster": "/storage/media/poster.jpg",
        "title": "New Arrivals",
        "cta_text": "Explore",
        "cta_link": "/new"
      }
    ]
  },
  "settings": {
    "autoplay": true,
    "interval": 5000,
    "show_arrows": true,
    "show_dots": true
  }
}
```

### Product Grid Section
```json
{
  "section_type": "product_grid",
  "title": "Featured Products",
  "content": {
    "layout": "grid",
    "columns": 4,
    "items": [
      {
        "type": "product_card",
        "image": "/storage/media/gear.jpg",
        "title": "Perfect Gear Awaits",
        "description": "Premium fitness equipment",
        "cta_text": "Shop Now",
        "cta_link": "/category/gear",
        "badge": "40% OFF"
      }
    ]
  },
  "settings": {
    "show_prices": true,
    "show_ratings": true
  }
}
```

### Brand Showcase Section
```json
{
  "section_type": "brand_showcase",
  "title": "Turn Your Home Into A Complete Fitness Space",
  "content": {
    "background_type": "image",
    "background_url": "/storage/media/nordictrack-bg.jpg",
    "logo": "/storage/media/nordictrack-logo.png",
    "description": "NordicTrack is a leader in home fitness equipment...",
    "cta_text": "Shop Nordictrack",
    "cta_link": "/brand/nordictrack"
  },
  "settings": {
    "text_color": "white",
    "overlay": true,
    "overlay_opacity": 0.5
  }
}
```

### Category Grid Section
```json
{
  "section_type": "category_grid",
  "title": "Explore Categories",
  "content": {
    "categories": [
      {
        "name": "Bikes",
        "image": "/storage/media/bikes.jpg",
        "link": "/category/bikes",
        "product_count": 45
      },
      {
        "name": "Treadmills",
        "image": "/storage/media/treadmills.jpg",
        "link": "/category/treadmills",
        "product_count": 32
      }
    ]
  },
  "settings": {
    "columns": 4,
    "show_border": true,
    "hover_effect": "zoom"
  }
}
```

### Banner Section
```json
{
  "section_type": "banner",
  "content": {
    "image": "/storage/media/sale-banner.jpg",
    "title": "Flash Sale",
    "subtitle": "Limited Time Offer",
    "cta_text": "Shop Now",
    "cta_link": "/flash-deals",
    "countdown": {
      "enabled": true,
      "end_date": "2026-03-15T23:59:59Z"
    }
  }
}
```

## Frontend Implementation

### React/Vue Example
```javascript
// Fetch page data
const response = await fetch('/api/pages/home');
const page = await response.json();

// Render sections dynamically
page.sections.map(section => {
  switch(section.section_type) {
    case 'hero_slider':
      return <HeroSlider data={section.content} settings={section.settings} />;
    case 'product_grid':
      return <ProductGrid data={section.content} />;
    case 'brand_showcase':
      return <BrandShowcase data={section.content} />;
    case 'category_grid':
      return <CategoryGrid data={section.content} />;
    default:
      return null;
  }
});
```

## Best Practices

1. **Use Media Library**: Upload assets to media library for reusability
2. **Optimize Images**: Compress images before upload
3. **Lazy Loading**: Load sections progressively
4. **Cache Pages**: Cache page data for better performance
5. **SEO**: Always set meta_title and meta_description
6. **Mobile First**: Design sections to be responsive
7. **A/B Testing**: Create multiple page versions to test

## Migration & Seeding

Run migrations:
```bash
php artisan migrate
```

Seed sample data:
```bash
php artisan db:seed --class=PageSeeder
```

## Use Cases

1. **Landing Pages**: Build custom landing pages for campaigns
2. **Brand Pages**: Showcase specific brands with custom layouts
3. **Flash Deals**: Create urgency with countdown timers
4. **Gallery Pages**: Display product galleries and lookbooks
5. **Seasonal Campaigns**: Quick setup for holiday promotions
6. **A/B Testing**: Test different layouts and content

This system gives you complete flexibility to build any page layout without touching code!
