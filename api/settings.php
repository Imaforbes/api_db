<?php

/**
 * Settings Management API Endpoint
 * Handles CRUD operations for system configuration settings
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

require_once '../config/database.php';
require_once '../config/response.php';
require_once '../auth/session.php';

// Also set CORS headers using the handler (as backup)
CorsHandler::setHeaders();

// Start session explicitly
session_start();

// Enhanced authentication check with debugging
error_log("Settings API - Checking authentication...");
error_log("Settings API - Session data: " . json_encode($_SESSION ?? []));
error_log("Settings API - Session ID: " . session_id());

// Check multiple session variables for backward compatibility
$isAuthenticated = (
    isset($_SESSION['admin_user_id']) ||
    (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) ||
    isset($_SESSION['admin_username'])
);

error_log("Settings API - Authentication result: " . ($isAuthenticated ? 'AUTHENTICATED' : 'NOT AUTHENTICATED'));

if (!$isAuthenticated) {
    error_log("Settings API - Authentication failed, returning 401");
    error_log("Settings API - Available session keys: " . implode(', ', array_keys($_SESSION ?? [])));
    ApiResponse::unauthorized('Authentication required');
}

$method = $_SERVER['REQUEST_METHOD'];

// Debug logging
error_log("Settings API - Request Method: " . $method);
error_log("Settings API - Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));

$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            handleGetSettings($db);
            break;
        case 'POST':
        case 'PUT':
        case 'PATCH':
            handleUpdateSettings($db);
            break;
        default:
            error_log("Settings API - Unsupported method: " . $method);
            ApiResponse::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Settings API error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred while processing the request');
}

function handleGetSettings($db)
{
    try {
        // Get all settings from the portfolio_settings table
        $sql = "SELECT setting_key, setting_value, description FROM portfolio_settings ORDER BY setting_key";
        $stmt = $db->query($sql);
        $settings = $stmt->fetchAll();

        // Convert to associative array
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['setting_key']] = [
                'value' => $setting['setting_value'],
                'description' => $setting['description']
            ];
        }

        // Get system status
        $systemStatus = getSystemStatus($db);

        $response = [
            'settings' => $settingsArray,
            'system_status' => $systemStatus
        ];

        ApiResponse::success($response, 'Settings retrieved successfully');
    } catch (Exception $e) {
        error_log("Get settings error: " . $e->getMessage());
        ApiResponse::serverError('Failed to retrieve settings');
    }
}

function handleUpdateSettings($db)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    try {
        $db->beginTransaction();

        $updatedSettings = [];

        foreach ($input as $key => $value) {
            if (is_array($value) && isset($value['value'])) {
                // Handle nested settings (like notifications, security, etc.)
                $settingValue = json_encode($value['value']);
            } else {
                $settingValue = is_array($value) ? json_encode($value) : $value;
            }

            // Update or insert setting
            $sql = "INSERT INTO portfolio_settings (setting_key, setting_value, description) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    description = VALUES(description)";
            
            $description = getSettingDescription($key);
            $stmt = $db->query($sql, [$key, $settingValue, $description]);
            
            $updatedSettings[$key] = $settingValue;
        }

        $db->commit();

        // Get updated settings
        $sql = "SELECT setting_key, setting_value FROM portfolio_settings";
        $stmt = $db->query($sql);
        $allSettings = $stmt->fetchAll();

        $settingsArray = [];
        foreach ($allSettings as $setting) {
            $settingsArray[$setting['setting_key']] = $setting['setting_value'];
        }

        ApiResponse::success($settingsArray, 'Settings updated successfully');
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Update settings error: " . $e->getMessage());
        ApiResponse::serverError('Failed to update settings');
    }
}

function getSystemStatus($db)
{
    try {
        // Check database connection
        $dbStatus = 'connected';
        
        // Check API status
        $apiStatus = 'working';
        
        // Check backup status (simulate)
        $backupStatus = 'pending';
        
        // Get message count
        $stmt = $db->query("SELECT COUNT(*) as count FROM datos");
        $messageCount = $stmt->fetch()['count'];
        
        return [
            'database' => $dbStatus,
            'api' => $apiStatus,
            'backup' => $backupStatus,
            'message_count' => $messageCount,
            'last_check' => date('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        return [
            'database' => 'error',
            'api' => 'error',
            'backup' => 'error',
            'message_count' => 0,
            'last_check' => date('Y-m-d H:i:s'),
            'error' => $e->getMessage()
        ];
    }
}

function getSettingDescription($key)
{
    $descriptions = [
        'site_name' => 'Main site title',
        'site_description' => 'Site meta description',
        'admin_email' => 'Administrator email address',
        'notifications' => 'Notification preferences',
        'security' => 'Security settings',
        'appearance' => 'Appearance and theme settings',
        'database' => 'Database configuration',
        'contact_email' => 'Contact form email address',
        'github_url' => 'GitHub profile URL',
        'linkedin_url' => 'LinkedIn profile URL',
        'max_upload_size' => 'Maximum file upload size in bytes',
        'allowed_file_types' => 'Allowed file extensions for uploads'
    ];

    return $descriptions[$key] ?? 'System setting';
}
