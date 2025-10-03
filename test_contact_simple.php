<?php

/**
 * Simple Contact Form Test
 * Test the contact form after database setup
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Contact Form Test</h1>";

// Test data
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'message' => 'This is a test message with more than 10 characters to pass validation.'
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test the contact API endpoint
echo "<h2>Testing Contact API:</h2>";

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
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ Contact form test PASSED!</p>";
        echo "<p>The contact form is working correctly.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ Contact form test FAILED!</p>";
        if (isset($response['errors'])) {
            echo "<h4>Validation Errors:</h4>";
            echo "<pre>" . json_encode($response['errors'], JSON_PRETTY_PRINT) . "</pre>";
        }
    }
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ Invalid JSON response!</p>";
    echo "<p>This might indicate a server error or database connection issue.</p>";
}

// Test database connection directly
echo "<h2>Database Connection Test:</h2>";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful</p>";

    // Test if we can query the contact_messages table
    $stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages");
    $count = $stmt->fetch()['count'];
    echo "<p style='color: green;'>✅ contact_messages table accessible (contains $count messages)</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please run the <a href='simple_setup.php'>database setup</a> first.</p>";
}
