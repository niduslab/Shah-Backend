# Brand Page Complete Guide

## Overview

Brand pages are special dynamic pages connected to your brands database. Each brand can have its own customized page showcasing products, story, and categories.

---

## Brand Page Structure (5 Sections)

Based on your NordicTrack design, a complete brand page consists of:

### 1. Brand Full Width CTA Banner (HERO)
Full-width hero banner with background image - "Turn Your Home Into Fitness Space"

### 2. Brand Category Grid
Equipment categories on dark background - Bikes, Treadmills, Ellipticals, Rowers

### 3. Brand Featured Products
Two large product cards (Treadmill + Elliptical with badges)

### 4. Brand Content with Images  
Brand story "Thinking Behind the Work" with stats and image collage

### 5. Brand Product Hero
Side-by-side product showcase - Rowing machine (moved to last position)

---

## Section Templates

### 1. Brand Full Width CTA Banner (HERO)

**Template Type**: `brand_full_width_cta`

**Visual Layout**:
```
┌─────────────────────────────────────────────────────┐
│  ╔═══════════════════════════════════════════════╗ │
│  ║  [Background Image: Person on Treadmill]      ║ │
│  ║                                               ║ │
│  ║  Turn Your Home Into A Complete               ║ │
│  ║  Fitness Space  [Yellow Text]                 ║ │
│  ║                                               ║ │
│  ║  NordicTrack is a leader in home fitness...  ║ │
│  ║                                               ║ │
│  ║  [Shop Nordictrack →]                         ║ │
│  ║                                               ║ │
│  ╚═══════════════════════════════════════════════╝ │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "brand_id": 1,
  "background_image": "/images/home-gym-banner.jpg",
  "heading": "Turn Your Home Into A Complete",
  "subheading": "Fitness Space",
  "subheading_color": "#FFC107",
  "description": "NordicTrack is a leader in home fitness equipment...",
  "cta_text": "Shop Nordictrack",
  "cta_link": "/brands/nordictrack"
}
```

**Settings**:
```json
{
  "height": "large",
  "text_position": "left",
  "text_color": "#ffffff",
  "overlay_opacity": 50
}
```

---

### 2. Brand Category Grid

**Template Type**: `brand_category_grid`

**Visual Layout**:
```
┌─────────────────────────────────────────────────────┐
│  ╔═══════════════════════════════════════════════╗ │
│  ║  [Dark Background #2c2c2c]                    ║ │
│  ║                                               ║ │
│  ║  Explore The Nordictrack Categories           ║ │
│  ║                                               ║ │
│  ║  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐     ║ │
│  ║  │Bikes │  │Tread-│  │Ellip-│  │Rowers│     ║ │
│  ║  │Image │  │mills │  │ticals│  │Image │     ║ │
│  ║  │      │  │Image │  │Image │  │      │     ║ │
│  ║  │Bikes │  │Tread-│  │Ellip-│  │Rowers│     ║ │
│  ║  └──────┘  └──────┘  └──────┘  └──────┘     ║ │
│  ║                                               ║ │
│  ╚═══════════════════════════════════════════════╝ │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "brand_id": 1,
  "section_title": "Explore The Nordictrack Categories",
  "background_color": "#2c2c2c",
  "text_color": "#ffffff",
  "categories": [
    {"image": "/images/bikes.jpg", "title": "Bikes", "link": "/brands/nordictrack/bikes"},
    {"image": "/images/treadmills.jpg", "title": "Treadmills", "link": "/brands/nordictrack/treadmills"},
    {"image": "/images/ellipticals.jpg", "title": "Ellipticals", "link": "/brands/nordictrack/ellipticals"},
    {"image": "/images/rowers.jpg", "title": "Rowers", "link": "/brands/nordictrack/rowers"}
  ]
}
```

---

### 3. Brand Featured Products

**Template Type**: `brand_featured_products`

