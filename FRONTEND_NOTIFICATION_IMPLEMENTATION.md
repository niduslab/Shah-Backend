# Frontend Notification Implementation Guide

Complete guide for implementing real-time notifications in your React/Vue/Angular frontend.

## Table of Contents
1. [Installation](#installation)
2. [Configuration](#configuration)
3. [React Implementation](#react-implementation)
4. [Vue Implementation](#vue-implementation)
5. [API Integration](#api-integration)
6. [Styling](#styling)
7. [Testing](#testing)

## Installation

### Step 1: Install Dependencies

```bash
npm install pusher-js laravel-echo
# or
yarn add pusher-js laravel-echo
```

### Step 2: Install UI Dependencies (Optional)

```bash
# For toast notifications
npm install react-toastify
# or
npm install react-hot-toast

# For icons
npm install lucide-react
# or
npm install react-icons
```

## Configuration

### Create Echo Configuration File

Create `src/config/echo.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const initializeEcho = (authToken) => {
    if (window.Echo) {
        return window.Echo;
    }

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'a0b93b5b3a7936dfac19',
        cluster: 'ap2',
        forceTLS: true,
        authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
        auth: {
            headers: {
                Authorization: `Bearer ${authToken}`,
                Accept: 'application/json',
            },
        },
    });

    return window.Echo;
};

export const disconnectEcho = () => {
    if (window.Echo) {
        window.Echo.disconnect();
        window.Echo = null;
    }
};
```

## React Implementation

### 1. Create Notification Context

Create `src/contexts/NotificationContext.jsx`:

```javascript
import React, { createContext, useContext, useState, useEffect } from 'react';
import { initializeEcho, disconnectEcho } from '../config/echo';
import { toast } from 'react-toastify';

const NotificationContext = createContext();

export const useNotifications = () => {
    const context = useContext(NotificationContext);
    if (!context) {
        throw new Error('useNotifications must be used within NotificationProvider');
    }
    return context;
};

export const NotificationProvider = ({ children, user, token }) => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user || !token) {
            setLoading(false);
            return;
        }

        // Initialize Echo
        const echo = initializeEcho(token);

        // Subscribe to user's private channel
        const userChannel = echo.private(`user.${user.id}`);
        
        userChannel.notification((notification) => {
            console.log('Notification received:', notification);
            
            // Add to notifications list
            setNotifications(prev => [notification, ...prev]);
            setUnreadCount(prev => prev + 1);
            
            // Show toast
            toast.info(notification.message, {
                onClick: () => {
                    window.location.href = notification.action_url;
                }
            });
            
            // Play sound
            playNotificationSound();
        });

        // Subscribe to admin channel if user is admin
        if (user.user_type === 'admin') {
            const adminChannel = echo.private('admin');
            
            adminChannel.notification((notification) => {
                console.log('Admin notification:', notification);
                
                setNotifications(prev => [notification, ...prev]);
                setUnreadCount(prev => prev + 1);
                
                // Show different toast for admin
                toast.warning(notification.message, {
                    autoClose: false,
                    onClick: () => {
                        window.location.href = notification.action_url;
                    }
                });
                
                // Play sound for important notifications
                if (notification.type === 'new_order') {
                    playNotificationSound();
                    showDesktopNotification(notification);
                }
            });
        }

        // Fetch initial notifications
        fetchNotifications();

        // Cleanup
        return () => {
            disconnectEcho();
        };
    }, [user, token]);

    const fetchNotifications = async () => {
        try {
            const response = await fetch('http://127.0.0.1:8000/api/notifications', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            if (data.success) {
                setNotifications(data.data.data);
                const unread = data.data.data.filter(n => !n.read_at).length;
                setUnreadCount(unread);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        } finally {
            setLoading(false);
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            await fetch(`http://127.0.0.1:8000/api/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            setNotifications(prev => 
                prev.map(n => n.id === notificationId ? { ...n, read_at: new Date() } : n)
            );
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await fetch('http://127.0.0.1:8000/api/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            setNotifications(prev => prev.map(n => ({ ...n, read_at: new Date() })));
            setUnreadCount(0);
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    };

    const deleteNotification = async (notificationId) => {
        try {
            await fetch(`http://127.0.0.1:8000/api/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            setNotifications(prev => prev.filter(n => n.id !== notificationId));
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    };

    const clearAll = async () => {
        try {
            await fetch('http://127.0.0.1:8000/api/notifications/clear', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            setNotifications([]);
            setUnreadCount(0);
        } catch (error) {
            console.error('Error clearing notifications:', error);
        }
    };

    const value = {
        notifications,
        unreadCount,
        loading,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        clearAll,
        refreshNotifications: fetchNotifications,
    };

    return (
        <NotificationContext.Provider value={value}>
            {children}
        </NotificationContext.Provider>
    );
};

// Utility functions
const playNotificationSound = () => {
    const audio = new Audio('/sounds/notification.mp3');
    audio.volume = 0.5;
    audio.play().catch(err => console.log('Audio play failed:', err));
};

const showDesktopNotification = (notification) => {
    if (!('Notification' in window)) return;

    if (Notification.permission === 'granted') {
        new Notification(notification.title, {
            body: notification.message,
            icon: '/logo.png',
            badge: '/logo.png',
        });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showDesktopNotification(notification);
            }
        });
    }
};
```

### 2. Create Notification Bell Component

Create `src/components/NotificationBell.jsx`:

```javascript
import React, { useState, useRef, useEffect } from 'react';
import { Bell, X, Check, Trash2 } from 'lucide-react';
import { useNotifications } from '../contexts/NotificationContext';
import { formatDistanceToNow } from 'date-fns';

const NotificationBell = () => {
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);
    const {
        notifications,
        unreadCount,
        loading,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        clearAll,
    } = useNotifications();

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleNotificationClick = (notification) => {
        markAsRead(notification.id);
        window.location.href = notification.data.action_url;
        setShowDropdown(false);
    };

    const handleDelete = (e, notificationId) => {
        e.stopPropagation();
        deleteNotification(notificationId);
    };

    const getNotificationIcon = (type) => {
        const icons = {
            new_order: '🛒',
            order_confirmed: '✅',
            order_cancelled: '❌',
            order_status_changed: '📦',
            new_review: '⭐',
            review_response: '💬',
            low_stock: '⚠️',
            flash_deal_starting: '🔥',
        };
        return icons[type] || '🔔';
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                onClick={() => setShowDropdown(!showDropdown)}
                className="relative p-2 rounded-full hover:bg-gray-100 transition-colors"
                aria-label="Notifications"
            >
                <Bell size={24} className="text-gray-700" />
                {unreadCount > 0 && (
                    <span className="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            {showDropdown && (
                <div className="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[600px] flex flex-col">
                    {/* Header */}
                    <div className="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 className="font-semibold text-lg">Notifications</h3>
                        <div className="flex gap-2">
                            {unreadCount > 0 && (
                                <button
                                    onClick={markAllAsRead}
                                    className="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
                                >
                                    <Check size={16} />
                                    Mark all read
                                </button>
                            )}
                            {notifications.length > 0 && (
                                <button
                                    onClick={clearAll}
                                    className="text-sm text-red-600 hover:text-red-800 flex items-center gap-1"
                                >
                                    <Trash2 size={16} />
                                    Clear all
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Notifications List */}
                    <div className="overflow-y-auto flex-1">
                        {loading ? (
                            <div className="p-8 text-center text-gray-500">
                                Loading notifications...
                            </div>
                        ) : notifications.length === 0 ? (
                            <div className="p-8 text-center text-gray-500">
                                <Bell size={48} className="mx-auto mb-2 text-gray-300" />
                                <p>No notifications yet</p>
                            </div>
                        ) : (
                            notifications.map((notification) => (
                                <div
                                    key={notification.id}
                                    onClick={() => handleNotificationClick(notification)}
                                    className={`p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors ${
                                        !notification.read_at ? 'bg-blue-50' : ''
                                    }`}
                                >
                                    <div className="flex items-start gap-3">
                                        <span className="text-2xl flex-shrink-0">
                                            {getNotificationIcon(notification.data.type)}
                                        </span>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-2">
                                                <p className="font-medium text-sm text-gray-900">
                                                    {notification.data.title}
                                                </p>
                                                <button
                                                    onClick={(e) => handleDelete(e, notification.id)}
                                                    className="text-gray-400 hover:text-red-600 flex-shrink-0"
                                                >
                                                    <X size={16} />
                                                </button>
                                            </div>
                                            <p className="text-sm text-gray-600 mt-1">
                                                {notification.data.message}
                                            </p>
                                            <div className="flex items-center justify-between mt-2">
                                                <p className="text-xs text-gray-400">
                                                    {formatDistanceToNow(new Date(notification.created_at), {
                                                        addSuffix: true,
                                                    })}
                                                </p>
                                                {!notification.read_at && (
                                                    <span className="w-2 h-2 bg-blue-500 rounded-full"></span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default NotificationBell;
```

### 3. Integrate in App Component

Update `src/App.jsx`:

```javascript
import React, { useEffect } from 'react';
import { NotificationProvider } from './contexts/NotificationContext';
import NotificationBell from './components/NotificationBell';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

function App() {
    const user = /* get from your auth context */;
    const token = /* get from localStorage or auth context */;

    // Request notification permission on mount
    useEffect(() => {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }, []);

    return (
        <NotificationProvider user={user} token={token}>
            <div className="App">
                {/* Your app header */}
                <header className="flex items-center justify-between p-4">
                    <h1>Your App</h1>
                    {user && <NotificationBell />}
                </header>

                {/* Your app content */}
                <main>{/* Your routes and components */}</main>

                {/* Toast container for notifications */}
                <ToastContainer
                    position="top-right"
                    autoClose={5000}
                    hideProgressBar={false}
                    newestOnTop
                    closeOnClick
                    rtl={false}
                    pauseOnFocusLoss
                    draggable
                    pauseOnHover
                />
            </div>
        </NotificationProvider>
    );
}

export default App;
```

## Vue Implementation

### 1. Create Notification Composable

Create `src/composables/useNotifications.js`:

```javascript
import { ref, onMounted, onUnmounted } from 'vue';
import { initializeEcho, disconnectEcho } from '../config/echo';
import { useToast } from 'vue-toastification';

export function useNotifications(user, token) {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(true);
    const toast = useToast();

    const fetchNotifications = async () => {
        try {
            const response = await fetch('http://127.0.0.1:8000/api/notifications', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            if (data.success) {
                notifications.value = data.data.data;
                unreadCount.value = data.data.data.filter(n => !n.read_at).length;
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        } finally {
            loading.value = false;
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            await fetch(`http://127.0.0.1:8000/api/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            const notification = notifications.value.find(n => n.id === notificationId);
            if (notification) {
                notification.read_at = new Date();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await fetch('http://127.0.0.1:8000/api/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            notifications.value.forEach(n => n.read_at = new Date());
            unreadCount.value = 0;
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    };

    const deleteNotification = async (notificationId) => {
        try {
            await fetch(`http://127.0.0.1:8000/api/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });
            
            notifications.value = notifications.value.filter(n => n.id !== notificationId);
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    };

    onMounted(() => {
        if (!user || !token) return;

        const echo = initializeEcho(token);

        // Subscribe to user channel
        echo.private(`user.${user.id}`)
            .notification((notification) => {
                notifications.value.unshift(notification);
                unreadCount.value++;
                toast.info(notification.message);
            });

        // Subscribe to admin channel if admin
        if (user.user_type === 'admin') {
            echo.private('admin')
                .notification((notification) => {
                    notifications.value.unshift(notification);
                    unreadCount.value++;
                    toast.warning(notification.message);
                });
        }

        fetchNotifications();
    });

    onUnmounted(() => {
        disconnectEcho();
    });

    return {
        notifications,
        unreadCount,
        loading,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        fetchNotifications,
    };
}
```

### 2. Create Notification Bell Component

Create `src/components/NotificationBell.vue`:

```vue
<template>
  <div class="relative" ref="dropdownRef">
    <button
      @click="toggleDropdown"
      class="relative p-2 rounded-full hover:bg-gray-100 transition-colors"
    >
      <BellIcon class="w-6 h-6 text-gray-700" />
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[600px] flex flex-col"
    >
      <!-- Header -->
      <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-semibold text-lg">Notifications</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-sm text-blue-600 hover:text-blue-800"
        >
          Mark all read
        </button>
      </div>

      <!-- Notifications List -->
      <div class="overflow-y-auto flex-1">
        <div v-if="loading" class="p-8 text-center text-gray-500">
          Loading notifications...
        </div>
        <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
          <BellIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
          <p>No notifications yet</p>
        </div>
        <div
          v-else
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          :class="[
            'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
            !notification.read_at ? 'bg-blue-50' : ''
          ]"
        >
          <div class="flex items-start gap-3">
            <span class="text-2xl">{{ getNotificationIcon(notification.data.type) }}</span>
            <div class="flex-1">
              <p class="font-medium text-sm text-gray-900">{{ notification.data.title }}</p>
              <p class="text-sm text-gray-600 mt-1">{{ notification.data.message }}</p>
              <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-gray-400">{{ formatTime(notification.created_at) }}</p>
                <span v-if="!notification.read_at" class="w-2 h-2 bg-blue-500 rounded-full"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useNotifications } from '../composables/useNotifications';
import { formatDistanceToNow } from 'date-fns';
import BellIcon from './icons/BellIcon.vue';

const props = defineProps({
  user: Object,
  token: String,
});

const showDropdown = ref(false);
const dropdownRef = ref(null);

const {
  notifications,
  unreadCount,
  loading,
  markAsRead,
  markAllAsRead,
} = useNotifications(props.user, props.token);

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const handleNotificationClick = (notification) => {
  markAsRead(notification.id);
  window.location.href = notification.data.action_url;
  showDropdown.value = false;
};

const getNotificationIcon = (type) => {
  const icons = {
    new_order: '🛒',
    order_confirmed: '✅',
    order_cancelled: '❌',
    order_status_changed: '📦',
    new_review: '⭐',
    review_response: '💬',
    low_stock: '⚠️',
    flash_deal_starting: '🔥',
  };
  return icons[type] || '🔔';
};

const formatTime = (time) => {
  return formatDistanceToNow(new Date(time), { addSuffix: true });
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>
```

## API Integration

### Create API Service

Create `src/services/notificationService.js`:

```javascript
const API_BASE_URL = 'http://127.0.0.1:8000/api';

export const notificationService = {
    async getNotifications(token, page = 1) {
        const response = await fetch(`${API_BASE_URL}/notifications?page=${page}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
        return response.json();
    },

    async getUnreadCount(token) {
        const response = await fetch(`${API_BASE_URL}/notifications/unread-count`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
        return response.json();
    },

    async markAsRead(token, notificationId) {
        const response = await fetch(`${API_BASE_URL}/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
        return response.json();
    },

    async markAllAsRead(token) {
        const response = await fetch(`${API_BASE_URL}/notifications/mark-all-as-read`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
        return response.json();
    },

    async deleteNotification(token, notificationId) {
        const response = await fetch(`${API_BASE_URL}/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
        return response.json();
    },

    async clearAll(token) {
        const response = await fetch(`${API_BASE_URL}/notifications/clear`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
        return response.json();
    },
};
```

## Styling

### Tailwind CSS Configuration

Add to `tailwind.config.js`:

```javascript
module.exports = {
  theme: {
    extend: {
      animation: {
        'bounce-slow': 'bounce 3s infinite',
        'pulse-slow': 'pulse 3s infinite',
      },
    },
  },
};
```

### Custom CSS for Notifications

Create `src/styles/notifications.css`:

```css
/* Notification Bell Animation */
.notification-bell-ring {
    animation: ring 0.5s ease-in-out;
}

@keyframes ring {
    0%, 100% { transform: rotate(0deg); }
    10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
    20%, 40%, 60%, 80% { transform: rotate(10deg); }
}

/* Notification Badge Pulse */
.notification-badge-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Notification Item Slide In */
.notification-item-enter {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Toast Notification Styles */
.Toastify__toast--info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.Toastify__toast--warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.Toastify__toast--success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
```

## Testing

### Test Notification Reception

Create `src/tests/notifications.test.js`:

```javascript
import { render, screen, waitFor } from '@testing-library/react';
import { NotificationProvider } from '../contexts/NotificationContext';
import NotificationBell from '../components/NotificationBell';

describe('Notification System', () => {
    const mockUser = { id: 1, user_type: 'customer' };
    const mockToken = 'test-token';

    test('renders notification bell', () => {
        render(
            <NotificationProvider user={mockUser} token={mockToken}>
                <NotificationBell />
            </NotificationProvider>
        );
        
        expect(screen.getByLabelText('Notifications')).toBeInTheDocument();
    });

    test('displays unread count', async () => {
        render(
            <NotificationProvider user={mockUser} token={mockToken}>
                <NotificationBell />
            </NotificationProvider>
        );
        
        await waitFor(() => {
            const badge = screen.queryByText(/\d+/);
            expect(badge).toBeInTheDocument();
        });
    });
});
```

### Manual Testing Checklist

```markdown
## Frontend Testing Checklist

### Initial Setup
- [ ] Echo initialized successfully
- [ ] WebSocket connection established
- [ ] User channel subscribed
- [ ] Admin channel subscribed (if admin)

### Notification Reception
- [ ] Real-time notifications received
- [ ] Toast notifications displayed
- [ ] Notification bell badge updates
- [ ] Sound plays for notifications
- [ ] Desktop notifications work

### Notification Interactions
- [ ] Click notification navigates to correct URL
- [ ] Mark as read works
- [ ] Mark all as read works
- [ ] Delete notification works
- [ ] Clear all works

### UI/UX
- [ ] Dropdown opens/closes correctly
- [ ] Unread notifications highlighted
- [ ] Time formatting correct
- [ ] Icons display correctly
- [ ] Responsive on mobile

### Performance
- [ ] No memory leaks
- [ ] Smooth animations
- [ ] Fast notification delivery (<200ms)
- [ ] Efficient re-renders
```

## Troubleshooting

### Common Issues

#### 1. WebSocket Connection Failed

```javascript
// Check connection status
console.log(window.Echo.connector.pusher.connection.state);
// Should output: "connected"

// If failed, check:
// - Pusher credentials
// - CORS configuration
// - Bearer token validity
```

#### 2. Notifications Not Received

```javascript
// Test channel subscription
window.Echo.private(`user.${userId}`)
    .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (e) => {
        console.log('Raw event:', e);
    });
```

#### 3. Authentication Failed

```javascript
// Verify token is sent
console.log(window.Echo.connector.options.auth.headers);

// Check backend logs
// tail -f storage/logs/laravel.log
```

## Best Practices

### 1. Performance Optimization

```javascript
// Debounce notification updates
import { debounce } from 'lodash';

const debouncedUpdate = debounce((notification) => {
    setNotifications(prev => [notification, ...prev]);
}, 300);
```

### 2. Error Handling

```javascript
// Add error boundaries
class NotificationErrorBoundary extends React.Component {
    componentDidCatch(error, errorInfo) {
        console.error('Notification error:', error, errorInfo);
    }

    render() {
        return this.props.children;
    }
}
```

### 3. Accessibility

```javascript
// Add ARIA labels
<button
    aria-label={`${unreadCount} unread notifications`}
    aria-expanded={showDropdown}
>
    <Bell />
</button>
```

## Production Deployment

### Environment Variables

Create `.env.production`:

```env
REACT_APP_API_URL=https://your-api.com
REACT_APP_PUSHER_KEY=a0b93b5b3a7936dfac19
REACT_APP_PUSHER_CLUSTER=ap2
```

### Build Optimization

```javascript
// Lazy load notification components
const NotificationBell = lazy(() => import('./components/NotificationBell'));

// Use in app
<Suspense fallback={<div>Loading...</div>}>
    <NotificationBell />
</Suspense>
```

## Summary

You now have a complete frontend notification system with:

✅ Real-time WebSocket notifications via Pusher
✅ React and Vue implementations
✅ Notification bell component with dropdown
✅ Toast notifications
✅ Desktop notifications
✅ Complete API integration
✅ Responsive design
✅ Accessibility support
✅ Testing examples

### Quick Start

1. Install dependencies: `npm install pusher-js laravel-echo react-toastify`
2. Copy Echo configuration
3. Add NotificationProvider to your app
4. Add NotificationBell component to header
5. Test with backend: `php artisan notifications:test all`

### Next Steps

- Customize notification icons and colors
- Add notification preferences
- Implement notification grouping
- Add notification search/filter
- Create notification history page

---

**Need Help?** Check the main documentation in `README_NOTIFICATIONS.md`
