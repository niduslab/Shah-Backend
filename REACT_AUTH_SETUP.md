# React Frontend Setup for Laravel Sanctum HTTP-Only Cookies

## Important: CSRF Cookie Flow

For HTTP-only cookie authentication to work, you MUST follow this exact flow:

### Step 1: Get CSRF Cookie FIRST
Before any login/register request, you must call:
```javascript
await api.get('/sanctum/csrf-cookie');
```

This sets the XSRF-TOKEN cookie that Laravel needs for CSRF protection.

### Step 2: Then Login/Register
After getting the CSRF cookie, you can make authenticated requests.

## Backend Configuration Complete ✓

Your Laravel backend is now configured for HTTP-only cookie authentication with Sanctum.

## React Frontend Implementation

### 1. Install Axios (if not already installed)

```bash
npm install axios
```

### 2. Create API Client (`src/api/axios.js`)

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000',
  withCredentials: true, // CRITICAL: This enables cookies
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

export default api;
```

### 3. Authentication Service (`src/services/authService.js`)

```javascript
import api from '../api/axios';

const authService = {
  // Get CSRF cookie before login/register
  async getCsrfCookie() {
    await api.get('/sanctum/csrf-cookie');
  },

  // Register
  async register(data) {
    await this.getCsrfCookie();
    const response = await api.post('/api/auth/register', data);
    return response.data;
  },

  // Login
  async login(email, password) {
    await this.getCsrfCookie();
    const response = await api.post('/api/auth/login', { email, password });
    return response.data;
  },

  // Logout
  async logout() {
    const response = await api.post('/api/auth/logout');
    return response.data;
  },

  // Get authenticated user
  async getUser() {
    const response = await api.get('/api/auth/user');
    return response.data;
  },

  // Update profile
  async updateProfile(data) {
    const response = await api.put('/api/auth/profile', data);
    return response.data;
  },

  // Change password
  async changePassword(data) {
    const response = await api.put('/api/auth/password', data);
    return response.data;
  },
};

export default authService;
```

### 4. Auth Context (`src/context/AuthContext.jsx`)

```javascript
import { createContext, useContext, useState, useEffect } from 'react';
import authService from '../services/authService';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const response = await authService.getUser();
      setUser(response.data);
    } catch (error) {
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    const response = await authService.login(email, password);
    setUser(response.data.user);
    return response;
  };

  const register = async (data) => {
    const response = await authService.register(data);
    setUser(response.data.user);
    return response;
  };

  const logout = async () => {
    await authService.logout();
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, login, register, logout, loading, checkAuth }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

### 5. Login Component Example (`src/components/Login.jsx`)

```javascript
import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await login(email, password);
      navigate('/dashboard');
    } catch (err) {
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

### 6. Protected Route Component (`src/components/ProtectedRoute.jsx`)

```javascript
import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const ProtectedRoute = ({ children }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return children;
};

export default ProtectedRoute;
```

### 7. App Setup (`src/App.jsx`)

```javascript
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import Login from './components/Login';
import Dashboard from './components/Dashboard';
import ProtectedRoute from './components/ProtectedRoute';

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <Dashboard />
              </ProtectedRoute>
            }
          />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}

export default App;
```

### 8. Making Authenticated API Calls

```javascript
import api from '../api/axios';

// Example: Fetch orders
const getOrders = async () => {
  try {
    const response = await api.get('/api/orders');
    return response.data;
  } catch (error) {
    console.error('Error fetching orders:', error);
    throw error;
  }
};

// Example: Add to cart
const addToCart = async (productId, quantity) => {
  try {
    const response = await api.post('/api/cart', {
      product_id: productId,
      quantity: quantity,
    });
    return response.data;
  } catch (error) {
    console.error('Error adding to cart:', error);
    throw error;
  }
};
```

## Important Notes

### CORS Configuration
- Your backend is configured to accept requests from `localhost:3000` and `localhost:5173`
- If your React app runs on a different port, update `FRONTEND_URL` in `.env`

### Session Configuration
- Sessions use cookies (not file storage)
- Cookies are HTTP-only (JavaScript cannot access them)
- CSRF protection is enabled automatically

### Development vs Production

For production, update your `.env`:

```env
SESSION_DOMAIN=yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SESSION_SECURE_COOKIE=true
FRONTEND_URL=https://yourdomain.com
APP_URL=https://api.yourdomain.com
```

### Testing the Setup

1. Start Laravel backend:
```bash
php artisan serve
```

2. Start React frontend:
```bash
npm run dev
```

3. Test login flow:
   - CSRF cookie is fetched automatically
   - Login creates a session cookie
   - All subsequent requests include the cookie automatically
   - No need to manually manage tokens

### Troubleshooting

**Issue: CORS errors**
- Ensure `withCredentials: true` in axios config
- Check `FRONTEND_URL` matches your React app URL
- Verify `supports_credentials: true` in `config/cors.php`
- Make sure both frontend and backend use same domain (localhost)

**Issue: 419 CSRF token mismatch**
- **CRITICAL**: You MUST call `/sanctum/csrf-cookie` BEFORE login/register
- Ensure cookies are being sent (inspect Network tab in browser)
- Check that `withCredentials: true` is set in axios
- Verify `SESSION_DOMAIN` in `.env` matches your domain
- Clear browser cookies and try again

**Issue: Session not persisting**
- Verify `SESSION_DOMAIN` is correct (use `localhost` not `127.0.0.1` for local dev)
- Check browser allows cookies (not in incognito/private mode)
- Ensure both frontend and backend use same domain (localhost)
- Check `SESSION_DRIVER=cookie` in `.env`

**Issue: 401 Unauthorized on protected routes**
- Check that user is logged in
- Verify `auth:sanctum` middleware is applied
- Ensure cookies are being sent with requests
- Try logging out and logging in again
