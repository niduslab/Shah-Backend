# Product Import Fields Reference

**Complete list of all CSV fields for bulk product import**

**Last Updated:** April 21, 2026  
**Total Fields:** 53 columns (35 basic + 6 images + 12 variations)

---

## Quick Overview

### Required Fields (3)
1. `name` - Product name
2. `category_id` - Category ID number
3. `price` - Selling price

### Optional Fields (50)
- 32 basic product fields
- 6 image fields (3 images × 2 fields each)
- 12 variation fields (3 variations × 4 fields each)

---

## Complete Field List

### BASIC PRODUCT FIELDS (35 fields)

#### 1. **name** ⭐ REQUIRED
- **Type:** Text (max 255 characters)
- **Example:** `Treadmill Pro 3000`
- **Description:** Product name/title

#### 2. **sku**
- **Type:** Text (max 100 characters)
- **Example:** `TM-3000`
- **Description:** Stock Keeping Unit (unique product code)
- **Note:** Leave empty to auto-generate

#### 3. **category_id** ⭐ REQUIRED
- **Type:** Number
- **Example:** `5`
- **Description:** Product category ID (must exist in system)

#### 4. **brand_id**
- **Type:** Number
- **Example:** `3`
- **Description:** Brand ID (must exist in system)

#### 5. **model_id**
- **Type:** Number
- **Example:** `12`
- **Description:** Product model ID

#### 6. **shipping_class_id**
- **Type:** Number
- **Example:** `2`
- **Description:** Shipping class ID for special shipping rules

#### 7. **short_description**
- **Type:** Text
- **Example:** `High-performance treadmill for home use`
- **Description:** Brief product summary (shown in listings)

#### 8. **description**
- **Type:** Long text
- **Example:** `Professional grade treadmill with advanced features...`
- **Description:** Full product description

#### 9. **price** ⭐ REQUIRED
- **Type:** Decimal (10,2)
- **Example:** `1299.99`
- **Description:** Selling price (no currency symbol)

#### 10. **compare_price**
- **Type:** Decimal (10,2)
- **Example:** `1599.99`
- **Description:** Original price before discount

#### 11. **cost_price**
- **Type:** Decimal (10,2)
- **Example:** `899.99`
- **Description:** Your cost/wholesale price (internal use)

#### 12. **quantity**
- **Type:** Integer
- **Example:** `25`
- **Default:** `0`
- **Description:** Stock quantity available

#### 13. **low_stock_threshold**
- **Type:** Integer
- **Example:** `5`
- **Default:** `5`
- **Description:** Alert level for low stock

#### 14. **weight**
- **Type:** Decimal (8,2)
- **Example:** `85.5`
- **Description:** Product weight

#### 15. **weight_unit**
- **Type:** Enum (g, kg, lb)
- **Example:** `kg`
- **Default:** `kg`
- **Description:** Unit of weight measurement

#### 16. **length**
- **Type:** Decimal (8,2)
- **Example:** `180`
- **Description:** Product length (cm)

#### 17. **width**
- **Type:** Decimal (8,2)
- **Example:** `80`
- **Description:** Product width (cm)

#### 18. **height**
- **Type:** Decimal (8,2)
- **Example:** `150`
- **Description:** Product height (cm)

#### 19. **shipping_type**
- **Type:** Enum (default, free, fixed, per_item)
- **Example:** `default`
- **Default:** `default`
- **Description:** Shipping calculation method

#### 20. **shipping_cost**
- **Type:** Decimal (10,2)
- **Example:** `50.00`
- **Description:** Custom shipping cost

#### 21. **requires_shipping**
- **Type:** Boolean (1 or 0)
- **Example:** `1`
- **Default:** `1`
- **Description:** Does product need physical shipping?

#### 22. **separate_shipping**
- **Type:** Boolean (1 or 0)
- **Example:** `0`
- **Default:** `0`
- **Description:** Ships separately from other items?

#### 23. **shipping_notes**
- **Type:** Text (max 500 characters)
- **Example:** `Fragile - Handle with care`
- **Description:** Special shipping instructions

#### 24. **is_featured**
- **Type:** Boolean (1 or 0)
- **Example:** `1`
- **Default:** `0`
- **Description:** Show in featured products section?

#### 25. **is_trending**
- **Type:** Boolean (1 or 0)
- **Example:** `0`
- **Default:** `0`
- **Description:** Show in trending products section?

