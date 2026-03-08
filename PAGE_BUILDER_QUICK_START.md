# Page Builder Quick Start Guide

Get your page builder system up and running in 5 minutes!

---

## Step 1: Run Migrations

```bash
php artisan migrate
```

This creates the necessary database tables:
- `pages` - Stores page metadata
- `page_sections` - Stores section content
- `media_library` - Manages uploaded images

---

## Step 2: Seed Sample Data

```bash
php artisan db:seed --class=PageTemplateSeeder
```

This creates:
- A complete NordicTrack-style homepage
- An About Us page with sample content
- All sections properly configured

---

## Step 3: Test the API

### Get Available Templates

```bash
curl http://localhost:8000/api/admin/page-templates
```

You should see 10 templates: hero_banner, product_hero, two_column_cards, etc.

### Get Homepage

```bash
curl http://localhost:8000/api/pages/home
```

You should see the complete homepage with all sections.

---

## Step 4: Create Your First Page

### Using cURL

```bash
# 1. Create a page
curl -X POST http://localhost:8000/api/admin/pages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "My Landing Page",
    "slug": "my-landing",
    "type": "landing",
    "is_active": true
  }'

# 2. Add a hero section
curl -X POST http://localhost:8000/api/admin/pages/1/sections \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "section_type": "product_hero",
    "content": {
      "product_image": "/images/product.jpg",
      "heading": "Amazing Product",
      "description": "This is an amazing product...",
      "cta_text": "Shop Now",
      "cta_link": "/products",
      "background_color": "#f5f5f5"
    },
    "settings": {
      "layout": "image_left"
    }
  }'
```

### Using Postman

1. Import the API collection (see PAGE_BUILDER_API_REFERENCE.md)
2. Set your auth token
3. Create page → Add sections → Publish

---

## Step 5: View Your Page

Visit: `http://localhost:8000/api/pages/my-landing`

You should see your page with all sections in JSON format.

---

## Step 6: Build Frontend (React)

### Install Dependencies

```bash
npm install @tanstack/react-query
```

### Create Dynamic Page Route

```jsx
// pages/[slug].jsx
import { useRouter } from 'next/router';
import { useQuery } from '@tanstack/react-query';
import SectionRenderer from '@/components/SectionRenderer';

export default function DynamicPage() {
  const router = useRouter();
  const { slug } = router.query;

  const { data: page } = useQuery({
    queryKey: ['page', slug],
    queryFn: () => fetch(`/api/pages/${slug}`).then(res => res.json())
  });

  if (!page) return <div>Loading...</div>;

  return (
    <div>
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

### Create Section Renderer

```jsx
// components/SectionRenderer.jsx
import ProductHero from './sections/ProductHero';
import TwoColumnCards from './sections/TwoColumnCards';
// ... import other sections

const COMPONENTS = {
  product_hero: ProductHero,
  two_column_cards: TwoColumnCards,
  // ... map other templates
};

export default function SectionRenderer({ type, content, settings }) {
  const Component = COMPONENTS[type];
  if (!Component) return null;
  return <Component content={content} settings={settings} />;
}
```

### Create Section Components

```jsx
// components/sections/ProductHero.jsx
export default function ProductHero({ content, settings }) {
  return (
    <section style={{ backgroundColor: content.background_color }}>
      <div className="container">
        <div className={`layout-${settings.layout}`}>
          <img src={content.product_image} alt={content.heading} />
          <div>
            <h1>{content.heading}</h1>
            <p>{content.description}</p>
            <a href={content.cta_link}>{content.cta_text}</a>
          </div>
        </div>
      </div>
    </section>
  );
}
```

See **FRONTEND_PAGE_BUILDER_GUIDE.md** for complete component examples.

---

## Step 7: Build Admin Panel (Optional)

### Template Browser

```jsx
// components/admin/TemplateBrowser.jsx
import { useQuery } from '@tanstack/react-query';

export default function TemplateBrowser({ onSelect }) {
  const { data: templates } = useQuery({
    queryKey: ['templates'],
    queryFn: () => fetch('/api/admin/page-templates').then(res => res.json())
  });

  return (
    <div className="template-grid">
      {Object.entries(templates || {}).map(([type, template]) => (
        <div key={type} onClick={() => onSelect(type)}>
          <h3>{template.name}</h3>
          <p>{template.description}</p>
        </div>
      ))}
    </div>
  );
}
```

### Section Editor

```jsx
// components/admin/SectionEditor.jsx
export default function SectionEditor({ sectionType, onSave }) {
  const [content, setContent] = useState({});

  const handleSubmit = async (e) => {
    e.preventDefault();
    await fetch(`/api/admin/pages/${pageId}/sections`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        section_type: sectionType,
        content
      })
    });
    onSave();
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Render form fields based on template schema */}
      <button type="submit">Save Section</button>
    </form>
  );
}
```

---

## Common Tasks

### Add a New Section to Existing Page

```bash
curl -X POST http://localhost:8000/api/admin/pages/1/sections \
  -H "Content-Type: application/json" \
  -d '{
    "section_type": "stats_section",
    "content": {
      "section_title": "Our Achievements",
      "stats": [
        {"value": "10K+", "label": "Happy Customers"},
        {"value": "50+", "label": "Countries"}
      ]
    }
  }'
