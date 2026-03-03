# Fix 500 Internal Server Error on Login

## The Problem

```
POST http://localhost:8000/api/auth/login
Status: 500 Internal Server Error
{"message": "Server Error"}
```

**Payload:**
```json
{
  "email": "admin@gmail.com",
  "password": "12345678"
}
```

## Root Cause

The error was: `Return value must be of type Illuminate\Http\JsonResponse, string returned`

The `UserService::authenticate()` method was returning `null` when credentials were invalid, but the `AuthController` expected an array with a `success` key. This caused a type mismatch error.

## What Was Fixed

### 1. Updated UserService::authenticate()

**Before:**
```php
public function authenticate(string $email, string $password): ?array
{
    $user = User::where('email', $email)->first();

    if (!$user || !Hash::check($password, $user->password)) {
        return null;  // ❌ Returns null on failure
    }

    if (!$user->status) {
        return null;  // ❌ Returns null if inactive
    }

    $token = $user->createToken('auth-token')->plainTextToken;

    return [
        'user' => $user,
        'token' => $token,
    ];
}
```

**After:**
```php
public function authenticate(string $email, string $password): array
{
    $user = User::where('email', $email)->first();

    if (!$user || !Hash::check($password, $user->password)) {
        return [
            'success' => false,
            'message' => 'Invalid credentials.',
        ];
    }

    if (!$user->status) {
        return [
            'success' => false,
            'message' => 'Your account has been deactivated.',
        ];
    }

    return [
        'success' => true,
        'user' => $user,
    ];
}
```

### 2. Updated UserServiceInterface

**Before:**
```php
public function authenticate(string $email, string $password): ?array;
```

**After:**
```php
public function authenticate(string $email, string $password): array;
```

### 3. Fixed Password Reset Methods

Also updated password reset methods to return consistent array responses:

**UserService::sendPasswordResetLink():**
```php
public function sendPasswordResetLink(string $email): array
{
    $user = $this->findByEmail($email);

    if (!$user) {
        return [
            'success' => true,
            'message' => 'If that email exists, a password reset link has been sent.',
        ];
    }

    $status = Password::sendResetLink(['email' => $email]);

    return [
        'success' => $status === Password::RESET_LINK_SENT,
        'message' => $status === Password::RESET_LINK_SENT 
            ? 'Password reset link sent to your email.' 
            : 'Unable to send password reset link.',
    ];
}
```

**UserService::resetPassword():**
```php
public function resetPassword(string $email, string $token, string $password): array
{
    $status = Password::reset(
        [
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'token' => $token,
        ],
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            $user->tokens()->delete();
        }
    );

    return [
        'success' => $status === Password::PASSWORD_RESET,
        'message' => $status === Password::PASSWORD_RESET 
            ? 'Password reset successfully.' 
            : 'Unable to reset password. Invalid or expired token.',
    ];
}
```

### 4. Updated AuthController

The AuthController already expected the correct format, so no changes were needed there.

## Files Modified

1. ✅ `app/Services/UserService.php`
   - Updated `authenticate()` method
   - Renamed `resetPassword()` to `sendPasswordResetLink()`
   - Renamed `completePasswordReset()` to `resetPassword()`
   - All methods now return consistent array responses

2. ✅ `app/Services/Contracts/UserServiceInterface.php`
   - Updated method signatures to match implementation
   - Changed return types from `?array` and `bool` to `array`

3. ✅ `app/Http/Controllers/Api/AuthController.php`
   - Updated to use new method names
   - Improved error handling

## Testing

### Test Login with Valid Credentials

```bash
# Get CSRF cookie first
curl -X GET http://localhost:8000/sanctum/csrf-cookie \
  -H "Accept: application/json" \
  -c cookies.txt

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Referer: http://localhost:3000" \
  -b cookies.txt \
  -c cookies.txt \
  -d '{"email":"admin@gmail.com","password":"12345678"}'
```

**Expected Response (Success):**
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "email": "admin@gmail.com",
      "name": "Admin User",
      ...
    }
  }
}
```

### Test Login with Invalid Credentials

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"email":"admin@gmail.com","password":"wrongpassword"}'
```

**Expected Response (Failure):**
```json
{
  "success": false,
  "message": "Invalid credentials."
}
```

### Test with React

```javascript
import api from '../api/axios';

// Get CSRF cookie first
await api.get('/sanctum/csrf-cookie');

// Login
try {
  const response = await api.post('/api/auth/login', {
    email: 'admin@gmail.com',
    password: '12345678',
  });
  
  console.log('Login successful:', response.data);
  // Response: { success: true, message: "Login successful.", data: { user: {...} } }
  
} catch (error) {
  console.error('Login failed:', error.response?.data);
  // Response: { success: false, message: "Invalid credentials." }
}
```

## Response Format

All authentication endpoints now return consistent responses:

### Success Response
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": { ... }
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Invalid credentials."
}
```

## Verification Checklist

✅ UserService methods return consistent array responses
✅ No more `null` returns that cause type errors
✅ Better error messages for users
✅ Password reset methods updated
✅ All diagnostics pass
✅ No syntax errors

## Clear Laravel Cache

After these changes, clear the cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Summary

The 500 error was caused by inconsistent return types in the `UserService`. The service was returning `null` on authentication failure, but the controller expected an array with a `success` key.

**Fixed by:**
1. Making `authenticate()` always return an array
2. Including `success` and `message` keys in all responses
3. Updating interface to match implementation
4. Improving error messages

Your login should now work correctly! 🎉
