# User Creation Fix - Guest Checkout

## Issue
When creating an account during guest checkout, the system was failing with:
```
SQLSTATE[HY000]: General error: 1364 Field 'first_name' doesn't have a default value
```

## Root Cause
The `users` table schema uses `first_name` and `last_name` fields, but the guest checkout code was trying to use `full_name` field which doesn't exist in the database.

## Solution
Updated `app/Services/OrderService.php` to properly split the guest name into first and last name components.

### Before (Incorrect)
```php
$user = User::create([
    'full_name' => $shippingData['guest_name'],  // ❌ Wrong field
    'email' => $shippingData['guest_email'],
    'phone' => $shippingData['guest_phone'],
    'password' => bcrypt($shippingData['password']),
    'role' => 'customer',  // ❌ Wrong field (should be user_type)
]);
```

### After (Correct)
```php
// Split guest name into first and last name
$nameParts = explode(' ', $shippingData['guest_name'], 2);
$firstName = $nameParts[0];
$lastName = $nameParts[1] ?? '';

$user = User::create([
    'first_name' => $firstName,  // ✅ Correct
    'last_name' => $lastName,    // ✅ Correct
    'email' => $shippingData['guest_email'],
    'phone' => $shippingData['guest_phone'],
    'password' => bcrypt($shippingData['password']),
    'user_type' => 'customer',   // ✅ Correct
    'status' => true,            // ✅ Added
]);
```

## Changes Made

### File: `app/Services/OrderService.php`

1. **Name Splitting:** Guest name is now split into first and last name
   - Single word names: First name only, last name is empty
   - Multiple words: First word is first name, rest is last name
   - Example: "John Doe" → first_name: "John", last_name: "Doe"
   - Example: "John" → first_name: "John", last_name: ""

2. **Field Mapping:** Corrected field names to match database schema
   - `full_name` → `first_name` + `last_name`
   - `role` → `user_type`
   - Added `status` field (set to `true` for active users)

## User Model Fields

The `users` table has these fields:
```php
'first_name'  // Required
'last_name'   // Required (can be empty string)
'email'       // Required, unique
'phone'       // Optional
'user_type'   // Required: 'customer', 'admin', 'vendor'
'password'    // Required
'status'      // Boolean: true (active), false (inactive)
'google_id'   // Optional (for OAuth)
'avatar'      // Optional
'provider'    // Optional (for OAuth)
```

## Testing

### Test Account Creation During Checkout

```bash
curl -X POST http://127.0.0.1:8000/api/checkout/process \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 1, "quantity": 1, "price": 100}],
    "guest_email": "newuser@example.com",
    "guest_name": "John Doe",
    "guest_phone": "01712345678",
    "shipping_address": {
      "address_line_1": "123 Street",
      "city": "Dhaka",
      "country": "Bangladesh",
      "phone": "01712345678"
    },
    "shipping_method": "standard",
    "payment_method": "cod",
    "create_account": true,
    "password": "SecurePass123"
  }'
```

### Expected Result
✅ User created successfully with:
- first_name: "John"
- last_name: "Doe"
- email: "newuser@example.com"
- user_type: "customer"
- status: true

### Test Single Name

```json
{
  "guest_name": "Madonna",
  "create_account": true,
  ...
}
```

Result:
- first_name: "Madonna"
- last_name: ""

### Test Multiple Names

```json
{
  "guest_name": "John Paul Smith",
  "create_account": true,
  ...
}
```

Result:
- first_name: "John"
- last_name: "Paul Smith"

## Frontend Considerations

### Name Input
You can either:

1. **Single Field (Current):**
   ```html
   <input name="guest_name" placeholder="Full Name" />
   ```
   Backend will split automatically.

2. **Separate Fields (Recommended):**
   ```html
   <input name="first_name" placeholder="First Name" />
   <input name="last_name" placeholder="Last Name" />
   ```
   Then combine: `guest_name: firstName + ' ' + lastName`

### Example React Component

```javascript
const [guestData, setGuestData] = useState({
  firstName: '',
  lastName: '',
  email: '',
  phone: ''
});

const handleCheckout = async () => {
  const payload = {
    ...checkoutData,
    guest_name: `${guestData.firstName} ${guestData.lastName}`.trim(),
    guest_email: guestData.email,
    guest_phone: guestData.phone,
    create_account: true,
    password: guestData.password
  };

  // Send to API
};
```

## Database Verification

After creating an account, verify in database:

```sql
SELECT id, first_name, last_name, email, user_type, status 
FROM users 
WHERE email = 'newuser@example.com';
```

Expected:
```
id | first_name | last_name | email              | user_type | status
1  | John       | Doe       | newuser@example.com| customer  | 1
```

## Error Handling

### Duplicate Email
If email already exists:
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### Missing Required Fields
```json
{
  "message": "The guest name field is required when user is not present.",
  "errors": {
    "guest_name": ["The guest name field is required when user is not present."]
  }
}
```

## Summary

✅ Fixed user creation during guest checkout  
✅ Properly splits name into first and last name  
✅ Uses correct database field names  
✅ Sets user_type to 'customer'  
✅ Activates user account by default  
✅ Handles single-word names  
✅ No breaking changes to API  

The guest checkout with account creation now works correctly!
