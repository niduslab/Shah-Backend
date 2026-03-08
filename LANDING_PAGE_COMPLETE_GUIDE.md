# Landing Page Complete Guide

## Overview

Landing pages are generic homepage/marketing pages designed for broad appeal. They showcase products, promotions, and categories without being tied to a specific brand.

---

## Landing Page Structure (3 Sections)

Based on your design, a complete landing page consists of:

### 1. Landing Hero Grid (4 Sections OR Video)
Main hero with 1 large card + 3 smaller promotional cards

### 2. Category Cards (Two Column)
Two large category cards with badges and descriptions

### 3. Pre-Order Showcase
Product grid with "Pre-Order Now & Save Big" heading

---

## Section Templates

### 1. Landing Hero Grid

**Template Type**: `landing_hero_grid`

**Visual Layout**:
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

**Content Fields**:
```json
{
  "use_video": false,
  
  // Main card (left - large with bike)
  "main_card_image": "/images/bike-promo.jpg",
  "main_card_heading": "Elevate Your",
  "main_card_subheading": "Fitness Journey",
  "main_card_badge": "Up to",
  "main_card_badge_text": "40% Discounts",
  "main_card_cta_text": "Shop Now",
  "main_card_cta_link": "/products",
  
  // Top right card (Perfect Gear)
  "top_right_image": "/images/gear-promo.jpg",
  "top_right_title": "Perfect Gear Awaits",
  "top_right_cta_text": "Shop Now",
  "top_right_cta_link": "/products/accessories",
  
  // Bottom left card (Weights)
  "bottom_left_image": "/images/weights-promo.jpg",
  "bottom_left_title": "Shine Bright with Weights",
  "bottom_left_cta_text": "Shop Now",
  "bottom_left_cta_link": "/products/weights",
  
  // Bottom right card (TOP PICKS)
  "bottom_right_image": "/images/top-picks.jpg",
  "bottom_right_title": "TOP PICKS",
  "bottom_right_cta_text": "Shop Now",
  "bottom_right_cta_link": "/products/featured"
}
```

**Settings**:
```json
{
  "main_card_bg_overlay": true,
  "text_color": "#ffffff"
}
```

**Special Feature - Video Option**:
```json
{
  "use_video": true,
  "video_url": "https://www.youtube.com/watch?v=VIDEO_ID",
  "video_poster": "/images/video-poster.jpg"
}
```

When `use_video` is `true`, the entire hero section displays a video player instead of the 4-card grid.

---

### 2. Category Cards (Two Column)

**Template Type**: `category_cards_two_column`

**Visual Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌────────────────────┐  ┌────────────────────┐   │
│  │                    │  │                    │   │
│  │  Cardio Equipment  │  │ Free Weight Equip. │   │
│  │                    │  │                    │   │
│  │  Burn calories and │  │  Burn calories and │   │
│  │  boost endurance...│  │  boost endurance...│   │
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
  // Left card - Cardio Equipment
  "left_card_image": "/images/cardio-equipment.jpg",
  "left_card_title": "Cardio Equipment's",
  "left_card_description": "Burn calories and boost endurance with our premium cardio machines",
  "left_card_badge": "Save all 45%",
  "left_card_cta_text": "Shop Now",
  "left_card_cta_link": "/categories/cardio",
  
  // Right card - Free Weights
  "right_card_image": "/images/free-weights.jpg",
  "right_card_title": "Free Weight Equipment's",
  "right_card_description": "Burn calories and boost endurance with our premium cardio machines",
  "right_card_badge": "Up to 30%",
  "right_card_cta_text": "Shop Now",
  "right_card_cta_link": "/categories/weights"
}
```

**Settings**:
```json
{
  "card_height": "large",
  "gap": "medium"
}
```

**Key Features**:
- Circular badge overlays (e.g., "Save all 45%")
- Full-width background images
- Description text
- Yellow CTA buttons

---

### 3. Pre-Order Showcase

**Template Type**: `preorder_showcase`

**Visual Layout**:
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
│  │T Ser-│  │Step  │  │S22i  │  │RW900 │          │
│  │ies 10│  │Climb │  │Cycle │  │Rower │          │
│  │      │  │er XL │  │      │  │      │          │
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
      "image": "/images/treadmill-preorder.jpg",
      "title": "Nordictrack T Series 10 Treadmill",
      "badge": "Save 30%",
      "cta_text": "Preorder Now",
      "cta_link": "/products/t-series-10",
      "has_carousel": true
    },
    {
      "image": "/images/elliptical-preorder.jpg",
      "title": "Step Climber XL",
      "badge": "",
      "cta_text": "Preorder Now",
      "cta_link": "/products/step-climber-xl",
      "has_carousel": false
    },
    {
      "image": "/images/bike-preorder.jpg",
      "title": "Commercial S22i Studio Cycle",
      "badge": "",
      "cta_text": "Preorder Now",
      "cta_link": "/products/s22i",
      "has_carousel": false
    },
    {
      "image": "/images/rower-preorder.jpg",
      "title": "RW900 Rower",
      "badge": "",
      "cta_text": "Preorder Now",
      "cta_link": "/products/rw900",
      "has_carousel": false
    }
  ]
}
```

