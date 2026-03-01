# New Features Implementation

This document outlines the newly implemented features: Pre-orders with Partial Payment, Flash Deals, and RDX Gallery.

---

## 1. PRE-ORDERS WITH PARTIAL PAYMENT

### Overview
Customers can now place pre-orders for products that are not yet available, with the option to pay a deposit amount upfront and the remaining balance later.

### Database Changes

#### Products Table
- `is_preorder` (boolean): Marks product as available for pre-order
- `preorder_release_date` (datetime): Expected release date
- `preorder_limit` (integer): Maximum number of pre-orders allowed
- `preorder_deposit_amount` (decimal): Deposit amount or percentage
- `preorder_deposit_type` (enum: 'percentage', 'fixed'): Type of deposit calculation

#### Orders Table
- `is_preorder` (boolean): Marks order as pre-order
- `preorder_deposit_paid` (decimal): Amount paid as deposit
- `preorder_remaining_amount` (decimal): Remaining balance to be paid
- `preorder_payment_status` (enum: 'deposit_paid', 'fully_paid', 'pending'): Payment status

### API Endpoints

#### Admin - Manage Pre-order Products
```
PUT /api/admin/products/{id}
```
**Request Body:**
```json
{
  "is_preorder": true,
  "preorder_release_date": "2026-06-01T00:00:00Z",
  "preorder_limit": 100,
  "preorder_deposit_amount": 30,
  "preorder_deposit_type": "percentage"
}
```

#### Customer - Checkout Preview with Pre-order
```
POST /api/checkout/preview
```
**Request Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 5000,
      "is_preorder": true
    }
  ],
  "is_preorder": true,
  "pay_deposit_only": true,
  "shipping_method": "pathao_courier"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 10000,
    "shipping_cost": 100,
    "coupon_discount": 0,
    "total": 10100,
    "is_preorder": true,
    "deposit_amount": 3000,
    "remaining_amount": 7100,
    "payable_now": 3000
  }
}
```

#### Customer - Place Pre-order
```
POST /api/checkout/process
```
**Request Body:**
```json
{
  "items": [...],
  "is_preorder": true,
  "pay_deposit_only": true,
  "shipping_address_id": 1,
  "payment_method": "ssl_commerz"
}
```

#### Customer - Pay Remaining Balance
```
POST /api/payments/{orderNumber}/pay-preorder-balance
```
**Request Body:**
```json
{
  "payment_method": "ssl_commerz"
}
```

### Product Model Methods
- `isPreorderAvailable()`: Check if product accepts pre-orders
- `calculatePreorderDeposit()`: Calculate deposit amount
- `scopePreorder()`: Query scope for pre-order products

### Order Model Methods
- `isPreorder()`: Check if order is pre-order
- `isPreorderDepositPaid()`: Check deposit payment status
- `isPreorderFullyPaid()`: Check full payment status
- `getRemainingPreorderAmount()`: Get remaining balance

---

## 2. FLASH DEALS

### Overview
Time-limited deals with countdown timers, quantity limits, and special pricing for products.

### Database Schema

#### flash_deals Table
- `title`: Deal name
- `description`: Deal description
- `starts_at`: Start datetime
- `ends_at`: End datetime
- `discount_type`: 'percentage' or 'fixed_amount'
- `discount_value`: Discount value
- `max_discount_amount`: Maximum discount cap
- `quantity_limit`: Total quantity available
- `quantity_sold`: Quantity sold
- `per_user_limit`: Max quantity per user
- `is_active`: Active status
- `priority`: Display priority

#### flash_deal_products Table (Pivot)
- `flash_deal_id`
- `product_id`
- `flash_price`: Special flash deal price
- `quantity_limit`: Product-specific quantity limit
- `quantity_sold`: Product-specific quantity sold

### API Endpoints

#### Public - Get Active Flash Deals
```
GET /api/flash-deals
```
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Weekend Flash Sale",
      "description": "50% off on selected items",
      "starts_at": "2026-03-01T00:00:00Z",
      "ends_at": "2026-03-03T23:59:59Z",
      "time_remaining": {
        "status": "active",
        "seconds": 172800
      },
      "remaining_quantity": 45,
      "products": [...]
    }
  ]
}
```

#### Public - Get Upcoming Flash Deals
```
GET /api/flash-deals/upcoming
```

#### Public - Get Single Flash Deal
```
GET /api/flash-deals/{id}
```

#### Admin - Create Flash Deal
```
POST /api/admin/flash-deals
```
**Request Body:**
```json
{
  "title": "Weekend Flash Sale",
  "description": "Limited time offer",
  "starts_at": "2026-03-05T00:00:00Z",
  "ends_at": "2026-03-07T23:59:59Z",
  "discount_type": "percentage",
  "discount_value": 50,
  "quantity_limit": 100,
  "per_user_limit": 5,
  "is_active": true,
  "priority": 10,
  "products": [
    {
      "product_id": 1,
      "flash_price": 2500,
      "quantity_limit": 50
    }
  ]
}
```

#### Admin - Update Flash Deal
```
PUT /api/admin/flash-deals/{id}
```

#### Admin - Delete Flash Deal
```
DELETE /api/admin/flash-deals/{id}
```

#### Admin - Toggle Flash Deal Status
```
POST /api/admin/flash-deals/{id}/toggle
```

#### Admin - Get Flash Deal Statistics
```
GET /api/admin/flash-deals/{id}/statistics
```

