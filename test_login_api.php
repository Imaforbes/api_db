<?php

/**
 * Test Login API
 * Test the login API endpoint directly
 */

echo "<h1>Test Login API</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} pre{background:#f8f8f8;padding:10px;border:1px solid #ddd;}</style>";

// Test data
$testData = [
    'username' => 'admin',
    'password' => 'admin123'
];

echo "<h2>Testing Login API</h2>";
echo "<p><strong>Test data:</strong></p>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Make the API call
$url = 'http://localhost/api_db/login_simple.php';
$postData = json_encode($testData);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

echo "<h2>API Response</h2>";
echo "<p><strong>URL:</strong> $url</p>";

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "<p class='error'>❌ Failed to get response from API</p>";
} else {
    echo "<p class='success'>✅ Got response from API</p>";
    echo "<p><strong>Raw response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";

    // Try to decode JSON
    $jsonData = json_decode($response, true);
    if ($jsonData) {
        echo "<p><strong>Decoded response:</strong></p>";
        echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";

        if (isset($jsonData['success']) && $jsonData['success']) {
            echo "<p class='success'>✅ Login successful!</p>";
        } else {
            echo "<p class='error'>❌ Login failed: " . ($jsonData['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Response is not valid JSON</p>";
    }
}

echo "<h2>Alternative Tests</h2>";
echo "<p><a href='test_login.php'>Test login form</a></p>";
echo "<p><a href='complete_setup.php'>Run complete setup</a></p>";
echo "<p><a href='debug_login.php'>Debug login system</a></p>";
