<?php

/**
 * Email Configuration Template
 * 
 * ⚠️  SECURITY WARNING: This is a template file!
 * 
 * 1. Copy this file to 'email.php'
 * 2. Replace the placeholder values with your actual email settings
 * 3. NEVER commit the actual 'email.php' file to version control
 * 4. Add 'config/email.php' to your .gitignore file
 */

class EmailConfig {
    // SMTP Settings
    const SMTP_HOST = 'smtp.your-provider.com';    // Your SMTP host
    const SMTP_PORT = 587;                          // SMTP port (587 for TLS, 465 for SSL)
    const SMTP_USER = 'your-email@domain.com';     // Your email address
    const SMTP_PASS = 'your-email-password';       // Your email password
    const SMTP_SECURE = 'tls';                     // 'tls' or 'ssl'
    
    // Email settings
    const FROM_EMAIL = 'noreply@yourdomain.com';   // From email address
    const FROM_NAME = 'Your Portfolio';             // From name
    const REPLY_TO = 'contact@yourdomain.com';     // Reply-to email
    
    // Security settings
    const MAX_RECIPIENTS = 50;
    const RATE_LIMIT = 10; // emails per minute
    const MAX_ATTACHMENT_SIZE = 5242880; // 5MB in bytes
}
