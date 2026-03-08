# Frontend Dynamic Pages Implementation Guide

## Overview
This guide shows frontend developers how to fetch and render dynamic pages created by admins through the API. Pages are built with modular sections that can be rendered as React/Vue components.

---

## Public API Endpoints (No Authentication Required)

### 1. Get Page by Slug
Fetch a complete page with all its sections.

```http
GET /api/pages/{slug}
```

**Example Request:**
```javascript
fetch('/api/pages/home')
  .then(res => res.json())
  .then(data => console.log(data));
```

**Response:**
```json
{
  "id": 1,
  "title": "Home",
  "slug": "home",
  "type": "landing",
  "meta_title": "Fitness Equipment Store",
  "meta_description": "Best fitness equipment for your home gym",
  "is_active": true,
  "sort_order": 0,
  "created_at": "2026-03-07T10:00:00.000000Z",
  "updated_at": "2026-03-07T10:00:00.000000Z",
  "sections": [
    {
      "id": 1,
      "page_id": 1,
      "section_type": "hero_slider",
      "title": "Hero Slider",
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
          }
        ]
      },
      "settings": {
        "autoplay": true,
        "interval": 5000,
        "show_arrows": true,
        "show_dots": true
      },
      "sort_order": 1,
      "is_active": true
    },
    {
      "id": 2,
      "page_id": 1,
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
      },
      "sort_order": 2,
      "is_active": true
    }
  ]
}
```

### 2. Get Pages by Type
Fetch all pages of a specific type.

```http
GET /api/pages/type/{type}
```

**Types:** `landing`, `brand`, `flash_deal`, `gallery`, `custom`

**Example Request:**
```javascript
fetch('/api/pages/type/brand')
  .then(res => res.json())
  .then(data => console.log(data));
```

**Response:**
```json
[
  {
    "id": 2,
    "title": "NordicTrack Brand Page",
    "slug": "nordictrack",
    "type": "brand",
    "sections": [...]
  },
  {
    "id": 3,
    "title": "Peloton Brand Page",
    "slug": "peloton",
    "type": "brand",
    "sections": [...]
  }
]
```

---

## Section Types & Content Structure

### Hero Slider Section
**Type:** `hero_slider`

```json
{
  "section_type": "hero_slider",
  "title": "Main Hero",
  "content": {
    "slides": [
      {
        "type": "image",
        "media_url": "/storage/media/hero-1.jpg",
        "title": "Elevate Your Fitness Journey",
        "subtitle": "Up to 40% Discounts",
        "cta_text": "Shop Now",
        "cta_link": "/shop",
        "text_position": "left",
        "text_color": "white"
      },
      {
        "type": "video",
        "media_url": "/storage/media/promo.mp4",
        "poster": "/storage/media/poster.jpg",
        "title": "New Arrivals",
        "subtitle": "Check out our latest collection",
        "cta_text": "Explore",
        "cta_link": "/new",
        "text_position": "center"
      }
    ]
  },
  "settings": {
    "autoplay": true,
    "interval": 5000,
    "show_arrows": true,
    "show_dots": true,
    "transition": "fade"
  }
}
```

**React Component Example:**
```jsx
function HeroSlider({ data, settings }) {
  return (
    <Swiper
      autoplay={settings.autoplay}
      interval={settings.interval}
      arrows={settings.show_arrows}
      dots={settings.show_dots}
    >
      {data.slides.map((slide, index) => (
        <SwiperSlide key={index}>
          {slide.type === 'image' ? (
            <img src={slide.media_url} alt={slide.title} />
          ) : (
            <video src={slide.media_url} poster={slide.poster} autoPlay muted loop />
          )}
          <div className={`content ${slide.text_position}`}>
            <h1>{slide.title}</h1>
            <p>{slide.subtitle}</p>
            <a href={slide.cta_link} className="btn">{slide.cta_text}</a>
          </div>
        </SwiperSlide>
      ))}
    </Swiper>
  );
}
```

---