#### 26. **kinomap**
- **Type:** Boolean (1 or 0)
- **Example:** `0`
- **Default:** `0`
- **Description:** Kinomap compatible product?

#### 27. **status**
- **Type:** Enum (active, inactive, draft)
- **Example:** `active`
- **Default:** `draft`
- **Description:** Product visibility status

#### 28. **meta_title**
- **Type:** Text (max 255 characters)
- **Example:** `Treadmill Pro 3000 - Professional Grade`
- **Description:** SEO page title

#### 29. **meta_description**
- **Type:** Text
- **Example:** `Buy the best treadmill for home gym`
- **Description:** SEO description (shown in search results)

#### 30. **meta_keywords**
- **Type:** Text (max 255 characters)
- **Example:** `treadmill,fitness,gym,exercise`
- **Description:** SEO keywords (comma-separated)

#### 31. **is_preorder**
- **Type:** Boolean (1 or 0)
- **Example:** `0`
- **Default:** `0`
- **Description:** Is this a preorder product?

#### 32. **preorder_release_date**
- **Type:** Date (YYYY-MM-DD)
- **Example:** `2026-06-01`
- **Description:** When product will be available

#### 33. **preorder_limit**
- **Type:** Integer
- **Example:** `100`
- **Description:** Maximum preorder quantity

#### 34. **preorder_deposit_amount**
- **Type:** Decimal (10,2)
- **Example:** `200.00`
- **Description:** Deposit amount required

#### 35. **preorder_deposit_type**
- **Type:** Enum (percentage, fixed)
- **Example:** `fixed`
- **Description:** Deposit calculation method

---

### IMAGE FIELDS (6 fields = 3 images × 2 fields)

#### 36. **image_1**
- **Type:** Text (URL)
- **Example:** `https://cdn.example.com/products/tm3000-front.jpg`
- **Description:** First product image URL (becomes primary)

#### 37. **image_1_alt**
- **Type:** Text (max 255 characters)
- **Example:** `Treadmill Pro 3000 front view`
- **Description:** Alt text for first image (SEO/accessibility)

#### 38. **image_2**
- **Type:** Text (URL)
- **Example:** `https://cdn.example.com/products/tm3000-side.jpg`
- **Description:** Second product image URL

#### 39. **image_2_alt**
- **Type:** Text (max 255 characters)
- **Example:** `Treadmill Pro 3000 side view`
- **Description:** Alt text for second image

#### 40. **image_3**
- **Type:** Text (URL)
- **Example:** `https://cdn.example.com/products/tm3000-display.jpg`
- **Description:** Third product image URL

#### 41. **image_3_alt**
- **Type:** Text (max 255 characters)
- **Example:** `Treadmill Pro 3000 display panel`
- **Description:** Alt text for third image

---

### VARIATION FIELDS (12 fields = 3 variations × 4 fields)

#### Variation 1 (4 fields)

#### 42. **variation_1_sku**
- **Type:** Text (max 100 characters)
- **Example:** `TM-3000-V1`
- **Description:** Unique SKU for variation 1
- **Note:** Leave empty to auto-generate

#### 43. **variation_1_attributes**
- **Type:** Text (format: `attribute:value|attribute:value`)
- **Example:** `color:Black|warranty:2 Years`
- **Description:** Variation attributes (what makes it different)

#### 44. **variation_1_price**
- **Type:** Decimal (10,2)
- **Example:** `1299.99`
- **Description:** Price for variation 1 (leave empty to use main price)

#### 45. **variation_1_quantity**
- **Type:** Integer
- **Example:** `15`
- **Default:** `0`
- **Description:** Stock quantity for variation 1

#### Variation 2 (4 fields)

#### 46. **variation_2_sku**
- **Type:** Text (max 100 characters)
- **Example:** `TM-3000-V2`
- **Description:** Unique SKU for variation 2

#### 47. **variation_2_attributes**
- **Type:** Text (format: `attribute:value|attribute:value`)
- **Example:** `color:Silver|warranty:2 Years`
- **Description:** Variation attributes

#### 48. **variation_2_price**
- **Type:** Decimal (10,2)
- **Example:** `1349.99`
- **Description:** Price for variation 2

#### 49. **variation_2_quantity**
- **Type:** Integer
- **Example:** `10`
- **Default:** `0`
- **Description:** Stock quantity for variation 2

