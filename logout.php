<?php

/**
 * Logout API Endpoint
 * Handles admin logout
 */

require_once 'config/response.php';

// Set CORS headers
CorsHandler::setHeaders();

// Start session
session_start();

// Destroy session
session_unset();
session_destroy();

ApiResponse::success(null, 'Logout successful');
