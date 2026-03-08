# Landing & Brand Pages Implementation Guide

## Overview

The page builder now supports two distinct page types:

1. **Landing Pages** - Generic homepage/marketing pages
2. **Brand Pages** - Pages connected to specific brands in your database

Each page type has its own set of optimized section templates.

---

## Page Types

### Landing Page (`type: 'landing'`)

**Purpose**: Generic homepage, campaign pages, seasonal promotions

**Available Templates**:
- Landing Hero Grid (4 sections OR video)
- Pre-Order Showcase
- Category Cards (Two Column)
- Stats Section
- Promo Grid
- Category Grid
- Full Width Banner
- Product Showcase
- CTA Section

**Example URL**: `/pages/home`, `/pages/summer-sale`

---

### Brand Page (`type: 'brand'`)

**Purpose**: Showcase specific brand products and information

**Connected to**: `brands` table in database

**Available Templates**:
- Brand Hero Banner
- Brand Products Grid (auto-filtered by brand)
- Stats Section
- Category Grid
- Promo Grid
- Full Width Banner
- Product Showcase
- CTA Section

**Example URL**: `/pages/brands/nordictrack`, `/pages/brands/proform`

---

## New Section Templates

### 1. Landing Hero Grid (4 Sections)

**Template Type**: `landing_hero_grid`

**Page Types**: Landing only

**Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌──────────────────┐  ┌──────────┐               │
│  │                  │  │  Perfect │               │
│  │   Elevate Your   │  │   Gear   │               │
│  │   Fitness        │  │  Awaits  │               │
│  │   Journey        │  │          │               │
│  │                  │  │[Shop Now]│               │
│  │  [Up to 40%]     │  └──────────┘               │
│  │  [Discounts]     │                             │
│  │                  │  ┌──────────┐               │
│  │  [Shop Now]      │  │  Shine   │               │
│  │                  │  │  Bright  │               │
│  │                  │  │  with    │               │
│  │                  │  │ Weights  │               │
│  │                  │  │          │               │
│  │                  │  │[Shop Now]│               │
│  └──────────────────┘  └──────────┘               │
│                                                     │
│                        ┌──────────┐               │
│                        │   TOP    │               │
│                        │  PICKS   │               │
│                        │          │               │
│                        │[Shop Now]│               │
│                        └──────────┘               │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Special Feature**: Can use video instead of 4-section grid

**Content Fields**:
```json
{
  "use_video": false,
  
  // Video option (if use_video = true)
  "video_url": "https://youtube.com/watch?v=...",
  "video_poster": "/images/video-poster.jpg",
  
  // Main card (left - large)
  "main_card_image": "/images/bike.jpg",
  "main_card_heading": "Elevate Your",
  "main_card_subheading": "Fitness Journey",
  "main_card_badge": "Up to",
  "main_card_badge_text": "40% Discounts",
  "main_card_cta_text": "Shop Now",
  "main_card_cta_link": "/products",
  
  // Top right card
  "top_right_image": "/images/gear.jpg",
  "top_right_title": "Perfect Gear Awaits",
  "top_right_cta_text": "Shop Now",
  "top_right_cta_link": "/products/accessories",
  
  // Bottom left card
  "bottom_left_image": "/images/weights.jpg",
  "bottom_left_title": "Shine Bright with Weights",
  "bottom_left_cta_text": "Shop Now",
  "bottom_left_cta_link": "/products/weights",
  
  // Bottom right card
  "bottom_right_image": "/images/top-picks.jpg",
  "bottom_right_title": "TOP PICKS",
  "bottom_right_cta_text": "Shop Now",
  "bottom_right_cta_link": "/products/featured"
}
```

---

### 2. Pre-Order Showcase

**Template Type**: `preorder_showcase`

**Page Types**: Landing, Brand

**Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│        Pre-Order Now & Save Big                     │
│                          [View All Preorder →]      │
│                                                     │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐          │
│  │Save  │  │      │  │      │  │      │          │
│  │30%   │  │      │  │      │  │      │          │
│  │      │  │      │  │      │  │      │          │
│  │Tread-│  │Ellip-│  │ Bike │  │Rower │          │
│  │mill  │  │tical │  │      │  │      │          │
│  │      │  │      │  │      │  │      │          │
│  │[Pre- │  │[Pre- │  │[Pre- │  │[Pre- │          │
│  │order]│  │order]│  │order]│  │order]│          │
│  │• • • │  │      │  │      │  │      │          │
│  └──────┘  └──────┘  └──────┘  └──────┘          │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "section_heading": "Pre-Order Now & Save Big",
  "view_all_text": "View All Preorder Products",
  "view_all_link": "/products/preorder",
  "products": [
    {
      "image": "/images/treadmill.jpg",
      "title": "Nordictrack T Series 10 Treadmill",
      "badge": "Save 30%",
      "cta_text": "Preorder Now",
      "cta_link": "/products/t-series-10",
      "has_carousel": true
    }
  ]
}
```

---

### 3. Category Cards (Two Column)

**Template Type**: `category_cards_two_column`

**Page Types**: Landing, Brand

**Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌────────────────────┐  ┌────────────────────┐   │
│  │                    │  │                    │   │
│  │  Cardio Equipment  │  │ Free Weight Equip. │   │
│  │                    │  │                    │   │
│  │  Burn calories...  │  │  Burn calories...  │   │
│  │                    │  │                    │   │
│  │  [Save all 45%]    │  │  [Up to 30%]       │   │
│  │                    │  │                    │   │
│  │  [Shop Now →]      │  │  [Shop Now →]      │   │
│  │                    │  │                    │   │
│  └────────────────────┘  └────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "left_card_image": "/images/cardio.jpg",
  "left_card_title": "Cardio Equipment's",
  "left_card_description": "Burn calories and boost endurance...",
  "left_card_badge": "Save all 45%",
  "left_card_cta_text": "Shop Now",
  "left_card_cta_link": "/categories/cardio",
  
  "right_card_image": "/images/weights.jpg",
  "right_card_title": "Free Weight Equipment's",
  "right_card_description": "Build strength and muscle...",
  "right_card_badge": "Up to 30%",
  "right_card_cta_text": "Shop Now",
  "right_card_cta_link": "/categories/weights"
}
```

---

### 4. Brand Hero Banner

**Template Type**: `brand_hero`

**Page Types**: Brand only

**Special Feature**: Can link to brand from database

**Content Fields**:
```json
{
  "brand_id": 1,
  "background_image": "/images/nordictrack-hero.jpg",
  "logo": "/images/nordictrack-logo.png",
  "heading": "Smart Rowing. Full-Body Results.",
  "subheading": "Real Progress.",
  "description": "Experience a powerful workout...",
  "cta_text": "Shop Rowers",
  "cta_link": "/brands/nordictrack/rowers"
}
```

**Settings**:
```json
{
  "height": "large",
  "text_position": "left",
  "text_color": "#ffffff",
  "overlay_opacity": 40
}
```

---

### 5. Brand Products Grid

**Template Type**: `brand_products_grid`

**Page Types**: Brand only

**Special Feature**: Auto-filters products by brand

**Content Fields**:
```json
{
  "brand_id": 1,
  "section_title": "Explore NordicTrack Equipment",
  "product_source": "brand_all",
  "limit": 12
}
```

**Product Sources**:
- `brand_all` - All products from the brand
- `brand_featured` - Only featured products from the brand
- `manual` - Manually selected products

**Settings**:
```json
{
  "columns": 4,
  "show_price": true,
  "show_add_to_cart": true
}
```

---

## API Usage

### Get Templates for Landing Pages

```bash
GET /api/admin/page-templates/page-type/landing
```

**Response**:
```json
{
  "landing_hero_grid": { ... },
  "preorder_showcase": { ... },
  "category_cards_two_column": { ... },
  ...
}
```

---

### Get Templates for Brand Pages

```bash
GET /api/admin/page-templates/page-type/brand
```

**Response**:
```json
{
  "brand_hero": { ... },
  "brand_products_grid": { ... },
  "stats_section": { ... },
  ...
}
```

---

### Create Landing Page

```bash
POST /api/admin/pages
```

```json
{
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Welcome to Our Store",
  "is_active": true
}
```

---

### Create Brand Page

```bash
POST /api/admin/pages
```

```json
{
  "title": "NordicTrack",
  "slug": "brands/nordictrack",
  "type": "brand",
  "meta_title": "NordicTrack - Premium Fitness Equipment",
  "is_active": true
}
```

