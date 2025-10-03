<?php

/**
 * Admin Logout API Endpoint
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

require_once '../../config/response.php';
require_once '../../auth/session.php';

// Also set CORS headers using the handler (as backup)
CorsHandler::setHeaders();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    SessionManager::logout();
    ApiResponse::success(null, 'Logout successful');
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred during logout');
}
