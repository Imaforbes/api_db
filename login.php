<?php

/**
 * Login API Endpoint
 * Handles admin authentication
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/response.php';

// Set CORS headers
CorsHandler::setHeaders();

// Start session
session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    // Get database connection
    $db = Database::getInstance();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    // Validate required fields
    $errors = [];

    if (empty($input['username'])) {
        $errors['username'] = 'Username is required';
    }

    if (empty($input['password'])) {
        $errors['password'] = 'Password is required';
    }

    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }

    $username = InputValidator::sanitizeString($input['username'], 100);
    $password = $input['password'];

    // Check if user exists and verify password
    $stmt = $db->query("SELECT password_hash FROM usuarios WHERE username = ?", [$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, create session
        session_regenerate_id(true);

        // Get user ID for session
        $userStmt = $db->query("SELECT id FROM usuarios WHERE username = ?", [$username]);
        $userData = $userStmt->fetch();

        // Set session variables that match SessionManager expectations
        $_SESSION['admin_user_id'] = $userData['id'];
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = 'admin';

        // Legacy session variables for backward compatibility
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;

        ApiResponse::success([
            'username' => $username,
            'logged_in' => true
        ], 'Login successful');
    } else {
        ApiResponse::error('Invalid credentials', 401);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred during login');
}
