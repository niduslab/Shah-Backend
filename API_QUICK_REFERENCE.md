# API Quick Reference Guide

## 🚀 Base URL
```
http://your-domain.com/api
```

## 🔑 Authentication
```
Authorization: Bearer {token}
```

---

## 📦 Public APIs (No Auth Required)

### Catalog
- `GET /catalog/categories` - All categories
- `GET /catalog/categories/{slug}` - Category details
- `GET /catalog/categories/{slug}/products` - Products by category
- `GET /catalog/brands` - All brands
- `GET /catalog/brands/{slug}/products` - Products by brand
- `GET /catalog/products` - Search products
- `GET /catalog/products/{slug}` - Product details
- `GET /catalog/products/featured` - Featured products
- `GET /catalog/products/trending` - Trending products

### Flash Deals
- `GET /flash-deals` - Active flash deals
- `GET /flash-deals/upcoming` - Upcoming deals
- `GET /flash-deals/{id}` - Deal details

### Cart
- `POST /cart/summary` - Calculate cart
- `POST /cart/validate-coupon` - Validate coupon
- `POST /cart/check-availability` - Check stock

### Other
- `GET /galleries` - All galleries
- `GET /galleries/{slug}` - Gallery details
- `GET /products/{productId}/reviews` - Product reviews
- `GET /policies` - All policies
- `GET /policies/{type}` - Specific policy
- `GET /pages/{slug}` - CMS page
- `GET /banners/{position}` - Banners
- `GET /orders/{orderNumber}/track` - Track order

---

## 👤 Customer APIs (Auth Required)

### Authentication
- `POST /auth/register` - Register
- `POST /auth/login` - Login
- `POST /auth/logout` - Logout
- `GET /auth/user` - Get user
- `PUT /auth/profile` - Update profile
- `PUT /auth/password` - Change password

### Dashboard
- `GET /dashboard` - Dashboard stats
- `GET /profile` - User profile

### Addresses
- `GET /addresses` - List addresses
- `POST /addresses` - Create address
- `GET /addresses/{id}` - Get address
- `PUT /addresses/{id}` - Update address
- `DELETE /addresses/{id}` - Delete address
- `POST /addresses/{id}/set-default` - Set default

### Wishlist
- `GET /wishlist` - Get wishlist
- `POST /wishlist` - Add to wishlist
- `DELETE /wishlist/{id}` - Remove item
- `DELETE /wishlist/product/{productId}` - Remove by product
- `GET /wishlist/check/{productId}` - Check status
- `POST /wishlist/clear` - Clear wishlist

### Checkout & Orders
- `POST /checkout/shipping-methods` - Get shipping
- `POST /checkout/preview` - Preview order
- `POST /checkout/process` - Place order
- `GET /orders` - Order history
- `GET /orders/{orderNumber}` - Order details
- `POST /orders/{orderNumber}/cancel` - Cancel order
- `GET /orders/{orderNumber}/invoice` - Download invoice

### Reviews
- `GET /reviews/my-reviews` - My reviews
- `POST /reviews` - Submit review
- `POST /reviews/{reviewId}/helpful` - Mark helpful

### Returns
- `GET /returns` - My returns
- `POST /returns` - Create return
- `GET /returns/{id}` - Return details

### Notifications
- `GET /notifications` - All notifications
- `GET /notifications/unread-count` - Unread count
- `POST /notifications/{id}/mark-as-read` - Mark read
- `POST /notifications/mark-all-as-read` - Mark all read
- `DELETE /notifications/{id}` - Delete
- `POST /notifications/clear` - Clear all

---

## 🔧 Admin APIs (Admin Auth Required)

### Dashboard & Reports
- `GET /admin/dashboard` - Dashboard stats
- `GET /admin/reports/sales` - Sales report
- `GET /admin/reports/products` - Products report
- `GET /admin/reports/customers` - Customers report
- `GET /admin/reports/inventory` - Inventory report
- `GET /admin/reports/order-status` - Order status report

### Products
- `GET /admin/products` - List products
- `POST /admin/products` - Create product
- `GET /admin/products/{id}` - Product details
- `PUT /admin/products/{id}` - Update product
- `DELETE /admin/products/{id}` - Delete product
- `POST /admin/products/{id}/variations` - Add variation
- `PUT /admin/products/{productId}/variations/{variationId}` - Update variation
- `DELETE /admin/products/{productId}/variations/{variationId}` - Delete variation

### Categories
- `GET /admin/categories` - List categories
- `GET /admin/categories/tree` - Category tree
- `POST /admin/categories` - Create category
- `GET /admin/categories/{id}` - Category details
- `PUT /admin/categories/{id}` - Update category
- `DELETE /admin/categories/{id}` - Delete category

### Brands
- `GET /admin/brands` - List brands
- `POST /admin/brands` - Create brand
- `GET /admin/brands/{id}` - Brand details
- `PUT /admin/brands/{id}` - Update brand
- `DELETE /admin/brands/{id}` - Delete brand

### Orders
- `GET /admin/orders` - List orders
- `GET /admin/orders/{id}` - Order details
- `PUT /admin/orders/{id}/status` - Update status
- `POST /admin/orders/{id}/cancel` - Cancel order
- `POST /admin/orders/{id}/tracking` - Add tracking
- `PUT /admin/orders/{id}/notes` - Update notes

### POS
- `GET /admin/pos/products/search` - Search products
- `GET /admin/pos/products/sku/{sku}` - Get by SKU
- `POST /admin/pos/calculate` - Calculate totals
- `POST /admin/pos/orders` - Create POS order

