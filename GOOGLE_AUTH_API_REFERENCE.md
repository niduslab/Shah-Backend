# Google OAuth API Quick Reference

## Endpoint

```
POST /api/auth/google/callback
```

## Request

### Headers
```
Content-Type: application/json
```

### Body
```json
{
  "token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjU5N..."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| token | string | Yes | Google ID token from frontend OAuth flow |

## Responses

### Success (200 OK)
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
      "phone": null,
      "google_id": "1234567890",
      "avatar": "https://lh3.googleusercontent.com/a/...",
      "provider": "google",
      "user_type": "customer",
      "status": true,
      "email_verified_at": "2024-03-09T10:30:00.000000Z",
      "created_at": "2024-03-09T10:30:00.000000Z",
      "updated_at": "2024-03-09T10:30:00.000000Z"
    }
  }
}
```

### Invalid Token (401 Unauthorized)
```json
{
  "success": false,
  "message": "Invalid Google token."
}
```

### Account Deactivated (403 Forbidden)
```json
{
  "success": false,
  "message": "Your account has been deactivated."
}
```

### Server Error (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Google authentication failed.",
  "error": "Error details..."
}
```

## Behavior

### New User
- Creates a new user account
- Sets `email_verified_at` to current timestamp
- Generates a random password
- Sets `user_type` to "customer"
- Sets `status` to true (active)

### Existing User (Same Email)
- Links Google account to existing user
- Updates `google_id` field
- Updates `provider` field
- Updates `avatar` if provided
- Verifies email if not already verified

### Existing Google User
- Logs in the user
- Updates avatar if changed
- Returns existing user data

## Frontend Implementation

### React with @react-oauth/google

```bash
npm install @react-oauth/google
```

```jsx
import { GoogleOAuthProvider, GoogleLogin } from '@react-oauth/google';

function App() {
  return (
    <GoogleOAuthProvider clientId="YOUR_GOOGLE_CLIENT_ID">
      <LoginComponent />
    </GoogleOAuthProvider>
  );
}

function LoginComponent() {
  const handleSuccess = async (credentialResponse) => {
    const response = await fetch('/api/auth/google/callback', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token: credentialResponse.credential })
    });
    
    const data = await response.json();
    if (data.success) {
      // Handle successful login
      localStorage.setItem('user', JSON.stringify(data.data.user));
      window.location.href = '/dashboard';
    }
  };

  return (
    <GoogleLogin
      onSuccess={handleSuccess}
      onError={() => console.log('Login Failed')}
    />
  );
}
```

### Vue 3 with vue3-google-login

```bash
npm install vue3-google-login
```

```vue
<template>
  <GoogleLogin :callback="handleLogin" />
</template>

<script setup>
import { GoogleLogin } from 'vue3-google-login'

const handleLogin = async (response) => {
  const result = await fetch('/api/auth/google/callback', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token: response.credential })
  })
  
  const data = await result.json()
  if (data.success) {
    // Handle successful login
  }
}
</script>
```

### Angular with angularx-social-login

```bash
npm install @abacritt/angularx-social-login
```

```typescript
import { SocialAuthService, GoogleLoginProvider } from '@abacritt/angularx-social-login';

export class LoginComponent {
  constructor(
    private authService: SocialAuthService,
    private http: HttpClient
  ) {}

  signInWithGoogle(): void {
    this.authService.signIn(GoogleLoginProvider.PROVIDER_ID).then(user => {
      this.http.post('/api/auth/google/callback', {
        token: user.idToken
      }).subscribe(response => {
        // Handle successful login
      });
    });
  }
}
```

## Testing with cURL

```bash
# Replace YOUR_GOOGLE_ID_TOKEN with actual token from Google
curl -X POST http://localhost:8000/api/auth/google/callback \
  -H "Content-Type: application/json" \
  -d '{
    "token": "YOUR_GOOGLE_ID_TOKEN"
  }'
```

## Notes

- The token expires after 1 hour
- Session cookies are set automatically
- CSRF protection is handled by Laravel
- The endpoint works with both new and existing users
- Email is automatically verified for OAuth users
