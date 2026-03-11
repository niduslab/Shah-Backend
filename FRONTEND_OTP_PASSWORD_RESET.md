# Frontend OTP Password Reset - Complete Implementation Guide

## 🚀 Quick Start (5 Minutes)

### What You'll Build
A modern 3-step password reset flow:
1. User enters email → Receives OTP
2. User enters OTP → Verifies code
3. User sets new password → Done!

### API Base URL
```javascript
const API_BASE = 'http://localhost:8000/api/auth';
```

### Three Endpoints You Need
```javascript
POST /api/auth/send-otp           // Step 1: Send OTP
POST /api/auth/verify-otp         // Step 2: Verify OTP (optional)
POST /api/auth/reset-password-otp // Step 3: Reset Password
```

---

## 📱 Complete React Implementation

### 1. API Service (Create `services/authApi.js`)

```javascript
const API_BASE = 'http://localhost:8000/api/auth';

export const authApi = {
  // Send OTP to email
  sendOtp: async (email) => {
    const response = await fetch(`${API_BASE}/send-otp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // No CSRF token needed - endpoints are excluded from CSRF protection
      },
      body: JSON.stringify({ email }),
    });
    return response.json();
  },

  // Verify OTP code (optional step)
  verifyOtp: async (email, otp) => {
    const response = await fetch(`${API_BASE}/verify-otp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, otp }),
    });
    return response.json();
  },

  // Reset password with OTP
  resetPassword: async (email, otp, password, passwordConfirmation) => {
    const response = await fetch(`${API_BASE}/reset-password-otp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email,
        otp,
        password,
        password_confirmation: passwordConfirmation,
      }),
    });
    return response.json();
  },
};
```

### 2. OTP Input Component (Create `components/OtpInput.jsx`)

```jsx
import { useState, useRef, useEffect } from 'react';

const OtpInput = ({ length = 6, value, onChange, disabled = false }) => {
  const [otp, setOtp] = useState(new Array(length).fill(''));
  const inputRefs = useRef([]);

  useEffect(() => {
    if (value) {
      setOtp(value.split(''));
    }
  }, [value]);

  const handleChange = (element, index) => {
    if (isNaN(element.value)) return;

    const newOtp = [...otp];
    newOtp[index] = element.value;
    setOtp(newOtp);

    // Call parent onChange
    onChange(newOtp.join(''));

    // Focus next input
    if (element.value && index < length - 1) {
      inputRefs.current[index + 1].focus();
    }
  };

  const handleKeyDown = (e, index) => {
    // Handle backspace
    if (e.key === 'Backspace' && !otp[index] && index > 0) {
      inputRefs.current[index - 1].focus();
    }
  };

  const handlePaste = (e) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData('text').slice(0, length);
    const newOtp = pastedData.split('');
    setOtp(newOtp);
    onChange(newOtp.join(''));
    
    // Focus last filled input
    const lastIndex = Math.min(newOtp.length - 1, length - 1);
    inputRefs.current[lastIndex]?.focus();
  };

  return (
    <div className="otp-input-container">
      {otp.map((digit, index) => (
        <input
          key={index}
          ref={(ref) => (inputRefs.current[index] = ref)}
          type="text"
          inputMode="numeric"
          maxLength={1}
          value={digit}
          onChange={(e) => handleChange(e.target, index)}
          onKeyDown={(e) => handleKeyDown(e, index)}
          onPaste={handlePaste}
          disabled={disabled}
          className="otp-input"
          autoFocus={index === 0}
        />
      ))}
    </div>
  );
};

export default OtpInput;
```

### 3. Countdown Timer Component (Create `components/CountdownTimer.jsx`)

```jsx
import { useState, useEffect } from 'react';

