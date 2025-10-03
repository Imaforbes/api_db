<?php

/**
 * Check Existing Tables Structure
 * See what columns exist in your datos and usuarios tables
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Check Existing Tables Structure</h1>";

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

    // Check structure of datos table
    if (in_array('datos', $tables)) {
        echo "<h2>Structure of 'datos' table:</h2>";
        $stmt = $db->query("DESCRIBE datos");
        $columns = $stmt->fetchAll();

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

        // Check if datos table has any data
        $stmt = $db->query("SELECT COUNT(*) as count FROM datos");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>Records in datos table:</strong> $count</p>";

        // Show sample data if exists
        if ($count > 0) {
            echo "<h3>Sample data from datos table:</h3>";
            $stmt = $db->query("SELECT * FROM datos LIMIT 3");
            $sampleData = $stmt->fetchAll();
            echo "<pre>" . json_encode($sampleData, JSON_PRETTY_PRINT) . "</pre>";
        }
    }

    // Check structure of usuarios table
    if (in_array('usuarios', $tables)) {
        echo "<h2>Structure of 'usuarios' table:</h2>";
        $stmt = $db->query("DESCRIBE usuarios");
        $columns = $stmt->fetchAll();

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

        // Check if usuarios table has any data
        $stmt = $db->query("SELECT COUNT(*) as count FROM usuarios");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>Records in usuarios table:</strong> $count</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in <code>config/database.php</code></p>";
}
