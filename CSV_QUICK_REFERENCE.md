# CSV Import Quick Reference Sheet
**Print this for easy reference while filling the CSV**

---

## ✅ REQUIRED FIELDS (Must Fill)

| Field | Format | Example |
|-------|--------|---------|
| **name** | Text (max 255) | `Treadmill Pro 3000` |
| **category_id** | Number | `5` |
| **price** | Decimal (no $) | `1299.99` |

---

## 📋 COMMON OPTIONAL FIELDS

| Field | Format | Example | Notes |
|-------|--------|---------|-------|
| **sku** | Text | `TM-3000` | Leave empty to auto-generate |
| **brand_id** | Number | `3` | Check Brands page for IDs |
| **quantity** | Number | `25` | Stock available |
| **status** | Text | `active` | Options: active, inactive, draft |
| **short_description** | Text | `High-performance treadmill` | Brief summary |
| **description** | Text | `Professional grade...` | Full details |

---

## 💰 PRICING

| Field | Format | Example | Notes |
|-------|--------|---------|-------|
| **price** | Decimal | `1299.99` | Selling price (required) |
| **compare_price** | Decimal | `1599.99` | Original price (shows discount) |
| **cost_price** | Decimal | `899.99` | Your cost (internal only) |

---

## 📦 INVENTORY

| Field | Format | Example | Notes |
|-------|--------|---------|-------|
| **quantity** | Number | `25` | Stock available |
| **low_stock_threshold** | Number | `5` | Alert when stock falls below |

---

## 📏 DIMENSIONS

| Field | Format | Example | Notes |
|-------|--------|---------|-------|
| **weight** | Decimal | `85.5` | Product weight |
| **weight_unit** | Text | `kg` | Options: g, kg, lb |
| **length** | Decimal | `180` | Length in cm |
| **width** | Decimal | `80` | Width in cm |
| **height** | Decimal | `150` | Height in cm |

---

## 🚚 SHIPPING

| Field | Format | Example | Notes |
|-------|--------|---------|-------|
| **shipping_type** | Text | `default` | Options: default, free, fixed, per_item |
| **shipping_cost** | Decimal | `50.00` | Only if type is fixed/per_item |
| **requires_shipping** | 1 or 0 | `1` | 1=yes, 0=no (digital products) |
| **separate_shipping** | 1 or 0 | `0` | 1=ships separately |

---

## 🏷️ FLAGS (Use 1 or 0)

| Field | Format | Example | Notes |
|-------|--------|---------|-------|
| **is_featured** | 1 or 0 | `1` | Show in featured section |
| **is_trending** | 1 or 0 | `0` | Show in trending section |
| **kinomap** | 1 or 0 | `0` | Kinomap compatible |
| **status** | Text | `active` | active, inactive, draft |

---

## 🔍 SEO

| Field | Format | Example |
|-------|--------|---------|
| **meta_title** | Text (max 255) | `Treadmill Pro 3000 - Best Price` |
| **meta_description** | Text | `Buy the best treadmill...` |
| **meta_keywords** | Text | `treadmill,fitness,gym` |

---

## 🖼️ IMAGES (3 Images)

| Field | Format | Example |
|-------|--------|---------|
| **image_1** | Full URL | `https://cdn.example.com/product-1.jpg` |
| **image_1_alt** | Text | `Product front view` |
| **image_2** | Full URL | `https://cdn.example.com/product-2.jpg` |
| **image_2_alt** | Text | `Product side view` |
| **image_3** | Full URL | `https://cdn.example.com/product-3.jpg` |
| **image_3_alt** | Text | `Product display` |

