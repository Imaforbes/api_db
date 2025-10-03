<?php

/**
 * Simple Test Messages Script
 * Test message retrieval without authentication
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    echo "<h1>Simple Messages Test</h1>";
    echo "<p>Testing message retrieval...</p>";

    // Check datos table structure
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns in datos:</strong> " . implode(', ', $columns) . "</p>";

    // Check total records
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $count = $stmt->fetch();
    echo "<p><strong>Total records:</strong> {$count['total']}</p>";

    if ($count['total'] > 0) {
        // Show all records
        $stmt = $db->query("SELECT * FROM datos ORDER BY id DESC");
        $records = $stmt->fetchAll();

        echo "<h2>All Records in datos table:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";

        foreach ($records as $record) {
            echo "<tr>";
            foreach ($columns as $column) {
                $value = $record[$column] ?? 'NULL';
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No records found in datos table</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
