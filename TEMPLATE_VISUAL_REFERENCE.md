# Template Visual Reference

Visual guide showing how each template maps to your design images.

---

## Image 1: Hero Section with Product

### Template: `product_hero`

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  ┌──────────────┐    ┌──────────────────────────────┐ │
│  │              │    │  Smart Rowing. Full-Body     │ │
│  │   Rowing     │    │  Results.                    │ │
│  │   Machine    │    │  Real Progress.              │ │
│  │   Image      │    │                              │ │
│  │              │    │  Experience a powerful...    │ │
│  │              │    │                              │ │
│  └──────────────┘    │  [Shop Rowers →]             │ │
│                      └──────────────────────────────┘ │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Content Fields**:
- `product_image`: Rowing machine photo
- `heading`: "Smart Rowing. Full-Body Results."
- `subheading`: "Real Progress."
- `description`: Full description text
- `cta_text`: "Shop Rowers"
- `background_color`: "#e8f4f8"

**Settings**:
- `layout`: "image_left"
- `image_width`: "50%"

---

## Image 2: Two Featured Products

### Template: `two_column_cards`

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  ┌─────────────────────┐  ┌─────────────────────────┐ │
│  │                     │  │                         │ │
│  │   Treadmill         │  │   Elliptical            │ │
│  │   Image             │  │   Image                 │ │
│  │                     │  │                         │ │
│  │   T Series 16       │  │   Step Climber XL       │ │
│  │   Treadmill         │  │                         │ │
│  │                     │  │                         │ │
│  │   [Shop Treadmill]  │  │   [Shop Ellipticals]    │ │
│  │                     │  │                         │ │
│  └─────────────────────┘  └─────────────────────────┘ │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Content Fields**:
- `card_1_image`: Treadmill photo
- `card_1_title`: "T Series 16 Treadmill"
- `card_1_cta_text`: "Shop Treadmill"
- `card_2_image`: Elliptical photo
- `card_2_title`: "Step Climber XL"
- `card_2_cta_text`: "Shop Ellipticals"

**Settings**:
- `card_height`: "large"
- `gap`: "medium"

---

## Image 3: Stats Section

### Template: `stats_section`

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│              Thinking Behind the Work                   │
│                                                         │
│  NordicTrack delivers a premium personal training...   │
│                                                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐            │
│  │  51 +    │  │  1M +    │  │  50 +    │            │
│  │  Years of│  │  Happy   │  │Available │            │
│  │Experience│  │Customers │  │Countries │            │
│  └──────────┘  └──────────┘  └──────────┘            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Content Fields**:
- `section_title`: "Thinking Behind the Work"
- `section_description`: Full description
- `stats`: Array of 3 stat objects
  - `{ value: "51 +", label: "Years of Experiences" }`
  - `{ value: "1M +", label: "Happy Customers" }`
  - `{ value: "50 +", label: "Available in Countries" }`

**Settings**:
- `layout`: "horizontal"
- `background_color`: "#ffffff"

---

## Image 4: Promotional Grid

### Template: `promo_grid`

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  ┌──────────────────────┐  ┌──────────────┐           │
│  │                      │  │              │           │
│  │   Bike on Dark BG    │  │  Gear/Foam   │           │
│  │                      │  │  Roller      │           │
│  │  Elevate Your        │  │              │           │
│  │  Fitness Journey     │  │  Perfect     │           │
│  │                      │  │  Gear Awaits │           │
│  │  [Up to 40%]         │  │              │           │
│  │  Discounts           │  │  [Shop Now]  │           │
│  │                      │  │              │           │
│  │  [Shop Now →]        │  └──────────────┘           │
│  │                      │                             │
│  │                      │  ┌──────────────┐           │
│  │                      │  │              │           │
│  │                      │  │  Weights     │           │
│  │                      │  │              │           │
│  │                      │  │  Shine Bright│           │
│  │                      │  │  with Weights│           │
│  │                      │  │              │           │
│  │                      │  │  [Shop Now]  │           │
│  └──────────────────────┘  └──────────────┘           │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Content Fields**:
- Main Card:
  - `main_card_image`: Bike photo
  - `main_card_title`: "Elevate Your Fitness Journey"
  - `main_card_badge`: "Up to 40% Discounts"
  - `main_card_cta_text`: "Shop Now"
