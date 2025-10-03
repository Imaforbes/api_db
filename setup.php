<?php

/**
 * Database Setup Script
 * Run this script to initialize the database and create necessary tables
 */

require_once 'config/database.php';

echo "<h1>Portfolio API Database Setup</h1>";

try {
    $db = Database::getInstance();
    echo "<p>✅ Database connection successful</p>";

    // Read and execute the schema file
    $schemaFile = 'database_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: {$schemaFile}");
    }

    $schema = file_get_contents($schemaFile);
    $statements = explode(';', $schema);

    $executed = 0;
    $errors = [];

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;

        try {
            $db->query($statement);
            $executed++;
        } catch (Exception $e) {
            $errors[] = "Statement failed: " . substr($statement, 0, 50) . "... Error: " . $e->getMessage();
        }
    }

    echo "<p>✅ Executed {$executed} SQL statements</p>";

    if (!empty($errors)) {
        echo "<h3>⚠️ Warnings:</h3>";
        foreach ($errors as $error) {
            echo "<p style='color: orange;'>{$error}</p>";
        }
    }

    // Verify tables were created
    $tables = ['contact_messages', 'projects', 'admin_users', 'admin_sessions', 'portfolio_settings'];
    $existingTables = [];

    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SHOW TABLES LIKE ?", [$table]);
            if ($stmt->fetch()) {
                $existingTables[] = $table;
            }
        } catch (Exception $e) {
            // Table doesn't exist
        }
    }

    echo "<h3>📊 Database Status:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        $status = in_array($table, $existingTables) ? "✅" : "❌";
        echo "<li>{$status} {$table}</li>";
    }
    echo "</ul>";

    // Test admin user
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
        $adminCount = $stmt->fetch()['count'];

        if ($adminCount > 0) {
            echo "<p>✅ Default admin user created</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
        } else {
            echo "<p>❌ Default admin user not found</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error checking admin user: " . $e->getMessage() . "</p>";
    }

    // Create upload directories
    $uploadDirs = ['uploads/images', 'uploads/documents'];

    echo "<h3>📁 File System:</h3>";
    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<p>✅ Created directory: {$dir}</p>";
            } else {
                echo "<p>❌ Failed to create directory: {$dir}</p>";
            }
        } else {
            echo "<p>✅ Directory exists: {$dir}</p>";
        }
    }

    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<p>Your portfolio API is ready to use.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test the contact form endpoint: <code>POST /api/contact.php</code></li>";
    echo "<li>Login to admin panel: <code>POST /api/auth/login.php</code></li>";
    echo "<li>View API documentation: <code>API_ENDPOINTS.md</code></li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in <code>config/database.php</code></p>";
}
