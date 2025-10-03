# Portfolio API Backend

A comprehensive REST API backend for the portfolio application with contact form management, project CRUD operations, and admin functionality.

## 🚀 Quick Start

### 1. Database Setup

```bash
# Run the database setup script
php setup.php
```

### 2. Test the API

```bash
# Run the API test suite
php test_api.php
```

### 3. Default Admin Credentials

- **Username:** `admin`
- **Password:** `admin123`

## 📁 Project Structure

```
api_db/
├── api/                    # API endpoints
│   ├── contact.php         # Contact form submission
│   ├── messages.php        # Message management (Admin)
│   ├── projects.php        # Project CRUD operations
│   ├── auth/               # Authentication endpoints
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── verify.php
│   ├── admin/              # Admin-only endpoints
│   │   └── stats.php
│   └── upload/             # File upload endpoints
│       ├── image.php
│       └── document.php
├── auth/                   # Authentication system
│   └── session.php
├── config/                 # Configuration files
│   ├── database.php
│   └── response.php
├── uploads/               # File upload directories
│   ├── images/
│   └── documents/
├── database_schema.sql    # Database schema
├── setup.php             # Database setup script
├── test_api.php          # API test suite
└── API_ENDPOINTS.md      # Complete API documentation
```

## 🛠️ Features

### Contact Form

- ✅ Secure form submission
- ✅ Input validation and sanitization
- ✅ Spam protection
- ✅ IP tracking and user agent logging

### Project Management

- ✅ CRUD operations for portfolio projects
- ✅ Featured project support
- ✅ Technology tags (JSON)
- ✅ Image and URL management
- ✅ Status management (draft, published, archived)

### Admin Dashboard

- ✅ Authentication system
- ✅ Session management
- ✅ Dashboard statistics
- ✅ Message management
- ✅ File upload system

### Security Features

- ✅ CORS protection
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Input validation
- ✅ File type validation
- ✅ Password hashing (bcrypt)

## 🔧 Configuration

### Database Configuration

Edit `config/database.php` to match your database settings:

```php
private $host = 'localhost';
private $username = 'root';
private $password = '';
private $database = 'portfolio';
```

### CORS Configuration

Update allowed origins in `config/response.php`:

```php
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'https://yourdomain.com'
];
```

## 📊 API Endpoints

### Public Endpoints

- `POST /api/contact.php` - Submit contact form
- `GET /api/projects.php` - Get published projects
- `GET /api/projects.php?id={id}` - Get specific project

### Admin Endpoints (Authentication Required)

- `GET /api/messages.php` - Get contact messages
- `PATCH /api/messages.php?id={id}` - Update message status
- `DELETE /api/messages.php?id={id}` - Delete message
- `GET /api/admin/stats.php` - Get dashboard statistics
- `POST /api/upload/image.php` - Upload image
- `POST /api/upload/document.php` - Upload document

### Authentication Endpoints

- `POST /api/auth/login.php` - Admin login
- `POST /api/auth/logout.php` - Admin logout
- `GET /api/auth/verify.php` - Verify authentication

## 🧪 Testing

### Run API Tests

```bash
php test_api.php
```

### Manual Testing

1. **Contact Form:**

   ```bash
   curl -X POST http://localhost/api_db/api/contact.php \
     -H "Content-Type: application/json" \
     -d '{"name":"Test","email":"test@example.com","message":"Hello"}'
   ```

2. **Admin Login:**
   ```bash
   curl -X POST http://localhost/api_db/api/auth/login.php \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"admin123"}'
   ```

## 🔒 Security Considerations

### Production Setup

1. **Change default admin password**
2. **Use HTTPS in production**
3. **Configure proper CORS origins**
4. **Set up file upload restrictions**
5. **Enable error logging**
6. **Use environment variables for sensitive data**

### File Upload Security

- Maximum file sizes: 5MB (images), 10MB (documents)
- Allowed image types: JPEG, PNG, GIF, WebP
- Allowed document types: PDF, DOC, DOCX, TXT
- Files are stored outside web root for security

## 📈 Performance

### Database Optimization

- Indexed columns for faster queries
- Prepared statements for security
- Connection pooling support
- Query optimization

### Caching Strategy

- Session-based authentication
- Database query optimization
- File upload caching

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**

   - Check database credentials in `config/database.php`
   - Ensure MySQL is running
   - Verify database exists

2. **CORS Errors**

   - Update allowed origins in `config/response.php`
   - Check frontend URL configuration

3. **File Upload Issues**

   - Check directory permissions
   - Verify upload size limits
   - Ensure upload directories exist

4. **Authentication Issues**
   - Verify admin user exists in database
   - Check session configuration
   - Clear browser cookies

### Debug Mode

Enable error logging by setting:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📚 Documentation

- **Complete API Documentation:** [API_ENDPOINTS.md](API_ENDPOINTS.md)
- **Database Schema:** [database_schema.sql](database_schema.sql)
- **Frontend Integration:** See React portfolio project

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is part of the portfolio application. See the main project for license information.

## 🆘 Support

For issues and questions:

1. Check the troubleshooting section
2. Review the API documentation
3. Run the test suite
4. Check error logs

---

**🎉 Your Portfolio API is ready to power your portfolio application!**
