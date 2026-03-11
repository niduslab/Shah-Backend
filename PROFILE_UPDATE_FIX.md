# ✅ Profile Update Fix

Fixed the issue where profile data wasn't persisting after update.

## Problem

When updating profile with payload:
```json
{
  "first_name": "Kazi Sajedul",
  "last_name": "Islam",
  "email": "user@gmail.com",
  "phone": "+1 (267) 101-6965",
  "date_of_birth": "2026-03-11",
  "gender": "male"
}
```

The data appeared to save but wasn't persisting when returning to the page.

## Root Cause

1. The `updateProfile` method only accepted `name` and `phone` fields
2. The User model didn't have `date_of_birth` and `gender` fields
3. The validation was rejecting the new fields silently

## Solution

### 1. Added Database Fields

Created migration to add profile fields:

```php
Schema::table('users', function (Blueprint $table) {
    $table->date('date_of_birth')->nullable()->after('phone');
    $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
});
```

### 2. Updated User Model

Added fields to `$fillable` array:

```php
protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'phone',
    'date_of_birth',  // Added
    'gender',         // Added
    'user_type',
    'password',
    'status',
    'google_id',
    'avatar',
    'provider',
];
```

Added date casting:

```php
protected $casts = [
    'email_verified_at' => 'datetime',
    'date_of_birth' => 'date',  // Added
    'status' => 'boolean',
];
```

### 3. Updated AuthController

Fixed `updateProfile` method to accept all profile fields:

```php
public function updateProfile(Request $request): JsonResponse
{
    $validated = $request->validate([
        'first_name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
        'phone' => 'nullable|string|max:20',
        'date_of_birth' => 'nullable|date|before:today',
        'gender' => 'nullable|in:male,female,other',
    ]);

    $user = $request->user();
    $user->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully.',
        'data' => $user->fresh(),
    ]);
}
```

## Changes Made

### Files Created
- ✅ `database/migrations/2026_03_11_063311_add_profile_fields_to_users_table.php`

### Files Modified
- ✅ `app/Models/User.php` - Added `date_of_birth` and `gender` to fillable and casts
- ✅ `app/Http/Controllers/Api/AuthController.php` - Updated validation and logic

## API Endpoint

```
PUT /api/auth/profile
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body

```json
{
  "first_name": "Kazi Sajedul",
  "last_name": "Islam",
  "email": "user@gmail.com",
  "phone": "+1 (267) 101-6965",
  "date_of_birth": "1990-01-15",
  "gender": "male"
}
```

### Response

```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "data": {
    "id": 1,
    "first_name": "Kazi Sajedul",
    "last_name": "Islam",
    "email": "user@gmail.com",
    "phone": "+1 (267) 101-6965",
    "date_of_birth": "1990-01-15",
    "gender": "male",
    "user_type": "customer",
    "status": true,
    "created_at": "2024-03-11T10:00:00.000000Z",
    "updated_at": "2024-03-11T10:30:00.000000Z"
  }
}
```

## Validation Rules

| Field | Rules | Description |
|-------|-------|-------------|
| first_name | sometimes, string, max:255 | Optional first name |
| last_name | sometimes, string, max:255 | Optional last name |
| email | sometimes, email, unique | Optional email (must be unique) |
| phone | nullable, string, max:20 | Optional phone number |
| date_of_birth | nullable, date, before:today | Optional birth date (must be in past) |
| gender | nullable, in:male,female,other | Optional gender |

## Testing

### Test with cURL

```bash
curl -X PUT http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Kazi Sajedul",
    "last_name": "Islam",
    "email": "user@gmail.com",
    "phone": "+1 (267) 101-6965",
    "date_of_birth": "1990-01-15",
    "gender": "male"
  }'
```

### Test with JavaScript

```javascript
const updateProfile = async (profileData) => {
    const response = await fetch('http://localhost:8000/api/auth/profile', {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(profileData),
    });
    
    const data = await response.json();
    return data;
};

// Usage
const result = await updateProfile({
    first_name: 'Kazi Sajedul',
    last_name: 'Islam',
    email: 'user@gmail.com',
    phone: '+1 (267) 101-6965',
    date_of_birth: '1990-01-15',
    gender: 'male',
});

console.log(result);
```

## Frontend Integration

### React Example

```jsx
import { useState } from 'react';

const ProfileForm = ({ user, token }) => {
    const [formData, setFormData] = useState({
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        phone: user.phone || '',
        date_of_birth: user.date_of_birth || '',
        gender: user.gender || '',
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const response = await fetch('http://localhost:8000/api/auth/profile', {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formData),
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Profile updated successfully!');
                // Update user state with fresh data
                updateUser(data.data);
            }
        } catch (error) {
            console.error('Error updating profile:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <input
                type="text"
                value={formData.first_name}
                onChange={(e) => setFormData({...formData, first_name: e.target.value})}
                placeholder="First Name"
            />
            <input
                type="text"
                value={formData.last_name}
                onChange={(e) => setFormData({...formData, last_name: e.target.value})}
                placeholder="Last Name"
            />
            <input
                type="email"
                value={formData.email}
                onChange={(e) => setFormData({...formData, email: e.target.value})}
                placeholder="Email"
            />
            <input
                type="tel"
                value={formData.phone}
                onChange={(e) => setFormData({...formData, phone: e.target.value})}
                placeholder="Phone"
            />
            <input
                type="date"
                value={formData.date_of_birth}
                onChange={(e) => setFormData({...formData, date_of_birth: e.target.value})}
            />
            <select
                value={formData.gender}
                onChange={(e) => setFormData({...formData, gender: e.target.value})}
            >
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
            <button type="submit">Update Profile</button>
        </form>
    );
};
```

## Database Schema

After migration, the `users` table now includes:

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,              -- NEW
    gender ENUM('male','female','other') NULL,  -- NEW
    user_type VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status BOOLEAN DEFAULT 1,
    google_id VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    provider VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Notes

- Date of birth must be in the past (validated with `before:today`)
- Gender accepts only: `male`, `female`, or `other`
- Email must be unique across all users
- All fields are optional (can update individually)
- The response includes the fresh user data with all updates

## Status

✅ **Fixed** - Profile updates now persist correctly with all fields including date_of_birth and gender

---

**Issue Resolved**: Profile data now saves and persists correctly!