#### Variation 3 (4 fields)

#### 50. **variation_3_sku**
- **Type:** Text (max 100 characters)
- **Example:** `TM-3000-V3`
- **Description:** Unique SKU for variation 3

#### 51. **variation_3_attributes**
- **Type:** Text (format: `attribute:value|attribute:value`)
- **Example:** `color:White|warranty:3 Years`
- **Description:** Variation attributes

#### 52. **variation_3_price**
- **Type:** Decimal (10,2)
- **Example:** `1399.99`
- **Description:** Price for variation 3

#### 53. **variation_3_quantity**
- **Type:** Integer
- **Example:** `8`
- **Default:** `0`
- **Description:** Stock quantity for variation 3

---

## CSV Header Row

Copy this exact header row for your CSV file:

```csv
name,sku,category_id,brand_id,model_id,shipping_class_id,short_description,description,price,compare_price,cost_price,quantity,low_stock_threshold,weight,weight_unit,length,width,height,shipping_type,shipping_cost,requires_shipping,separate_shipping,shipping_notes,is_featured,is_trending,kinomap,status,meta_title,meta_description,meta_keywords,is_preorder,preorder_release_date,preorder_limit,preorder_deposit_amount,preorder_deposit_type,image_1,image_1_alt,image_2,image_2_alt,image_3,image_3_alt,variation_1_sku,variation_1_attributes,variation_1_price,variation_1_quantity,variation_2_sku,variation_2_attributes,variation_2_price,variation_2_quantity,variation_3_sku,variation_3_attributes,variation_3_price,variation_3_quantity
```

---

## Example CSV Row

### Simple Product (No Variations)

```csv
"Yoga Mat Premium","YM-001",8,7,,,,"Premium yoga mat for all levels","High-quality non-slip yoga mat with excellent cushioning. Perfect for yoga, pilates, and floor exercises.",49.99,69.99,25.00,150,10,2.5,"kg",183,61,0.6,"default",,1,0,"",1,0,0,"active","Premium Yoga Mat - Non-Slip | YourStore","Buy premium yoga mat with non-slip surface. Perfect for yoga and pilates.","yoga mat,exercise mat,non-slip mat,fitness",0,,,,"","https://cdn.example.com/yoga-mat-1.jpg","Premium yoga mat top view","https://cdn.example.com/yoga-mat-2.jpg","Yoga mat rolled up","https://cdn.example.com/yoga-mat-3.jpg","Person using yoga mat",,,,,,,,,,,,
```

### Product with Variations

```csv
"Treadmill Pro 3000","TM-3000",5,3,12,,"High-performance treadmill","Professional grade treadmill with advanced features including speed control, incline adjustment, and heart rate monitoring.",1299.99,1599.99,899.99,0,5,85.5,"kg",180,80,150,"default",,1,0,"Requires 2-person delivery",1,0,0,"active","Treadmill Pro 3000 - Professional Grade | YourStore","Buy the best treadmill for home gym. Professional features at affordable price.","treadmill,fitness,gym,exercise,cardio",0,,,,"","https://cdn.example.com/tm3000-1.jpg","Treadmill Pro 3000 front view","https://cdn.example.com/tm3000-2.jpg","Treadmill Pro 3000 side view","https://cdn.example.com/tm3000-3.jpg","Treadmill display panel closeup","TM-3000-V1","color:Black|warranty:2 Years",1299.99,15,"TM-3000-V2","color:Silver|warranty:2 Years",1349.99,10,"TM-3000-V3","color:White|warranty:3 Years",1399.99,8
```

---

## Field Categories Summary

### By Category

| Category | Fields | Count |
|----------|--------|-------|
| **Required** | name, category_id, price | 3 |
| **Identification** | sku, brand_id, model_id, shipping_class_id | 4 |
| **Content** | short_description, description | 2 |
| **Pricing** | compare_price, cost_price | 2 |
| **Inventory** | quantity, low_stock_threshold | 2 |
| **Dimensions** | weight, weight_unit, length, width, height | 5 |
| **Shipping** | shipping_type, shipping_cost, requires_shipping, separate_shipping, shipping_notes | 5 |
| **Flags** | is_featured, is_trending, kinomap, status | 4 |
| **SEO** | meta_title, meta_description, meta_keywords | 3 |
| **Preorder** | is_preorder, preorder_release_date, preorder_limit, preorder_deposit_amount, preorder_deposit_type | 5 |
| **Images** | image_1, image_1_alt, image_2, image_2_alt, image_3, image_3_alt | 6 |
| **Variations** | variation_1-3 (sku, attributes, price, quantity) | 12 |
| **TOTAL** | | **53** |