---

### Add Landing Hero Grid Section

```bash
POST /api/admin/pages/{pageId}/sections
```

```json
{
  "section_type": "landing_hero_grid",
  "content": {
    "use_video": false,
    "main_card_image": "/images/bike.jpg",
    "main_card_heading": "Elevate Your",
    "main_card_subheading": "Fitness Journey",
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
  }
}
```

---

### Add Landing Hero with Video

```json
{
  "section_type": "landing_hero_grid",
  "content": {
    "use_video": true,
    "video_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "video_poster": "/images/video-poster.jpg"
  }
}
```

---

## Sample Pages

### Landing Page Structure

```
1. Landing Hero Grid (4 sections)
2. Pre-Order Showcase
3. Category Cards (Two Column)
```

### Brand Page Structure

```
1. Brand Hero Banner
2. Stats Section
3. Brand Products Grid
4. Category Grid
```

---

## Frontend Implementation

### Landing Hero Grid Component

```jsx
// components/sections/LandingHeroGrid.jsx
export default function LandingHeroGrid({ content, settings }) {
  if (content.use_video) {
    return (
      <section className="landing-hero-video">
        <video 
          src={content.video_url}
          poster={content.video_poster}
          controls
          autoPlay
          muted
        />
      </section>
    );
  }

  return (
    <section className="landing-hero-grid">
      <div className="container">
        <div className="grid-layout">
          {/* Main card - large */}
          <div className="main-card">
            <img src={content.main_card_image} alt={content.main_card_heading} />
            <div className="overlay">
              <h1>{content.main_card_heading}</h1>
              <h2>{content.main_card_subheading}</h2>
              {content.main_card_badge && (
                <div className="badge">
                  <span className="badge-value">{content.main_card_badge}</span>
                  <span className="badge-text">{content.main_card_badge_text}</span>
                </div>
              )}
              <a href={content.main_card_cta_link} className="btn">
                {content.main_card_cta_text}
              </a>
            </div>
          </div>

          {/* Top right card */}
          <div className="small-card">
            <img src={content.top_right_image} alt={content.top_right_title} />
            <div className="overlay">
              <h3>{content.top_right_title}</h3>
              <a href={content.top_right_cta_link} className="btn">
                {content.top_right_cta_text}
              </a>
            </div>
          </div>

          {/* Bottom left card */}
          <div className="small-card">
            <img src={content.bottom_left_image} alt={content.bottom_left_title} />
            <div className="overlay">
              <h3>{content.bottom_left_title}</h3>
              <a href={content.bottom_left_cta_link} className="btn">
                {content.bottom_left_cta_text}
              </a>
            </div>
          </div>

          {/* Bottom right card */}
          <div className="small-card">
            <img src={content.bottom_right_image} alt={content.bottom_right_title} />
            <div className="overlay">
              <h3>{content.bottom_right_title}</h3>
              <a href={content.bottom_right_cta_link} className="btn">
                {content.bottom_right_cta_text}
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
```

### CSS Grid Layout

```css
.landing-hero-grid .grid-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  grid-template-rows: 1fr 1fr 1fr;
  gap: 16px;
  height: 600px;
}

.main-card {
  grid-row: 1 / 4;
  grid-column: 1;
}

.small-card:nth-child(2) {
  grid-row: 1 / 2;
  grid-column: 2;
}

.small-card:nth-child(3) {
  grid-row: 2 / 3;
  grid-column: 2;
}

.small-card:nth-child(4) {
  grid-row: 3 / 4;
  grid-column: 2;
}
```

---

## Testing

### Test Landing Page

```bash
# Seed sample data
php artisan db:seed --class=PageTemplateSeeder

# Get landing page
curl http://localhost:8000/api/pages/home
```

### Test Brand Page

```bash
# Get brand page
curl http://localhost:8000/api/pages/brands/nordictrack
```

---

## Summary

You now have:

✅ **Landing Pages** - Generic homepage with 4-section hero grid (or video)
✅ **Brand Pages** - Connected to brands database with auto-filtered products
✅ **9 New Templates** - Optimized for each page type
✅ **Sample Data** - Complete landing and brand page examples
✅ **API Filtering** - Get templates by page type
✅ **Frontend Components** - React examples for all templates

Run the seeder to see both page types in action!
