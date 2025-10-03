<?php

/**
 * Email Configuration for Hostinger SMTP
 * This file contains the SMTP settings for sending emails through Hostinger
 */

// Hostinger SMTP Configuration
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587); // Use 587 for TLS, 465 for SSL
define('SMTP_USERNAME', 'imanol@imaforbes.com'); // Your Hostinger email
define('SMTP_PASSWORD', 'q9*zb8hDXe3_5HN'); // You need to set this
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

// Email settings
define('FROM_EMAIL', 'imanol@imaforbes.com');
define('FROM_NAME', 'IMAFORBES Portfolio');

// Recipient email (where contact form submissions will be sent)
define('CONTACT_EMAIL', 'imanol@imaforbes.com');

// Auto-reply settings
define('AUTO_REPLY_ENABLED', true);
define('AUTO_REPLY_SUBJECT', 'Thank you for contacting IMAFORBES');

// Email template settings
define('COMPANY_NAME', 'IMAFORBES');
define('DEVELOPER_NAME', 'Imanol Pérez Arteaga');
define('RESPONSE_TIME', 'As soon as possible');