- Side Card 1:
  - `side_card_1_image`: Gear photo
  - `side_card_1_title`: "Perfect Gear Awaits"
  - `side_card_1_cta_text`: "Shop Now"
- Side Card 2:
  - `side_card_2_image`: Weights photo
  - `side_card_2_title`: "Shine Bright with Weights"
  - `side_card_2_cta_text`: "Shop Now"

**Settings**:
- `layout`: "main_left"
- `background_color`: "#ffffff"

---

## Image 5: Full Width Banner

### Template: `full_width_banner`

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  ╔═══════════════════════════════════════════════════╗ │
│  ║                                                   ║ │
│  ║   [Background: Person on Treadmill in Home Gym]  ║ │
│  ║                                                   ║ │
│  ║   Turn Your Home Into A Complete                 ║ │
│  ║   Fitness Space                                   ║ │
│  ║                                                   ║ │
│  ║   [Shop Nordictrack →]                           ║ │
│  ║                                                   ║ │
│  ╚═══════════════════════════════════════════════════╝ │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Content Fields**:
- `background_image`: Home gym photo
- `heading`: "Turn Your Home Into A Complete"
- `subheading`: "Fitness Space"
- `cta_text`: "Shop Nordictrack"
- `cta_link`: "/products"

**Settings**:
- `height`: "large"
- `text_alignment`: "left"
- `text_color`: "#ffffff"

---

## Image 5 (Bottom): Category Grid

### Template: `category_grid`

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│         Explore The Nordictrack Categories              │
│                                                         │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐              │
│  │      │  │      │  │      │  │      │              │
│  │Bikes │  │Tread-│  │Ellip-│  │Rowers│              │
│  │Image │  │mills │  │ticals│  │Image │              │
│  │      │  │Image │  │Image │  │      │              │
│  │      │  │      │  │      │  │      │              │
│  │Bikes │  │Tread-│  │Ellip-│  │Rowers│              │
│  │      │  │mills │  │ticals│  │      │              │
│  └──────┘  └──────┘  └──────┘  └──────┘              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Content Fields**:
- `section_title`: "Explore The Nordictrack Categories"
- `categories`: Array of 4 category objects
  - `{ image: "/images/bikes.jpg", title: "Bikes", link: "/products/bikes" }`
  - `{ image: "/images/treadmills.jpg", title: "Treadmills", link: "/products/treadmills" }`
  - `{ image: "/images/ellipticals.jpg", title: "Ellipticals", link: "/products/ellipticals" }`
  - `{ image: "/images/rowers.jpg", title: "Rowers", link: "/products/rowers" }`

**Settings**:
- `columns`: 4
- `card_style`: "bordered"

---

## Complete Homepage Layout

Combining all templates to recreate your design:

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  1. PRODUCT HERO                                        │
│     Smart Rowing. Full-Body Results.                    │
│     [Image Left + Text Right]                           │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  2. TWO COLUMN CARDS                                    │
│     [Treadmill Card] [Elliptical Card]                  │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  3. STATS SECTION                                       │
│     Thinking Behind the Work                            │
│     [51+ Years] [1M+ Customers] [50+ Countries]         │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  4. PROMO GRID                                          │
│     [Large Bike Card] [Gear Card]                       │
│                       [Weights Card]                    │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  5. FULL WIDTH BANNER                                   │
│     Turn Your Home Into A Complete Fitness Space        │
│     [Background Image with Text Overlay]                │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  6. CATEGORY GRID                                       │
│     Explore The Nordictrack Categories                  │
│     [Bikes] [Treadmills] [Ellipticals] [Rowers]         │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## Template Selection Guide

### When to Use Each Template

**Product Hero** (`product_hero`)
- ✅ Homepage hero
- ✅ Product launch pages
- ✅ Feature highlights
- ❌ Multiple products (use two_column_cards)

**Two Column Cards** (`two_column_cards`)
- ✅ Featured categories
- ✅ Product comparisons
- ✅ Before/after showcases
- ❌ More than 2 items (use category_grid)

**Stats Section** (`stats_section`)
- ✅ Company achievements
- ✅ Social proof
- ✅ Key metrics
- ❌ Long descriptions (use text_with_images)

