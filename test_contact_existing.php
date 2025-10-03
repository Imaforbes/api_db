<?php

/**
 * Test Contact Form with Existing Database
 * Test the contact form using your existing datos table
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Contact Form with Existing Database</h1>";

// Test data
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'message' => 'This is a test message with more than 10 characters to pass validation.'
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// First, check the structure of your datos table
echo "<h2>Checking datos table structure:</h2>";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful</p>";

    // Check structure of datos table
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll();

    echo "<h3>Columns in datos table:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check if we can use datos table for contact form
    $columnNames = array_column($columns, 'Field');
    $hasName = in_array('name', $columnNames) || in_array('nombre', $columnNames);
    $hasEmail = in_array('email', $columnNames) || in_array('correo', $columnNames);
    $hasMessage = in_array('message', $columnNames) || in_array('mensaje', $columnNames) || in_array('comentario', $columnNames);

    echo "<h3>Contact Form Compatibility:</h3>";
    echo "<p>Name field: " . ($hasName ? "✅ Available" : "❌ Missing") . "</p>";
    echo "<p>Email field: " . ($hasEmail ? "✅ Available" : "❌ Missing") . "</p>";
    echo "<p>Message field: " . ($hasMessage ? "✅ Available" : "❌ Missing") . "</p>";

    if ($hasName && $hasEmail && $hasMessage) {
        echo "<p style='color: green; font-weight: bold;'>✅ Your datos table is compatible with the contact form!</p>";
    } else {
        echo "<p style='color: orange; font-weight: bold;'>⚠️ Your datos table doesn't have the right columns for contact form. A contact_messages table will be created automatically.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration.</p>";
}

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
        echo "<p>The contact form is working correctly with your existing database.</p>";
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

// Check what was actually inserted
echo "<h2>Checking what was inserted:</h2>";
try {
    $db = Database::getInstance();

    // Check if contact_messages table was created
    $stmt = $db->query("SHOW TABLES LIKE 'contact_messages'");
    if ($stmt->fetch()) {
        echo "<p style='color: blue;'>ℹ️ contact_messages table was created</p>";
        $stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages");
        $count = $stmt->fetch()['count'];
        echo "<p>Records in contact_messages: $count</p>";

        if ($count > 0) {
            $stmt = $db->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 1");
            $lastRecord = $stmt->fetch();
            echo "<h4>Last inserted record:</h4>";
            echo "<pre>" . json_encode($lastRecord, JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ contact_messages table was not created (using existing datos table)</p>";
    }

    // Check datos table
    $stmt = $db->query("SELECT COUNT(*) as count FROM datos");
    $count = $stmt->fetch()['count'];
    echo "<p>Records in datos table: $count</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking inserted data: " . $e->getMessage() . "</p>";
}