**Settings**:
```json
{
  "background_color": "#f8f9fa",
  "grid_columns": 4
}
```

**Key Features**:
- Circular "Save 30%" badge on first product
- Carousel indicator dots (• • •) on products with `has_carousel: true`
- "View All Preorder Products" link in top right
- Grid layout (2, 3, or 4 columns)
- Yellow CTA buttons

---

## Creating a Landing Page

### Step 1: Create the Page

```bash
POST /api/admin/pages
```

```json
{
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Premium Fitness Equipment - Transform Your Home Gym",
  "meta_description": "Discover premium fitness equipment for your home gym",
  "is_active": true
}
```

---

### Step 2: Add Section 1 - Landing Hero Grid

```bash
POST /api/admin/pages/{pageId}/sections
```

```json
{
  "section_type": "landing_hero_grid",
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
    "top_right_cta_link": "/products/accessories",
    
    "bottom_left_image": "/images/weights-promo.jpg",
    "bottom_left_title": "Shine Bright with Weights",
    "bottom_left_cta_text": "Shop Now",
    "bottom_left_cta_link": "/products/weights",
    
    "bottom_right_image": "/images/top-picks.jpg",
    "bottom_right_title": "TOP PICKS",
    "bottom_right_cta_text": "Shop Now",
    "bottom_right_cta_link": "/products/featured"
  },
  "settings": {
    "main_card_bg_overlay": true,
    "text_color": "#ffffff"
  },
  "sort_order": 1
}
```

---

### Step 3: Add Section 2 - Category Cards

```json
{
  "section_type": "category_cards_two_column",
  "content": {
    "left_card_image": "/images/cardio-equipment.jpg",
    "left_card_title": "Cardio Equipment's",
    "left_card_description": "Burn calories and boost endurance with our premium cardio machines",
    "left_card_badge": "Save all 45%",
    "left_card_cta_text": "Shop Now",
    "left_card_cta_link": "/categories/cardio",
    
    "right_card_image": "/images/free-weights.jpg",
    "right_card_title": "Free Weight Equipment's",
    "right_card_description": "Burn calories and boost endurance with our premium cardio machines",
    "right_card_badge": "Up to 30%",
    "right_card_cta_text": "Shop Now",
    "right_card_cta_link": "/categories/weights"
  },
  "settings": {
    "card_height": "large",
    "gap": "medium"
  },
  "sort_order": 2
}
```

---

### Step 4: Add Section 3 - Pre-Order Showcase

```json
{
  "section_type": "preorder_showcase",
  "content": {
    "section_heading": "Pre-Order Now & Save Big",
    "view_all_text": "View All Preorder Products",
    "view_all_link": "/products/preorder",
    "products": [
      {
        "image": "/images/treadmill-preorder.jpg",
        "title": "Nordictrack T Series 10 Treadmill",
        "badge": "Save 30%",
        "cta_text": "Preorder Now",
        "cta_link": "/products/t-series-10",
        "has_carousel": true
      },
      {
        "image": "/images/elliptical-preorder.jpg",
        "title": "Step Climber XL",
        "badge": "",
        "cta_text": "Preorder Now",
        "cta_link": "/products/step-climber-xl",
        "has_carousel": false
      },
      {
        "image": "/images/bike-preorder.jpg",
        "title": "Commercial S22i Studio Cycle",
        "badge": "",
        "cta_text": "Preorder Now",
        "cta_link": "/products/s22i",
        "has_carousel": false
      },
      {
        "image": "/images/rower-preorder.jpg",
        "title": "RW900 Rower",
        "badge": "",
        "cta_text": "Preorder Now",
        "cta_link": "/products/rw900",
        "has_carousel": false
      }
    ]
  },
  "settings": {
    "background_color": "#f8f9fa",
    "grid_columns": 4
  },
  "sort_order": 3
}
```

---

## Frontend Implementation

### Landing Hero Grid Component