---

## Data Type Reference

### Text Fields (20)
- name, sku, short_description, description, shipping_notes
- meta_title, meta_description, meta_keywords
- image_1, image_1_alt, image_2, image_2_alt, image_3, image_3_alt
- variation_1_sku, variation_1_attributes, variation_2_sku, variation_2_attributes, variation_3_sku, variation_3_attributes

### Number Fields (18)
- category_id, brand_id, model_id, shipping_class_id
- price, compare_price, cost_price, shipping_cost
- quantity, low_stock_threshold, preorder_limit, preorder_deposit_amount
- weight, length, width, height
- variation_1_quantity, variation_2_quantity, variation_3_quantity

### Boolean Fields (6)
- requires_shipping, separate_shipping
- is_featured, is_trending, kinomap, is_preorder

### Enum Fields (4)
- weight_unit (g, kg, lb)
- shipping_type (default, free, fixed, per_item)
- status (active, inactive, draft)
- preorder_deposit_type (percentage, fixed)

### Date Fields (1)
- preorder_release_date (YYYY-MM-DD)

### Decimal Fields (8)
- price, compare_price, cost_price, shipping_cost
- weight, length, width, height
- variation_1_price, variation_2_price, variation_3_price, preorder_deposit_amount

---

## Important Notes

### Required Fields
Only 3 fields are absolutely required:
1. **name** - Product name
2. **category_id** - Must exist in system
3. **price** - Must be > 0

### Auto-Generated Fields
These fields are auto-generated if left empty:
- **sku** - Format: `SS-XXXXXXXX`
- **variation_1_sku, variation_2_sku, variation_3_sku** - Format: `{PRODUCT_SKU}-V{N}`

### Boolean Format
Use `1` for true/yes, `0` for false/no:
- ✅ Correct: `1` or `0`
- ❌ Wrong: `true`, `false`, `yes`, `no`, `TRUE`, `FALSE`

### Decimal Format
Use decimal point (not comma):
- ✅ Correct: `1299.99`
- ❌ Wrong: `1299,99`, `$1299.99`, `1,299.99`

### Image URLs
Must be full HTTP/HTTPS URLs:
- ✅ Correct: `https://cdn.example.com/image.jpg`
- ❌ Wrong: `C:\images\image.jpg`, `/images/image.jpg`, `image.jpg`

### Variation Attributes Format
Use `attribute:value|attribute:value`:
- ✅ Correct: `color:Red|size:Large`
- ❌ Wrong: `Red, Large`, `color=Red,size=Large`

### Date Format
Use YYYY-MM-DD:
- ✅ Correct: `2026-06-01`
- ❌ Wrong: `06/01/2026`, `01-06-2026`, `June 1, 2026`

---

## Expandable Fields

The template shows 3 images and 3 variations, but the system supports up to:
- **10 images** (image_1 to image_10, with alt text for each)
- **10 variations** (variation_1 to variation_10, with 4 fields each)

To use more, simply add the additional columns following the same naming pattern.

---

## Download Template

**API Endpoint:**
```
GET /api/admin/products/import/template
```

**Using cURL:**
```bash
curl -X GET "http://localhost:8000/api/admin/products/import/template" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output template.csv
```

---

## Quick Reference

### Minimum Valid CSV (Required Fields Only)
```csv
name,category_id,price
"Test Product",5,99.99
```

### Simple Product (Common Fields)
```csv
name,sku,category_id,brand_id,price,quantity,status,image_1
"Product Name","PROD-001",5,3,99.99,100,"active","https://cdn.example.com/image.jpg"
```

### Product with Variations (Common Fields)
```csv
name,category_id,price,quantity,variation_1_attributes,variation_1_quantity,variation_2_attributes,variation_2_quantity
"T-Shirt",3,29.99,0,"size:Small|color:Blue",50,"size:Large|color:Blue",75
```

---

**Document Version:** 1.0  
**Last Updated:** April 21, 2026  
**Total Fields:** 53 columns

