# Backend Changes Summary - HTTP-Only Cookie Authentication

## What Changed

### 1. Environment Variables (`.env`)

```env
# Session Configuration
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,localhost

# Frontend URLs for CORS
FRONTEND_URL=http://localhost:3000,http://localhost:5173
```

### 2. AuthController (`app/Http/Controllers/Api/AuthController.php`)

**Register Method:**
- Removed: `$token = $user->createToken('auth_token')->plainTextToken;`
- Added: `Auth::login($user);`
- Response no longer includes token

**Login Method:**
- Removed: `$token = $result['user']->createToken('auth_token')->plainTextToken;`
- Added: `Auth::login($result['user']);`
- Response no longer includes token

**Logout Method:**
- Removed: `$request->user()->currentAccessToken()->delete();`
- Added: Session-based logout with `Auth::guard('web')->logout()`, `$request->session()->invalidate()`, and `$request->session()->regenerateToken()`

### 3. CORS Configuration (`config/cors.php`)

```php
'allowed_origins' => explode(',', env('FRONTEND_URL', 'http://localhost:3000,http://localhost:5173')),
```

Now supports multiple frontend URLs from environment variable.

### 4. Kernel Middleware (`app/Http/Kernel.php`)

**API Middleware Group:**
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

This ensures Sanctum's stateful authentication works properly with session cookies.

### 5. Session Configuration (`config/session.php`)

Added partitioned cookie support for better security.

## How It Works

### Authentication Flow

1. **Frontend requests CSRF cookie:**
   ```
   GET /sanctum/csrf-cookie
   ```
   Backend sets XSRF-TOKEN cookie

2. **Frontend sends login request:**
   ```
   POST /api/auth/login
   ```
   Backend creates session and sets session cookie

3. **All subsequent requests:**
   - Include session cookie automatically
   - Include XSRF-TOKEN in headers
   - No need to manually manage tokens

### Middleware Stack

The `api` middleware group includes:
```php
\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
```

This middleware:
- Checks if request is from stateful domain
- Enables session-based authentication
- Validates CSRF tokens
- Encrypts cookies

### Security Features

1. **HTTP-Only Cookies:** JavaScript cannot access session cookies
2. **CSRF Protection:** Automatic token validation on state-changing requests
3. **SameSite Policy:** Prevents CSRF attacks
4. **Encrypted Cookies:** Session data is encrypted
5. **Stateful Domains:** Only configured domains can use cookie auth

## Testing the Backend

### Important: Use localhost, not 127.0.0.1

For cookie authentication to work properly, both frontend and backend must use the same domain.

**Start Laravel server on localhost:**
```bash
php artisan serve --host=localhost --port=8000
```

**Update your React app to use localhost:**
```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000',  // Not 127.0.0.1
  withCredentials: true,
});
```

### 1. Get CSRF Cookie
```bash
curl -X GET http://127.0.0.1:8000/sanctum/csrf-cookie \
  -H "Accept: application/json" \
  -c cookies.txt
```

### 2. Login
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: <token-from-cookie>" \
  -b cookies.txt \
  -c cookies.txt \
  -d '{"email":"user@example.com","password":"password"}'
```

### 3. Access Protected Route
```bash
curl -X GET http://127.0.0.1:8000/api/auth/user \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: <token-from-cookie>" \
  -b cookies.txt
```

### 4. Logout
```bash
curl -X POST http://127.0.0.1:8000/api/auth/logout \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: <token-from-cookie>" \
  -b cookies.txt
```

## Troubleshooting 419 CSRF Error

If you get a 419 CSRF token mismatch error:

1. **Use localhost everywhere** (not 127.0.0.1)
   - Backend: `http://localhost:8000`
   - Frontend: `http://localhost:3000`

2. **Call `/sanctum/csrf-cookie` BEFORE login/register**
   ```javascript
   await api.get('/sanctum/csrf-cookie');
   await api.post('/api/auth/login', { email, password });
   ```

3. **Ensure `withCredentials: true`** in axios config

4. **Clear browser cookies** for localhost and 127.0.0.1

5. **Restart Laravel server** on localhost:
   ```bash
   php artisan serve --host=localhost --port=8000
   ```

See **FIX_419_CSRF_ERROR.md** for detailed troubleshooting guide.

## Migration Notes

### For Existing Users

If you have existing users with tokens, they will need to:
1. Log out from their current sessions
2. Log in again to get session cookies

### Database

No database changes required. The `personal_access_tokens` table is no longer used for authentication but can remain for other purposes (API tokens, etc.).

### Backward Compatibility

If you need to support both token and cookie authentication:

1. Keep token creation in a separate endpoint
2. Use different middleware for different routes
3. Check authentication method in controllers

## Production Deployment

Update `.env` for production:

```env
SESSION_DRIVER=cookie
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com,app.yourdomain.com
FRONTEND_URL=https://yourdomain.com,https://www.yourdomain.com
APP_URL=https://api.yourdomain.com
```

### Important Production Settings

- `SESSION_SECURE_COOKIE=true` - Only send cookies over HTTPS
- `SESSION_SAME_SITE=none` - Required for cross-domain cookies with HTTPS
- Use proper domain names (not IP addresses)
- Ensure SSL certificates are valid

## Advantages of HTTP-Only Cookies

1. **More Secure:** Immune to XSS attacks
2. **Automatic:** Browser handles cookie storage and sending
3. **Stateful:** Better for traditional web apps
4. **CSRF Protected:** Built-in CSRF protection
5. **No Token Management:** No need to store/refresh tokens in frontend

## Disadvantages

1. **CORS Complexity:** Requires proper CORS configuration
2. **Mobile Apps:** More complex for native mobile apps
3. **Third-party APIs:** Not suitable for third-party API access
4. **Subdomain Issues:** Requires careful domain configuration

## When to Use Tokens Instead

- Building a public API
- Native mobile applications
- Third-party integrations
- Microservices architecture
- When frontend and backend are on completely different domains
