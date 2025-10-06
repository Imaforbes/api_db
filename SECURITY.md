# 🔒 Security Guidelines for API_DB Project

## ⚠️ CRITICAL SECURITY MEASURES

### 🚫 NEVER COMMIT THESE FILES TO GITHUB:
- `config/database.php` - Contains database credentials
- `config/email.php` - Contains email server credentials
- `conexion.php` - Database connection file
- Any files with `.env` extension
- Any files containing passwords, API keys, or secrets

### 📁 Protected Files & Directories:
```
config/database.php          # Database credentials
config/email.php            # Email server settings
conexion.php               # Database connection
.env*                      # Environment variables
uploads/                   # User uploaded files
tmp/                       # Temporary files
cache/                     # Cache files
*.log                      # Log files
test_*.php                 # Test files
debug_*.php               # Debug files
```

### 🔐 Environment Setup:

1. **Database Configuration:**
   ```bash
   # Copy the template
   cp config/database.example.php config/database.php
   
   # Edit with your actual credentials
   nano config/database.php
   ```

2. **Email Configuration:**
   ```bash
   # Copy the template
   cp config/email.example.php config/email.php
   
   # Edit with your actual SMTP settings
   nano config/email.php
   ```

3. **Environment Variables:**
   ```bash
   # Create environment file
   touch .env
   
   # Add your secrets
   echo "DB_HOST=localhost" >> .env
   echo "DB_NAME=your_database" >> .env
   echo "DB_USER=your_username" >> .env
   echo "DB_PASS=your_password" >> .env
   ```

### 🛡️ Security Best Practices:

1. **File Permissions:**
   ```bash
   # Set secure permissions
   chmod 600 config/database.php
   chmod 600 config/email.php
   chmod 600 .env
   ```

2. **Database Security:**
   - Use strong passwords
   - Limit database user permissions
   - Enable SSL connections
   - Regular backups

3. **API Security:**
   - Validate all inputs
   - Use prepared statements
   - Implement rate limiting
   - Enable CORS properly
   - Use HTTPS in production

### 🚨 Before Pushing to GitHub:

1. **Check for sensitive data:**
   ```bash
   # Search for potential secrets
   grep -r "password\|secret\|key\|token" --include="*.php" .
   ```

2. **Verify .gitignore:**
   ```bash
   # Check ignored files
   git status --ignored
   ```

3. **Test without sensitive files:**
   ```bash
   # Ensure project works with example files
   cp config/database.example.php config/database.php
   cp config/email.example.php config/email.php
   ```

### 📋 Deployment Checklist:

- [ ] All sensitive files are in .gitignore
- [ ] Example files are provided
- [ ] No hardcoded credentials
- [ ] Environment variables are used
- [ ] File permissions are secure
- [ ] HTTPS is enabled in production
- [ ] Database connections are encrypted
- [ ] Error messages don't expose sensitive info

### 🆘 If You Accidentally Commit Sensitive Data:

1. **Immediately remove from history:**
   ```bash
   git filter-branch --force --index-filter \
   'git rm --cached --ignore-unmatch config/database.php' \
   --prune-empty --tag-name-filter cat -- --all
   ```

2. **Force push to update remote:**
   ```bash
   git push origin --force --all
   ```

3. **Change all passwords and keys immediately**

### 📞 Support:
If you need help with security setup, contact the development team.
