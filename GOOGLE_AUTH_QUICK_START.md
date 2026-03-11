# Google OAuth Quick Start Checklist

## ✅ Completed Steps

- [x] Laravel Socialite installed
- [x] Database migration created
- [x] User model updated with OAuth fields
- [x] UserService updated with OAuth methods
- [x] AuthController updated with Google login
- [x] API route added
- [x] Services configuration updated

## 🔲 Remaining Steps

### 1. Run Database Migration
```bash
php artisan migrate
```

### 2. Get Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Go to "APIs & Services" → "Credentials"
4. Click "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure OAuth consent screen if prompted
6. Application type: "Web application"
7. Add Authorized redirect URIs:
   - Development: `http://localhost:8000/auth/google/callback`
   - Production: `https://yourdomain.com/auth/google/callback`
8. Copy Client ID and Client Secret

### 3. Update .env File

Add these lines to your `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id-here.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 4. Test the Endpoint

#### Option A: Using cURL
```bash
# Get a Google ID token from your frontend first, then:
curl -X POST http://localhost:8000/api/auth/google/callback \
  -H "Content-Type: application/json" \
  -d '{"token":"YOUR_GOOGLE_ID_TOKEN"}'
```

#### Option B: Using Postman
1. Method: POST
2. URL: `http://localhost:8000/api/auth/google/callback`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "token": "YOUR_GOOGLE_ID_TOKEN"
}
```

### 5. Frontend Integration

Choose your framework:

#### React
```bash
npm install @react-oauth/google
```

```jsx
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';

<GoogleOAuthProvider clientId="YOUR_GOOGLE_CLIENT_ID">
  <GoogleLogin
    onSuccess={async (response) => {
      const result = await fetch('/api/auth/google/callback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: response.credential })
      });
      const data = await result.json();
      console.log(data);
    }}
  />
</GoogleOAuthProvider>
```

#### Vue 3
```bash
npm install vue3-google-login
```

```vue
<script setup>
import { GoogleLogin } from 'vue3-google-login'

const handleLogin = async (response) => {
  const result = await fetch('/api/auth/google/callback', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token: response.credential })
  })
  const data = await result.json()
  console.log(data)
}
</script>

<template>
  <GoogleLogin :callback="handleLogin" />
</template>
```

#### Vanilla JavaScript
```html
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="YOUR_GOOGLE_CLIENT_ID"
     data-callback="handleCredentialResponse">
</div>
<div class="g_id_signin"></div>

<script>
function handleCredentialResponse(response) {
  fetch('/api/auth/google/callback', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token: response.credential })
  })
  .then(res => res.json())
  .then(data => console.log(data));
}
</script>
```

## 📝 API Endpoint

**POST** `/api/auth/google/callback`

**Request:**
```json
{
  "token": "google-id-token"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "google_id": "1234567890",
      "avatar": "https://...",
      "provider": "google"
    }
  }
}
```

## 🔍 Verification

After setup, verify:

1. ✅ Migration ran successfully
2. ✅ Google credentials in `.env`
3. ✅ Can receive Google ID token from frontend
4. ✅ Backend successfully verifies token
5. ✅ User is created/logged in
6. ✅ Session is established

## 📚 Additional Documentation

- Full setup guide: `GOOGLE_AUTH_SETUP.md`
- API reference: `GOOGLE_AUTH_API_REFERENCE.md`
- Implementation details: `GOOGLE_AUTH_IMPLEMENTATION_SUMMARY.md`

## 🆘 Common Issues

**Issue:** "Invalid Google token"
- **Fix:** Verify Client ID matches in frontend and backend

**Issue:** Dependency conflict
- **Fix:** Already resolved! Using Laravel Socialite instead

**Issue:** Token expired
- **Fix:** Google tokens expire after 1 hour, get a fresh token

**Issue:** CORS errors
- **Fix:** Configure CORS in `config/cors.php` for your frontend domain

## 🎉 You're Ready!

Once you complete the remaining steps, your users can login with Google!