**Promo Grid** (`promo_grid`)
- ✅ Sales promotions
- ✅ Featured collections
- ✅ Highlighting deals
- ❌ Equal-sized items (use category_grid)

**Category Grid** (`category_grid`)
- ✅ Browse categories
- ✅ Product types
- ✅ Collections
- ❌ Detailed descriptions (use two_column_cards)

**Full Width Banner** (`full_width_banner`)
- ✅ Mid-page CTAs
- ✅ Announcements
- ✅ Brand messaging
- ❌ Detailed content (use product_hero)

---

## Responsive Behavior

### Desktop (1200px+)
```
[Image 50%] [Text 50%]     ← Product Hero
[Card 50%] [Card 50%]      ← Two Column Cards
[Stat] [Stat] [Stat]       ← Stats (Horizontal)
[Large Card] [Small Cards] ← Promo Grid
[Cat] [Cat] [Cat] [Cat]    ← Category Grid (4 cols)
```

### Tablet (768px - 1199px)
```
[Image 50%] [Text 50%]     ← Product Hero
[Card 50%] [Card 50%]      ← Two Column Cards
[Stat] [Stat] [Stat]       ← Stats (Horizontal)
[Large Card] [Small Cards] ← Promo Grid
[Cat] [Cat]                ← Category Grid (2 cols)
[Cat] [Cat]
```

### Mobile (< 768px)
```
[Image 100%]               ← Product Hero
[Text 100%]

[Card 100%]                ← Two Column Cards
[Card 100%]

[Stat]                     ← Stats (Stacked)
[Stat]
[Stat]

[Large Card]               ← Promo Grid
[Small Card]
[Small Card]

[Category]                 ← Category Grid (1 col)
[Category]
[Category]
[Category]
```

---

## Color Scheme Reference

Based on your images:

```css
/* Primary Colors */
--yellow: #FFC107;        /* CTA buttons */
--dark-bg: #2C2C2C;       /* Dark sections */
--light-bg: #F5F5F5;      /* Light sections */
--blue-tint: #E8F4F8;     /* Hero backgrounds */

/* Text Colors */
--text-dark: #1A1A1A;     /* Headings */
--text-body: #4A4A4A;     /* Body text */
--text-light: #FFFFFF;    /* On dark backgrounds */

/* Accent Colors */
--orange-badge: #FF6B35;  /* Discount badges */
--border: #E0E0E0;        /* Card borders */
```

---

## Typography Reference

```css
/* Headings */
h1 { font-size: 48px; font-weight: 700; line-height: 1.2; }
h2 { font-size: 36px; font-weight: 700; line-height: 1.3; }
h3 { font-size: 24px; font-weight: 600; line-height: 1.4; }

/* Body */
p { font-size: 16px; line-height: 1.6; }

/* CTA Buttons */
.btn { font-size: 16px; font-weight: 600; padding: 12px 32px; }

/* Stats */
.stat-value { font-size: 48px; font-weight: 700; }
.stat-label { font-size: 14px; font-weight: 400; }
```

---

## Image Specifications

### Product Hero
- Dimensions: 800x600px (4:3 ratio)
- Format: JPG or WebP
- Max size: 300KB

### Two Column Cards
- Dimensions: 600x800px (3:4 ratio)
- Format: JPG or WebP
- Max size: 250KB each

### Promo Grid
- Main card: 800x1000px
- Side cards: 400x400px
- Format: JPG or WebP

### Category Grid
- Dimensions: 400x300px (4:3 ratio)
- Format: JPG or WebP
- Max size: 150KB each

### Full Width Banner
- Dimensions: 1920x600px (16:5 ratio)
- Format: JPG or WebP
- Max size: 500KB

---

## Quick Reference Table

| Template | Use Case | Image Count | CTA Buttons | Best For |
|----------|----------|-------------|-------------|----------|
| product_hero | Hero section | 1 | 1 | Product launches |
| two_column_cards | Featured items | 2 | 2 | Categories |
| stats_section | Metrics | 0-4 | 0 | Social proof |
| promo_grid | Promotions | 3 | 3 | Sales |
| category_grid | Browse | 2-8 | 0-8 | Navigation |
| full_width_banner | CTA | 1 (bg) | 1 | Conversions |

---

This visual reference should help you understand exactly how each template maps to your design images!
