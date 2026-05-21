# Product Import User Guide
## Complete Guide for Bulk Product Upload

**Version:** 1.0  
**Last Updated:** April 2026  
**For:** Company Staff & Administrators

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [CSV Template Overview](#csv-template-overview)
3. [Column Descriptions](#column-descriptions)
4. [Step-by-Step Instructions](#step-by-step-instructions)
5. [Examples](#examples)
6. [Common Mistakes](#common-mistakes)
7. [Troubleshooting](#troubleshooting)

---

## Quick Start

### What You Need
- ✅ Admin access to the system
- ✅ Product data in Excel or CSV format
- ✅ Product images uploaded to a CDN or image hosting service
- ✅ Category IDs and Brand IDs from the system

### 5-Minute Quick Start
1. Download the CSV template
2. Fill in your product data
3. Upload the CSV file
4. Monitor the import progress
5. Check for any errors and fix them

---

## CSV Template Overview

### Download Template
**URL:** `GET /api/admin/products/import/template`

The template includes:
- **35 basic product fields** (name, price, description, etc.)
- **3 image slots** (with alt text for each)
- **3 variation slots** (for product variants like color, size)

### Template Structure
```
Basic Info → Pricing → Inventory → Dimensions → Shipping → SEO → Images → Variations
```

---

## Column Descriptions

### 📋 REQUIRED FIELDS (Must Fill)

#### 1. **name**
- **What it is:** Product name/title
- **Format:** Text (max 255 characters)
- **Example:** `"Treadmill Pro 3000"`
- **Tips:** 
  - Use clear, descriptive names
  - Include brand and model if applicable
  - Avoid special characters

#### 2. **category_id**
- **What it is:** Product category ID number
- **Format:** Number
- **Example:** `5`
- **How to find:** 
  - Go to Categories page in admin
  - Note the ID number for each category
  - Use the correct ID for your product
- **Tips:** Category must exist in system before import

#### 3. **price**
- **What it is:** Selling price
- **Format:** Decimal number (no currency symbol)
- **Example:** `1299.99`
- **Tips:**
  - Use decimal point (not comma)
  - Don't include currency symbols ($, €, etc.)
  - Must be greater than 0

---

### 📝 OPTIONAL BASIC FIELDS

#### 4. **sku**
- **What it is:** Stock Keeping Unit (unique product code)
- **Format:** Text (max 100 characters)
- **Example:** `"TM-3000"`
- **Tips:**
  - Leave empty to auto-generate
  - Must be unique across all products
  - Use letters, numbers, hyphens

#### 5. **brand_id**
- **What it is:** Brand ID number
- **Format:** Number
- **Example:** `3`
- **How to find:** Check Brands page in admin
- **Tips:** Leave empty if no brand

#### 6. **model_id**
- **What it is:** Product model ID
- **Format:** Number
- **Example:** `12`
- **Tips:** Leave empty if not applicable

#### 7. **shipping_class_id**
- **What it is:** Shipping class for special shipping rules
- **Format:** Number
- **Example:** `2`
- **Tips:** Leave empty for default shipping

#### 8. **short_description**
- **What it is:** Brief product summary (shown in listings)
- **Format:** Text
- **Example:** `"High-performance treadmill for home use"`
- **Tips:** Keep under 200 characters

#### 9. **description**
- **What it is:** Full product description
- **Format:** Text (can be long)
- **Example:** `"Professional grade treadmill with advanced features..."`
- **Tips:** Include features, benefits, specifications

---

### 💰 PRICING FIELDS

#### 10. **compare_price**
- **What it is:** Original price (before discount)
- **Format:** Decimal number
- **Example:** `1599.99`
- **Tips:** 
  - Should be higher than selling price
  - Shows "Save X%" to customers
  - Leave empty if no discount

#### 11. **cost_price**
- **What it is:** Your cost/wholesale price
- **Format:** Decimal number
- **Example:** `899.99`
- **Tips:** 
  - For internal use only
  - Not shown to customers
  - Used for profit calculations

---

### 📦 INVENTORY FIELDS

#### 12. **quantity**
- **What it is:** Stock quantity available
- **Format:** Whole number
- **Example:** `25`
- **Tips:**
  - Use 0 for out of stock
  - System tracks this automatically after sales
  - **For products WITH variations:** This is the base product quantity (usually set to 0, as variations have their own quantities)
  - **For products WITHOUT variations:** This is the actual stock quantity
  - Inventory is properly updated when:
    - Orders are placed (quantity decreases)
    - Returns are processed (quantity increases)
    - Manual adjustments are made via Inventory Management
  - All inventory changes are logged in `inventory_logs` table with reason and timestamp

#### 13. **low_stock_threshold**
- **What it is:** Alert level for low stock
- **Format:** Whole number
- **Example:** `5`
- **Default:** `5`
- **Tips:** 
  - Get notified when stock falls below this number
  - System sends low stock alerts automatically
  - Applies to both simple products and variations

---

### 📏 DIMENSIONS & WEIGHT

#### 14. **weight**
- **What it is:** Product weight
- **Format:** Decimal number
- **Example:** `85.5`
- **Tips:** Used for shipping calculations

#### 15. **weight_unit**
- **What it is:** Unit of weight measurement
- **Format:** One of: `g`, `kg`, `lb`
- **Example:** `kg`
- **Default:** `kg`

#### 16. **length**
- **What it is:** Product length (cm)
- **Format:** Decimal number
- **Example:** `180`

#### 17. **width**
- **What it is:** Product width (cm)
- **Format:** Decimal number
- **Example:** `80`

#### 18. **height**
- **What it is:** Product height (cm)
- **Format:** Decimal number
- **Example:** `150`

---

### 🚚 SHIPPING FIELDS

#### 19. **shipping_type**
- **What it is:** Shipping calculation method
- **Format:** One of: `default`, `free`, `fixed`, `per_item`
- **Example:** `default`
- **Options:**
  - `default` - Use standard shipping rates
  - `free` - Free shipping for this product
  - `fixed` - Fixed shipping cost (set in shipping_cost)
  - `per_item` - Multiply shipping_cost by quantity

#### 20. **shipping_cost**
- **What it is:** Custom shipping cost
- **Format:** Decimal number
- **Example:** `50.00`
- **Tips:** Only used if shipping_type is `fixed` or `per_item`

#### 21. **requires_shipping**
- **What it is:** Does product need physical shipping?
- **Format:** `1` (yes) or `0` (no)
- **Example:** `1`
- **Default:** `1`
- **Tips:** Use `0` for digital products

#### 22. **separate_shipping**
- **What it is:** Ships separately from other items?
- **Format:** `1` (yes) or `0` (no)
- **Example:** `0`
- **Default:** `0`
- **Tips:** Use `1` for oversized items

#### 23. **shipping_notes**
- **What it is:** Special shipping instructions
- **Format:** Text (max 500 characters)
- **Example:** `"Fragile - Handle with care"`

---

### 🏷️ PRODUCT FLAGS

#### 24. **is_featured**
- **What it is:** Show in featured products section?
- **Format:** `1` (yes) or `0` (no)
- **Example:** `1`
- **Default:** `0`

#### 25. **is_trending**
- **What it is:** Show in trending products section?
- **Format:** `1` (yes) or `0` (no)
- **Example:** `0`
- **Default:** `0`

#### 26. **kinomap**
- **What it is:** Kinomap compatible product?
- **Format:** `1` (yes) or `0` (no)
- **Example:** `0`
- **Default:** `0`

#### 27. **status**
- **What it is:** Product visibility status
- **Format:** One of: `active`, `inactive`, `draft`
- **Example:** `active`
- **Default:** `draft`
- **Options:**
  - `active` - Visible to customers
  - `inactive` - Hidden from customers
  - `draft` - Work in progress

---

### 🔍 SEO FIELDS

#### 28. **meta_title**
- **What it is:** SEO page title
- **Format:** Text (max 255 characters)
- **Example:** `"Treadmill Pro 3000 - Professional Grade | YourStore"`
- **Tips:** Include keywords customers search for

#### 29. **meta_description**
- **What it is:** SEO description (shown in search results)
- **Format:** Text
- **Example:** `"Buy the best treadmill for home gym. Professional features at affordable price."`
- **Tips:** 150-160 characters optimal

#### 30. **meta_keywords**
- **What it is:** SEO keywords (comma-separated)
- **Format:** Text (max 255 characters)
- **Example:** `"treadmill,fitness,gym,exercise,cardio"`
- **Tips:** Use relevant search terms

---

### 📅 PREORDER FIELDS

#### 31. **is_preorder**
- **What it is:** Is this a preorder product?
- **Format:** `1` (yes) or `0` (no)
- **Example:** `0`
- **Default:** `0`

#### 32. **preorder_release_date**
- **What it is:** When product will be available
- **Format:** Date (YYYY-MM-DD)
- **Example:** `2026-06-01`
- **Tips:** Only if is_preorder = 1

#### 33. **preorder_limit**
- **What it is:** Maximum preorder quantity
- **Format:** Whole number
- **Example:** `100`

#### 34. **preorder_deposit_amount**
- **What it is:** Deposit amount required
- **Format:** Decimal number
- **Example:** `200.00`

#### 35. **preorder_deposit_type**
- **What it is:** Deposit calculation method
- **Format:** One of: `percentage`, `fixed`
- **Example:** `fixed`
- **Options:**
  - `percentage` - Percentage of product price
  - `fixed` - Fixed amount

---

### 🖼️ IMAGE FIELDS (3 Images)

#### 36-37. **image_1** & **image_1_alt**
- **What it is:** First product image (primary)
- **Format:** 
  - `image_1`: Full URL to image
  - `image_1_alt`: Alt text for accessibility
- **Example:** 
  - `image_1`: `"https://cdn.yourstore.com/products/tm3000-front.jpg"`
  - `image_1_alt`: `"Treadmill Pro 3000 front view"`
- **Tips:**
  - First image is automatically set as primary
  - Use high-quality images (800x600 or larger)
  - Images must be publicly accessible URLs
  - Supported formats: JPG, PNG, GIF, WebP

#### 38-39. **image_2** & **image_2_alt**
- **What it is:** Second product image
- **Format:** Same as image_1
- **Example:**
  - `image_2`: `"https://cdn.yourstore.com/products/tm3000-side.jpg"`
  - `image_2_alt`: `"Treadmill Pro 3000 side view"`

#### 40-41. **image_3** & **image_3_alt**
- **What it is:** Third product image
- **Format:** Same as image_1
- **Example:**
  - `image_3`: `"https://cdn.yourstore.com/products/tm3000-display.jpg"`
  - `image_3_alt`: `"Treadmill Pro 3000 display panel"`

**Image Tips:**
- ✅ Upload images to CDN or image hosting first
- ✅ Use descriptive alt text for SEO
- ✅ Keep image file sizes under 500KB
- ✅ Use consistent image dimensions
- ❌ Don't use local file paths (C:\images\...)
- ❌ Don't use password-protected URLs

---

### 🎨 VARIATION FIELDS (3 Variations)

Variations are different versions of the same product (e.g., different colors, sizes, warranties).

#### 🔄 How Variation Reuse Works

**IMPORTANT:** The system intelligently reuses variation types and options across all products for consistency and efficiency.

**Variation Types (Color, Size, Material, etc.):**
- ✅ Created once, reused across ALL products
- ✅ Case-insensitive matching (color = Color = COLOR)
- ✅ Stored in `variations` table

**Variation Options (Red, Blue, Small, Large, etc.):**
- ✅ Created once per variation type, reused across ALL products
- ✅ Case-sensitive for values (Red ≠ red)
- ✅ Stored in `variation_options` table

**Example:**
```
Product 1: T-Shirt with "color:Red"
Product 2: Pants with "color:Red"
Product 3: Shoes with "color:Red"

Result: All three products use the SAME "Color" type and SAME "Red" option
Database: 1 variation type, 1 variation option, linked to 3 products
```

**Benefits:**
- ✅ Consistent data across your catalog
- ✅ Easy filtering (find all "Red" products)
- ✅ Efficient database usage
- ✅ No duplicate variation data

#### 42-45. **Variation 1 Fields**

**variation_1_sku**
- **What it is:** Unique SKU for this variation
- **Format:** Text (max 100 characters)
- **Example:** `"TM-3000-V1"`
- **Tips:** 
  - Leave empty to auto-generate (recommended)
  - If duplicate exists, system auto-generates unique SKU automatically
  - ✅ **No import failures due to duplicate variation SKUs**
  - System logs when auto-generation occurs
  - Format: `{PRODUCT_SKU}-V{NUMBER}` (e.g., TM-3000-V1)

**variation_1_attributes**
- **What it is:** Variation attributes (what makes it different)
- **Format:** `attribute:value|attribute:value`
- **Example:** `"color:Black|warranty:2 Years"`
- **Tips:**
  - Use pipe `|` to separate multiple attributes
  - Use colon `:` between attribute name and value
  - Common attributes: color, size, material, warranty, style
  - If value contains `|`, use `~~` as separator instead
  - **Variation types are reused:** If "Color" exists, it's reused across products
  - **Variation options are reused:** If "Red" exists, it's reused across products
  - Case-insensitive for attribute names (color = Color = COLOR)
  - Case-sensitive for values (Red ≠ red) - use consistent capitalization
  - **Best Practice:** Use consistent attribute names across all products

**variation_1_price**
- **What it is:** Price for this variation
- **Format:** Decimal number
- **Example:** `1299.99`
- **Tips:** 
  - Leave empty to use main product price
  - Each variation can have different pricing
  - Useful for premium options (e.g., extended warranty costs more)

**variation_1_quantity**
- **What it is:** Stock quantity for this variation
- **Format:** Whole number
- **Example:** `15`
- **Default:** `0`
- **Tips:**
  - Each variation has independent inventory tracking
  - When product has variations, total stock = sum of all variation quantities
  - Inventory automatically decreases on orders
  - Inventory automatically increases on returns
  - All changes logged in `inventory_logs` table

#### 46-49. **Variation 2 Fields**
Same format as Variation 1

**Example:**
- `variation_2_sku`: `"TM-3000-V2"`
- `variation_2_attributes`: `"color:Silver|warranty:2 Years"`
- `variation_2_price`: `1349.99`
- `variation_2_quantity`: `10`

#### 50-53. **Variation 3 Fields**
Same format as Variation 1

**Example:**
- `variation_3_sku`: `"TM-3000-V3"`
- `variation_3_attributes`: `"color:White|warranty:3 Years"`
- `variation_3_price`: `1399.99`
- `variation_3_quantity`: `8`

**Variation Tips:**
- ✅ First variation is automatically set as default
- ✅ Use consistent attribute names across products (e.g., always use "color" not "colour")
- ✅ Use consistent capitalization for values (e.g., always "Red" not "red")
- ✅ Leave all variation fields empty for simple products
- ✅ Each variation can have different price and stock
- ✅ Variation types and options are automatically reused across products
- ✅ Leave variation SKUs empty to auto-generate (recommended)
- ✅ System handles duplicate variation SKUs automatically
- ❌ Don't use different spellings for same attribute (color vs colour)
- ❌ Don't leave attributes empty if using variations
- ❌ Don't worry about duplicate variation SKUs - system auto-generates unique ones

**Variation Reuse Example:**
```csv
Product 1: T-Shirt, variation_1_attributes: "color:Red|size:Large"
Product 2: Hoodie, variation_1_attributes: "color:Red|size:Large"
Product 3: Pants, variation_1_attributes: "color:Red|size:Medium"

Result:
- "Color" variation type: Created once, used by all 3 products
- "Size" variation type: Created once, used by all 3 products
- "Red" option: Created once, used by products 1, 2, and 3
- "Large" option: Created once, used by products 1 and 2
- "Medium" option: Created once, used by product 3

Database efficiency: 2 variation types, 3 options, linked to 3 products
```

---

## Step-by-Step Instructions

### Step 1: Prepare Your Data

#### A. Get Required IDs
1. **Category IDs:**
   - Go to Admin → Categories
   - Note the ID number for each category
   - Example: Electronics = 5, Fitness = 8

2. **Brand IDs:**
   - Go to Admin → Brands
   - Note the ID number for each brand
   - Example: Nike = 3, Adidas = 7

#### B. Prepare Images
1. Upload all product images to your CDN or image hosting
2. Get the full URL for each image
3. Example: `https://cdn.yourstore.com/products/product-123.jpg`

#### C. Organize Product Data
Create a spreadsheet with:
- Product names
- Prices
- Descriptions
- Stock quantities
- Image URLs
- Variation details (if applicable)

---

### Step 2: Download Template

1. Log in to admin panel
2. Go to Products → Import
3. Click "Download Template"
4. Open the CSV file in Excel or Google Sheets

---

### Step 3: Fill in the Template

#### For Simple Products (No Variations)
1. Fill in required fields: name, category_id, price
2. Fill in optional fields as needed
3. Add image URLs (at least image_1)
4. Leave variation fields empty
5. Set status to `active` when ready

#### For Products with Variations
1. Fill in main product details
2. Fill in variation_1_sku, variation_1_attributes, etc.
3. Add more variations if needed (up to 3)
4. Each variation can have different price and quantity

**Example Row:**
```csv
"Treadmill Pro 3000","TM-3000",5,3,,"","High-performance treadmill","Professional grade...",1299.99,1599.99,899.99,25,5,85.5,"kg",180,80,150,"default",,1,0,"",1,0,0,"active","Treadmill Pro 3000","Buy the best treadmill","treadmill,fitness",0,,,,"","https://cdn.example.com/tm3000-1.jpg","Front view","https://cdn.example.com/tm3000-2.jpg","Side view","https://cdn.example.com/tm3000-3.jpg","Display","TM-3000-V1","color:Black|warranty:2 Years",1299.99,15,"TM-3000-V2","color:Silver|warranty:2 Years",1349.99,10,"TM-3000-V3","color:White|warranty:3 Years",1399.99,8
```

---

### Step 4: Validate Your Data

Before uploading, check:
- ✅ All required fields filled (name, category_id, price)
- ✅ Category IDs exist in system
- ✅ Brand IDs exist in system (if used)
- ✅ SKUs are unique
- ✅ Prices are numbers without currency symbols
- ✅ Boolean fields use 1 or 0
- ✅ Image URLs are accessible
- ✅ Variation attributes use correct format

---

### Step 5: Upload CSV

1. Go to Products → Import
2. Click "Upload CSV"
3. Select your CSV file
4. Click "Start Import"
5. Note the Import ID shown

---

### Step 6: Monitor Progress

1. The import runs in the background
2. Check progress:
   - Go to Products → Import → View Imports
   - Or use the Import ID to check status
3. Progress shows:
   - Total rows
   - Processed rows
   - Successful rows
   - Failed rows
   - Percentage complete

**Import Status:**
- `pending` - Waiting to start
- `processing` - Currently importing
- `completed` - Finished successfully
- `failed` - Critical error occurred
- `cancelled` - You cancelled it

---

### Step 7: Check for Errors

If any rows failed:
1. Go to Import Details
2. Click "View Errors"
3. Download error report (CSV)
4. Fix the errors in your original file
5. Re-upload only the failed rows

**Common Errors:**
- Category ID doesn't exist
- SKU already exists
- Invalid price format
- Missing required fields
- Image URL not accessible

---

### Step 8: Verify Products

1. Go to Products → All Products
2. Check that products were created correctly
3. Verify:
   - Product details
   - Images display correctly
   - Variations show properly
   - Prices are correct
   - Stock quantities are accurate
   - Inventory tracking is working

**Inventory Verification:**
- Check product quantity matches your CSV
- For products with variations, verify each variation's quantity
- Test placing an order to confirm inventory decreases
- Check Inventory Logs to see all changes tracked

**Variation Verification:**
- Verify variation types are reused (check Variations page in admin)
- Confirm variation options are consistent across products
- Test that customers can select variations properly
- Check that variation SKUs are unique

---

## Inventory Management

### How Inventory Works

**For Simple Products (No Variations):**
- Stock quantity stored in `products.quantity` field
- Decreases when orders are placed
- Increases when returns are processed
- Low stock alerts when below threshold

**For Products with Variations:**
- Main product quantity usually set to 0
- Each variation has its own quantity in `product_variations.quantity`
- Total stock = sum of all variation quantities
- Each variation tracked independently

**Inventory Tracking:**
All inventory changes are logged in `inventory_logs` table with:
- Quantity before change
- Quantity change amount
- Quantity after change
- Reason (sale, return, adjustment, restock, damage, pos_sale)
- Reference to order/return/adjustment
- User who made the change
- Timestamp

**Automatic Inventory Updates:**
- ✅ Order placed → Quantity decreases
- ✅ Order cancelled → Quantity increases
- ✅ Return approved → Quantity increases
- ✅ POS sale → Quantity decreases
- ✅ Manual adjustment → Quantity changes
- ✅ All changes logged automatically

**Low Stock Alerts:**
- System monitors stock levels
- Sends alerts when quantity ≤ low_stock_threshold
- Applies to both simple products and variations
- Helps prevent stockouts

### Inventory Best Practices

1. **Set Accurate Initial Quantities**
   - Double-check quantities in CSV before import
   - For variations, set each variation's quantity correctly
   - Set main product quantity to 0 if using variations

2. **Set Appropriate Thresholds**
   - Use low_stock_threshold based on your reorder time
   - Consider sales velocity when setting thresholds
   - Default is 5, adjust based on your needs

3. **Monitor Inventory Regularly**
   - Check Inventory → Low Stock report
   - Review inventory logs for unusual changes
   - Set up low stock notifications

4. **Handle Variations Correctly**
   - Each variation is tracked separately
   - Don't set main product quantity if using variations
   - Ensure variation quantities are accurate

---

## Variation Reuse System

### Understanding Variation Reuse

The system uses a smart variation reuse system to maintain consistency across your product catalog.

**Three-Level Structure:**

1. **Variation Types** (e.g., Color, Size, Material)
   - Created once in `variations` table
   - Reused across ALL products
   - Case-insensitive matching

2. **Variation Options** (e.g., Red, Blue, Small, Large)
   - Created once per variation type in `variation_options` table
   - Reused across ALL products
   - Case-sensitive for values

3. **Product Variations** (specific product variants)
   - Created per product in `product_variations` table
   - Links to variation options
   - Has unique SKU, price, quantity

**Example Flow:**

```
Import Product 1: T-Shirt with "color:Red"
→ System creates "Color" variation type (ID: 1)
→ System creates "Red" option under Color (ID: 1)
→ System creates product variation linking to option ID 1

Import Product 2: Pants with "color:Red"
→ System finds existing "Color" variation type (ID: 1)
→ System finds existing "Red" option (ID: 1)
→ System creates product variation linking to option ID 1

Result: Both products share the same Color:Red definition
```

**Benefits:**

1. **Consistency:** All "Red" products use the same "Red" definition
2. **Efficiency:** No duplicate variation data in database
3. **Filtering:** Easy to find all products with specific attributes
4. **Maintenance:** Update variation once, affects all products
5. **Reporting:** Accurate reports on popular variations

**Important Notes:**

- ✅ Attribute names are case-insensitive (color = Color = COLOR)
- ⚠️ Attribute values are case-sensitive (Red ≠ red)
- ✅ Use consistent capitalization for values across all products
- ✅ System automatically handles reuse - you don't need to do anything special
- ✅ Existing variations are never duplicated

**Checking Variation Reuse:**

1. Go to Admin → Variations
2. See all variation types (Color, Size, etc.)
3. Click on a variation type to see all options
4. See which products use each option

---

## Examples

### Example 1: Simple Product (No Variations)

```csv
name,sku,category_id,brand_id,price,quantity,status,image_1,image_1_alt
"Yoga Mat Premium","YM-001",8,7,49.99,150,"active","https://cdn.example.com/yoga-mat.jpg","Premium yoga mat"
```

**Result:** Creates a simple product with one image, no variations.

---

### Example 2: Product with 3 Variations

```csv
name,sku,category_id,price,image_1,variation_1_sku,variation_1_attributes,variation_1_price,variation_1_quantity,variation_2_sku,variation_2_attributes,variation_2_price,variation_2_quantity,variation_3_sku,variation_3_attributes,variation_3_price,variation_3_quantity
"T-Shirt Classic","TS-001",3,29.99,"https://cdn.example.com/tshirt.jpg","TS-001-S","size:Small|color:Blue",29.99,50,"TS-001-M","size:Medium|color:Blue",29.99,75,"TS-001-L","size:Large|color:Blue",29.99,60
```

**Result:** Creates product with 3 size variations, each with different stock.

---

### Example 3: Featured Product with Full Details

```csv
name,sku,category_id,brand_id,short_description,description,price,compare_price,quantity,weight,weight_unit,is_featured,status,meta_title,image_1,image_1_alt,image_2,image_2_alt,image_3,image_3_alt
"Dumbbell Set 20kg","DB-20KG",6,4,"Complete dumbbell set","Professional dumbbell set with 20kg total weight. Includes multiple plates and adjustable bar.",89.99,119.99,50,20,"kg",1,"active","Buy 20kg Dumbbell Set | Professional Quality","https://cdn.example.com/db-1.jpg","Dumbbell set complete","https://cdn.example.com/db-2.jpg","Dumbbell plates closeup","https://cdn.example.com/db-3.jpg","Dumbbell in use"
```

**Result:** Creates featured product with full details and 3 images.

---

## Common Mistakes

### ❌ Mistake 1: Using Wrong Category ID
**Problem:** Category ID 99 doesn't exist  
**Error:** "Category ID does not exist"  
**Solution:** Check Categories page for correct IDs

### ❌ Mistake 2: Duplicate Product SKU
**Problem:** Product SKU "TM-001" already exists  
**Error:** "Product SKU already exists in database"  
**Solution:** 
- Use unique SKUs for each product
- Or leave SKU empty to auto-generate
- **Note:** Duplicate variation SKUs are handled automatically (no error)

### ❌ Mistake 3: Duplicate Variation SKU (Not an Error Anymore!)
**Previous Problem:** Variation SKU "TM-001-V1" already exists  
**Now:** ✅ System automatically generates unique SKU  
**Solution:** 
- Leave variation SKUs empty (recommended)
- System auto-generates unique SKUs
- If you provide duplicate, system detects and auto-generates new one
- Check logs to see what SKU was generated

### ❌ Mistake 4: Invalid Price Format
**Problem:** Price is "$99.99" or "99,99"  
**Error:** "Price must be a positive number"  
**Solution:** Use `99.99` (no symbols, use decimal point)

### ❌ Mistake 5: Local Image Paths
**Problem:** Image path is "C:\images\product.jpg"  
**Error:** Image won't display  
**Solution:** Upload to CDN first, use full URL

### ❌ Mistake 6: Wrong Boolean Format
**Problem:** is_featured is "yes" or "true"  
**Error:** Field not recognized as boolean  
**Solution:** Use `1` for yes/true, `0` for no/false

### ❌ Mistake 7: Invalid Variation Format
**Problem:** Attributes are "Black, Large"  
**Error:** Variations not created properly  
**Solution:** Use `color:Black|size:Large` format

### ❌ Mistake 8: Inconsistent Variation Values
**Problem:** Using "Red", "red", "RED" in different products  
**Error:** Creates 3 separate options instead of reusing  
**Solution:** Use consistent capitalization (always "Red")

### ❌ Mistake 9: Missing Required Fields
**Problem:** No product name or price  
**Error:** "Product name is required"  
**Solution:** Fill all required fields (name, category_id, price)

### ❌ Mistake 10: Wrong Quantity for Products with Variations
**Problem:** Set main product quantity to 100, but product has variations  
**Error:** Confusing stock levels  
**Solution:** Set main product quantity to 0, set each variation's quantity

### ❌ Mistake 11: Special Characters in CSV
**Problem:** Description contains quotes or commas  
**Error:** CSV parsing fails  
**Solution:** Excel handles this automatically, or escape quotes

---

## Troubleshooting

### Issue: Import Stuck in "Pending"
**Cause:** Queue workers not running  
**Solution:** Contact system administrator

### Issue: All Rows Failing
**Cause:** Wrong CSV format or encoding  
**Solution:**
1. Save as CSV UTF-8
2. Check column headers match template exactly
3. Verify no extra columns

### Issue: Images Not Showing
**Cause:** Image URLs not accessible  
**Solution:**
1. Test URLs in browser
2. Ensure images are publicly accessible
3. Check for typos in URLs

### Issue: Variations Not Created
**Cause:** Wrong attribute format  
**Solution:**
1. Use `attribute:value|attribute:value` format
2. Don't use spaces around `:` or `|`
3. Check for typos
4. Use consistent attribute names across products
5. Use consistent capitalization for values

### Issue: Inventory Not Updating
**Cause:** Various reasons  
**Solution:**
1. Check if product has variations - use variation quantities
2. Verify quantity field is numeric
3. Check Inventory Logs for tracking
4. Ensure orders are completing successfully
5. Contact administrator if issue persists

### Issue: Duplicate Variation Options Created
**Cause:** Inconsistent capitalization  
**Solution:**
1. Use consistent capitalization (e.g., always "Red" not "red")
2. Check existing variations in admin before import
3. System is case-sensitive for values
4. Update CSV to match existing values

### Issue: Import Cancelled Unexpectedly
**Cause:** System error or manual cancellation  
**Solution:**
1. Check error message
2. Fix issues in CSV
3. Re-upload

---

## Tips for Success

### ✅ Best Practices

1. **Start Small**
   - Test with 10-20 products first
   - Verify everything works correctly
   - Then upload full catalog

2. **Use Consistent Naming**
   - Keep SKU format consistent (e.g., BRAND-MODEL-VARIANT)
   - Use standard attribute names (color, size, material)
   - Follow same pattern for all products

3. **Prepare Images First**
   - Upload all images before creating CSV
   - Use descriptive filenames
   - Keep organized folder structure

4. **Validate Before Upload**
   - Check all required fields filled
   - Verify IDs exist in system
   - Test image URLs in browser

5. **Keep Backup**
   - Save original CSV file
   - Keep copy of product data
   - Document any custom fields used

6. **Monitor Progress**
   - Don't close browser during import
   - Check status regularly
   - Download error report if issues occur

7. **Fix Errors Promptly**
   - Review error report immediately
   - Fix issues in original file
   - Re-upload failed rows only

8. **Understand Inventory Tracking**
   - For simple products: use main quantity field
   - For products with variations: set variation quantities
   - System automatically tracks all changes
   - Check Inventory Logs for audit trail

9. **Use Variation Reuse Properly**
   - Use consistent attribute names (always "color" not "colour")
   - Use consistent capitalization for values (always "Red" not "red")
   - System automatically reuses existing variations
   - Check Variations page to see existing options

---

## Getting Help

### Support Resources
- **Documentation:** Check all .md files in project
- **Error Reports:** Download from import details page
- **System Logs:** Contact administrator for detailed logs

### Contact Information
- **Technical Support:** [Your support email]
- **System Administrator:** [Admin contact]
- **Emergency:** [Emergency contact]

---

## Appendix

### Quick Reference Card

**Required Fields:**
- name, category_id, price

**Boolean Fields (use 1 or 0):**
- is_featured, is_trending, kinomap, requires_shipping, separate_shipping, is_preorder

**Status Options:**
- active, inactive, draft

**Shipping Types:**
- default, free, fixed, per_item

**Weight Units:**
- g, kg, lb

**Variation Format:**
- `attribute:value|attribute:value`
- Alternative delimiter: `~~` (if value contains `|`)

**Image Format:**
- Full URL (https://...)

**Inventory Notes:**
- Simple products: Use main quantity field
- Products with variations: Set variation quantities, main quantity = 0
- All changes logged automatically

**Variation Reuse:**
- Attribute names: Case-insensitive (color = Color)
- Attribute values: Case-sensitive (Red ≠ red)
- Use consistent capitalization across all products
- System automatically reuses existing variations

**SKU Auto-Generation:**
- Product SKU: Leave empty to auto-generate (SS-XXXXXXXX)
- Variation SKU: Leave empty to auto-generate (PRODUCT-SKU-V1)
- Duplicate variation SKUs: Automatically handled, no errors

---

**Document Version:** 1.0  
**Last Updated:** April 2026  
**Questions?** Contact your system administrator

---

**Happy Importing! 🚀**
