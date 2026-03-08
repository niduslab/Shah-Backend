# Admin Page Builder Guide

## Overview

The Page Builder system allows you to create professional, dynamic pages using predefined section templates - similar to WordPress themes or Shopify page builders. You don't need to write code; just select templates, fill in content, and arrange sections.

## Key Concepts

### 1. Pages
A page is a container that holds multiple sections. Each page has:
- **Title**: Display name
- **Slug**: URL-friendly identifier (e.g., `/pages/home`)
- **Type**: landing, brand, flash_deal, gallery, or custom
- **Meta Info**: SEO title and description
- **Status**: Active/Inactive

### 2. Sections
Sections are the building blocks of your pages. Each section:
- Uses a predefined **template** (e.g., hero_banner, product_hero, promo_grid)
- Contains **content** (text, images, links)
- Has **settings** (layout options, colors, styles)
- Can be reordered via drag-and-drop

### 3. Templates
Templates are pre-designed section layouts. Think of them as Lego blocks - each has a specific purpose and structure.

---

## Available Section Templates

### Hero Sections

#### 1. Hero Banner
**Purpose**: Full-width hero section with background image and call-to-action

**Content Fields**:
- Background Image (required)
- Heading (required, max 100 chars)
- Subheading (optional, max 200 chars)
- Description (optional)
- CTA Text & Link (optional)
- Text Position (left/center/right)
- Overlay Opacity (0-100)

**Settings**:
- Height: small, medium, large, full
- Text Color
- Button Style: primary, secondary, outline

**Use Case**: Homepage hero, landing page header

---

#### 2. Product Hero with Image
**Purpose**: Showcase a single product with side-by-side layout

**Content Fields**:
- Product Image (required)
- Heading (required)
- Subheading (optional)
- Description (required)
- CTA Text (required)
- CTA Link (optional)
- Background Color

**Settings**:
- Layout: image_left, image_right
- Image Width: 40%, 50%, 60%

**Use Case**: Product launches, featured equipment showcase

**Example**: "Smart Rowing. Full-Body Results." section

---

### Content Sections

#### 3. Two Column Product Cards
**Purpose**: Display two side-by-side product/category cards

**Content Fields** (for each card):
- Image (required)
- Title (required)
- CTA Text (required)
- CTA Link (optional)

**Settings**:
- Card Height: small, medium, large
- Gap: small, medium, large

**Use Case**: Featured categories, product comparisons

**Example**: Treadmill + Elliptical cards

---

#### 4. Promotional Grid (3 Cards)
**Purpose**: Asymmetric grid with 1 large card + 2 smaller cards

**Content Fields**:
- **Main Card**: Image, Title, Subtitle, Badge (e.g., "40% Off"), CTA
- **Side Card 1**: Image, Title, CTA
- **Side Card 2**: Image, Title, CTA

**Settings**:
- Layout: main_left, main_right
- Background Color

**Use Case**: Sales promotions, featured collections

**Example**: "Elevate Your Fitness Journey" with discount badge

---

#### 5. Category Grid (4 Columns)
**Purpose**: Grid of category cards

**Content Fields**:
- Section Title (optional)
- Categories (repeater, max 8):
  - Image (required)
  - Title (required)
  - Link (optional)

**Settings**:
- Columns: 2, 3, or 4
- Card Style: bordered, shadow, flat

**Use Case**: Browse categories, equipment types

**Example**: "Explore The Nordictrack Categories" - Bikes, Treadmills, Ellipticals, Rowers

---

#### 6. Statistics Section
**Purpose**: Display key metrics/achievements

**Content Fields**:
- Section Title (optional)
- Section Description (optional)
- Stats (repeater, max 6):
  - Value (e.g., "51 +")
  - Label (e.g., "Years of Experience")

**Settings**:
- Layout: horizontal, grid
- Background Color

**Use Case**: Company achievements, social proof

**Example**: "51+ Years", "1M+ Customers", "50+ Countries"

---

#### 7. Text Block with Side Images
**Purpose**: Text content with image collage on the side

**Content Fields**:
- Heading (required)
- Description (required)
- Images (repeater, max 4):
  - Image (required)
  - Alt Text (optional)

**Settings**:
- Text Position: left, right
- Image Layout: collage, grid, stack

**Use Case**: About us, mission statements, storytelling

