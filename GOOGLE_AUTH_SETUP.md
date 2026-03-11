# Google OAuth Authentication Setup Guide

## Overview
This guide explains how to set up and use Google OAuth authentication in your Laravel application using Laravel Socialite.

## Installation Steps

### 1. Install Laravel Socialite
```bash
composer require laravel/socialite
```

✅ Already installed!

### 2. Run Database Migration
```bash
php artisan migrate
```

This will add the following fields to the `users` table:
- `google_id` - Stores the Google user ID
- `avatar` - Stores the user's profile picture URL
- `provider` - Stores the OAuth provider name (google)

### 3. Configure Environment Variables

Add the following to your `.env` file:

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 4. Get Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure the OAuth consent screen
6. Add authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback` (development)
   - `https://yourdomain.com/auth/google/callback` (production)
7. Copy the Client ID and Client Secret to your `.env` file

## API Endpoints

### Google Login
**POST** `/api/auth/google/callback`

**Request Body:**
```json
{
  "token": "google-id-token-from-frontend"
}
```

**Success Response (200):**
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
      "avatar": "https://lh3.googleusercontent.com/...",
      "provider": "google",
      "user_type": "customer",
      "status": true
    }
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid Google token."
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Your account has been deactivated."
}
```

## Frontend Integration

### Using Google Sign-In Button (React Example)

```javascript
import { GoogleLogin } from '@react-oauth/google';

function LoginPage() {
  const handleGoogleSuccess = async (credentialResponse) => {
    try {
      const response = await fetch('/api/auth/google/callback', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          token: credentialResponse.credential
        })
      });

      const data = await response.json();
      
      if (data.success) {
        // Store user data and redirect
        console.log('Login successful:', data.data.user);
      }
    } catch (error) {
      console.error('Login failed:', error);
    }
  };

  return (
    <GoogleLogin
      onSuccess={handleGoogleSuccess}
      onError={() => console.log('Login Failed')}
    />
  );
}
```

### Using Google Identity Services (Vanilla JS)

```html
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="YOUR_GOOGLE_CLIENT_ID"
     data-callback="handleCredentialResponse">
</div>
<div class="g_id_signin" data-type="standard"></div>

<script>
function handleCredentialResponse(response) {
  fetch('/api/auth/google/callback', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      token: response.credential
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      console.log('Login successful:', data.data.user);
      // Redirect or update UI
    }
  })
  .catch(error => console.error('Error:', error));
}
</script>
```

## How It Works

1. **User clicks "Sign in with Google"** on your frontend
2. **Google authenticates the user** and returns an ID token
3. **Frontend sends the token** to `/api/auth/google/callback`
4. **Backend verifies the token** using Laravel Socialite
5. **Backend checks if user exists:**
   - If user exists with this Google ID → Log them in
   - If user exists with this email → Link Google account and log in
   - If user doesn't exist → Create new account and log in
6. **Session is created** and user data is returned

## User Model Updates

The User model now includes:
- `google_id` - For linking Google accounts
- `avatar` - For storing profile pictures
- `provider` - For tracking OAuth provider

## Security Features

- Token verification with Google's servers
- Account deactivation check
- Email verification on OAuth registration
- Automatic password generation for OAuth users
- Session regeneration on login

## Testing

### Test with cURL:
```bash
curl -X POST http://localhost:8000/api/auth/google/callback \
  -H "Content-Type: application/json" \
  -d '{"token":"YOUR_GOOGLE_ID_TOKEN"}'
```

### Test Account Linking:
1. Create a regular account with email `test@example.com`
2. Sign in with Google using the same email
3. The Google account will be linked to the existing user

## Troubleshooting

### "Invalid Google token" Error
- Ensure your Google Client ID is correct in `.env`
- Check that the token hasn't expired (tokens expire after 1 hour)
- Verify the token was generated for your Client ID
- Make sure Laravel Socialite is properly configured

### "Account has been deactivated" Error
- Check the user's `status` field in the database
- Reactivate the account if needed

### Token Verification Fails
- Ensure `laravel/socialite` package is installed
- Check your internet connection (backend needs to reach Google's servers)
- Verify Google OAuth credentials in `config/services.php`
- Check that Google OAuth 2.0 is enabled in Google Cloud Console

## Additional Notes

- OAuth users have a random password generated automatically
- OAuth users are automatically verified (email_verified_at is set)
- Users can link multiple OAuth providers to the same account
- The system prevents duplicate accounts with the same email