const CountdownTimer = ({ initialSeconds = 300, onExpire, onResend }) => {
  const [seconds, setSeconds] = useState(initialSeconds);
  const [isExpired, setIsExpired] = useState(false);

  useEffect(() => {
    if (seconds <= 0) {
      setIsExpired(true);
      onExpire?.();
      return;
    }

    const timer = setInterval(() => {
      setSeconds((prev) => prev - 1);
    }, 1000);

    return () => clearInterval(timer);
  }, [seconds, onExpire]);

  const formatTime = (secs) => {
    const minutes = Math.floor(secs / 60);
    const remainingSeconds = secs % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  const handleResend = () => {
    setSeconds(initialSeconds);
    setIsExpired(false);
    onResend?.();
  };

  return (
    <div className="countdown-timer">
      {!isExpired ? (
        <p className="timer-text">
          Code expires in: <strong>{formatTime(seconds)}</strong>
        </p>
      ) : (
        <div className="timer-expired">
          <p>Code expired!</p>
          <button onClick={handleResend} className="resend-button">
            Resend OTP
          </button>
        </div>
      )}
    </div>
  );
};

export default CountdownTimer;
```

### 4. Complete Password Reset Flow (Create `pages/ForgotPassword.jsx`)

```jsx
import { useState } from 'react';
import { authApi } from '../services/authApi';
import OtpInput from '../components/OtpInput';
import CountdownTimer from '../components/CountdownTimer';

const ForgotPassword = () => {
  const [step, setStep] = useState(1); // 1: Email, 2: OTP, 3: Success
  const [email, setEmail] = useState('');
  const [otp, setOtp] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  // Step 1: Send OTP
  const handleSendOtp = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const data = await authApi.sendOtp(email);
      
      if (data.success) {
        setMessage(data.message);
        setStep(2);
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Failed to send OTP. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  // Step 2: Reset Password with OTP
  const handleResetPassword = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    // Validate password match
    if (password !== passwordConfirmation) {
      setError('Passwords do not match');
      setLoading(false);
      return;
    }

    // Validate password length
    if (password.length < 8) {
      setError('Password must be at least 8 characters');
      setLoading(false);
      return;
    }

    try {
      const data = await authApi.resetPassword(
        email,
        otp,
        password,
        passwordConfirmation
      );
      
      if (data.success) {
        setMessage(data.message);
        setStep(3);
        
        // Redirect to login after 3 seconds
        setTimeout(() => {
          window.location.href = '/login';
        }, 3000);
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Failed to reset password. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  // Resend OTP
  const handleResendOtp = async () => {
    setLoading(true);
    setError('');
    
    try {
      const data = await authApi.sendOtp(email);
      setMessage(data.message);
    } catch (err) {
      setError('Failed to resend OTP');
    } finally {
      setLoading(false);
    }
  };

  // Handle OTP expiration
  const handleOtpExpire = () => {
    setError('OTP has expired. Please request a new one.');
  };

  return (
    <div className="forgot-password-container">
      <div className="forgot-password-card">
        {/* Step 1: Enter Email */}
        {step === 1 && (
          <form onSubmit={handleSendOtp}>
            <h2>Forgot Password?</h2>
            <p>Enter your email to receive a verification code</p>
            
            <div className="form-group">
              <label htmlFor="email">Email Address</label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="your@email.com"
                required
                disabled={loading}
                className="form-input"
              />
            </div>

            {error && <div className="alert alert-error">{error}</div>}
            
            <button 
              type="submit" 
              disabled={loading}
              className="btn btn-primary"
            >
              {loading ? 'Sending...' : 'Send Verification Code'}
            </button>

            <div className="form-footer">
              <a href="/login">Back to Login</a>
            </div>
          </form>
        )}

        {/* Step 2: Enter OTP and New Password */}
        {step === 2 && (
          <form onSubmit={handleResetPassword}>
            <h2>Reset Password</h2>
            <p>Enter the 6-digit code sent to <strong>{email}</strong></p>
            
            <div className="form-group">
              <label>Verification Code</label>
              <OtpInput
                length={6}
                value={otp}
                onChange={setOtp}
                disabled={loading}
              />
            </div>

            <CountdownTimer
              initialSeconds={300}
              onExpire={handleOtpExpire}
              onResend={handleResendOtp}
            />

            <div className="form-group">
              <label htmlFor="password">New Password</label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="At least 8 characters"
                minLength={8}
                required
                disabled={loading}
                className="form-input"
              />
            </div>

            <div className="form-group">
              <label htmlFor="passwordConfirmation">Confirm Password</label>
              <input
                id="passwordConfirmation"
                type="password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
                placeholder="Re-enter password"
                minLength={8}
                required
                disabled={loading}
                className="form-input"
              />
            </div>

            {error && <div className="alert alert-error">{error}</div>}
            {message && <div className="alert alert-success">{message}</div>}
            
            <button 
              type="submit" 
              disabled={loading || otp.length !== 6}
              className="btn btn-primary"
            >
              {loading ? 'Resetting...' : 'Reset Password'}
            </button>

            <div className="form-footer">
              <button 
                type="button"
                onClick={() => setStep(1)}
                className="btn-link"
              >
                Change Email
              </button>
            </div>
          </form>
        )}

        {/* Step 3: Success */}
        {step === 3 && (
          <div className="success-message">
            <div className="success-icon">✓</div>
            <h2>Password Reset Successful!</h2>
            <p>Your password has been reset successfully.</p>
            <p>Redirecting to login page...</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default ForgotPassword;
```


### 5. Styling with Tailwind CSS

```css
/* Add to your global CSS or Tailwind config */

.forgot-password-container {
  @apply min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4;
}

.forgot-password-card {
  @apply max-w-md w-full bg-white rounded-lg shadow-lg p-8;
}

.form-group {
  @apply mb-4;
}

.form-group label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}

.form-input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.form-input:disabled {
  @apply bg-gray-100 cursor-not-allowed;
}

.btn {
  @apply w-full py-3 px-4 rounded-lg font-medium transition-colors;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed;
}

.btn-link {
  @apply text-blue-600 hover:text-blue-800 underline;
}

.alert {
  @apply p-3 rounded-lg mb-4;
}

.alert-error {
  @apply bg-red-50 text-red-800 border border-red-200;
}

.alert-success {
  @apply bg-green-50 text-green-800 border border-green-200;
}

.otp-input-container {
  @apply flex gap-2 justify-center;
}

.otp-input {
  @apply w-12 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200;
}

.countdown-timer {
  @apply text-center my-4;
}

.timer-text {
  @apply text-sm text-gray-600;
}

.timer-expired {
  @apply text-center;
}

.resend-button {
  @apply mt-2 text-blue-600 hover:text-blue-800 font-medium;
}

.success-message {
  @apply text-center;
}

.success-icon {
  @apply w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4;
}

.form-footer {
  @apply mt-4 text-center text-sm;
}
```

---

## 🎨 Vue 3 Implementation

### Complete Vue Component (Create `ForgotPassword.vue`)

```vue
<template>
  <div class="forgot-password-container">
    <div class="forgot-password-card">
      <!-- Step 1: Enter Email -->
      <form v-if="step === 1" @submit.prevent="handleSendOtp">
        <h2>Forgot Password?</h2>
        <p>Enter your email to receive a verification code</p>
        
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="your@email.com"
            required
            :disabled="loading"
            class="form-input"
          />
        </div>

        <div v-if="error" class="alert alert-error">{{ error }}</div>
        
        <button 
          type="submit" 
          :disabled="loading"
          class="btn btn-primary"
        >
          {{ loading ? 'Sending...' : 'Send Verification Code' }}
        </button>

        <div class="form-footer">
          <router-link to="/login">Back to Login</router-link>
        </div>
      </form>

      <!-- Step 2: Enter OTP and New Password -->
      <form v-else-if="step === 2" @submit.prevent="handleResetPassword">
        <h2>Reset Password</h2>
        <p>Enter the 6-digit code sent to <strong>{{ email }}</strong></p>
        
        <div class="form-group">
          <label>Verification Code</label>
          <OtpInput
            :length="6"
            v-model="otp"
            :disabled="loading"
          />
        </div>

        <CountdownTimer
          :initial-seconds="300"
          @expire="handleOtpExpire"
          @resend="handleResendOtp"
        />

        <div class="form-group">
          <label for="password">New Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="At least 8 characters"
            minlength="8"
            required
            :disabled="loading"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="passwordConfirmation">Confirm Password</label>
          <input
            id="passwordConfirmation"
            v-model="passwordConfirmation"
            type="password"
            placeholder="Re-enter password"
            minlength="8"
            required
            :disabled="loading"
            class="form-input"
          />
        </div>

        <div v-if="error" class="alert alert-error">{{ error }}</div>
        <div v-if="message" class="alert alert-success">{{ message }}</div>
        
        <button 
          type="submit" 
          :disabled="loading || otp.length !== 6"
          class="btn btn-primary"
        >
          {{ loading ? 'Resetting...' : 'Reset Password' }}
        </button>

        <div class="form-footer">
          <button 
            type="button"
            @click="step = 1"
            class="btn-link"
          >
            Change Email
          </button>
        </div>
      </form>

      <!-- Step 3: Success -->
      <div v-else-if="step === 3" class="success-message">
        <div class="success-icon">✓</div>
        <h2>Password Reset Successful!</h2>
        <p>Your password has been reset successfully.</p>
        <p>Redirecting to login page...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import OtpInput from '../components/OtpInput.vue';
import CountdownTimer from '../components/CountdownTimer.vue';
import { authApi } from '../services/authApi';

const router = useRouter();

const step = ref(1);
const email = ref('');
const otp = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const error = ref('');
const message = ref('');

const handleSendOtp = async () => {
  loading.value = true;
  error.value = '';

  try {
    const data = await authApi.sendOtp(email.value);
    
    if (data.success) {
      message.value = data.message;
      step.value = 2;
    } else {
      error.value = data.message;
    }
  } catch (err) {
    error.value = 'Failed to send OTP. Please try again.';
  } finally {
    loading.value = false;
  }
};

const handleResetPassword = async () => {
  loading.value = true;
  error.value = '';

  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match';
    loading.value = false;
    return;
  }

  if (password.value.length < 8) {
    error.value = 'Password must be at least 8 characters';
    loading.value = false;
    return;
  }

  try {
    const data = await authApi.resetPassword(
      email.value,
      otp.value,
      password.value,
      passwordConfirmation.value
    );
    
    if (data.success) {
      message.value = data.message;
      step.value = 3;
      
      setTimeout(() => {
        router.push('/login');
      }, 3000);
    } else {
      error.value = data.message;
    }
  } catch (err) {
    error.value = 'Failed to reset password. Please try again.';
  } finally {
    loading.value = false;
  }
};

const handleResendOtp = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const data = await authApi.sendOtp(email.value);
    message.value = data.message;
  } catch (err) {
    error.value = 'Failed to resend OTP';
  } finally {
    loading.value = false;
  }
};

const handleOtpExpire = () => {
  error.value = 'OTP has expired. Please request a new one.';
};
</script>
```


---

## 🌐 Vanilla JavaScript Implementation

### Simple HTML + JavaScript (No Framework)

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; background: #f5f5f5; }
    .container { max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 10px; color: #333; }
    p { margin-bottom: 20px; color: #666; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
    input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
    input:focus { outline: none; border-color: #4CAF50; }
    .otp-container { display: flex; gap: 10px; justify-content: center; }
    .otp-input { width: 50px; height: 50px; text-align: center; font-size: 24px; font-weight: bold; }
    button { width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    button:hover { background: #45a049; }
    button:disabled { background: #ccc; cursor: not-allowed; }
    .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef5350; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #66bb6a; }
    .timer { text-align: center; margin: 15px 0; color: #666; }
    .hidden { display: none; }
    .success-icon { width: 80px; height: 80px; background: #4CAF50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 48px; margin: 0 auto 20px; }
  </style>
</head>
<body>
  <div class="container">
    <!-- Step 1: Email -->
    <div id="step1">
      <h2>Forgot Password?</h2>
      <p>Enter your email to receive a verification code</p>
      <form id="emailForm">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" required placeholder="your@email.com">
        </div>
        <div id="emailError" class="alert alert-error hidden"></div>
        <button type="submit" id="sendOtpBtn">Send Verification Code</button>
      </form>
    </div>

    <!-- Step 2: OTP & Password -->
    <div id="step2" class="hidden">
      <h2>Reset Password</h2>
      <p>Enter the 6-digit code sent to <strong id="emailDisplay"></strong></p>
      <form id="resetForm">
        <div class="form-group">
          <label>Verification Code</label>
          <div class="otp-container">
            <input type="text" class="otp-input" maxlength="1" data-index="0">
            <input type="text" class="otp-input" maxlength="1" data-index="1">
            <input type="text" class="otp-input" maxlength="1" data-index="2">
            <input type="text" class="otp-input" maxlength="1" data-index="3">
            <input type="text" class="otp-input" maxlength="1" data-index="4">
            <input type="text" class="otp-input" maxlength="1" data-index="5">
          </div>
        </div>
        <div class="timer">
          Code expires in: <strong id="countdown">5:00</strong>
        </div>
        <div class="form-group">
          <label for="password">New Password</label>
          <input type="password" id="password" required minlength="8" placeholder="At least 8 characters">
        </div>
        <div class="form-group">
          <label for="passwordConfirm">Confirm Password</label>
          <input type="password" id="passwordConfirm" required minlength="8" placeholder="Re-enter password">
        </div>
        <div id="resetError" class="alert alert-error hidden"></div>
        <div id="resetSuccess" class="alert alert-success hidden"></div>
        <button type="submit" id="resetBtn">Reset Password</button>
      </form>
    </div>

    <!-- Step 3: Success -->
    <div id="step3" class="hidden">
      <div class="success-icon">✓</div>
      <h2 style="text-align: center;">Password Reset Successful!</h2>
      <p style="text-align: center;">Redirecting to login page...</p>
    </div>
  </div>

  <script>
    const API_BASE = 'http://localhost:8000/api/auth';
    let userEmail = '';
    let countdownInterval;

    // Step 1: Send OTP
    document.getElementById('emailForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const btn = document.getElementById('sendOtpBtn');
      const errorDiv = document.getElementById('emailError');
      
      btn.disabled = true;
      btn.textContent = 'Sending...';
      errorDiv.classList.add('hidden');

      try {
        const response = await fetch(`${API_BASE}/send-otp`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email }),
        });
        const data = await response.json();

        if (data.success) {
          userEmail = email;
          document.getElementById('emailDisplay').textContent = email;
          document.getElementById('step1').classList.add('hidden');
          document.getElementById('step2').classList.remove('hidden');
          startCountdown(300);
        } else {
          errorDiv.textContent = data.message;
          errorDiv.classList.remove('hidden');
        }
      } catch (error) {
        errorDiv.textContent = 'Failed to send OTP. Please try again.';
        errorDiv.classList.remove('hidden');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send Verification Code';
      }
    });

    // OTP Input handling
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, index) => {
      input.addEventListener('input', (e) => {
        if (e.target.value && index < 5) {
          otpInputs[index + 1].focus();
        }
      });
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
          otpInputs[index - 1].focus();
        }
      });
    });

    // Step 2: Reset Password
    document.getElementById('resetForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const otp = Array.from(otpInputs).map(input => input.value).join('');
      const password = document.getElementById('password').value;
      const passwordConfirm = document.getElementById('passwordConfirm').value;
      const btn = document.getElementById('resetBtn');
      const errorDiv = document.getElementById('resetError');
      const successDiv = document.getElementById('resetSuccess');

      errorDiv.classList.add('hidden');
      successDiv.classList.add('hidden');

      if (password !== passwordConfirm) {
        errorDiv.textContent = 'Passwords do not match';
        errorDiv.classList.remove('hidden');
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Resetting...';

      try {
        const response = await fetch(`${API_BASE}/reset-password-otp`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: userEmail,
            otp,
            password,
            password_confirmation: passwordConfirm,
          }),
        });
        const data = await response.json();

        if (data.success) {
          clearInterval(countdownInterval);
          document.getElementById('step2').classList.add('hidden');
          document.getElementById('step3').classList.remove('hidden');
          setTimeout(() => {
            window.location.href = '/login';
          }, 3000);
        } else {
          errorDiv.textContent = data.message;
          errorDiv.classList.remove('hidden');
        }
      } catch (error) {
        errorDiv.textContent = 'Failed to reset password. Please try again.';
        errorDiv.classList.remove('hidden');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Reset Password';
      }
    });

    // Countdown Timer
    function startCountdown(seconds) {
      let remaining = seconds;
      const countdownEl = document.getElementById('countdown');
      
      countdownInterval = setInterval(() => {
        const minutes = Math.floor(remaining / 60);
        const secs = remaining % 60;
        countdownEl.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;
        
        if (remaining <= 0) {
          clearInterval(countdownInterval);
          countdownEl.textContent = 'Expired';
        }
        remaining--;
      }, 1000);
    }
  </script>
</body>
</html>
```


---

## 📱 Mobile Optimization Tips

### 1. Auto-focus OTP Input
```javascript
// Auto-focus first input when step changes
useEffect(() => {
  if (step === 2) {
    inputRefs.current[0]?.focus();
  }
}, [step]);
```

### 2. Numeric Keyboard on Mobile
```jsx
<input
  type="text"
  inputMode="numeric"  // Shows numeric keyboard on mobile
  pattern="[0-9]*"     // iOS numeric keyboard
  maxLength={1}
/>
```

### 3. Prevent Zoom on Input Focus (iOS)
```css
input {
  font-size: 16px; /* Prevents iOS zoom */
}
```

### 4. Handle Paste from SMS (iOS)
```javascript
// iOS will auto-suggest OTP from SMS
input.addEventListener('paste', (e) => {
  const pastedData = e.clipboardData.getData('text');
  // Auto-fill all inputs
});
```

---

## 🔐 Security Best Practices

### 1. Rate Limiting
```javascript
// Add rate limiting to prevent abuse
let requestCount = 0;
const MAX_REQUESTS = 3;
const TIMEOUT = 60000; // 1 minute

const handleSendOtp = async () => {
  if (requestCount >= MAX_REQUESTS) {
    setError('Too many requests. Please try again later.');
    return;
  }
  
  requestCount++;
  setTimeout(() => requestCount--, TIMEOUT);
  
  // Send OTP...
};
```

### 2. Input Validation
```javascript
// Validate email format
const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

// Validate OTP format
const isValidOtp = (otp) => {
  return /^\d{6}$/.test(otp);
};

// Validate password strength
const isStrongPassword = (password) => {
  return password.length >= 8 &&
         /[A-Z]/.test(password) &&
         /[a-z]/.test(password) &&
         /[0-9]/.test(password);
};
```

### 3. Secure Password Display
```jsx
const [showPassword, setShowPassword] = useState(false);

<div className="password-input-wrapper">
  <input
    type={showPassword ? 'text' : 'password'}
    value={password}
    onChange={(e) => setPassword(e.target.value)}
  />
  <button
    type="button"
    onClick={() => setShowPassword(!showPassword)}
  >
    {showPassword ? '👁️' : '👁️‍🗨️'}
  </button>
</div>
```

---

## 🎯 Error Handling

### Common Errors and Solutions

```javascript
const ERROR_MESSAGES = {
  'Invalid OTP code.': 'The code you entered is incorrect. Please try again.',
  'OTP has expired.': 'Your code has expired. Click "Resend" to get a new one.',
  'OTP has already been used.': 'This code has already been used. Request a new one.',
  'Failed to send OTP.': 'Unable to send code. Please check your email and try again.',
  'User not found.': 'No account found with this email address.',
};

const getUserFriendlyError = (apiError) => {
  return ERROR_MESSAGES[apiError] || 'Something went wrong. Please try again.';
};
```

### Network Error Handling
```javascript
const handleApiCall = async (apiFunction) => {
  try {
    return await apiFunction();
  } catch (error) {
    if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
      throw new Error('Network error. Please check your connection.');
    }
    if (error.response?.status === 429) {
      throw new Error('Too many requests. Please wait a moment.');
    }
    throw error;
  }
};
```

---

## 🧪 Testing Checklist

### Functional Tests
- [ ] Email input accepts valid email formats
- [ ] OTP is sent successfully
- [ ] OTP input accepts only numbers
- [ ] OTP auto-advances to next input
- [ ] Backspace moves to previous input
- [ ] Paste functionality works
- [ ] Countdown timer displays correctly
- [ ] Countdown timer expires at 0:00
- [ ] Resend OTP button works
- [ ] Password validation works (min 8 chars)
- [ ] Password confirmation matches
- [ ] Success message displays
- [ ] Redirect to login works

### Error Handling Tests
- [ ] Invalid email shows error
- [ ] Wrong OTP shows error
- [ ] Expired OTP shows error
- [ ] Used OTP shows error
- [ ] Password mismatch shows error
- [ ] Weak password shows error
- [ ] Network error shows error

### UI/UX Tests
- [ ] Mobile responsive design
- [ ] Numeric keyboard on mobile
- [ ] Loading states display
- [ ] Buttons disable during loading
- [ ] Error messages are clear
- [ ] Success feedback is visible
- [ ] Auto-focus works correctly

---

## 🚀 Performance Optimization

### 1. Debounce API Calls
```javascript
import { debounce } from 'lodash';

const debouncedSendOtp = debounce(async (email) => {
  await authApi.sendOtp(email);
}, 500);
```

### 2. Lazy Load Components
```javascript
import { lazy, Suspense } from 'react';

const OtpInput = lazy(() => import('./components/OtpInput'));

<Suspense fallback={<div>Loading...</div>}>
  <OtpInput />
</Suspense>
```

### 3. Memoize Components
```javascript
import { memo } from 'react';

const OtpInput = memo(({ value, onChange }) => {
  // Component code
});
```

---

## 📊 Analytics Integration

### Track User Flow
```javascript
// Track when user requests OTP
const handleSendOtp = async () => {
  analytics.track('Password Reset - OTP Requested', {
    email: email,
    timestamp: new Date(),
  });
  
  // Send OTP...
};

// Track successful password reset
const handleResetPassword = async () => {
  // Reset password...
  
  if (success) {
    analytics.track('Password Reset - Completed', {
      email: email,
      timestamp: new Date(),
    });
  }
};
```

---

## 🌍 Internationalization (i18n)

### Multi-language Support
```javascript
const translations = {
  en: {
    title: 'Forgot Password?',
    emailLabel: 'Email Address',
    sendButton: 'Send Verification Code',
    otpLabel: 'Verification Code',
    passwordLabel: 'New Password',
    confirmLabel: 'Confirm Password',
    resetButton: 'Reset Password',
    successMessage: 'Password Reset Successful!',
  },
  es: {
    title: '¿Olvidaste tu contraseña?',
    emailLabel: 'Correo electrónico',
    sendButton: 'Enviar código de verificación',
    otpLabel: 'Código de verificación',
    passwordLabel: 'Nueva contraseña',
    confirmLabel: 'Confirmar contraseña',
    resetButton: 'Restablecer contraseña',
    successMessage: '¡Contraseña restablecida con éxito!',
  },
};

const t = (key) => translations[currentLanguage][key];
```

---

## 🎨 Accessibility (a11y)

### ARIA Labels and Roles
```jsx
<form role="form" aria-label="Password reset form">
  <label htmlFor="email">
    Email Address
    <span className="sr-only">(required)</span>
  </label>
  <input
    id="email"
    type="email"
    aria-required="true"
    aria-invalid={error ? 'true' : 'false'}
    aria-describedby={error ? 'email-error' : undefined}
  />
  {error && (
    <div id="email-error" role="alert" aria-live="polite">
      {error}
    </div>
  )}
</form>
```

### Keyboard Navigation
```javascript
// Ensure all interactive elements are keyboard accessible
<button
  onKeyDown={(e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      handleClick();
    }
  }}
>
  Submit
</button>
```

---

## 📝 API Response Examples

### Success Responses

**Send OTP Success:**
```json
{
  "success": true,
  "message": "OTP sent to your email. Valid for 5 minutes."
}
```

**Verify OTP Success:**
```json
{
  "success": true,
  "message": "OTP verified successfully."
}
```

**Reset Password Success:**
```json
{
  "success": true,
  "message": "Password reset successfully."
}
```

### Error Responses

**Invalid OTP:**
```json
{
  "success": false,
  "message": "Invalid OTP code."
}
```

**Expired OTP:**
```json
{
  "success": false,
  "message": "OTP has expired. Please request a new one."
}
```

**Validation Error:**
```json
{
  "success": false,
  "message": "The password field must be at least 8 characters.",
  "errors": {
    "password": ["The password field must be at least 8 characters."]
  }
}
```

---

## 🔧 Troubleshooting

### Issue: OTP not received
**Solutions:**
1. Check spam/junk folder
2. Verify email configuration in backend
3. Check email service logs
4. Test with different email provider

### Issue: OTP expired too quickly
**Solution:**
```php
// In OtpService.php, increase expiration time
'expires_at' => Carbon::now()->addMinutes(10), // Changed from 5 to 10
```

### Issue: CORS errors
**Solution:**
```php
// In config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

### Issue: Rate limiting too strict
**Solution:**
```php
// In routes/api.php
Route::post('send-otp', [AuthController::class, 'sendOtp'])
    ->middleware('throttle:5,1'); // 5 requests per minute instead of 3
```

---

## ✅ Production Checklist

Before deploying to production:

- [ ] Update API_BASE URL to production domain
- [ ] Enable HTTPS for all API calls
- [ ] Configure CORS properly
- [ ] Set up rate limiting
- [ ] Enable email logging
- [ ] Test on multiple devices
- [ ] Test on multiple browsers
- [ ] Add error tracking (Sentry, etc.)
- [ ] Add analytics tracking
- [ ] Test email delivery
- [ ] Verify OTP expiration works
- [ ] Test password validation
- [ ] Check mobile responsiveness
- [ ] Verify accessibility compliance
- [ ] Load test the API
- [ ] Set up monitoring alerts

---

## 🎉 Summary

You now have everything needed to implement OTP password reset:

✅ Complete React implementation with hooks
✅ Complete Vue 3 implementation
✅ Vanilla JavaScript implementation
✅ Reusable OTP input component
✅ Countdown timer component
✅ Mobile optimization
✅ Security best practices
✅ Error handling
✅ Accessibility features
✅ Testing checklist
✅ Production deployment guide

**Ready to integrate!** Start with the API service, add the components, and customize the styling to match your brand.