```jsx
// components/sections/LandingHeroGrid.jsx
export default function LandingHeroGrid({ content, settings }) {
  // Video option
  if (content.use_video) {
    return (
      <section className="landing-hero-video">
        <video 
          src={content.video_url}
          poster={content.video_poster}
          controls
          autoPlay
          muted
          className="w-full h-screen object-cover"
        />
      </section>
    );
  }

  // 4-section grid layout
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
                <div className="badge-circle">
                  <span className="badge-value">{content.main_card_badge}</span>
                  <span className="badge-text">{content.main_card_badge_text}</span>
                </div>
              )}
              <a href={content.main_card_cta_link} className="btn btn-yellow">
                {content.main_card_cta_text}
              </a>
            </div>
          </div>

          {/* Top right card */}
          <div className="small-card top-right">
            <img src={content.top_right_image} alt={content.top_right_title} />
            <div className="overlay">
              <h3>{content.top_right_title}</h3>
              <a href={content.top_right_cta_link} className="btn btn-yellow">
                {content.top_right_cta_text}
              </a>
            </div>
          </div>

          {/* Bottom left card */}
          <div className="small-card bottom-left">
            <img src={content.bottom_left_image} alt={content.bottom_left_title} />
            <div className="overlay">
              <h3>{content.bottom_left_title}</h3>
              <a href={content.bottom_left_cta_link} className="btn btn-yellow">
                {content.bottom_left_cta_text}
              </a>
            </div>
          </div>

          {/* Bottom right card */}
          <div className="small-card bottom-right">
            <img src={content.bottom_right_image} alt={content.bottom_right_title} />
            <div className="overlay">
              <h3>{content.bottom_right_title}</h3>
              <a href={content.bottom_right_cta_link} className="btn btn-yellow">
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
  grid-template-rows: repeat(3, 1fr);
  gap: 16px;
  height: 600px;
}

.main-card {
  grid-row: 1 / 4;
  grid-column: 1;
  position: relative;
}

.small-card.top-right {
  grid-row: 1;
  grid-column: 2;
}

.small-card.bottom-left {
  grid-row: 2;
  grid-column: 2;
}

.small-card.bottom-right {
  grid-row: 3;
  grid-column: 2;
}

.badge-circle {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 120px;
  height: 120px;
  background: #FF6B35;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}
```

---

### Category Cards Component

```jsx
// components/sections/CategoryCardsTwoColumn.jsx
export default function CategoryCardsTwoColumn({ content, settings }) {
  return (
    <section className="category-cards-two-column">
      <div className="container">
        <div className={`cards-grid gap-${settings.gap}`}>
          {/* Left card */}
          <div className={`category-card card-${settings.card_height}`}>
            <img src={content.left_card_image} alt={content.left_card_title} />
            <div className="overlay">
              {content.left_card_badge && (
                <div className="badge-circle">
                  {content.left_card_badge}
                </div>
              )}
              <h3>{content.left_card_title}</h3>
              <p>{content.left_card_description}</p>
              <a href={content.left_card_cta_link} className="btn btn-yellow">
                {content.left_card_cta_text}
              </a>
            </div>
          </div>

          {/* Right card */}
          <div className={`category-card card-${settings.card_height}`}>
            <img src={content.right_card_image} alt={content.right_card_title} />
            <div className="overlay">
              {content.right_card_badge && (
                <div className="badge-circle">
                  {content.right_card_badge}
                </div>
              )}
              <h3>{content.right_card_title}</h3>
              <p>{content.right_card_description}</p>
              <a href={content.right_card_cta_link} className="btn btn-yellow">
                {content.right_card_cta_text}
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
```

---

### Pre-Order Showcase Component

```jsx
// components/sections/PreorderShowcase.jsx
export default function PreorderShowcase({ content, settings }) {
  return (
    <section 
      className="preorder-showcase"
      style={{ backgroundColor: settings.background_color }}
    >
      <div className="container">
        <div className="section-header">
          <h2>{content.section_heading}</h2>
          <a href={content.view_all_link} className="view-all-link">
            {content.view_all_text} →
          </a>
        </div>

        <div 
          className="products-grid"
          style={{
            gridTemplateColumns: `repeat(${settings.grid_columns}, 1fr)`
          }}
        >
          {content.products.map((product, index) => (
            <div key={index} className="product-card">
              {product.badge && (
                <div className="badge-circle">
                  {product.badge}
                </div>
              )}
              <img src={product.image} alt={product.title} />
              <h3>{product.title}</h3>
              <a href={product.cta_link} className="btn btn-yellow">
                {product.cta_text}
              </a>
              {product.has_carousel && (
                <div className="carousel-dots">
                  <span className="dot active"></span>
                  <span className="dot"></span>
                  <span className="dot"></span>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
```

---

## Testing

```bash
# Seed sample landing page
php artisan db:seed --class=PageTemplateSeeder

# Get landing page
curl http://localhost:8000/api/pages/home
```

---

## Summary

You now have a complete 3-section landing page system:

✅ **Section 1**: Landing Hero Grid - 4 sections OR video option
✅ **Section 2**: Category Cards - Cardio + Free Weights with badges
✅ **Section 3**: Pre-Order Showcase - Product grid with "Save 30%" badges

All sections are optimized for landing pages and ready to use!
