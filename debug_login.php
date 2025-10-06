<?php

/**
 * Debug Login Script for Hostinger
 * This script helps debug login issues in production
 */

// Set CORS headers
header('Access-Control-Allow-Origin: https://www.imaforbes.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config/database.php';
require_once 'auth/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Test login
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input && isset($input['username']) && isset($input['password'])) {
        try {
            $username = $input['username'];
            $password = $input['password'];

            // Test database connection first
            $db = Database::getInstance();

            // Check if user exists
            $sql = "SELECT id, username, password_hash FROM usuarios WHERE username = ?";
            $stmt = $db->query($sql, [$username]);
            $user = $stmt->fetch();

            if ($user) {
                // Check password
                $passwordValid = password_verify($password, $user['password_hash']);

                if ($passwordValid) {
                    // Start session
                    SessionManager::startSession();
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = 'admin';

                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'role' => 'admin'
                        ],
                        'session' => [
                            'id' => session_id(),
                            'data' => $_SESSION
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid password',
                        'debug' => [
                            'user_found' => true,
                            'password_check' => false
                        ]
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not found',
                    'debug' => [
                        'user_found' => false,
                        'username' => $username
                    ]
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input data'
        ]);
    }
} else {
    // GET request - show debug info
    try {
        $db = Database::getInstance();
        $users = $db->query("SELECT id, username FROM usuarios")->fetchAll();

        SessionManager::startSession();

        echo json_encode([
            'success' => true,
            'message' => 'Debug info',
            'database' => [
                'connected' => true,
                'users' => $users
            ],
            'session' => [
                'status' => session_status(),
                'id' => session_id(),
                'data' => $_SESSION ?? []
            ],
            'environment' => [
                'php_version' => PHP_VERSION,
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'https' => isset($_SERVER['HTTPS']) ? 'yes' : 'no'
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
    }
}
