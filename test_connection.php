<?php

/**
 * Test Connection
 * Comprehensive test to identify connection issues
 */

echo "<h1>Connection Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f8f8f8;padding:10px;border:1px solid #ddd;}</style>";

// 1. Test database connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connected</p>";

    // Check if admin user exists
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $count = $stmt->fetch()['total'];
    echo "<p><strong>Admin users:</strong> $count</p>";

    if ($count == 0) {
        echo "<p class='warning'>⚠️ No admin users found</p>";
        echo "<p><a href='complete_setup.php'>Run setup</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

// 2. Test API endpoints
echo "<h2>2. API Endpoints Test</h2>";

// Test get_mensajes.php
echo "<h3>Testing get_mensajes.php</h3>";
try {
    // Simulate session
    session_start();
    $_SESSION['user_logged_in'] = true;
    $_SESSION['username'] = 'admin';

    // Capture output
    ob_start();
    include 'get_mensajes.php';
    $output = ob_get_clean();

    echo "<p class='success'>✅ get_mensajes.php executed</p>";
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";

    // Check if it's valid JSON
    $jsonData = json_decode($output, true);
    if ($jsonData) {
        echo "<p class='success'>✅ Valid JSON response</p>";
    } else {
        echo "<p class='error'>❌ Invalid JSON response</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ get_mensajes.php error: " . $e->getMessage() . "</p>";
}

// 3. Test CORS
echo "<h2>3. CORS Test</h2>";
echo "<p>Testing CORS headers...</p>";

// Test CORS headers
require_once 'config/response.php';
CorsHandler::setHeaders();
echo "<p class='success'>✅ CORS headers set</p>";

// 4. Test login endpoint
echo "<h2>4. Login Endpoint Test</h2>";
echo "<p>Testing login_simple.php...</p>";

$testData = [
    'username' => 'admin',
    'password' => 'admin123'
];

$url = 'http://localhost/api_db/login_simple.php';
$postData = json_encode($testData);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $postData
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "<p class='error'>❌ Login API not responding</p>";
} else {
    echo "<p class='success'>✅ Login API responding</p>";
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

// 5. Test React app connection
echo "<h2>5. React App Connection Test</h2>";
echo "<p>Testing if React app can reach the API...</p>";

// Test the exact URL the React app would use
$reactUrl = 'http://localhost/api_db/get_mensajes.php';
echo "<p><strong>React would call:</strong> $reactUrl</p>";

// Simulate the request
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents($reactUrl, false, $context);

if ($response === false) {
    echo "<p class='error'>❌ React app cannot reach API</p>";
} else {
    echo "<p class='success'>✅ React app can reach API</p>";
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

// 6. Recommendations
echo "<h2>6. Recommendations</h2>";
echo "<ul>";
echo "<li><a href='complete_setup.php'>Run complete setup</a></li>";
echo "<li><a href='test_login.php'>Test login form</a></li>";
echo "<li><a href='../my-portfolio-react/'>Go to portfolio</a></li>";
echo "</ul>";

echo "<p><strong>Common issues:</strong></p>";
echo "<ul>";
echo "<li>Database not set up properly</li>";
echo "<li>No admin users created</li>";
echo "<li>CORS issues</li>";
echo "<li>Session problems</li>";
echo "<li>API endpoint not responding</li>";
echo "</ul>";