### Product Grid Section
**Type:** `product_grid`

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
        "badge": "40% OFF",
        "badge_color": "orange"
      },
      {
        "type": "product_card",
        "image": "/storage/media/weights.jpg",
        "title": "Shine Bright with Weights",
        "description": "Professional grade weights",
        "cta_text": "Shop Now",
        "cta_link": "/category/weights"
      }
    ]
  },
  "settings": {
    "show_prices": true,
    "show_ratings": true,
    "hover_effect": "zoom"
  }
}
```

**React Component Example:**
```jsx
function ProductGrid({ data, settings }) {
  return (
    <section className="product-grid">
      <h2>{data.title}</h2>
      <div className={`grid grid-cols-${data.columns}`}>
        {data.items.map((item, index) => (
          <div key={index} className="product-card">
            {item.badge && (
              <span className="badge" style={{backgroundColor: item.badge_color}}>
                {item.badge}
              </span>
            )}
            <img src={item.image} alt={item.title} />
            <h3>{item.title}</h3>
            <p>{item.description}</p>
            <a href={item.cta_link} className="btn">{item.cta_text}</a>
          </div>
        ))}
      </div>
    </section>
  );
}
```

---

### Brand Showcase Section
**Type:** `brand_showcase`

```json
{
  "section_type": "brand_showcase",
  "title": "Turn Your Home Into A Complete Fitness Space",
  "content": {
    "background_type": "image",
    "background_url": "/storage/media/nordictrack-bg.jpg",
    "logo": "/storage/media/nordictrack-logo.png",
    "description": "NordicTrack is a leader in home fitness equipment, known for innovative treadmills, ellipticals, and exercise bikes with iFIT technology integration.",
    "cta_text": "Shop Nordictrack",
    "cta_link": "/brand/nordictrack"
  },
  "settings": {
    "text_color": "white",
    "overlay": true,
    "overlay_opacity": 0.5,
    "text_align": "left"
  }
}
```

**React Component Example:**
```jsx
function BrandShowcase({ data, settings }) {
  return (
    <section 
      className="brand-showcase"
      style={{
        backgroundImage: `url(${data.background_url})`,
        color: settings.text_color
      }}
    >
      {settings.overlay && (
        <div className="overlay" style={{opacity: settings.overlay_opacity}} />
      )}
      <div className={`content text-${settings.text_align}`}>
        {data.logo && <img src={data.logo} alt="Brand Logo" className="logo" />}
        <h2>{data.title}</h2>
        <p>{data.description}</p>
        <a href={data.cta_link} className="btn">{data.cta_text}</a>
      </div>
    </section>
  );
}
```

---

### Category Grid Section
**Type:** `category_grid`

```json
{
  "section_type": "category_grid",
  "title": "Explore The Nordictrack Categories",
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
      },
      {
        "name": "Ellipticals",
        "image": "/storage/media/ellipticals.jpg",
        "link": "/category/ellipticals",
        "product_count": 28
      },
      {
        "name": "Rowers",
        "image": "/storage/media/rowers.jpg",
        "link": "/category/rowers",
        "product_count": 15
      }
    ]
  },
  "settings": {
    "columns": 4,
    "show_border": true,
    "hover_effect": "zoom",
    "show_product_count": true
  }
}
```

**React Component Example:**
```jsx
function CategoryGrid({ data, settings }) {
  return (
    <section className="category-grid">
      <h2>{data.title}</h2>
      <div className={`grid grid-cols-${settings.columns}`}>
        {data.categories.map((category, index) => (
          <a 
            key={index} 
            href={category.link}
            className={`category-card ${settings.show_border ? 'bordered' : ''}`}
          >
            <img src={category.image} alt={category.name} />
            <h3>{category.name}</h3>
            {settings.show_product_count && (
              <span className="count">{category.product_count} Products</span>
            )}
          </a>
        ))}
      </div>
    </section>
  );
}
```

---

### Banner Section
**Type:** `banner`

```json
{
  "section_type": "banner",
  "title": "Flash Sale",
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
  },
  "settings": {
    "full_width": true,
    "height": "400px"
  }
}
```

**React Component Example:**
```jsx
function Banner({ data, settings }) {
  return (
    <section 
      className={`banner ${settings.full_width ? 'full-width' : ''}`}
      style={{
        backgroundImage: `url(${data.image})`,
        height: settings.height
      }}
    >
      <div className="content">
        <h2>{data.title}</h2>
        <p>{data.subtitle}</p>
        {data.countdown?.enabled && (
          <Countdown endDate={data.countdown.end_date} />
        )}
        <a href={data.cta_link} className="btn">{data.cta_text}</a>
      </div>
    </section>
  );
}
```

---

### Video Section
**Type:** `video_section`

```json
{
  "section_type": "video_section",
  "title": "See Our Products in Action",
  "content": {
    "video_url": "/storage/media/product-demo.mp4",
    "poster": "/storage/media/video-poster.jpg",
    "youtube_id": "dQw4w9WgXcQ",
    "title": "Product Demo",
    "description": "Watch how our equipment transforms your workout"
  },
  "settings": {
    "autoplay": false,
    "controls": true,
    "loop": false,
    "muted": false
  }
}
```

---

### Text Content Section
**Type:** `text_content`

```json
{
  "section_type": "text_content",
  "title": "About Our Store",
  "content": {
    "html": "<p>We are the leading provider of fitness equipment...</p>",
    "text_align": "center"
  },
  "settings": {
    "max_width": "800px",
    "padding": "60px 20px"
  }
}
```

---

### Custom Section
**Type:** `custom`

```json
{
  "section_type": "custom",
  "title": "Custom Component",
  "content": {
    "component_name": "NewsletterSignup",
    "props": {
      "title": "Subscribe to Our Newsletter",
      "placeholder": "Enter your email",
      "button_text": "Subscribe"
    }
  }
}
```

---

## Complete Frontend Implementation

### React Example

```jsx
import React, { useEffect, useState } from 'react';

