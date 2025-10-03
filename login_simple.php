<?php

/**
 * Simple Login API
 * Simplified version with better error handling
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Start session
    session_start();

    // Get database connection
    require_once 'config/database.php';
    $db = Database::getInstance();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }

    // Validate required fields
    if (empty($input['username'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }

    if (empty($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        exit;
    }

    $username = trim($input['username']);
    $password = $input['password'];

    // Check if user exists and verify password
    $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, create session
        session_regenerate_id(true);
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'username' => $username,
                'logged_in' => true
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred during login: ' . $e->getMessage()]);
}
