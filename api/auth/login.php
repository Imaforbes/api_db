<?php

/**
 * Admin Login API Endpoint
 */

// Set CORS headers FIRST, before any other output
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:5175',
    'http://localhost:3000',
    'https://www.imaforbes.com',
    'https://imaforbes.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Fallback for production
    header("Access-Control-Allow-Origin: https://www.imaforbes.com");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../config/database.php';
require_once '../../config/response.php';
require_once '../../auth/session.php';

// Also set CORS headers using the handler (as backup)
CorsHandler::setHeaders();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

try {
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

    // Attempt login
    $user = SessionManager::login($username, $password);

    if ($user) {
        // Clean up expired sessions
        SessionManager::cleanupExpiredSessions();

        ApiResponse::success($user, 'Login successful');
    } else {
        ApiResponse::error('Invalid credentials', 401);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred during login');
}