**Image Tips:**
- ✅ Use full URLs (https://...)
- ✅ Upload to CDN first
- ✅ First image is primary
- ❌ Don't use local paths

---

## 🎨 VARIATIONS (3 Variations)

### Variation 1
| Field | Format | Example |
|-------|--------|---------|
| **variation_1_sku** | Text | `TM-3000-V1` |
| **variation_1_attributes** | `key:value\|key:value` | `color:Black\|warranty:2 Years` |
| **variation_1_price** | Decimal | `1299.99` |
| **variation_1_quantity** | Number | `15` |

### Variation 2
Same format as Variation 1

**Example:**
- `variation_2_sku`: `TM-3000-V2`
- `variation_2_attributes`: `color:Silver|warranty:2 Years`
- `variation_2_price`: `1349.99`
- `variation_2_quantity`: `10`

### Variation 3
Same format as Variation 1

**Variation Tips:**
- ✅ Use `attribute:value|attribute:value` format
- ✅ Common attributes: color, size, material, warranty
- ✅ Leave empty for simple products
- ✅ First variation is default
- ❌ Don't use spaces around `:` or `|`

---

## 📝 FORMAT RULES

### Text Fields
- Use quotes if text contains commas
- Example: `"High-quality, professional grade"`

### Numbers
- No currency symbols: `99.99` not `$99.99`
- Use decimal point: `99.99` not `99,99`
- Whole numbers: `25` not `25.0`

### Boolean (Yes/No)
- Use `1` for YES/TRUE
- Use `0` for NO/FALSE
- Examples: is_featured = `1`, requires_shipping = `0`

### Dates
- Format: `YYYY-MM-DD`
- Example: `2026-06-01`

### URLs
- Must start with `http://` or `https://`
- Must be publicly accessible
- Example: `https://cdn.example.com/image.jpg`

---

## ⚠️ COMMON MISTAKES

| Mistake | Wrong | Correct |
|---------|-------|---------|
| Price with symbol | `$99.99` | `99.99` |
| Comma in number | `1,299.99` | `1299.99` |
| Boolean as text | `yes` | `1` |
| Local image path | `C:\images\pic.jpg` | `https://cdn.../pic.jpg` |
| Wrong variation format | `Black, Large` | `color:Black\|size:Large` |
| Missing required field | (empty name) | `Product Name` |

---

## 🎯 QUICK CHECKLIST

Before uploading, verify:
- [ ] All required fields filled (name, category_id, price)
- [ ] Category IDs exist in system
- [ ] SKUs are unique (or empty)
- [ ] Prices are numbers without symbols
- [ ] Boolean fields use 1 or 0
- [ ] Image URLs are accessible
- [ ] Variation format is correct
- [ ] No special characters causing issues

---

## 📞 NEED HELP?

**Common Questions:**
1. **Where do I find category IDs?** → Admin → Categories page
2. **Where do I find brand IDs?** → Admin → Brands page
3. **How do I upload images?** → Upload to CDN first, then use URLs
4. **Can I leave fields empty?** → Yes, except name, category_id, price
5. **How many products can I upload?** → Unlimited (tested with 5000+)

**Error Messages:**
- "Category ID does not exist" → Check Categories page for correct ID
- "SKU already exists" → Use unique SKU or leave empty
- "Price must be positive number" → Remove $ symbol, use decimal point
- "Image URL not accessible" → Test URL in browser first

---

## 📊 EXAMPLE ROW

```csv
"Treadmill Pro 3000","TM-3000",5,3,,"","High-performance treadmill","Professional grade treadmill",1299.99,1599.99,899.99,25,5,85.5,"kg",180,80,150,"default",,1,0,"",1,0,0,"active","Treadmill Pro 3000","Buy the best treadmill","treadmill,fitness",0,,,,"","https://cdn.example.com/tm-1.jpg","Front view","https://cdn.example.com/tm-2.jpg","Side view","https://cdn.example.com/tm-3.jpg","Display","TM-3000-V1","color:Black|warranty:2 Years",1299.99,15,"TM-3000-V2","color:Silver|warranty:2 Years",1349.99,10,"TM-3000-V3","color:White|warranty:3 Years",1399.99,8
```

---

**Print this page and keep it handy while filling your CSV!**

**Version:** 1.0 | **Last Updated:** April 2026