**Visual Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌─────────────────────┐  ┌─────────────────────┐ │
│  │                     │  │                     │ │
│  │   Treadmill         │  │   Elliptical        │ │
│  │   Image             │  │   Image             │ │
│  │                     │  │                     │ │
│  │   T Series 16       │  │   Step Climber XL   │ │
│  │   Treadmill         │  │                     │ │
│  │                     │  │                     │ │
│  │   [12 MPH speed]    │  │                     │ │
│  │                     │  │                     │ │
│  │   [Shop Treadmill]  │  │   [Shop Ellipticals]│ │
│  │                     │  │                     │ │
│  └─────────────────────┘  └─────────────────────┘ │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "brand_id": 1,
  "left_product_image": "/images/treadmill.jpg",
  "left_product_title": "T Series 16 Treadmill",
  "left_product_badge": "12 MPH speed",
  "left_product_cta_text": "Shop Treadmill",
  "left_product_cta_link": "/brands/nordictrack/treadmills",
  
  "right_product_image": "/images/elliptical.jpg",
  "right_product_title": "Step Climber XL",
  "right_product_badge": "",
  "right_product_cta_text": "Shop Ellipticals",
  "right_product_cta_link": "/brands/nordictrack/ellipticals"
}
```

---

### 4. Brand Content with Images

**Template Type**: `brand_content_with_images`

**Visual Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌──────────────────────┐  ┌──────────────────┐   │
│  │ Thinking Behind      │  │  ┌────┐  ┌────┐ │   │
│  │ the Work             │  │  │Img1│  │Img2│ │   │
│  │                      │  │  └────┘  └────┘ │   │
│  │ NordicTrack delivers │  │                  │   │
│  │ a premium personal...│  │  ┌────┐          │   │
│  │                      │  │  │Img3│          │   │
│  │ 51 +                 │  │  └────┘          │   │
│  │ Years of Experiences │  │                  │   │
│  │                      │  │                  │   │
│  │ 1M +                 │  │                  │   │
│  │ Happy Customers      │  │                  │   │
│  │                      │  │                  │   │
│  │ 50 +                 │  │                  │   │
│  │ Available In         │  │                  │   │
│  │ Countries            │  │                  │   │
│  └──────────────────────┘  └──────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "brand_id": 1,
  "heading": "Thinking Behind the Work",
  "description": "NordicTrack delivers a premium personal training experience...",
  "stats": [
    {"value": "51 +", "label": "Years of Experiences"},
    {"value": "1M +", "label": "Happy Customers"},
    {"value": "50 +", "label": "Available In Countries"}
  ],
  "images": [
    {"image": "/images/brand-story-1.jpg", "alt_text": "Person on bike"},
    {"image": "/images/brand-story-2.jpg", "alt_text": "Person on rower"},
    {"image": "/images/brand-story-3.jpg", "alt_text": "Training session"}
  ]
}
```

---

### 5. Brand Product Hero

**Template Type**: `brand_product_hero`