```

### Update Section Content

```bash
curl -X PUT http://localhost:8000/api/admin/pages/1/sections/2 \
  -H "Content-Type: application/json" \
  -d '{
    "content": {
      "heading": "Updated Heading"
    }
  }'
```

### Reorder Sections

```bash
curl -X POST http://localhost:8000/api/admin/pages/1/sections/reorder \
  -H "Content-Type: application/json" \
  -d '{
    "sections": [
      {"id": 1, "sort_order": 1},
      {"id": 3, "sort_order": 2},
      {"id": 2, "sort_order": 3}
    ]
  }'
```

### Duplicate a Page

```bash
curl -X POST http://localhost:8000/api/admin/pages/1/duplicate \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Troubleshooting

### Issue: "Page not found"
**Solution**: Check that the page is active (`is_active = true`)

### Issue: "Section not rendering"
**Solution**: 
1. Verify section is active
2. Check `sort_order` is set
3. Ensure required fields are filled

### Issue: "Images not loading"
**Solution**: 
1. Use absolute paths starting with `/`
2. Verify images exist in public directory
3. Check file permissions

### Issue: "Template not found"
**Solution**: Check template type spelling matches exactly (e.g., `product_hero` not `productHero`)

---

## Next Steps

1. ✅ **Read Documentation**
   - ADMIN_PAGE_BUILDER_GUIDE.md - For content managers
   - PAGE_BUILDER_API_REFERENCE.md - For API details
   - FRONTEND_PAGE_BUILDER_GUIDE.md - For frontend implementation

2. ✅ **Customize Templates**
   - Add your brand colors
   - Adjust spacing and typography
   - Add custom CSS classes

3. ✅ **Create More Pages**
   - Landing pages for campaigns
   - Product category pages
   - About/Contact pages

4. ✅ **Build Admin UI**
   - Visual template browser
   - Drag-and-drop editor
   - Live preview

5. ✅ **Add More Templates**
   - Follow the guide in PageTemplateService.php
   - Create corresponding React components
   - Update SectionRenderer

---

## Example: Complete Homepage in 2 Minutes

```bash
# 1. Create page
PAGE_ID=$(curl -X POST http://localhost:8000/api/admin/pages \
  -H "Content-Type: application/json" \
  -d '{"title":"Home","slug":"home","type":"landing"}' \
  | jq -r '.id')

# 2. Add hero
curl -X POST http://localhost:8000/api/admin/pages/$PAGE_ID/sections \
  -H "Content-Type: application/json" \
  -d '{
    "section_type":"product_hero",
    "content":{
      "product_image":"/images/hero.jpg",
      "heading":"Welcome to Our Store",
      "description":"Discover amazing products",
      "cta_text":"Shop Now"
    }
  }'

# 3. Add stats
curl -X POST http://localhost:8000/api/admin/pages/$PAGE_ID/sections \
  -H "Content-Type: application/json" \
  -d '{
    "section_type":"stats_section",
    "content":{
      "stats":[
        {"value":"10K+","label":"Customers"},
        {"value":"50+","label":"Countries"}
      ]
    }
  }'

# 4. Add CTA
curl -X POST http://localhost:8000/api/admin/pages/$PAGE_ID/sections \
  -H "Content-Type: application/json" \
  -d '{
    "section_type":"cta_section",
    "content":{
      "heading":"Ready to Get Started?",
      "cta_text":"Sign Up Now"
    }
  }'

# Done! View at: /api/pages/home
```

---

## Resources

- **Documentation**: See all MD files in project root
- **Sample Data**: Run PageTemplateSeeder
- **API Collection**: Import into Postman
- **React Examples**: See FRONTEND_PAGE_BUILDER_GUIDE.md

---

## Support

For questions or issues:
1. Check documentation first
2. Review sample seeder code
3. Test with provided examples
4. Contact development team

---

You're all set! Start building beautiful pages with your new page builder system. 🚀
