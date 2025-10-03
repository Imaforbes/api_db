<?php

/**
 * Adapted Contact Form API Endpoint
 * Works with existing datos table structure
 */

require_once '../config/database.php';
require_once '../config/response.php';

// Set CORS headers
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
        ApiResponse::serverError('Database connection failed. Please check your database configuration.');
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

    // First, let's check what columns exist in the datos table
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Debug: Log available columns
    error_log("Available columns in datos table: " . json_encode($columns));

    // Try to insert into datos table with available columns
    try {
        // Check if we have the right columns for contact form
        $hasName = in_array('name', $columns) || in_array('nombre', $columns);
        $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
        $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);

        if (!$hasName || !$hasEmail || !$hasMessage) {
            // If datos table doesn't have the right structure, create a simple contact_messages table
            $createTableSql = "CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                email VARCHAR(200) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT
            )";

            $db->query($createTableSql);
            error_log("Created contact_messages table for contact form");

            // Insert into the new contact_messages table
            $sql = "INSERT INTO contact_messages (name, email, message, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->query($sql, [$name, $email, $message, $ipAddress, $userAgent]);
        } else {
            // Use existing datos table with mapped column names
            $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
            $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
            $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');

            $sql = "INSERT INTO datos ($nameColumn, $emailColumn, $messageColumn) 
                    VALUES (?, ?, ?)";
            $stmt = $db->query($sql, [$name, $email, $message]);
        }

        if ($stmt->rowCount() > 0) {
            $messageId = $db->lastInsertId();

            // Log successful submission
            error_log("Contact form submitted: ID {$messageId}, Email: {$email}");

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
