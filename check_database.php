<?php

/**
 * Check Database Status
 * Check what tables exist and what's missing
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Status Check</h1>";

try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful</p>";

    // Check what database we're connected to
    $stmt = $db->query("SELECT DATABASE() as current_db");
    $currentDb = $stmt->fetch()['current_db'];
    echo "<p><strong>Current Database:</strong> $currentDb</p>";

    // List all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h2>Existing Tables:</h2>";
    if (empty($tables)) {
        echo "<p style='color: red;'>❌ No tables found in the database</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li style='color: green;'>✅ $table</li>";
        }
        echo "</ul>";
    }

    // Check for required tables
    $requiredTables = [
        'contact_messages' => 'Contact form messages',
        'projects' => 'Portfolio projects',
        'admin_users' => 'Admin user accounts',
        'admin_sessions' => 'Admin user sessions',
        'portfolio_settings' => 'Portfolio configuration'
    ];

    echo "<h2>Required Tables Status:</h2>";
    $missingTables = [];

    foreach ($requiredTables as $table => $description) {
        if (in_array($table, $tables)) {
            echo "<p style='color: green;'>✅ $table - $description</p>";
        } else {
            echo "<p style='color: red;'>❌ $table - $description (MISSING)</p>";
            $missingTables[] = $table;
        }
    }

    // If we have the contact_messages table, check if it has data
    if (in_array('contact_messages', $tables)) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>Contact Messages:</strong> $count messages in database</p>";
    }

    // If we have the admin_users table, check if admin user exists
    if (in_array('admin_users', $tables)) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
        $adminCount = $stmt->fetch()['count'];
        if ($adminCount > 0) {
            echo "<p style='color: green;'>✅ Default admin user exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Default admin user not found</p>";
        }
    }

    // Summary
    echo "<h2>Summary:</h2>";
    if (empty($missingTables)) {
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🎉 All required tables exist! Your database is ready.</p>";
        echo "<p>You can now test the contact form: <a href='test_contact_simple.php'>Test Contact Form</a></p>";
    } else {
        echo "<p style='color: red; font-weight: bold; font-size: 18px;'>⚠️ Missing tables: " . implode(', ', $missingTables) . "</p>";
        echo "<p>You need to create the missing tables. You can:</p>";
        echo "<ul>";
        echo "<li><a href='simple_setup.php'>Run the complete setup</a> (recommended)</li>";
        echo "<li><a href='setup.php'>Run the original setup script</a></li>";
        echo "<li>Execute the SQL schema manually in your database</li>";
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in <code>config/database.php</code></p>";
}
