<?php
/**
 * Debug Login - Minimal version to identify the 500 error
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Test 1: Check if required files exist
    $required_files = [
        'config/database.php',
        'config/response.php'
    ];
    
    $missing_files = [];
    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            $missing_files[] = $file;
        }
    }
    
    if (!empty($missing_files)) {
        throw new Exception("Missing required files: " . implode(', ', $missing_files));
    }
    
    // Test 2: Try to include required files
    require_once 'config/database.php';
    require_once 'config/response.php';
    
    // Test 3: Check if classes exist
    if (!class_exists('Database')) {
        throw new Exception("Database class not found");
    }
    
    if (!class_exists('ApiResponse')) {
        throw new Exception("ApiResponse class not found");
    }
    
    if (!class_exists('CorsHandler')) {
        throw new Exception("CorsHandler class not found");
    }
    
    if (!class_exists('InputValidator')) {
        throw new Exception("InputValidator class not found");
    }
    
    // Test 4: Try to get database connection
    $db = Database::getInstance();
    
    // Test 5: Check if usuarios table exists
    $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        throw new Exception("usuarios table does not exist");
    }
    
    // Test 6: Check if there are any users
    $stmt = $db->query("SELECT COUNT(*) as count FROM usuarios");
    $user_count = $stmt->fetch()['count'];
    
    // If we get here, everything is working
    echo json_encode([
        'success' => true,
        'message' => 'All systems working',
        'debug_info' => [
            'files_exist' => true,
            'classes_loaded' => true,
            'database_connected' => true,
            'usuarios_table_exists' => true,
            'user_count' => $user_count,
            'php_version' => PHP_VERSION,
            'session_status' => session_status()
        ]
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Login debug error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Debug error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
