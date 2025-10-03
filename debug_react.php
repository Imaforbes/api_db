<?php

/**
 * Debug React Connection
 * Simple endpoint for React app to test connection
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Test database connection
    require_once 'config/database.php';
    $db = Database::getInstance();

    // Test session
    session_start();

    // Test admin user
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $count = $stmt->fetch()['total'];

    // Test datos table
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $messageCount = $stmt->fetch()['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Debug information',
        'data' => [
            'database_connected' => true,
            'admin_users' => $count,
            'messages_count' => $messageCount,
            'session_id' => session_id(),
            'user_logged_in' => isset($_SESSION['user_logged_in']) ? $_SESSION['user_logged_in'] : false,
            'timestamp' => date('c')
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Debug failed: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
