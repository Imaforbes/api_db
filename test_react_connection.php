<?php

/**
 * Test React Connection
 * Test if the React app can connect to the API
 */

// Set CORS headers for React app
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'API connection test successful',
    'timestamp' => date('c'),
    'server' => $_SERVER['SERVER_NAME'],
    'method' => $_SERVER['REQUEST_METHOD']
]);
