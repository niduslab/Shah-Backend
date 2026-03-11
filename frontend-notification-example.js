/**
 * Frontend Notification Integration Example
 * 
 * This file demonstrates how to integrate real-time notifications
 * in your React/Vue/Angular frontend application.
 */

// ============================================
// 1. INSTALLATION
// ============================================
// npm install pusher-js laravel-echo

// ============================================
// 2. ECHO CONFIGURATION
// ============================================

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialize Echo
const initializeEcho = (authToken) => {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.REACT_APP_PUSHER_KEY || 'a0b93b5b3a7936dfac19',
        cluster: process.env.REACT_APP_PUSHER_CLUSTER || 'ap2',
        forceTLS: true,
        authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
        auth: {
            headers: {
                Authorization: `Bearer ${authToken}`,
                Accept: 'application/json',
            },
        },
    });
};

// ============================================
// 3. REACT HOOKS EXAMPLE
// ============================================

import { useEffect, useState } from 'react';
import { toast } from 'react-toastify';

// Custom hook for notifications
export const useNotifications = (userId, isAdmin = false) => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        if (!userId) return;

        // Subscribe to user's private channel
        const userChannel = window.Echo.private(`user.${userId}`);
        
        userChannel.notification((notification) => {
            console.log('Notification received:', notification);
            
            // Add to notifications list
            setNotifications(prev => [notification, ...prev]);
            setUnreadCount(prev => prev + 1);
            
            // Show toast notification
            toast.info(notification.message, {
                onClick: () => {
                    // Navigate to action URL
                    window.location.href = notification.action_url;
                }
            });
            
            // Play sound for important notifications
            if (['order_confirmed', 'order_status_changed'].includes(notification.type)) {
                playNotificationSound();
            }
        });

        // If admin, also subscribe to admin channel
        if (isAdmin) {
            const adminChannel = window.Echo.private('admin');
            
            adminChannel.notification((notification) => {
                console.log('Admin notification:', notification);
                
                setNotifications(prev => [notification, ...prev]);
                setUnreadCount(prev => prev + 1);
                
                // Show different toast for admin notifications
                toast.warning(notification.message, {
                    autoClose: false, // Don't auto-close for admins
                    onClick: () => {
                        window.location.href = notification.action_url;
                    }
                });
                
                // Play sound for new orders
                if (notification.type === 'new_order') {
                    playNotificationSound();
                    // Show desktop notification
                    showDesktopNotification(notification);
                }
            });
        }

        // Cleanup
        return () => {
            userChannel.stopListening('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated');
            if (isAdmin) {
                window.Echo.leave('admin');
            }
        };
    }, [userId, isAdmin]);

    const markAsRead = async (notificationId) => {
        try {
            await fetch(`/api/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
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
            await fetch('/api/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                },
            });
            
            setNotifications(prev => 
                prev.map(n => ({ ...n, read_at: new Date() }))
            );
            setUnreadCount(0);
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    };

    return {
        notifications,
        unreadCount,
        markAsRead,
        markAllAsRead,
    };
};

// ============================================
// 4. REACT COMPONENT EXAMPLE
// ============================================

import React from 'react';
import { Bell } from 'lucide-react';

const NotificationBell = ({ userId, isAdmin }) => {
    const { notifications, unreadCount, markAsRead, markAllAsRead } = useNotifications(userId, isAdmin);
    const [showDropdown, setShowDropdown] = useState(false);

    const handleNotificationClick = (notification) => {
        markAsRead(notification.id);
        window.location.href = notification.action_url;
    };

    return (
        <div className="relative">
            <button 
                onClick={() => setShowDropdown(!showDropdown)}
                className="relative p-2 rounded-full hover:bg-gray-100"
            >
                <Bell size={24} />
                {unreadCount > 0 && (
                    <span className="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            {showDropdown && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50">
                    <div className="p-4 border-b flex justify-between items-center">
                        <h3 className="font-semibold">Notifications</h3>
                        {unreadCount > 0 && (
                            <button 
                                onClick={markAllAsRead}
                                className="text-sm text-blue-600 hover:underline"
                            >
                                Mark all as read
                            </button>
                        )}
                    </div>
                    
                    <div className="max-h-96 overflow-y-auto">
                        {notifications.length === 0 ? (
                            <div className="p-4 text-center text-gray-500">
                                No notifications
                            </div>
                        ) : (
                            notifications.map((notification) => (
                                <div
                                    key={notification.id}
                                    onClick={() => handleNotificationClick(notification)}
                                    className={`p-4 border-b cursor-pointer hover:bg-gray-50 ${
                                        !notification.read_at ? 'bg-blue-50' : ''
                                    }`}
                                >
                                    <div className="flex items-start">
                                        <div className="flex-1">
                                            <p className="font-medium text-sm">{notification.data.title}</p>
                                            <p className="text-sm text-gray-600 mt-1">
                                                {notification.data.message}
                                            </p>
                                            <p className="text-xs text-gray-400 mt-1">
                                                {new Date(notification.created_at).toLocaleString()}
                                            </p>
                                        </div>
                                        {!notification.read_at && (
                                            <div className="w-2 h-2 bg-blue-500 rounded-full ml-2 mt-1"></div>
                                        )}
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

// ============================================
// 5. VUE COMPONENT EXAMPLE
// ============================================

/*
<template>
  <div class="notification-bell">
    <button @click="toggleDropdown" class="relative">
      <bell-icon />
      <span v-if="unreadCount > 0" class="badge">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <div v-if="showDropdown" class="dropdown">
      <div class="header">
        <h3>Notifications</h3>
        <button v-if="unreadCount > 0" @click="markAllAsRead">
          Mark all as read
        </button>
      </div>

      <div class="notifications-list">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          :class="['notification-item', { unread: !notification.read_at }]"
        >
          <p class="title">{{ notification.data.title }}</p>
          <p class="message">{{ notification.data.message }}</p>
          <p class="time">{{ formatTime(notification.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotificationBell',
  props: {
    userId: {
      type: Number,
      required: true
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      notifications: [],
      unreadCount: 0,
      showDropdown: false
    }
  },
  mounted() {
    this.subscribeToNotifications();
  },
  methods: {
    subscribeToNotifications() {
      // Subscribe to user channel
      Echo.private(`user.${this.userId}`)
        .notification((notification) => {
          this.notifications.unshift(notification);
          this.unreadCount++;
          this.$toast.info(notification.message);
        });

      // Subscribe to admin channel if admin
      if (this.isAdmin) {
        Echo.private('admin')
          .notification((notification) => {
            this.notifications.unshift(notification);
            this.unreadCount++;
            this.$toast.warning(notification.message);
            
            if (notification.type === 'new_order') {
              this.playSound();
            }
          });
      }
    },
    async markAsRead(notificationId) {
      await this.$axios.post(`/api/notifications/${notificationId}/mark-as-read`);
      const notification = this.notifications.find(n => n.id === notificationId);
      if (notification) {
        notification.read_at = new Date();
        this.unreadCount = Math.max(0, this.unreadCount - 1);
      }
    },
    async markAllAsRead() {
      await this.$axios.post('/api/notifications/mark-all-as-read');
      this.notifications.forEach(n => n.read_at = new Date());
      this.unreadCount = 0;
    },
    handleNotificationClick(notification) {
      this.markAsRead(notification.id);
      this.$router.push(notification.data.action_url);
    },
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    formatTime(time) {
      return new Date(time).toLocaleString();
    },
    playSound() {
      const audio = new Audio('/sounds/notification.mp3');
      audio.play();
    }
  }
}
</script>
*/

// ============================================
// 6. UTILITY FUNCTIONS
// ============================================

// Play notification sound
const playNotificationSound = () => {
    const audio = new Audio('/sounds/notification.mp3');
    audio.volume = 0.5;
    audio.play().catch(err => console.log('Audio play failed:', err));
};

// Show desktop notification
const showDesktopNotification = (notification) => {
    if (!('Notification' in window)) {
        return;
    }

    if (Notification.permission === 'granted') {
        new Notification(notification.title, {
            body: notification.message,
            icon: '/logo.png',
            badge: '/logo.png',
            tag: notification.type,
        });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showDesktopNotification(notification);
            }
        });
    }
};

// Request notification permission on app load
export const requestNotificationPermission = () => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
};

// ============================================
// 7. APP INITIALIZATION
// ============================================

// In your main App component or entry file:
/*
import { useEffect } from 'react';

function App() {
    useEffect(() => {
        // Get auth token from localStorage or state
        const token = localStorage.getItem('token');
        
        if (token) {
            // Initialize Echo
            initializeEcho(token);
            
            // Request notification permission
            requestNotificationPermission();
        }
    }, []);

    return (
        // Your app components
    );
}
*/

// ============================================
// 8. NOTIFICATION TYPES REFERENCE
// ============================================

const NOTIFICATION_TYPES = {
    // Customer notifications
    ORDER_CONFIRMED: 'order_confirmed',
    ORDER_STATUS_CHANGED: 'order_status_changed',
    ORDER_CANCELLED: 'order_cancelled',
    REVIEW_RESPONSE: 'review_response',
    FLASH_DEAL_STARTING: 'flash_deal_starting',
    
    // Admin notifications
    NEW_ORDER: 'new_order',
    NEW_REVIEW: 'new_review',
    LOW_STOCK: 'low_stock',
};

// Handle notification based on type
const handleNotificationByType = (notification) => {
    switch (notification.type) {
        case NOTIFICATION_TYPES.NEW_ORDER:
            // Play urgent sound
            playNotificationSound();
            // Show desktop notification
            showDesktopNotification(notification);
            // Update order count in UI
            break;
            
        case NOTIFICATION_TYPES.ORDER_STATUS_CHANGED:
            // Update order status in UI if on orders page
            break;
            
        case NOTIFICATION_TYPES.LOW_STOCK:
            // Show urgent alert
            toast.error(notification.message, { autoClose: false });
            break;
            
        default:
            // Default handling
            toast.info(notification.message);
    }
};

export {
    initializeEcho,
    useNotifications,
    NotificationBell,
    playNotificationSound,
    showDesktopNotification,
    requestNotificationPermission,
    handleNotificationByType,
    NOTIFICATION_TYPES,
};
