# Visitor Popup Feature Documentation

## Overview
This feature allows you to capture visitor information through a popup when they first visit your site. The system collects name (required), email (optional), and phone (optional).

## Database Schema

### Table: `visitor_popups`
- `id` - Primary key
- `name` - Visitor's name (required)
- `email` - Visitor's email (optional)
- `phone` - Visitor's phone number (optional)
- `ip_address` - Visitor's IP address (auto-captured)
- `user_agent` - Visitor's browser/device info (auto-captured)
- `submitted_at` - Timestamp of submission
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

## API Endpoints

### Public Endpoint

#### Submit Visitor Popup
```
POST /api/visitor-popup
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: optional, valid email format, max 255 characters
- `phone`: optional, string, max 20 characters

**Success Response (201):**
```json
{
  "success": true,
  "message": "Thank you for your submission!",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "submitted_at": "2026-03-24T04:30:00.000000Z",
    "created_at": "2026-03-24T04:30:00.000000Z",
    "updated_at": "2026-03-24T04:30:00.000000Z"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."]
  }
}
```

### Admin Endpoints (Requires Authentication & Admin Role)

#### Get All Visitor Submissions
```
GET /api/admin/visitor-popups
```

**Query Parameters:**
- `per_page` - Items per page (default: 15)
- `search` - Search by name, email, or phone
- `has_email` - Filter by email presence (true/false)
- `has_phone` - Filter by phone presence (true/false)
- `date_from` - Filter submissions from date (Y-m-d format)
- `date_to` - Filter submissions to date (Y-m-d format)

**Example:**
```
GET /api/admin/visitor-popups?per_page=20&search=john&has_email=true&date_from=2026-03-01
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "ip_address": "192.168.1.1",
        "user_agent": "Mozilla/5.0...",
        "submitted_at": "2026-03-24T04:30:00.000000Z",
        "created_at": "2026-03-24T04:30:00.000000Z",
        "updated_at": "2026-03-24T04:30:00.000000Z"
      }
    ],
    "first_page_url": "http://example.com/api/admin/visitor-popups?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://example.com/api/admin/visitor-popups?page=1",
    "next_page_url": null,
    "path": "http://example.com/api/admin/visitor-popups",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

#### Get Single Visitor Submission
```
GET /api/admin/visitor-popups/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "submitted_at": "2026-03-24T04:30:00.000000Z",
    "created_at": "2026-03-24T04:30:00.000000Z",
    "updated_at": "2026-03-24T04:30:00.000000Z"
  }
}
```

#### Get Statistics
```
GET /api/admin/visitor-popups/statistics
```

**Query Parameters:**
- `days` - Number of days for recent submissions (default: 30)

**Response:**
```json
{
  "success": true,
  "data": {
    "total_submissions": 150,
    "recent_submissions": 45,
    "with_email": 120,
    "with_phone": 80,
    "with_both": 60,
    "days": 30
  }
}
```

#### Export Visitor Submissions
```
GET /api/admin/visitor-popups/export
```

**Query Parameters:**
- `format` - Export format: csv or json (default: csv)
- `date_from` - Filter from date (Y-m-d format)
- `date_to` - Filter to date (Y-m-d format)

**Example:**
```
GET /api/admin/visitor-popups/export?format=csv&date_from=2026-03-01&date_to=2026-03-31
```

**Response:**
- CSV file download with columns: ID, Name, Email, Phone, IP Address, Submitted At

#### Delete Visitor Submission
```
DELETE /api/admin/visitor-popups/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Visitor submission deleted successfully"
}
```

## Model Scopes

The `VisitorPopup` model includes helpful query scopes:

```php
// Get submissions from last 30 days
VisitorPopup::recent(30)->get();

// Get submissions with email
VisitorPopup::withEmail()->get();

// Get submissions with phone
VisitorPopup::withPhone()->get();

// Combine scopes
VisitorPopup::recent(7)->withEmail()->withPhone()->get();
```

## Frontend Implementation Example

### Basic Popup Form
```javascript
// Example using fetch API
async function submitVisitorPopup(formData) {
  try {
    const response = await fetch('/api/visitor-popup', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: formData.name,
        email: formData.email || null,
        phone: formData.phone || null,
      }),
    });

    const data = await response.json();
    
    if (data.success) {
      console.log('Submission successful:', data.message);
      // Store in localStorage to prevent showing popup again
      localStorage.setItem('visitor_popup_submitted', 'true');
      // Close popup
      closePopup();
    } else {
      console.error('Validation errors:', data.errors);
    }
  } catch (error) {
    console.error('Submission error:', error);
  }
}

// Check if popup should be shown
function shouldShowPopup() {
  return !localStorage.getItem('visitor_popup_submitted');
}

// Show popup on page load
window.addEventListener('load', () => {
  if (shouldShowPopup()) {
    showPopup();
  }
});
```

### React Example
```jsx
import { useState, useEffect } from 'react';

function VisitorPopup() {
  const [isOpen, setIsOpen] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
  });
  const [errors, setErrors] = useState({});

  useEffect(() => {
    const hasSubmitted = localStorage.getItem('visitor_popup_submitted');
    if (!hasSubmitted) {
      setIsOpen(true);
    }
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await fetch('/api/visitor-popup', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.success) {
        localStorage.setItem('visitor_popup_submitted', 'true');
        setIsOpen(false);
      } else {
        setErrors(data.errors || {});
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="popup-overlay">
      <div className="popup-content">
        <h2>Welcome! Please tell us about yourself</h2>
        <form onSubmit={handleSubmit}>
          <div>
            <label>Name *</label>
            <input
              type="text"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              required
            />
            {errors.name && <span className="error">{errors.name[0]}</span>}
          </div>

          <div>
            <label>Email (Optional)</label>
            <input
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
            />
            {errors.email && <span className="error">{errors.email[0]}</span>}
          </div>

          <div>
            <label>Phone (Optional)</label>
            <input
              type="tel"
              value={formData.phone}
              onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
            />
            {errors.phone && <span className="error">{errors.phone[0]}</span>}
          </div>

          <button type="submit">Submit</button>
          <button type="button" onClick={() => setIsOpen(false)}>Skip</button>
        </form>
      </div>
    </div>
  );
}

export default VisitorPopup;
```

## Security Features

1. **IP Address Tracking**: Automatically captures visitor IP for analytics
2. **User Agent Tracking**: Records browser/device information
3. **Validation**: Server-side validation for all inputs
4. **Optional Fields**: Email and phone are optional to reduce friction
5. **Admin Only Access**: Statistics and management require admin authentication

## Best Practices

1. **Show Once**: Use localStorage to prevent showing popup multiple times
2. **Timing**: Consider showing popup after a few seconds or on exit intent
3. **Skip Option**: Always provide a way to close/skip the popup
4. **Privacy**: Add a privacy notice about data collection
5. **GDPR Compliance**: Include consent checkbox if required by your region

## Notes

- The popup submission is completely public (no authentication required)
- IP address and user agent are automatically captured
- Admin routes require authentication and admin role
- All timestamps are stored in UTC
- Export feature supports CSV format for easy data analysis
