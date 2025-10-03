<?php

/**
 * Simple Contact Form Test
 * Test the contact form endpoint directly
 */

// Test data
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'message' => 'This is a test message from the API test script.'
];

// Make the request
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

echo "<h1>Contact Form API Test</h1>";
echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>Response:</h2>";
echo "<pre>" . htmlspecialchars($result) . "</pre>";

// Check if the response is valid JSON
$response = json_decode($result, true);
if ($response) {
    echo "<h2>Parsed Response:</h2>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";

    if (isset($response['success']) && $response['success']) {
        echo "<p style='color: green; font-weight: bold;'>✅ Contact form test PASSED!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Contact form test FAILED!</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Invalid JSON response!</p>";
}
