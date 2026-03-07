# Coupon Flexible Applies To - Implementation Guide

## Overview
The coupon system now supports flexible combinations of products, brands, and categories. Admins can select any combination they want!

## Database Migration Required

Run this migration first:
```bash
php artisan migrate
```

This changes the `applies_to` column from enum to string(100) to support combinations.

## How It Works

### Automatic Detection
The system automatically determines `applies_to` based on which IDs you provide:

- **No IDs provided** → `applies_to: "all"`
- **Only product_ids** → `applies_to: "products"`
- **Only brand_ids** → `applies_to: "brands"`
- **Only category_ids** → `applies_to: "categories"`
- **Products + Brands** → `applies_to: "products,brands"`
- **Products + Categories** → `applies_to: "products,categories"`
- **Brands + Categories** → `applies_to: "brands,categories"`
- **All three** → `applies_to: "products,brands,categories"`

## API Examples

### 1. Apply to All Products
```json
POST /api/admin/coupons
{
  "code": "SAVE20",
  "name": "Save 20% on Everything",
  "discount_type": "percentage",
  "discount_value": 20,
  "min_order_amount": 1000,
  "is_active": true
}
```
Result: `applies_to: "all"`

### 2. Apply to Specific Categories Only
```json
POST /api/admin/coupons
{
  "code": "SPORTS20",
  "name": "20% Off Sports Equipment",
  "discount_type": "percentage",
  "discount_value": 20,
  "category_ids": [1, 2, 29],
  "is_active": true
}
```
Result: `applies_to: "categories"`

### 3. Apply to Products + Brands
```json
POST /api/admin/coupons
{
  "code": "COMBO50",
  "name": "Special Combo Deal",
  "discount_type": "fixed_amount",
  "discount_value": 500,
  "product_ids": [10, 20, 30],
  "brand_ids": [5, 6],
  "is_active": true
}
```
Result: `applies_to: "products,brands"`

### 4. Apply to All Three Types
```json
POST /api/admin/coupons
{
  "code": "MEGA100",
  "name": "Mega Sale",
  "discount_type": "percentage",
  "discount_value": 15,
  "product_ids": [1, 2, 3],
  "brand_ids": [4, 5],
  "category_ids": [10, 11, 12],
  "is_active": true
}
```
Result: `applies_to: "products,brands,categories"`

## Validation Logic

The coupon applies to a cart item if:
- `applies_to` is `"all"`, OR
- Product ID matches any in `product_ids`, OR
- Brand ID matches any in `brand_ids`, OR
- Category ID matches any in `category_ids`

It's an **OR** relationship - the product just needs to match ONE of the criteria.

## Model Helper Methods

```php
$coupon->appliesToProducts();    // Check if applies to products
$coupon->appliesToBrands();      // Check if applies to brands
$coupon->appliesToCategories();  // Check if applies to categories
$coupon->appliesToAll();         // Check if applies to all
$coupon->applies_to_types;       // Get array of types
```

## Request Format (Fixed)

```json
{
  "code": "SAVE20",
  "name": "Save 20% Discount",
  "description": "Limited time offer",
  "discount_type": "percentage",
  "discount_value": 15,
  "min_order_amount": 5000,
  "max_discount_amount": 1000,
  "usage_limit": 2000,
  "once_per_customer": true,
  "starts_at": "2026-03-06T20:18",
  "expires_at": "2026-03-10T20:18",
  "is_active": true,
  "category_ids": [1, 2, 29]
}
```

## Key Changes Made

1. ✅ Removed `applies_to` from validation (auto-calculated)
2. ✅ Added `determineAppliesTo()` helper method
3. ✅ Updated CouponService to handle comma-separated values
4. ✅ Added model helper methods
5. ✅ Created migration to change column type
6. ✅ Updated both store() and update() methods

## Benefits

- ✅ More flexible coupon targeting
- ✅ Simpler API (no need to specify applies_to)
- ✅ Supports any combination
- ✅ Backward compatible with existing coupons
- ✅ Automatic validation logic
