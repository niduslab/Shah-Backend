# CSRF Token Mismatch Fix

## Problem
The application was experiencing "CSRF token mismatch" errors, particularly after page refreshes. This is common in Laravel Sanctum SPA setups.

## Root Causes Identified
1. **Incorrect session configuration**: `SESSION_SAME_SITE=none` with `SESSION_SECURE_COOKIE=false`
2. **Missing CSRF cookie endpoint** for SPA authentication
3. **Incomplete CSRF exclusions** for API endpoints
4. **CORS configuration gaps**

## Changes Made

### 1. Environment Configuration (.env)
```env
# Changed from SESSION_SAME_SITE=none to:
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false
```

**Why**: When using `same_site=none`, cookies must be secure (HTTPS). Since we're in local development with HTTP, we use `lax` instead.

### 2. Web Routes (routes/web.php)
Added CSRF cookie endpoint:
```php
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});
```

### 3. CSRF Middleware (app/Http/Middleware/VerifyCsrfToken.php)
Extended exclusions:
```php
protected $except = [
    '/login/check-auth',
    '/api/login',
    '/api/auth/login',
    '/api/auth/register',
    '/api/auth/google/callback',
    '/api/auth/forgot-password',
    '/api/auth/reset-password',
    '/api/payments/ssl-commerz/*',
    '/api/checkout/*',
];
```

### 4. CORS Configuration (config/cors.php)
Added login/logout paths:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth', 'login', 'logout'],
```

### 5. Auth Controller (app/Http/Controllers/Api/AuthController.php)
Added CSRF token endpoint:
```php
public function csrfToken(): JsonResponse
{
    return response()->json([
        'success' => true,
        'csrf_token' => csrf_token(),
    ]);
}
```

### 6. API Routes (routes/api.php)
Added CSRF token route:
```php
Route::get('csrf-token', [\App\Http\Controllers\Api\AuthController::class, 'csrfToken']);
```

## Frontend Implementation

### For SPA Authentication Flow:

1. **Get CSRF Cookie** (before any authenticated requests):
```javascript
// First, get the CSRF cookie
await axios.get('/sanctum/csrf-cookie');
```

2. **Alternative: Get CSRF Token via API**:
```javascript
const response = await axios.get('/api/auth/csrf-token');
const csrfToken = response.data.csrf_token;
```

3. **Include CSRF Token in Requests**:
```javascript
// Option 1: Automatic (if cookie is set)
axios.defaults.withCredentials = true;

// Option 2: Manual header
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
```

### Complete Authentication Flow:
```javascript
// 1. Initialize CSRF protection
await axios.get('/sanctum/csrf-cookie');

// 2. Login
const loginResponse = await axios.post('/api/auth/login', {
    email: 'user@example.com',
    password: 'password'
});

// 3. Make authenticated requests
const userResponse = await axios.get('/api/auth/user');
```

## Testing the Fix

### 1. Clear Application Cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan session:clear
```

### 2. Test CSRF Cookie Endpoint:
```bash
curl -X GET http://127.0.0.1:8000/sanctum/csrf-cookie \
  -H "Accept: application/json"
```

### 3. Test CSRF Token API:
```bash
curl -X GET http://127.0.0.1:8000/api/auth/csrf-token \
  -H "Accept: application/json"
```

### 4. Test Login with CSRF:
```bash
# First get CSRF cookie
curl -c cookies.txt -X GET http://127.0.0.1:8000/sanctum/csrf-cookie

# Then login with cookie
curl -b cookies.txt -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

## Key Points for Frontend Developers

1. **Always get CSRF cookie first** before making authenticated requests
2. **Set `withCredentials: true`** in your HTTP client configuration
3. **Handle CSRF token refresh** on 419 errors
4. **Use the `/api/auth/csrf-token` endpoint** if you need the token value directly

## Production Considerations

For production deployment, update these settings:
```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
APP_URL=https://yourdomain.com
FRONTEND_URL=https://yourfrontend.com
```

## Troubleshooting

If you still get CSRF errors:
1. Check browser cookies are being set
2. Verify CORS configuration matches your frontend URL
3. Ensure `withCredentials: true` is set in your HTTP client
4. Clear browser cookies and try again
5. Check that session driver is properly configured