// Section Components
import HeroSlider from './sections/HeroSlider';
import ProductGrid from './sections/ProductGrid';
import BrandShowcase from './sections/BrandShowcase';
import CategoryGrid from './sections/CategoryGrid';
import Banner from './sections/Banner';
import VideoSection from './sections/VideoSection';
import TextContent from './sections/TextContent';

function DynamicPage({ slug }) {
  const [page, setPage] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`/api/pages/${slug}`)
      .then(res => res.json())
      .then(data => {
        setPage(data);
        setLoading(false);
        
        // Set SEO meta tags
        document.title = data.meta_title || data.title;
        document.querySelector('meta[name="description"]')
          ?.setAttribute('content', data.meta_description || '');
      })
      .catch(err => {
        console.error('Error loading page:', err);
        setLoading(false);
      });
  }, [slug]);

  if (loading) return <div>Loading...</div>;
  if (!page) return <div>Page not found</div>;

  return (
    <div className="dynamic-page">
      {page.sections.map((section) => {
        switch (section.section_type) {
          case 'hero_slider':
            return (
              <HeroSlider
                key={section.id}
                data={section.content}
                settings={section.settings}
              />
            );
          
          case 'product_grid':
            return (
              <ProductGrid
                key={section.id}
                title={section.title}
                data={section.content}
                settings={section.settings}
              />
            );
          
          case 'brand_showcase':
            return (
              <BrandShowcase
                key={section.id}
                title={section.title}
                data={section.content}
                settings={section.settings}
              />
            );
          
          case 'category_grid':
            return (
              <CategoryGrid
                key={section.id}
                title={section.title}
                data={section.content}
                settings={section.settings}
              />
            );
          
          case 'banner':
            return (
              <Banner
                key={section.id}
                data={section.content}
                settings={section.settings}
              />
            );
          
          case 'video_section':
            return (
              <VideoSection
                key={section.id}
                title={section.title}
                data={section.content}
                settings={section.settings}
              />
            );
          
          case 'text_content':
            return (
              <TextContent
                key={section.id}
                title={section.title}
                data={section.content}
                settings={section.settings}
              />
            );
          
          default:
            console.warn(`Unknown section type: ${section.section_type}`);
            return null;
        }
      })}
    </div>
  );
}

export default DynamicPage;
```

### Vue Example

```vue
<template>
  <div class="dynamic-page">
    <div v-if="loading">Loading...</div>
    <div v-else-if="!page">Page not found</div>
    <div v-else>
      <component
        v-for="section in page.sections"
        :key="section.id"
        :is="getSectionComponent(section.section_type)"
        :title="section.title"
        :data="section.content"
        :settings="section.settings"
      />
    </div>
  </div>
</template>

<script>
import HeroSlider from './sections/HeroSlider.vue';
import ProductGrid from './sections/ProductGrid.vue';
import BrandShowcase from './sections/BrandShowcase.vue';
import CategoryGrid from './sections/CategoryGrid.vue';
import Banner from './sections/Banner.vue';
import VideoSection from './sections/VideoSection.vue';
import TextContent from './sections/TextContent.vue';

