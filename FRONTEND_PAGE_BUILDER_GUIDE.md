# Frontend Page Builder Implementation Guide

Guide for implementing the page builder on the frontend (React/Next.js).

---

## Architecture Overview

```
┌─────────────────────────────────────┐
│         Dynamic Page Route          │
│      /pages/[slug].jsx              │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│      Fetch Page Data from API       │
│      GET /api/pages/{slug}          │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│       Section Renderer              │
│   Maps section_type to Component    │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│     Individual Section Components   │
│  ProductHero, TwoColumnCards, etc.  │
└─────────────────────────────────────┘
```

---

## 1. Dynamic Page Component

### Next.js Example

```jsx
// pages/[slug].jsx
import { useRouter } from 'next/router';
import { useQuery } from '@tanstack/react-query';
import SectionRenderer from '@/components/SectionRenderer';
import Head from 'next/head';

export default function DynamicPage() {
  const router = useRouter();
  const { slug } = router.query;

  const { data: page, isLoading, error } = useQuery({
    queryKey: ['page', slug],
    queryFn: () => fetch(`/api/pages/${slug}`).then(res => res.json()),
    enabled: !!slug
  });

  if (isLoading) return <PageSkeleton />;
  if (error) return <ErrorPage />;
  if (!page) return <NotFound />;

  return (
    <>
      <Head>
        <title>{page.meta_title || page.title}</title>
        <meta name="description" content={page.meta_description} />
      </Head>

      <div className="dynamic-page">
        {page.sections.map((section) => (
          <SectionRenderer
            key={section.id}
            type={section.section_type}
            content={section.content}
            settings={section.settings}
          />
        ))}
      </div>
    </>
  );
}
```

### React Router Example

```jsx
// routes/DynamicPage.jsx
import { useParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import SectionRenderer from '../components/SectionRenderer';

export default function DynamicPage() {
  const { slug } = useParams();

  const { data: page, isLoading } = useQuery({
    queryKey: ['page', slug],
    queryFn: async () => {
      const res = await fetch(`/api/pages/${slug}`);
      if (!res.ok) throw new Error('Page not found');
      return res.json();
    }
  });

  if (isLoading) return <div>Loading...</div>;

  return (
    <div className="dynamic-page">
      {page.sections.map((section) => (
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

## 2. Section Renderer

```jsx
// components/SectionRenderer.jsx
import ProductHero from './sections/ProductHero';
import HeroBanner from './sections/HeroBanner';
import TwoColumnCards from './sections/TwoColumnCards';
import PromoGrid from './sections/PromoGrid';
import CategoryGrid from './sections/CategoryGrid';
import StatsSection from './sections/StatsSection';
import TextWithImages from './sections/TextWithImages';
import FullWidthBanner from './sections/FullWidthBanner';
import ProductShowcase from './sections/ProductShowcase';
import CTASection from './sections/CTASection';

const SECTION_COMPONENTS = {
  product_hero: ProductHero,
  hero_banner: HeroBanner,
  two_column_cards: TwoColumnCards,
  promo_grid: PromoGrid,
  category_grid: CategoryGrid,
  stats_section: StatsSection,
  text_with_images: TextWithImages,
  full_width_banner: FullWidthBanner,
  product_showcase: ProductShowcase,
  cta_section: CTASection,
};

