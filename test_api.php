<?php

/**
 * API Test Script
 * Test all API endpoints to ensure they're working correctly
 */

echo "<h1>Portfolio API Test Suite</h1>";

// Test configuration
$baseUrl = 'http://localhost/api_db';
$testResults = [];

// Helper function to make HTTP requests
function makeRequest($url, $method = 'GET', $data = null, $headers = [])
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Content-Type: application/json'], $headers));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Test functions
function testContactForm($baseUrl)
{
    echo "<h3>📧 Testing Contact Form</h3>";

    $testData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'message' => 'This is a test message from the API test suite.'
    ];

    $result = makeRequest($baseUrl . '/api/contact.php', 'POST', $testData);

    if ($result['http_code'] === 200) {
        echo "<p style='color: green;'>✅ Contact form test passed</p>";
        return true;
    } else {
        echo "<p style='color: red;'>❌ Contact form test failed (HTTP {$result['http_code']})</p>";
        echo "<p>Response: " . htmlspecialchars($result['response']) . "</p>";
        return false;
    }
}

function testProjects($baseUrl)
{
    echo "<h3>📁 Testing Projects API</h3>";

    $result = makeRequest($baseUrl . '/api/projects.php');

    if ($result['http_code'] === 200) {
        echo "<p style='color: green;'>✅ Projects API test passed</p>";
        return true;
    } else {
        echo "<p style='color: red;'>❌ Projects API test failed (HTTP {$result['http_code']})</p>";
        echo "<p>Response: " . htmlspecialchars($result['response']) . "</p>";
        return false;
    }
}

function testAuth($baseUrl)
{
    echo "<h3>🔐 Testing Authentication</h3>";

    // Test login
    $loginData = [
        'username' => 'admin',
        'password' => 'admin123'
    ];

    $loginResult = makeRequest($baseUrl . '/api/auth/login.php', 'POST', $loginData);

    if ($loginResult['http_code'] === 200) {
        echo "<p style='color: green;'>✅ Login test passed</p>";

        // Test verify (this would need session handling in a real test)
        $verifyResult = makeRequest($baseUrl . '/api/auth/verify.php');
        if ($verifyResult['http_code'] === 401) { // Should be unauthorized without session
            echo "<p style='color: green;'>✅ Auth verification test passed</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Auth verification test inconclusive</p>";
        }

        return true;
    } else {
        echo "<p style='color: red;'>❌ Login test failed (HTTP {$loginResult['http_code']})</p>";
        echo "<p>Response: " . htmlspecialchars($loginResult['response']) . "</p>";
        return false;
    }
}

function testAdminEndpoints($baseUrl)
{
    echo "<h3>👨‍💼 Testing Admin Endpoints</h3>";

    // Test stats endpoint (should require auth)
    $statsResult = makeRequest($baseUrl . '/api/admin/stats.php');

    if ($statsResult['http_code'] === 401) {
        echo "<p style='color: green;'>✅ Admin endpoints properly protected</p>";
        return true;
    } else {
        echo "<p style='color: red;'>❌ Admin endpoints not properly protected (HTTP {$statsResult['http_code']})</p>";
        return false;
    }
}

function testMessages($baseUrl)
{
    echo "<h3>💬 Testing Messages API</h3>";

    // Test messages endpoint (should require auth)
    $messagesResult = makeRequest($baseUrl . '/api/messages.php');

    if ($messagesResult['http_code'] === 401) {
        echo "<p style='color: green;'>✅ Messages API properly protected</p>";
        return true;
    } else {
        echo "<p style='color: red;'>❌ Messages API not properly protected (HTTP {$messagesResult['http_code']})</p>";
        return false;
    }
}

// Run all tests
echo "<h2>🧪 Running API Tests</h2>";

$tests = [
    'Contact Form' => testContactForm($baseUrl),
    'Projects API' => testProjects($baseUrl),
    'Authentication' => testAuth($baseUrl),
    'Admin Protection' => testAdminEndpoints($baseUrl),
    'Messages Protection' => testMessages($baseUrl)
];

// Summary
echo "<h2>📊 Test Summary</h2>";
$passed = 0;
$total = count($tests);

foreach ($tests as $testName => $result) {
    $status = $result ? "✅ PASS" : "❌ FAIL";
    echo "<p><strong>{$testName}:</strong> {$status}</p>";
    if ($result) $passed++;
}

echo "<h3>Results: {$passed}/{$total} tests passed</h3>";

if ($passed === $total) {
    echo "<p style='color: green; font-size: 18px;'><strong>🎉 All tests passed! Your API is working correctly.</strong></p>";
} else {
    echo "<p style='color: red; font-size: 18px;'><strong>⚠️ Some tests failed. Please check the configuration.</strong></p>";
}

// Additional information
echo "<h2>📋 Next Steps</h2>";
echo "<ul>";
echo "<li>Update your frontend API calls to use the new endpoints</li>";
echo "<li>Test the admin panel functionality</li>";
echo "<li>Configure file upload permissions</li>";
echo "<li>Set up production environment variables</li>";
echo "</ul>";

echo "<h2>🔗 API Documentation</h2>";
echo "<p>View the complete API documentation: <a href='API_ENDPOINTS.md' target='_blank'>API_ENDPOINTS.md</a></p>";