### Inventory
- `GET /admin/inventory` - Inventory overview
- `GET /admin/inventory/low-stock` - Low stock
- `GET /admin/inventory/{productId}` - Product inventory
- `POST /admin/inventory/{productId}/adjust` - Adjust stock
- `POST /admin/inventory/bulk-adjust` - Bulk adjust
- `GET /admin/inventory/logs` - Inventory logs

### Flash Deals
- `GET /admin/flash-deals` - List deals
- `POST /admin/flash-deals` - Create deal
- `GET /admin/flash-deals/{id}` - Deal details
- `PUT /admin/flash-deals/{id}` - Update deal
- `DELETE /admin/flash-deals/{id}` - Delete deal
- `POST /admin/flash-deals/{id}/toggle` - Toggle status
- `GET /admin/flash-deals/{id}/statistics` - Deal stats

### Promotions
- `GET /admin/promotions` - List promotions
- `POST /admin/promotions` - Create promotion
- `GET /admin/promotions/{id}` - Promotion details
- `PUT /admin/promotions/{id}` - Update promotion
- `DELETE /admin/promotions/{id}` - Delete promotion
- `POST /admin/promotions/{id}/toggle` - Toggle status

### Coupons
- `GET /admin/coupons` - List coupons
- `POST /admin/coupons` - Create coupon
- `GET /admin/coupons/{id}` - Coupon details
- `PUT /admin/coupons/{id}` - Update coupon
- `DELETE /admin/coupons/{id}` - Delete coupon
- `GET /admin/coupons/{id}/usage` - Usage history

### Content (CMS)
- `GET /admin/content/pages` - List pages
- `POST /admin/content/pages` - Create page
- `GET /admin/content/pages/{id}` - Page details
- `PUT /admin/content/pages/{id}` - Update page
- `DELETE /admin/content/pages/{id}` - Delete page
- `GET /admin/content/policies` - List policies
- `POST /admin/content/policies` - Create policy
- `GET /admin/content/policies/{id}` - Policy details
- `PUT /admin/content/policies/{id}` - Update policy
- `GET /admin/content/banners` - List banners
- `POST /admin/content/banners` - Create banner
- `PUT /admin/content/banners/{id}` - Update banner
- `DELETE /admin/content/banners/{id}` - Delete banner

### Users
- `GET /admin/users` - List users
- `POST /admin/users` - Create user
- `GET /admin/users/{id}` - User details
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete user
- `POST /admin/users/{id}/toggle-status` - Toggle status

### Reviews
- `GET /admin/reviews` - List reviews
- `GET /admin/reviews/statistics` - Review stats
- `GET /admin/reviews/{id}` - Review details
- `POST /admin/reviews/{id}/approve` - Approve
- `POST /admin/reviews/{id}/reject` - Reject
- `POST /admin/reviews/{id}/respond` - Respond
- `DELETE /admin/reviews/{id}` - Delete

### Returns
- `GET /admin/returns` - List returns
- `GET /admin/returns/{id}` - Return details
- `POST /admin/returns/{id}/approve` - Approve
- `POST /admin/returns/{id}/reject` - Reject
- `POST /admin/returns/{id}/process` - Process

### Refunds
- `GET /admin/refunds` - List refunds
- `POST /admin/refunds` - Create refund
- `GET /admin/refunds/{id}` - Refund details
- `POST /admin/refunds/{id}/process` - Process
- `POST /admin/refunds/{id}/cancel` - Cancel

### Shipping
- `GET /admin/shipping-rates` - List rates
- `POST /admin/shipping-rates` - Create rate
- `GET /admin/shipping-rates/{id}` - Rate details
- `PUT /admin/shipping-rates/{id}` - Update rate
- `DELETE /admin/shipping-rates/{id}` - Delete rate
- `GET /admin/shipping-classes` - List classes
- `POST /admin/shipping-classes` - Create class
- `PUT /admin/shipping-classes/{id}` - Update class
- `DELETE /admin/shipping-classes/{id}` - Delete class

### Galleries
- `GET /admin/galleries` - List galleries
- `POST /admin/galleries` - Create gallery
- `GET /admin/galleries/{id}` - Gallery details
- `PUT /admin/galleries/{id}` - Update gallery
- `DELETE /admin/galleries/{id}` - Delete gallery
- `POST /admin/galleries/{id}/images` - Add image
- `PUT /admin/galleries/{galleryId}/images/{imageId}` - Update image
- `DELETE /admin/galleries/{galleryId}/images/{imageId}` - Delete image

### Campaigns
- `GET /admin/campaigns` - List campaigns
- `POST /admin/campaigns` - Create campaign
- `GET /admin/campaigns/{id}` - Campaign details
- `PUT /admin/campaigns/{id}` - Update campaign
- `DELETE /admin/campaigns/{id}` - Delete campaign
- `POST /admin/campaigns/{id}/send` - Send campaign
- `GET /admin/campaigns/{id}/statistics` - Campaign stats
- `POST /admin/campaigns/preview-recipients` - Preview recipients

---

## 📊 Total Endpoints: 197

- **Public:** 20 endpoints
- **Customer:** 40 endpoints
- **Admin:** 137 endpoints

---

## 🔗 Full Documentation
See `COMPLETE_API_DOCUMENTATION.md` for detailed information including:
- Request/response examples
- Query parameters
- Validation rules
- Error handling
- SDK examples
- Best practices

---

**Status:** ✅ Production Ready  
**Version:** 1.0.0  
**Last Updated:** March 2, 2026