export default function SectionRenderer({ type, content, settings }) {
  const Component = SECTION_COMPONENTS[type];

  if (!Component) {
    console.warn(`Unknown section type: ${type}`);
    return null;
  }

  return <Component content={content} settings={settings} />;
}
```

---

## 3. Section Components

### Product Hero

```jsx
// components/sections/ProductHero.jsx
export default function ProductHero({ content, settings }) {
  const {
    product_image,
    heading,
    subheading,
    description,
    cta_text,
    cta_link,
    background_color
  } = content;

  const { layout = 'image_left', image_width = '50%' } = settings;

  return (
    <section 
      className="product-hero"
      style={{ backgroundColor: background_color }}
    >
      <div className="container">
        <div className={`hero-content layout-${layout}`}>
          <div className="hero-image" style={{ width: image_width }}>
            <img src={product_image} alt={heading} />
          </div>
          
          <div className="hero-text">
            <h1>{heading}</h1>
            {subheading && <h2>{subheading}</h2>}
            <p>{description}</p>
            {cta_text && (
              <a href={cta_link} className="btn btn-primary">
                {cta_text}
              </a>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
```

### Two Column Cards

```jsx
// components/sections/TwoColumnCards.jsx
export default function TwoColumnCards({ content, settings }) {
  const { card_height = 'medium', gap = 'medium' } = settings;

  return (
    <section className="two-column-cards">
      <div className="container">
        <div className={`cards-grid gap-${gap}`}>
          <Card
            image={content.card_1_image}
            title={content.card_1_title}
            ctaText={content.card_1_cta_text}
            ctaLink={content.card_1_cta_link}
            height={card_height}
          />
          <Card
            image={content.card_2_image}
            title={content.card_2_title}
            ctaText={content.card_2_cta_text}
            ctaLink={content.card_2_cta_link}
            height={card_height}
          />
        </div>
      </div>
    </section>
  );
}

function Card({ image, title, ctaText, ctaLink, height }) {
  return (
    <div className={`card card-${height}`}>
      <img src={image} alt={title} />
      <div className="card-overlay">
        <h3>{title}</h3>
        <a href={ctaLink} className="btn btn-yellow">
          {ctaText}
        </a>
      </div>
    </div>
  );
}
```

### Stats Section

```jsx
// components/sections/StatsSection.jsx
export default function StatsSection({ content, settings }) {
  const {
    section_title,
    section_description,
    stats = []
  } = content;

  const { layout = 'horizontal', background_color = '#ffffff' } = settings;

  return (
    <section 
      className="stats-section"
      style={{ backgroundColor: background_color }}
    >
      <div className="container">
        {section_title && <h2>{section_title}</h2>}
        {section_description && <p className="description">{section_description}</p>}
        
        <div className={`stats-grid layout-${layout}`}>
          {stats.map((stat, index) => (
            <div key={index} className="stat-item">
              <div className="stat-value">{stat.value}</div>
              <div className="stat-label">{stat.label}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
```

### Promo Grid

```jsx
// components/sections/PromoGrid.jsx
export default function PromoGrid({ content, settings }) {
  const { layout = 'main_left', background_color = '#ffffff' } = settings;

  return (
    <section 
      className="promo-grid"
      style={{ backgroundColor: background_color }}
    >
      <div className="container">
        <div className={`grid layout-${layout}`}>
          {/* Main Card */}
          <div className="main-card">
            <img src={content.main_card_image} alt={content.main_card_title} />
            <div className="card-content">
              {content.main_card_badge && (
                <span className="badge">{content.main_card_badge}</span>
              )}
              <h3>{content.main_card_title}</h3>
              {content.main_card_subtitle && <p>{content.main_card_subtitle}</p>}
              <a href={content.main_card_cta_link} className="btn btn-yellow">
                {content.main_card_cta_text}
              </a>
            </div>
          </div>

          {/* Side Cards */}
          <div className="side-cards">
            <div className="side-card">
              <img src={content.side_card_1_image} alt={content.side_card_1_title} />
              <div className="card-content">
                <h4>{content.side_card_1_title}</h4>
                <a href={content.side_card_1_cta_link} className="btn btn-yellow">
                  {content.side_card_1_cta_text}
                </a>
              </div>
            </div>

            <div className="side-card">
              <img src={content.side_card_2_image} alt={content.side_card_2_title} />
              <div className="card-content">
                <h4>{content.side_card_2_title}</h4>
                <a href={content.side_card_2_cta_link} className="btn btn-yellow">
                  {content.side_card_2_cta_text}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
```

### Category Grid

```jsx
// components/sections/CategoryGrid.jsx
export default function CategoryGrid({ content, settings }) {
  const { section_title, categories = [] } = content;
  const { columns = 4, card_style = 'bordered' } = settings;

  return (
    <section className="category-grid">
      <div className="container">
        {section_title && <h2 className="section-title">{section_title}</h2>}
        
        <div 
          className={`grid columns-${columns} style-${card_style}`}
          style={{
            gridTemplateColumns: `repeat(${columns}, 1fr)`
          }}
        >
          {categories.map((category, index) => (
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

### Full Width Banner

```jsx
// components/sections/FullWidthBanner.jsx
export default function FullWidthBanner({ content, settings }) {
  const {
    background_image,
    heading,
    subheading,
    cta_text,
    cta_link
  } = content;

  const {
    height = 'medium',
    text_alignment = 'center',
    text_color = '#ffffff'
  } = settings;

  return (
    <section 
      className={`full-width-banner height-${height} text-${text_alignment}`}
      style={{
        backgroundImage: `url(${background_image})`,
        color: text_color
      }}
    >
      <div className="banner-overlay">
        <div className="container">
          <div className="banner-content">
            <h2>{heading}</h2>
            {subheading && <h3 className="subheading">{subheading}</h3>}
            {cta_text && (
              <a href={cta_link} className="btn btn-yellow">
                {cta_text}
              </a>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
```

---

## 4. Styling (Tailwind CSS)

```jsx
// Example with Tailwind
export default function ProductHero({ content, settings }) {
  const { layout = 'image_left' } = settings;

  return (
    <section 
      className="py-16"
      style={{ backgroundColor: content.background_color }}
    >
      <div className="container mx-auto px-4">
        <div className={`flex flex-col md:flex-row items-center gap-8 ${
          layout === 'image_right' ? 'md:flex-row-reverse' : ''
        }`}>
          <div className="w-full md:w-1/2">
            <img 
              src={content.product_image} 
              alt={content.heading}
              className="w-full h-auto rounded-lg"
            />
          </div>
          
          <div className="w-full md:w-1/2">
            <h1 className="text-4xl md:text-5xl font-bold mb-4">
              {content.heading}
            </h1>
            {content.subheading && (
              <h2 className="text-2xl md:text-3xl font-semibold mb-4">
                {content.subheading}
              </h2>
            )}
            <p className="text-lg mb-6">{content.description}</p>
            {content.cta_text && (
              <a 
                href={content.cta_link}
                className="inline-block bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-8 py-3 rounded-lg transition"
              >
                {content.cta_text}
              </a>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
```

---

## 5. Admin Panel Integration

### Template Selector

```jsx
// components/admin/TemplateSelectorModal.jsx
import { useQuery } from '@tanstack/react-query';

export default function TemplateSelectorModal({ onSelect, onClose }) {
  const { data: templates } = useQuery({
    queryKey: ['templates'],
    queryFn: () => fetch('/api/admin/page-templates').then(res => res.json())
  });

  const categories = ['hero', 'content', 'banner', 'products', 'cta'];

  return (
    <div className="modal">
      <div className="modal-content">
        <h2>Select Section Template</h2>
        
        {categories.map(category => (
          <div key={category} className="template-category">
            <h3>{category}</h3>
            <div className="template-grid">
              {Object.entries(templates || {})
                .filter(([_, template]) => template.category === category)
                .map(([type, template]) => (
                  <div 
                    key={type}
                    className="template-card"
                    onClick={() => onSelect(type, template)}
                  >
                    <img src={template.preview_image} alt={template.name} />
                    <h4>{template.name}</h4>
                    <p>{template.description}</p>
                  </div>
                ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
```

### Section Editor

```jsx
// components/admin/SectionEditor.jsx
import { useQuery } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';

export default function SectionEditor({ sectionType, initialData, onSave }) {
  const { data: schema } = useQuery({
    queryKey: ['template-schema', sectionType],
    queryFn: () => 
      fetch(`/api/admin/page-templates/${sectionType}/schema`)
        .then(res => res.json())
  });

  const { register, handleSubmit } = useForm({
    defaultValues: initialData
  });

  if (!schema) return <div>Loading...</div>;

  return (
    <form onSubmit={handleSubmit(onSave)}>
      <h3>{schema.name}</h3>
      
      {/* Render fields based on schema */}
      {Object.entries(schema.schema).map(([field, rules]) => (
        <div key={field} className="form-group">
          <label>{field}</label>
          
          {rules.type === 'image' && (
            <ImageUploader {...register(`content.${field}`)} />
          )}
          
          {rules.type === 'text' && (
            <input 
              type="text"
              {...register(`content.${field}`, { required: rules.required })}
            />
          )}
          
          {rules.type === 'textarea' && (
            <textarea 
              {...register(`content.${field}`, { required: rules.required })}
            />
          )}
          
          {rules.type === 'select' && (
            <select {...register(`content.${field}`)}>
              {rules.options.map(option => (
                <option key={option} value={option}>{option}</option>
              ))}
            </select>
          )}
        </div>
      ))}

      <button type="submit">Save Section</button>
    </form>
  );
}
```

---

## 6. Performance Optimization

### Image Optimization

```jsx
import Image from 'next/image';

export default function ProductHero({ content }) {
  return (
    <div className="hero-image">
      <Image
        src={content.product_image}
        alt={content.heading}
        width={800}
        height={600}
        priority
        quality={85}
      />
    </div>
  );
}
```

### Lazy Loading Sections

```jsx
import { lazy, Suspense } from 'react';

const ProductHero = lazy(() => import('./sections/ProductHero'));
const PromoGrid = lazy(() => import('./sections/PromoGrid'));

export default function SectionRenderer({ type, content, settings }) {
  const Component = SECTION_COMPONENTS[type];

  return (
    <Suspense fallback={<SectionSkeleton />}>
      <Component content={content} settings={settings} />
    </Suspense>
  );
}
```

---

## 7. SEO & Analytics

```jsx
export default function DynamicPage() {
  const { data: page } = useQuery(/* ... */);

  useEffect(() => {
    // Track page view
    analytics.track('Page Viewed', {
      page_id: page.id,
      page_title: page.title,
      page_type: page.type
    });
  }, [page]);

  return (
    <>
      <Head>
        <title>{page.meta_title}</title>
        <meta name="description" content={page.meta_description} />
        <meta property="og:title" content={page.meta_title} />
        <meta property="og:description" content={page.meta_description} />
        <link rel="canonical" href={`https://example.com/pages/${page.slug}`} />
      </Head>
      {/* ... */}
    </>
  );
}
```

---

## Complete Example

See the full working example in the repository:
- `/examples/frontend/pages/[slug].jsx` - Dynamic page route
- `/examples/frontend/components/SectionRenderer.jsx` - Section renderer
- `/examples/frontend/components/sections/` - All section components
- `/examples/frontend/styles/sections.css` - Section styles
