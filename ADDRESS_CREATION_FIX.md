# Address Creation Fix - Guest Checkout

## Issue
When creating addresses during guest checkout, the system was failing with:
```
SQLSTATE[HY000]: General error: 1364 Field 'contact_no' doesn't have a default value
```

## Root Cause
The `addresses` table schema uses different field names than what the code was trying to use:
- Database uses `contact_no` but code used `phone`
- Database uses `address_type` but code used `type`
- Database uses `zip_code` but code used `postal_code`
- Database doesn't have `country` field

## Solution
Updated `app/Services/OrderService.php` to use correct field names matching the database schema.

### Field Mapping

| Frontend Field | Database Field | Notes |
|---------------|----------------|-------|
| `phone` | `contact_no` | Phone number |
| `type` | `address_type` | 'shipping_address' or 'billing_address' |
| `postal_code` or `zip_code` | `zip_code` | Accepts both formats |
| `country` | ❌ Not stored | Country field removed |

### Before (Incorrect)
```php
$shippingAddress = Address::create([
    'user_id' => $user?->id,
    'type' => 'shipping',              // ❌ Wrong field
    'phone' => $data['phone'],         // ❌ Wrong field
    'postal_code' => $data['postal_code'], // ❌ Wrong field
    'country' => $data['country'],     // ❌ Field doesn't exist
    ...
]);
```

### After (Correct)
```php
$shippingAddress = Address::create([
    'user_id' => $user?->id,
    'address_type' => 'shipping_address',  // ✅ Correct
    'contact_no' => $data['phone'],        // ✅ Correct
    'zip_code' => $data['zip_code'] ?? $data['postal_code'], // ✅ Accepts both
    // country field removed
    ...
]);
```

## Address Model Fields

The `addresses` table has these fields:
```php
'user_id'        // Foreign key to users (nullable for guests)
'address_line_1' // Required
'address_line_2' // Optional
'contact_no'     // Required - Phone number
'city'           // Required
'state'          // Optional
'zip_code'       // Optional
'address_type'   // Required: 'shipping_address' or 'billing_address'
'is_default'     // Boolean
```

## Frontend Payload

### Current Format (Works)
```json
{
  "shipping_address": {
    "address_line_1": "Corporis iusto facil",
    "city": "Chittagong",
    "state": "Manikganj",
    "zip_code": "73361",
    "country": "Bangladesh",
    "phone": "+1 (708) 862-5499"
  }
}
```

### Alternative Format (Also Works)
```json
{
  "shipping_address": {
    "address_line_1": "123 Street",
    "city": "Dhaka",
    "state": "Dhaka",
    "postal_code": "1200",  // Can use postal_code instead of zip_code
    "country": "Bangladesh",
    "phone": "01712345678"
  }
}
```

**Note:** The `country` field is accepted in the request but not stored in the database. It's used for display/validation purposes only.

## Changes Made

### File: `app/Services/OrderService.php`

1. **Field Name Corrections:**
   - `type` → `address_type`
   - `phone` → `contact_no`
   - `postal_code` → `zip_code` (with fallback support)
   - Removed `country` field from database insert

2. **Address Type Values:**
   - Shipping: `'shipping_address'`
   - Billing: `'billing_address'`

3. **Flexible Zip Code:**
   - Accepts both `zip_code` and `postal_code` from frontend
   - Stores as `zip_code` in database

## Testing

### Test Guest Checkout with Address Creation

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 25, "quantity": 3, "price": 33}],
    "guest_email": "user1@gmail.com",
    "guest_name": "Eaton Emerson",
    "guest_phone": "+1 (708) 862-5499",
    "shipping_address": {
      "address_line_1": "Corporis iusto facil",
      "city": "Chittagong",
      "state": "Manikganj",
      "zip_code": "73361",
      "country": "Bangladesh",
      "phone": "+1 (708) 862-5499"
    },
    "shipping_method": "standard",
    "payment_method": "cod",
    "create_account": true,
    "password": "Akash010@"
  }'
```

### Expected Result
✅ Address created successfully with:
- address_type: "shipping_address"
- contact_no: "+1 (708) 862-5499"
- zip_code: "73361"
- user_id: 15 (if account created)

### Database Verification

```sql
SELECT id, user_id, address_line_1, city, state, zip_code, contact_no, address_type
FROM addresses
WHERE user_id = 15;
```

Expected:
```
id | user_id | address_line_1      | city       | state     | zip_code | contact_no          | address_type
1  | 15      | Corporis iusto facil| Chittagong | Manikganj | 73361    | +1 (708) 862-5499  | shipping_address
```

## Frontend Considerations

### Address Form Fields

```javascript
const addressFields = {
  address_line_1: '',  // Required
  address_line_2: '',  // Optional
  city: '',            // Required
  state: '',           // Optional
  zip_code: '',        // Optional (can also use postal_code)
  country: 'Bangladesh', // For display only
  phone: ''            // Required
};
```

### React Component Example

```javascript
const AddressForm = () => {
  const [address, setAddress] = useState({
    address_line_1: '',
    city: '',
    state: '',
    zip_code: '',
    country: 'Bangladesh',
    phone: ''
  });

  return (
    <form>
      <input
        name="address_line_1"
        placeholder="Street Address"
        value={address.address_line_1}
        onChange={(e) => setAddress({...address, address_line_1: e.target.value})}
        required
      />
      
      <input
        name="city"
        placeholder="City"
        value={address.city}
        onChange={(e) => setAddress({...address, city: e.target.value})}
        required
      />
      
      <input
        name="state"
        placeholder="State/Division"
        value={address.state}
        onChange={(e) => setAddress({...address, state: e.target.value})}
      />
      
      <input
        name="zip_code"
        placeholder="Postal/Zip Code"
        value={address.zip_code}
        onChange={(e) => setAddress({...address, zip_code: e.target.value})}
      />
      
      <select
        name="country"
        value={address.country}
        onChange={(e) => setAddress({...address, country: e.target.value})}
      >
        <option value="Bangladesh">Bangladesh</option>
      </select>
      
      <input
        name="phone"
        placeholder="Phone Number"
        value={address.phone}
        onChange={(e) => setAddress({...address, phone: e.target.value})}
        required
      />
    </form>
  );
};
```

## Validation Rules

### Required Fields
- `address_line_1`
- `city`
- `phone` (stored as `contact_no`)

### Optional Fields
- `address_line_2`
- `state`
- `zip_code` (or `postal_code`)
- `country` (not stored)

## Error Handling

### Missing Required Field
```json
{
  "message": "The shipping address.phone field is required with shipping address.",
  "errors": {
    "shipping_address.phone": ["The shipping address.phone field is required with shipping address."]
  }
}
```

### Invalid Phone Format
If you add phone validation:
```json
{
  "message": "The shipping address.phone format is invalid.",
  "errors": {
    "shipping_address.phone": ["The shipping address.phone format is invalid."]
  }
}
```

## Summary

✅ Fixed address creation during guest checkout  
✅ Uses correct database field names  
✅ Supports both `zip_code` and `postal_code` from frontend  
✅ Properly sets `address_type` values  
✅ Stores phone as `contact_no`  
✅ Removed non-existent `country` field from database insert  
✅ No breaking changes to API  

The guest checkout with address creation now works correctly!