**Example**: "Thinking Behind the Work" section

---

### Banner Sections

#### 8. Full Width Banner
**Purpose**: Promotional banner spanning full width

**Content Fields**:
- Background Image (required)
- Heading (required)
- Subheading (optional)
- CTA Text & Link (optional)

**Settings**:
- Height: small, medium, large
- Text Alignment: left, center, right
- Text Color

**Use Case**: Mid-page promotions, announcements

**Example**: "Turn Your Home Into A Complete Fitness Space"

---

### Product Sections

#### 9. Product Showcase Slider
**Purpose**: Carousel of featured products

**Content Fields**:
- Section Title (optional)
- Products (repeater, max 10):
  - Product ID (select from catalog) OR
  - Custom Image, Title, Link

**Settings**:
- Autoplay: true/false
- Slides Per View: 1, 2, 3, 4

**Use Case**: Featured products, bestsellers, new arrivals

---

### CTA Sections

#### 10. Call-to-Action Section
**Purpose**: Centered CTA block

**Content Fields**:
- Heading (required)
- Description (optional)
- CTA Text (required)
- CTA Link (optional)

**Settings**:
- Background Color
- Button Style: primary, secondary, outline
- Padding: small, medium, large

**Use Case**: Newsletter signup, special offers, conversion points

---

## How to Build a Page

### Step 1: Create a Page

**API Endpoint**: `POST /api/admin/pages`

```json
{
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Welcome to Our Store",
  "meta_description": "Discover premium fitness equipment",
  "is_active": true
}
```

### Step 2: Browse Available Templates

**API Endpoint**: `GET /api/admin/page-templates`

Returns all available section templates with their schemas.

**Filter by Category**: `GET /api/admin/page-templates/category/hero`

Categories: `hero`, `content`, `banner`, `products`, `cta`

### Step 3: Get Template Schema

**API Endpoint**: `GET /api/admin/page-templates/{templateType}/schema`

Example: `GET /api/admin/page-templates/product_hero/schema`

This shows you exactly what fields are required and available.

### Step 4: Add Sections to Page

**API Endpoint**: `POST /api/admin/pages/{pageId}/sections`

```json
{
  "section_type": "product_hero",
  "title": "Hero Section",
  "content": {
    "product_image": "/images/rowing-machine.jpg",
    "heading": "Smart Rowing. Full-Body Results.",
    "subheading": "Real Progress.",
    "description": "Experience a powerful, low-impact workout...",
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

### Step 5: Reorder Sections

**API Endpoint**: `POST /api/admin/pages/{pageId}/sections/reorder`

```json
{
  "sections": [
    { "id": 1, "sort_order": 1 },
    { "id": 3, "sort_order": 2 },
    { "id": 2, "sort_order": 3 }
  ]
}
```

### Step 6: Update Section Content

**API Endpoint**: `PUT /api/admin/pages/{pageId}/sections/{sectionId}`

```json
{
  "content": {
    "heading": "Updated Heading",
    "description": "New description..."
  }
}
```

### Step 7: Duplicate a Page

**API Endpoint**: `POST /api/admin/pages/{pageId}/duplicate`

Creates a copy of the page with all its sections (marked as inactive).

---

## Best Practices

### 1. Page Structure
A typical landing page structure:
1. **Hero Section** - Grab attention
2. **Featured Products/Categories** - Show offerings
3. **Stats/Social Proof** - Build trust
4. **Promotional Grid** - Highlight deals
5. **Full Width Banner** - Mid-page conversion
6. **Category Grid** - Browse options
7. **CTA Section** - Final conversion

### 2. Content Guidelines
- **Headings**: Keep under 100 characters
- **Descriptions**: 2-3 sentences max
- **Images**: Use high-quality, optimized images
- **CTAs**: Use action verbs ("Shop Now", "Discover", "Explore")

### 3. Image Specifications
- **Hero Images**: 1920x800px minimum
- **Product Images**: 800x800px (square)
- **Category Cards**: 600x400px
- **Format**: JPG or WebP for photos, PNG for graphics
- **File Size**: Under 500KB per image

### 4. SEO Optimization
- Set unique meta titles (50-60 chars)
- Write compelling meta descriptions (150-160 chars)
- Use descriptive alt text for images
- Create SEO-friendly slugs

### 5. Mobile Responsiveness
All templates are mobile-responsive by default:
- 4-column grids become 2 columns on tablet, 1 on mobile
- Side-by-side layouts stack vertically on mobile
- Text sizes adjust automatically

---

## Example: Building a NordicTrack-Style Homepage

```javascript
// 1. Create page
const page = await createPage({
  title: "Home",
  slug: "home",
  type: "landing"
});

