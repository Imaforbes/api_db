# Portfolio API Endpoints Documentation

## Overview

This document describes all available API endpoints for the portfolio application. The API follows RESTful conventions and returns JSON responses.

## Base URL

- Development: `http://localhost/api_db`
- Production: `https://your-domain.com/api_db`

## Authentication

Most admin endpoints require authentication. Include the session cookie in your requests after logging in.

## Response Format

All responses follow this format:

```json
{
  "success": true|false,
  "message": "Response message",
  "data": {}, // Present when success is true
  "errors": {}, // Present when success is false and validation errors exist
  "timestamp": "2024-01-01T00:00:00+00:00",
  "status_code": 200
}
```

## Endpoints

### Contact Form

#### POST `/api/contact.php`

Submit a contact form message.

**Request Body:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "message": "Hello, I'm interested in your services."
}
```

**Response:**

```json
{
  "success": true,
  "message": "Message sent successfully!",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "message": "Hello, I'm interested in your services.",
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

### Projects

#### GET `/api/projects.php`

Get all published projects.

**Query Parameters:**

- `featured` (boolean): Filter featured projects only
- `limit` (integer): Number of projects to return (max 100)

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "E-commerce Website",
      "description": "Full-stack e-commerce solution...",
      "short_description": "Modern e-commerce platform",
      "image_url": "https://example.com/image.jpg",
      "technologies": ["React", "Node.js", "MongoDB"],
      "github_url": "https://github.com/user/project",
      "live_url": "https://project.example.com",
      "featured": true,
      "sort_order": 1,
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ]
}
```

#### GET `/api/projects.php?id={id}`

Get a specific project by ID.

#### POST `/api/projects.php` (Admin)

Create a new project.

**Request Body:**

```json
{
  "title": "Project Title",
  "description": "Detailed project description",
  "short_description": "Brief description",
  "image_url": "https://example.com/image.jpg",
  "technologies": ["React", "Node.js"],
  "github_url": "https://github.com/user/project",
  "live_url": "https://project.example.com",
  "featured": false,
  "status": "published",
  "sort_order": 1
}
```

#### PUT `/api/projects.php?id={id}` (Admin)

Update an existing project.

#### DELETE `/api/projects.php?id={id}` (Admin)

Delete a project.

### Messages Management (Admin)

#### GET `/api/messages.php` (Admin)

Get contact messages with pagination.

**Query Parameters:**

- `page` (integer): Page number (default: 1)
- `limit` (integer): Items per page (default: 10, max: 100)
- `status` (string): Filter by status (new, read, replied, archived)
- `search` (string): Search in name, email, or message

**Response:**

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "message": "Hello...",
        "status": "new",
        "created_at": "2024-01-01T00:00:00+00:00",
        "updated_at": "2024-01-01T00:00:00+00:00",
        "ip_address": "192.168.1.1"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 50,
      "items_per_page": 10,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

#### PATCH `/api/messages.php?id={id}` (Admin)

Update message status.

**Request Body:**

```json
{
  "status": "read"
}
```

#### DELETE `/api/messages.php?id={id}` (Admin)

Delete a message.

### Authentication

#### POST `/api/auth/login.php`

Admin login.

**Request Body:**

```json
{
  "username": "admin",
  "password": "password"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@portfolio.com",
    "role": "super_admin"
  }
}
```

#### POST `/api/auth/logout.php`

Admin logout.

#### GET `/api/auth/verify.php`

Verify authentication status.

### Admin Dashboard

#### GET `/api/admin/stats.php` (Admin)

Get dashboard statistics.

**Response:**

```json
{
  "success": true,
  "data": {
    "messages": {
      "total": 150,
      "by_status": {
        "new": 25,
        "read": 100,
        "replied": 20,
        "archived": 5
      },
      "last_30_days": 45,
      "last_7_days": 12
    },
    "projects": {
      "total": 10,
      "by_status": {
        "published": 8,
        "draft": 2
      },
      "featured": 3
    },
    "recent_messages": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "status": "new",
        "created_at": "2024-01-01T00:00:00+00:00"
      }
    ],
    "system": {
      "php_version": "8.1.0",
      "server_time": "2024-01-01T00:00:00+00:00",
      "database_status": "connected"
    }
  }
}
```

### File Upload

#### POST `/api/upload/image.php` (Admin)

Upload an image file.

**Request:** Multipart form data with `image` field.

**Response:**

```json
{
  "success": true,
  "data": {
    "filename": "unique_filename.jpg",
    "url": "https://example.com/uploads/images/unique_filename.jpg",
    "size": 1024000,
    "type": "image/jpeg"
  }
}
```

#### POST `/api/upload/document.php` (Admin)

Upload a document file.

**Request:** Multipart form data with `document` field.

**Response:**

```json
{
  "success": true,
  "data": {
    "filename": "unique_filename.pdf",
    "url": "https://example.com/uploads/documents/unique_filename.pdf",
    "size": 2048000,
    "type": "application/pdf",
    "extension": "pdf"
  }
}
```

## Error Codes

- `400` - Bad Request (invalid input)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `405` - Method Not Allowed
- `422` - Validation Error (invalid data)
- `500` - Internal Server Error

## Rate Limiting

- Contact form: 5 submissions per hour per IP
- Admin endpoints: No rate limiting (authenticated users)
- File uploads: 10MB max for images, 10MB max for documents

## Security Features

- CORS protection
- Input validation and sanitization
- SQL injection prevention (prepared statements)
- XSS protection
- File type validation
- Session-based authentication
- Password hashing (bcrypt)

## Database Schema

The API uses the following main tables:

- `contact_messages` - Contact form submissions
- `projects` - Portfolio projects
- `admin_users` - Admin user accounts
- `admin_sessions` - Active admin sessions
- `portfolio_settings` - Application settings
