<?php

/**
 * Debug Contact Form
 * Test the contact form with detailed debugging
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Contact Form Debug</h1>";

// Test data
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'message' => 'This is a test message with more than 10 characters to pass validation.'
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test database connection
echo "<h2>Database Connection Test:</h2>";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful</p>";

    // Test if contact_messages table exists
    $stmt = $db->query("SHOW TABLES LIKE 'contact_messages'");
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✅ contact_messages table exists</p>";
    } else {
        echo "<p style='color: red;'>❌ contact_messages table not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test the contact API endpoint
echo "<h2>API Endpoint Test:</h2>";
$url = 'http://localhost/api_db/api/contact.php';
$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($testData)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<h3>Raw Response:</h3>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";

// Parse and display response
$response = json_decode($result, true);
if ($response) {
    echo "<h3>Parsed Response:</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";

    if (isset($response['success']) && $response['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Contact form test PASSED!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Contact form test FAILED!</p>";
        if (isset($response['errors'])) {
            echo "<h4>Validation Errors:</h4>";
            echo "<pre>" . json_encode($response['errors'], JSON_PRETTY_PRINT) . "</pre>";
        }
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Invalid JSON response!</p>";
}

// Check error logs
echo "<h2>Recent Error Logs:</h2>";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = array_slice(explode("\n", $logs), -10);
    echo "<pre>" . htmlspecialchars(implode("\n", $recentLogs)) . "</pre>";
} else {
    echo "<p>No error log file found or accessible</p>";
}
