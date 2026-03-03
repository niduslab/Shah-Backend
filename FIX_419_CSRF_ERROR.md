# Fix 419 CSRF Token Mismatch Error

## The Problem

You're getting:
```
Status Code: 419
{"message": "CSRF token mismatch."}
```

When calling: `POST http://127.0.0.1:8000/api/auth/login`

## Why This Happens

Laravel Sanctum's HTTP-only cookie authentication requires CSRF protection. You MUST get a CSRF cookie before making any state-changing requests (POST, PUT, DELETE).

## The Solution

### Step 1: Update Your Frontend URL

Your React app is likely running on `localhost:3000` or `localhost:5173`, but you're calling the backend at `127.0.0.1:8000`. This causes cookie issues.

**Change your API base URL to use `localhost` instead of `127.0.0.1`:**

```javascript
// src/api/axios.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000',  // ← Use localhost, not 127.0.0.1
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

export default api;
```

### Step 2: Update Backend .env

Make sure your `.env` uses `localhost`:

```env
APP_URL=http://localhost:8000
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,localhost
```

### Step 3: Restart Laravel Server

After changing `.env`, restart your Laravel server:

```bash
# Stop the current server (Ctrl+C)
# Then start it on localhost
php artisan serve --host=localhost --port=8000
```

### Step 4: Get CSRF Cookie Before Login

Your frontend MUST call `/sanctum/csrf-cookie` before login:

```javascript
// src/services/authService.js
import api from '../api/axios';

const authService = {
  // CRITICAL: Call this BEFORE login/register
  async getCsrfCookie() {
    await api.get('/sanctum/csrf-cookie');
  },

  // Login
  async login(email, password) {
    // Get CSRF cookie first
    await this.getCsrfCookie();
    
    // Then login
    const response = await api.post('/api/auth/login', { email, password });
    return response.data;
  },

  // Register
  async register(data) {
    // Get CSRF cookie first
    await this.getCsrfCookie();
    
    // Then register
    const response = await api.post('/api/auth/register', data);
    return response.data;
  },
};

export default authService;
```

### Step 5: Clear Browser Cookies

Clear all cookies for `localhost` and `127.0.0.1` in your browser:

**Chrome/Edge:**
1. Open DevTools (F12)
2. Go to Application tab
3. Click Cookies
4. Delete all cookies for localhost and 127.0.0.1

**Firefox:**
1. Open DevTools (F12)
2. Go to Storage tab
3. Click Cookies
4. Delete all cookies

### Step 6: Test the Flow

```javascript
// Test in browser console or React component
import authService from './services/authService';

// This should work now
authService.login('user@example.com', 'password')
  .then(response => console.log('Success:', response))
  .catch(error => console.error('Error:', error));
```

## Complete Working Example

### Frontend: Login Component

```javascript
import { useState } from 'react';
import api from '../api/axios';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      // Step 1: Get CSRF cookie
      await api.get('/sanctum/csrf-cookie');
      
      // Step 2: Login
      const response = await api.post('/api/auth/login', {
        email,
        password,
      });
      
      console.log('Login successful:', response.data);
      // Redirect or update state
      
    } catch (err) {
      console.error('Login error:', err);
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Login</h2>
      {error && <div className="error">{error}</div>}
      
      <input
        type="email"
        placeholder="Email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
      />
      
      <input
        type="password"
        placeholder="Password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        required
      />
      
      <button type="submit" disabled={loading}>
        {loading ? 'Logging in...' : 'Login'}
      </button>
    </form>
  );
};

export default Login;
```

## Verification Checklist

✅ Backend `.env` uses `localhost` (not `127.0.0.1`)
✅ Frontend axios baseURL uses `localhost` (not `127.0.0.1`)
✅ `withCredentials: true` in axios config
✅ Laravel server running on `localhost:8000`
✅ React app running on `localhost:3000` or `localhost:5173`
✅ Call `/sanctum/csrf-cookie` BEFORE login/register
✅ Browser cookies cleared
✅ Not using incognito/private mode

## Testing with cURL

Test the flow with cURL to verify backend is working:

```bash
# Step 1: Get CSRF cookie
curl -X GET http://localhost:8000/sanctum/csrf-cookie \
  -H "Accept: application/json" \
  -c cookies.txt \
  -v

# Step 2: Login (extract XSRF-TOKEN from cookies.txt)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: YOUR_TOKEN_HERE" \
  -H "Referer: http://localhost:3000" \
  -b cookies.txt \
  -c cookies.txt \
  -d '{"email":"user@example.com","password":"password"}' \
  -v
```

## Common Mistakes

### ❌ Wrong: Using 127.0.0.1 and localhost mixed
```javascript
// Backend: http://127.0.0.1:8000
// Frontend: http://localhost:3000
// Result: Cookies won't work!
```

### ✅ Correct: Use localhost everywhere
```javascript
// Backend: http://localhost:8000
// Frontend: http://localhost:3000
// Result: Cookies work!
```

### ❌ Wrong: Not calling CSRF endpoint first
```javascript
// Direct login without CSRF cookie
await api.post('/api/auth/login', { email, password });
// Result: 419 error!
```

### ✅ Correct: Get CSRF cookie first
```javascript
// Get CSRF cookie first
await api.get('/sanctum/csrf-cookie');
// Then login
await api.post('/api/auth/login', { email, password });
// Result: Success!
```

### ❌ Wrong: Missing withCredentials
```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000',
  // Missing withCredentials!
});
```

### ✅ Correct: Include withCredentials
```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,  // ← Required!
});
```

## Still Not Working?

### Check Backend Logs

```bash
# In your Laravel project
tail -f storage/logs/laravel.log
```

### Check Browser Network Tab

1. Open DevTools (F12)
2. Go to Network tab
3. Make login request
4. Check:
   - Request Headers: Should include `Cookie` with session
   - Response Headers: Should include `Set-Cookie`
   - Cookies tab: Should show `XSRF-TOKEN` and session cookie

### Check Session Configuration

Verify your `.env`:

```env
SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,localhost
```

### Clear Laravel Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Production Setup

For production, update `.env`:

```env
APP_URL=https://api.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com,app.yourdomain.com
FRONTEND_URL=https://yourdomain.com
```

And update frontend:

```javascript
const api = axios.create({
  baseURL: 'https://api.yourdomain.com',
  withCredentials: true,
});
```

## Summary

The 419 error happens because:
1. You're not getting CSRF cookie first
2. You're mixing `localhost` and `127.0.0.1`
3. Cookies aren't being sent with requests

**Fix it by:**
1. Use `localhost` everywhere (not `127.0.0.1`)
2. Call `/sanctum/csrf-cookie` before login/register
3. Ensure `withCredentials: true` in axios
4. Clear browser cookies and restart servers

Your authentication should now work! 🎉
