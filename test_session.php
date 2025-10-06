<?php
/**
 * Test Session - Simple session test
 */

// Enable error reporting
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

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input['username']) && isset($input['password'])) {
        try {
            require_once 'config/database.php';
            $db = Database::getInstance();
            
            $username = trim($input['username']);
            $password = $input['password'];
            
            // Check user
            $stmt = $db->query("SELECT id, password_hash FROM usuarios WHERE username = ?", [$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_role'] = 'admin';
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $username;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'session_id' => session_id(),
                    'session_data' => $_SESSION
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password required'
        ]);
    }
} else {
    // GET request - show session info
    echo json_encode([
        'success' => true,
        'message' => 'Session info',
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_data' => $_SESSION ?? [],
        'cookies' => $_COOKIE ?? []
    ]);
}