export default {
  name: 'DynamicPage',
  components: {
    HeroSlider,
    ProductGrid,
    BrandShowcase,
    CategoryGrid,
    Banner,
    VideoSection,
    TextContent
  },
  props: {
    slug: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      page: null,
      loading: true
    };
  },
  mounted() {
    this.loadPage();
  },
  methods: {
    async loadPage() {
      try {
        const response = await fetch(`/api/pages/${this.slug}`);
        this.page = await response.json();
        
        // Set SEO meta tags
        document.title = this.page.meta_title || this.page.title;
        document.querySelector('meta[name="description"]')
          ?.setAttribute('content', this.page.meta_description || '');
      } catch (error) {
        console.error('Error loading page:', error);
      } finally {
        this.loading = false;
      }
    },
    getSectionComponent(type) {
      const components = {
        'hero_slider': 'HeroSlider',
        'product_grid': 'ProductGrid',
        'brand_showcase': 'BrandShowcase',
        'category_grid': 'CategoryGrid',
        'banner': 'Banner',
        'video_section': 'VideoSection',
        'text_content': 'TextContent'
      };
      return components[type] || null;
    }
  }
};
</script>
```

---

## Common Issues

### "NaN" in URL Error
If you see `/api/admin/pages/NaN/sections`, it means the page ID is not being passed correctly. 

**Solution:** Always create the page first and use the returned ID:
```javascript
// Step 1: Create page
const pageResponse = await fetch('/api/admin/pages', {
  method: 'POST',
  body: JSON.stringify({ title: 'My Page', type: 'landing' })
});
const page = await pageResponse.json();

// Step 2: Use page.id to add sections
await fetch(`/api/admin/pages/${page.id}/sections`, {
  method: 'POST',
  body: JSON.stringify({ section_type: 'hero_slider', content: {...} })
});
```

See [DYNAMIC_PAGES_TROUBLESHOOTING.md](./DYNAMIC_PAGES_TROUBLESHOOTING.md) for more solutions.

---

## Admin Workflow

### Step 1: Create a Page
```javascript
const response = await fetch('/api/admin/pages', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    title: 'Summer Sale 2026',
    slug: 'summer-sale',
    type: 'landing',
    meta_title: 'Summer Sale - Up to 50% Off',
    meta_description: 'Best deals of the summer on fitness equipment',
    is_active: true
  })
});

const page = await response.json();
// Returns: { id: 5, title: "Summer Sale 2026", ... }
```

### Step 2: Add Hero Section
```javascript
await fetch(`/api/admin/pages/${page.id}/sections`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    section_type: 'hero_slider',
    title: 'Main Hero',
    content: {
      slides: [
        {
          type: 'image',
          media_url: '/storage/media/summer-hero.jpg',
          title: 'Summer Sale',
          subtitle: 'Up to 50% Off',
          cta_text: 'Shop Now',
          cta_link: '/shop'
        }
      ]
    },
    settings: {
      autoplay: true,
      interval: 5000
    },
    sort_order: 1
  })
});
```

### Step 3: Add Product Grid
```javascript
await fetch(`/api/admin/pages/${page.id}/sections`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    section_type: 'product_grid',
    title: 'Hot Deals',
    content: {
      layout: 'grid',
      columns: 4,
      items: [
        {
          type: 'product_card',
          image: '/storage/media/bike.jpg',
          title: 'Exercise Bikes',
          cta_text: 'Shop Now',
          cta_link: '/category/bikes',
          badge: '50% OFF'
        }
      ]
    },
    sort_order: 2
  })
});
```

### Step 4: Reorder Sections
```javascript
await fetch(`/api/admin/pages/${page.id}/sections/reorder`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    sections: [
      { id: 2, sort_order: 1 },
      { id: 1, sort_order: 2 }
    ]
  })
});
```

---

## Best Practices

1. **Lazy Load Images**: Use lazy loading for section images
2. **Cache Pages**: Cache page data to reduce API calls
3. **Error Boundaries**: Wrap sections in error boundaries
4. **Loading States**: Show skeleton loaders while fetching
5. **SEO**: Always set meta tags from page data
6. **Responsive**: Make all sections mobile-friendly
7. **Analytics**: Track section interactions

---

## Common Use Cases

### Landing Page
```
- Hero Slider (main banner)
- Product Grid (featured products)
- Banner (promotion)
- Category Grid (shop by category)
- Text Content (about section)
```

### Brand Page
```
- Brand Showcase (hero with brand info)
- Category Grid (brand categories)
- Product Grid (brand products)
- Video Section (brand story)
```

### Flash Deal Page
```
- Banner (countdown timer)
- Product Grid (deal products)
- Text Content (terms & conditions)
```

### Gallery Page
```
- Hero Slider (gallery images)
- Text Content (description)
- Category Grid (related categories)
```

This system gives admins complete control over page layouts without requiring developer intervention!