### Product Model Methods
- `flashDeals()`: Get all flash deals for product
- `activeFlashDeal()`: Get current active flash deal
- `hasActiveFlashDeal()`: Check if product has active flash deal
- `getFlashPriceAttribute()`: Get flash deal price
- `getEffectivePriceAttribute()`: Get effective price (flash or regular)

### FlashDeal Model Methods
- `isActive()`: Check if deal is currently active
- `hasStock()`: Check if deal has remaining stock
- `getRemainingQuantityAttribute()`: Get remaining quantity
- `getTimeRemainingAttribute()`: Get countdown timer data

---

## 3. RDX GALLERY (Product Image Gallery Management)

### Overview
Advanced gallery management system for organizing product images, banners, and general media with categorization and sorting.

### Database Schema

#### galleries Table
- `title`: Gallery name
- `slug`: URL-friendly identifier
- `description`: Gallery description
- `type`: 'product', 'banner', or 'general'
- `is_active`: Active status
- `sort_order`: Display order

#### gallery_images Table
- `gallery_id`: Parent gallery
- `image_path`: Image file path
- `title`: Image title
- `description`: Image description
- `alt_text`: SEO alt text
- `sort_order`: Display order
- `is_featured`: Featured image flag

### API Endpoints

#### Public - Get All Galleries
```
GET /api/galleries?type=product
```
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Summer Collection 2026",
      "slug": "summer-collection-2026",
      "type": "product",
      "images": [
        {
          "id": 1,
          "image_path": "/storage/gallery/image1.jpg",
          "title": "Product Shot 1",
          "alt_text": "Summer collection product",
          "sort_order": 1,
          "is_featured": true
        }
      ]
    }
  ]
}
```

#### Public - Get Single Gallery
```
GET /api/galleries/{slug}
```

#### Admin - Create Gallery
```
POST /api/admin/galleries
```
**Request Body:**
```json
{
  "title": "Summer Collection 2026",
  "description": "Our latest summer products",
  "type": "product",
  "is_active": true,
  "sort_order": 1,
  "images": [
    {
      "image_path": "/storage/gallery/image1.jpg",
      "title": "Product Shot 1",
      "alt_text": "Summer product",
      "sort_order": 1,
      "is_featured": true
    }
  ]
}
```

#### Admin - Update Gallery
```
PUT /api/admin/galleries/{id}
```

#### Admin - Delete Gallery
```
DELETE /api/admin/galleries/{id}
```

#### Admin - Add Image to Gallery
```
POST /api/admin/galleries/{id}/images
```
**Request Body:**
```json
{
  "image_path": "/storage/gallery/new-image.jpg",
  "title": "New Product Image",
  "alt_text": "Product description",
  "sort_order": 5,
  "is_featured": false
}
```

#### Admin - Update Gallery Image
```
PUT /api/admin/galleries/{galleryId}/images/{imageId}
```

#### Admin - Delete Gallery Image
```
DELETE /api/admin/galleries/{galleryId}/images/{imageId}
```

### Gallery Model Methods
- `images()`: Get all images in gallery
- `featuredImage()`: Get featured image
- `scopeActive()`: Query active galleries
- `scopeByType()`: Filter by gallery type

---

## Migration Instructions

Run the following command to apply all database changes:

```bash
php artisan migrate
```

This will create:
1. Pre-order fields in products and orders tables
2. Flash deals and flash_deal_products tables
3. Galleries and gallery_images tables

---

## Usage Examples

### Example 1: Create a Pre-order Product
```php
$product = Product::create([
    'name' => 'iPhone 16 Pro',
    'price' => 120000,
    'is_preorder' => true,
    'preorder_release_date' => '2026-09-15',
    'preorder_limit' => 500,
    'preorder_deposit_amount' => 20, // 20%
    'preorder_deposit_type' => 'percentage',
    // ... other fields
]);
```

### Example 2: Create a Flash Deal
```php
$flashDeal = FlashDeal::create([
    'title' => '24 Hour Mega Sale',
    'starts_at' => now(),
    'ends_at' => now()->addHours(24),
    'discount_type' => 'percentage',
    'discount_value' => 40,
    'quantity_limit' => 200,
    'is_active' => true,
]);

$flashDeal->products()->attach($productId, [
    'flash_price' => 3000,
    'quantity_limit' => 50,
]);
```

### Example 3: Create a Gallery
```php
$gallery = Gallery::create([
    'title' => 'New Arrivals',
    'type' => 'product',
    'is_active' => true,
]);

$gallery->images()->create([
    'image_path' => '/storage/products/new-arrival-1.jpg',
    'title' => 'Latest Product',
    'alt_text' => 'New arrival product image',
    'is_featured' => true,
]);
```

---

## Testing Checklist

### Pre-orders
- [ ] Create pre-order product with deposit
- [ ] Place pre-order with deposit payment
- [ ] Pay remaining balance
- [ ] Verify preorder limits
- [ ] Test release date validation

### Flash Deals
- [ ] Create flash deal with products
- [ ] Verify countdown timer
- [ ] Test quantity limits
- [ ] Check per-user limits
- [ ] Verify flash prices in catalog

### Galleries
- [ ] Create gallery with images
- [ ] Update gallery images
- [ ] Delete images
- [ ] Filter by gallery type
- [ ] Test featured images

---

## Notes

1. **Pre-orders**: Ensure payment gateway supports partial payments
2. **Flash Deals**: Implement frontend countdown timer using `time_remaining` data
3. **Galleries**: Implement image upload handling on frontend
4. **Performance**: Consider caching active flash deals
5. **Inventory**: Pre-orders don't reduce stock until release date

---

## Support

For questions or issues, please contact the development team.
