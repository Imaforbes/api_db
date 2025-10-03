<?php

/**
 * Authentication Verification API Endpoint
 */

require_once '../../config/response.php';
require_once '../../auth/session.php';

// Set CORS headers
CorsHandler::setHeaders();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    if (SessionManager::isAuthenticated()) {
        $user = SessionManager::getCurrentUser();
        ApiResponse::success($user, 'User authenticated');
    } else {
        ApiResponse::unauthorized('Not authenticated');
    }
} catch (Exception $e) {
    error_log("Auth verification error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred during verification');
}
