<?php

/**
 * Contact Form API Endpoint
 * Handles contact form submissions and message management
 */

// Set CORS headers FIRST, before any other output
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/response.php';
require_once '../utils/EmailSender.php';

// Also set CORS headers using the handler (as backup)
CorsHandler::setHeaders();

// Only allow POST requests for contact form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    // Test database connection first
    try {
        $db = Database::getInstance();
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        ApiResponse::serverError('Database connection failed. Please run the database setup first.');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Debug: Log the received input
    error_log("Contact form input: " . json_encode($input));

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    // Validate required fields
    $errors = [];

    if (empty($input['name'])) {
        $errors['name'] = 'Name is required';
    }

    if (empty($input['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!InputValidator::validateEmail($input['email'])) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($input['message'])) {
        $errors['message'] = 'Message is required';
    }

    if (!empty($errors)) {
        // Debug: Log validation errors
        error_log("Contact form validation errors: " . json_encode($errors));
        ApiResponse::validationError($errors);
    }

    // Sanitize input
    $name = InputValidator::sanitizeString($input['name'], 200);
    $email = InputValidator::sanitizeString($input['email'], 200);
    $message = InputValidator::sanitizeText($input['message'], 2000);

    // Additional validation
    if (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    }

    if (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters';
    }

    if (!empty($errors)) {
        // Debug: Log additional validation errors
        error_log("Contact form additional validation errors: " . json_encode($errors));
        ApiResponse::validationError($errors);
    }

    // Get client information
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // Insert message into database using the existing datos table
    try {
        // Use the existing datos table with Spanish column names
        $sql = "INSERT INTO datos (nombre, email, mensaje, fecha) VALUES (?, ?, ?, CURDATE())";
        $stmt = $db->query($sql, [$name, $email, $message]);

        error_log("Using existing datos table with columns: nombre, email, mensaje, fecha");

        if ($stmt->rowCount() > 0) {
            $messageId = $db->lastInsertId();

            // Log successful submission
            error_log("Contact form submitted: ID {$messageId}, Email: {$email}");

            // Send email notifications
            try {
                $emailSender = new EmailSender();

                // Send notification to you
                $emailSent = $emailSender->sendContactNotification($name, $email, $message, $ipAddress);

                if ($emailSent) {
                    error_log("Email notification sent successfully");
                } else {
                    error_log("Failed to send email notification");
                }

                // Send auto-reply to the sender
                $autoReplySent = $emailSender->sendAutoReply($name, $email);

                if ($autoReplySent) {
                    error_log("Auto-reply sent successfully to: {$email}");
                } else {
                    error_log("Failed to send auto-reply to: {$email}");
                }
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
                // Don't fail the entire request if email fails
            }

            ApiResponse::success([
                'id' => $messageId,
                'name' => $name,
                'email' => $email,
                'message' => $message,
                'created_at' => date('c')
            ], 'Message sent successfully!');
        } else {
            ApiResponse::serverError('Failed to save message');
        }
    } catch (Exception $e) {
        error_log("Database query failed: " . $e->getMessage());
        ApiResponse::serverError('Database error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred while processing your message');
}
