# Session Logout Fix - Complete Solution

## Problem
Users were getting logged out after reloading the admin dashboard page. The `/api/auth/user` endpoint was also returning a 500 error.

## Root Causes Identified

### 1. Session Configuration Issues
- `SESSION_DOMAIN` was set to `localhost` but backend was running on `127.0.0.1:8000`
- `SANCTUM_STATEFUL_DOMAINS` didn't include `127.0.0.1:8000`
- `SESSION_SAME_SITE` was set to `lax` instead of `none` for cross-origin requests

### 2. Missing Profile Relationship
- `AuthController::user()` method was trying to load a non-existent `profile` relationship

## Fixes Applied

### 1. Updated `.env` Configuration
```env
SESSION_DRIVER=cookie
SESSION_DOMAIN=
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,localhost,127.0.0.1:8000,127.0.0.1
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=none
```

**Key Changes:**
- Removed `SESSION_DOMAIN=localhost` (now empty to work with both localhost and 127.0.0.1)
- Added `127.0.0.1:8000` and `127.0.0.1` to `SANCTUM_STATEFUL_DOMAINS`
- Changed `SESSION_SAME_SITE` from `lax` to `none` for cross-origin cookie support

### 2. Updated `config/sanctum.php`
Improved the stateful domains parsing to handle empty values properly:
```php
'stateful' => array_filter(array_map('trim', explode(',', env('SANCTUM_STATEFUL_DOMAINS', ...))))
```

### 3. Updated `config/cors.php`
Added proper trimming for allowed origins:
```php
'allowed_origins' => array_filter(array_map('trim', explode(',', env('FRONTEND_URL', ...))))
```

### 4. Fixed `AuthController.php`

**Added session regeneration on login:**
```php
Auth::login($result['user']);
$request->session()->regenerate();
```

**Added session regeneration on register:**
```php
Auth::login($user);
$request->session()->regenerate();
```

**Removed non-existent profile relationship:**
```php
// Before
return response()->json([
    'success' => true,
    'data' => $request->user()->load('profile'),
]);

// After
return response()->json([
    'success' => true,
    'data' => $request->user(),
]);
```

## Testing Steps

1. Clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

2. Test login from frontend (localhost:3000 or localhost:5173)

3. Verify session persists after page reload

4. Test `/api/auth/user` endpoint returns user data without 500 error

## Frontend Requirements

Make sure your frontend is sending credentials with requests:

```javascript
// Axios configuration
axios.defaults.withCredentials = true;

// Or for fetch
fetch(url, {
  credentials: 'include'
})
```

## Important Notes

- `SESSION_SAME_SITE=none` requires HTTPS in production
- For production, set `SESSION_SECURE_COOKIE=true` and use HTTPS
- The empty `SESSION_DOMAIN` allows cookies to work with both localhost and 127.0.0.1
- Always call `/sanctum/csrf-cookie` before making authenticated requests from frontend
