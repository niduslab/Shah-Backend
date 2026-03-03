# Quick Fix: 419 CSRF Token Mismatch

## The Issue
```
POST http://127.0.0.1:8000/api/auth/login
Status: 419
{"message": "CSRF token mismatch."}
```

## Quick Fix (3 Steps)

### 1. Update Backend .env
```env
APP_URL=http://localhost:8000
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,localhost
```

### 2. Restart Laravel on localhost
```bash
php artisan config:clear
php artisan serve --host=localhost --port=8000
```

### 3. Update Frontend to use localhost
```javascript
// src/api/axios.js
const api = axios.create({
  baseURL: 'http://localhost:8000',  // ← Change from 127.0.0.1
  withCredentials: true,
});
```

### 4. Get CSRF Cookie Before Login
```javascript
// MUST call this first!
await api.get('/sanctum/csrf-cookie');

// Then login
await api.post('/api/auth/login', { email, password });
```

### 5. Clear Browser Cookies
- Open DevTools (F12)
- Application/Storage tab → Cookies
- Delete all cookies for localhost and 127.0.0.1

## Why This Works

The issue happens because:
- ❌ Using `127.0.0.1` and `localhost` mixed → cookies don't work across different domains
- ❌ Not calling `/sanctum/csrf-cookie` first → no CSRF token
- ❌ Old cookies cached → stale session data

The fix:
- ✅ Use `localhost` everywhere → cookies work
- ✅ Call CSRF endpoint first → gets CSRF token
- ✅ Clear cookies → fresh start

## Test It

```javascript
// In browser console
fetch('http://localhost:8000/sanctum/csrf-cookie', {
  credentials: 'include'
})
.then(() => fetch('http://localhost:8000/api/auth/login', {
  method: 'POST',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password'
  })
}))
.then(r => r.json())
.then(console.log);
```

## Still Not Working?

See **FIX_419_CSRF_ERROR.md** for detailed troubleshooting.

## Backend Changes Made

✅ Fixed `app/Http/Kernel.php` - Cleaned up API middleware
✅ Updated `.env` - Set SESSION_DOMAIN=localhost
✅ AuthController already uses session-based auth

No other backend changes needed!