**Visual Layout**:
```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌──────────────┐    ┌──────────────────────────┐ │
│  │              │    │  Smart Rowing.           │ │
│  │   Rowing     │    │  Full-Body Results.      │ │
│  │   Machine    │    │  Real Progress.          │ │
│  │   Image      │    │                          │ │
│  │              │    │  Experience a powerful...│ │
│  │              │    │                          │ │
│  └──────────────┘    │  [Shop Rowers →]         │ │
│                      └──────────────────────────┘ │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Content Fields**:
```json
{
  "brand_id": 1,
  "product_image": "/images/rowing-machine.jpg",
  "heading": "Smart Rowing. Full-Body Results.",
  "subheading": "Real Progress.",
  "description": "Experience a powerful, low-impact workout...",
  "cta_text": "Shop Rowers",
  "cta_link": "/brands/nordictrack/rowers",
  "background_color": "#e8f4f8"
}
```

**Settings**:
```json
{
  "layout": "image_left",
  "image_width": "50%"
}
```

---

## Creating a Brand Page

### Step 1: Create the Page

```bash
POST /api/admin/pages
```

```json
{
  "title": "NordicTrack",
  "slug": "brands/nordictrack",
  "type": "brand",
  "meta_title": "NordicTrack - Premium Fitness Equipment",
  "meta_description": "Explore NordicTrack's complete line of equipment",
  "is_active": true
}
```

### Step 2: Add Section 1 - Full Width CTA Banner (HERO)

```bash
POST /api/admin/pages/{pageId}/sections
```

```json
{
  "section_type": "brand_full_width_cta",
  "content": {
    "brand_id": 1,
    "background_image": "/images/home-gym.jpg",
    "heading": "Turn Your Home Into A Complete",
    "subheading": "Fitness Space",
    "subheading_color": "#FFC107",
    "description": "NordicTrack is a leader in home fitness equipment...",
    "cta_text": "Shop Nordictrack",
    "cta_link": "/brands/nordictrack"
  },
  "settings": {
    "height": "large",
    "text_position": "left",
    "text_color": "#ffffff",
    "overlay_opacity": 50
  },
  "sort_order": 1
}
```

### Step 3: Add Section 2 - Category Grid

```json
{
  "section_type": "brand_category_grid",
  "content": {
    "brand_id": 1,
    "section_title": "Explore The Nordictrack Categories",
    "background_color": "#2c2c2c",
    "text_color": "#ffffff",
    "categories": [
      {"image": "/images/bikes.jpg", "title": "Bikes", "link": "/brands/nordictrack/bikes"},
      {"image": "/images/treadmills.jpg", "title": "Treadmills", "link": "/brands/nordictrack/treadmills"},
      {"image": "/images/ellipticals.jpg", "title": "Ellipticals", "link": "/brands/nordictrack/ellipticals"},
      {"image": "/images/rowers.jpg", "title": "Rowers", "link": "/brands/nordictrack/rowers"}
    ]
  },
  "sort_order": 2
}
```

### Step 4: Add Section 3 - Featured Products

```json
{
  "section_type": "brand_featured_products",
  "content": {
    "brand_id": 1,
    "left_product_image": "/images/treadmill.jpg",
    "left_product_title": "T Series 16 Treadmill",
    "left_product_badge": "12 MPH speed",
    "left_product_cta_text": "Shop Treadmill",
    "right_product_image": "/images/elliptical.jpg",
    "right_product_title": "Step Climber XL",
    "right_product_cta_text": "Shop Ellipticals"
  },
  "sort_order": 3
}
```

### Step 5: Add Section 4 - Content with Images

```json
{
  "section_type": "brand_content_with_images",
  "content": {
    "brand_id": 1,
    "heading": "Thinking Behind the Work",
    "description": "NordicTrack delivers a premium...",
    "stats": [
      {"value": "51 +", "label": "Years of Experiences"},
      {"value": "1M +", "label": "Happy Customers"},
      {"value": "50 +", "label": "Available In Countries"}
    ],
    "images": [
      {"image": "/images/story-1.jpg", "alt_text": "Training"},
      {"image": "/images/story-2.jpg", "alt_text": "Equipment"},
      {"image": "/images/story-3.jpg", "alt_text": "Results"}
    ]
  },
  "sort_order": 2
}
```

### Step 5: Add Section 4 - Content with Images

```json
{
  "section_type": "brand_content_with_images",
  "content": {
    "brand_id": 1,
    "heading": "Thinking Behind the Work",
    "description": "NordicTrack delivers a premium...",
    "stats": [
      {"value": "51 +", "label": "Years of Experiences"},
      {"value": "1M +", "label": "Happy Customers"},
      {"value": "50 +", "label": "Available In Countries"}
    ],
    "images": [
      {"image": "/images/story-1.jpg", "alt_text": "Training"},
      {"image": "/images/story-2.jpg", "alt_text": "Equipment"},
      {"image": "/images/story-3.jpg", "alt_text": "Results"}
    ]
  },
  "sort_order": 4
}
```

### Step 6: Add Section 5 - Product Hero (Last)

```json
{
  "section_type": "brand_product_hero",
  "content": {
    "brand_id": 1,
    "product_image": "/images/rowing-machine.jpg",
    "heading": "Smart Rowing. Full-Body Results.",
    "subheading": "Real Progress.",
    "description": "Experience a powerful workout...",
    "cta_text": "Shop Rowers",
    "cta_link": "/brands/nordictrack/rowers",
    "background_color": "#e8f4f8"
  },
  "settings": {
    "layout": "image_left",
    "image_width": "50%"
  },
  "sort_order": 5
}
```

---

## Frontend Implementation

### Brand Product Hero Component

```jsx
// components/sections/BrandProductHero.jsx
export default function BrandProductHero({ content, settings }) {
  const { layout = 'image_left' } = settings;

  return (
    <section 
      className="brand-product-hero"
      style={{ backgroundColor: content.background_color }}
    >
      <div className="container">
        <div className={`hero-layout layout-${layout}`}>
          <div className="hero-image">
            <img src={content.product_image} alt={content.heading} />
          </div>
          
          <div className="hero-content">
            <h1>{content.heading}</h1>
            {content.subheading && <h2>{content.subheading}</h2>}
            <p>{content.description}</p>
            <a href={content.cta_link} className="btn btn-yellow">
              {content.cta_text}
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}
```

### Brand Content with Images Component

```jsx
// components/sections/BrandContentWithImages.jsx
export default function BrandContentWithImages({ content, settings }) {
  return (
    <section 
      className="brand-content-images"
      style={{ backgroundColor: settings.background_color }}
    >
      <div className="container">
        <div className="content-layout">
          <div className="text-content">
            <h2>{content.heading}</h2>
            <p>{content.description}</p>
            
            <div className="stats-grid">
              {content.stats.map((stat, index) => (
                <div key={index} className="stat-item">
                  <div className="stat-value">{stat.value}</div>
                  <div className="stat-label">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
          
          <div className="images-collage">
            {content.images.map((img, index) => (
              <img 
                key={index}
                src={img.image} 
                alt={img.alt_text}
                className={`collage-img-${index + 1}`}
              />
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
```

### Brand Category Grid Component

```jsx
// components/sections/BrandCategoryGrid.jsx
export default function BrandCategoryGrid({ content, settings }) {
  return (
    <section 
      className="brand-category-grid"
      style={{
        backgroundColor: content.background_color,
        color: content.text_color
      }}
    >
      <div className="container">
        <h2 className="section-title">{content.section_title}</h2>
        
        <div 
          className="categories-grid"
          style={{
            gridTemplateColumns: `repeat(${settings.columns}, 1fr)`
          }}
        >
          {content.categories.map((category, index) => (
            <a 
              key={index}
              href={category.link}
              className="category-card"
            >
              <img src={category.image} alt={category.title} />
              <h3>{category.title}</h3>
            </a>
          ))}
        </div>
      </div>
    </section>
  );
}
```

### Brand Full Width CTA Component

```jsx
// components/sections/BrandFullWidthCTA.jsx
export default function BrandFullWidthCTA({ content, settings }) {
  return (
    <section 
      className={`brand-full-cta height-${settings.height} text-${settings.text_position}`}
      style={{
        backgroundImage: `url(${content.background_image})`,
        color: settings.text_color
      }}
    >
      <div className="overlay" style={{ opacity: settings.overlay_opacity / 100 }}>
        <div className="container">
          <div className="cta-content">
            <h2>{content.heading}</h2>
            <h3 style={{ color: content.subheading_color }}>
              {content.subheading}
            </h3>
            {content.description && <p>{content.description}</p>}
            <a href={content.cta_link} className="btn btn-yellow">
              {content.cta_text}
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}
```

---

## Connecting to Brands Database

### Backend: Get Brand Data

```php
// In your controller or service
$brand = Brand::find($brandId);
$page = Page::where('type', 'brand')
    ->where('slug', 'brands/' . $brand->slug)
    ->with('activeSections')
    ->first();

// Merge brand data with page data
$page->brand = $brand;
```

### Frontend: Display Brand Info

```jsx
// pages/brands/[slug].jsx
export default function BrandPage() {
  const { slug } = useParams();
  
  const { data: page } = useQuery(['brand-page', slug], () =>
    fetch(`/api/pages/brands/${slug}`).then(r => r.json())
  );

  return (
    <div className="brand-page">
      {/* Brand logo/header if needed */}
      {page.brand && (
        <div className="brand-header">
          <img src={page.brand.logo} alt={page.brand.name} />
        </div>
      )}
      
      {/* Render sections */}
      {page.sections.map(section => (
        <SectionRenderer
          key={section.id}
          type={section.section_type}
          content={section.content}
          settings={section.settings}
        />
      ))}
    </div>
  );
}
```

---

## Testing

```bash
# Seed sample brand page
php artisan db:seed --class=PageTemplateSeeder

# Get brand page
curl http://localhost:8000/api/pages/brands/nordictrack
```

---

## Summary

You now have a complete 5-section brand page system:

✅ **Section 1**: Full Width CTA Banner (Hero) - "Turn Your Home Into Fitness Space"
✅ **Section 2**: Category Grid (dark background) - Bikes, Treadmills, Ellipticals, Rowers
✅ **Section 3**: Featured Products - Treadmill + Elliptical with badges
✅ **Section 4**: Content with Images - "Thinking Behind the Work" + stats
✅ **Section 5**: Product Hero (last) - Rowing machine showcase

All sections are connected to your brands database and ready to use!