// 2. Add hero section
await addSection(page.id, {
  section_type: "product_hero",
  content: {
    product_image: "/images/rowing-machine.jpg",
    heading: "Smart Rowing. Full-Body Results.",
    description: "Experience a powerful workout...",
    cta_text: "Shop Rowers"
  }
});

// 3. Add two-column cards
await addSection(page.id, {
  section_type: "two_column_cards",
  content: {
    card_1_image: "/images/treadmill.jpg",
    card_1_title: "T Series 16 Treadmill",
    card_1_cta_text: "Shop Treadmill",
    card_2_image: "/images/elliptical.jpg",
    card_2_title: "Step Climber XL",
    card_2_cta_text: "Shop Ellipticals"
  }
});

// 4. Add stats section
await addSection(page.id, {
  section_type: "stats_section",
  content: {
    section_title: "Thinking Behind the Work",
    stats: [
      { value: "51 +", label: "Years of Experiences" },
      { value: "1M +", label: "Happy Customers" },
      { value: "50 +", label: "Available in Countries" }
    ]
  }
});

// 5. Add promo grid
await addSection(page.id, {
  section_type: "promo_grid",
  content: {
    main_card_image: "/images/bike-promo.jpg",
    main_card_title: "Elevate Your Fitness Journey",
    main_card_badge: "Up to 40% Discounts",
    side_card_1_title: "Perfect Gear Awaits",
    side_card_2_title: "Shine Bright with Weights"
  }
});

// 6. Add full-width banner
await addSection(page.id, {
  section_type: "full_width_banner",
  content: {
    background_image: "/images/home-gym.jpg",
    heading: "Turn Your Home Into A Complete",
    subheading: "Fitness Space",
    cta_text: "Shop Nordictrack"
  }
});

// 7. Add category grid
await addSection(page.id, {
  section_type: "category_grid",
  content: {
    section_title: "Explore The Nordictrack Categories",
    categories: [
      { image: "/images/bikes.jpg", title: "Bikes" },
      { image: "/images/treadmills.jpg", title: "Treadmills" },
      { image: "/images/ellipticals.jpg", title: "Ellipticals" },
      { image: "/images/rowers.jpg", title: "Rowers" }
    ]
  }
});
```

---

## Frontend Implementation

### Rendering Pages

**Public API**: `GET /api/pages/{slug}`

Returns page with all active sections in order.

### React Component Example

```jsx
function DynamicPage({ slug }) {
  const { data: page } = usePage(slug);
  
  return (
    <div className="dynamic-page">
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

function SectionRenderer({ type, content, settings }) {
  switch(type) {
    case 'product_hero':
      return <ProductHero {...content} {...settings} />;
    case 'two_column_cards':
      return <TwoColumnCards {...content} {...settings} />;
    case 'stats_section':
      return <StatsSection {...content} {...settings} />;
    // ... other templates
    default:
      return null;
  }
}
```

---

## Troubleshooting

### Section Not Appearing
- Check `is_active` is `true`
- Verify `sort_order` is set
- Ensure required fields are filled

### Images Not Loading
- Use absolute paths starting with `/`
- Verify images are uploaded to media library
- Check file permissions

### Layout Issues
- Review settings schema for the template
- Ensure content matches template requirements
- Test on different screen sizes

---

## Advanced Features

### Custom Styling
Each section supports custom CSS classes via settings:

```json
{
  "settings": {
    "custom_class": "my-custom-section",
    "custom_css": "padding: 60px 0;"
  }
}
```

### Dynamic Content
Link sections to live data:

```json
{
  "section_type": "product_showcase",
  "content": {
    "data_source": "featured_products",
    "limit": 8
  }
}
```

### A/B Testing
Create multiple versions and test:

```json
{
  "section_type": "hero_banner",
  "content": { ... },
  "settings": {
    "ab_test_variant": "A"
  }
}
```

---

## Support

For questions or custom template requests, contact the development team.
