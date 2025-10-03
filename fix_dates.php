<?php

/**
 * Fix Dates Script
 * Updates records with "0000-00-00" dates to current timestamp
 * Access: https://yourdomain.com/api_db/fix_dates.php
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();

    echo "<h1>Fix Dates Script</h1>";
    echo "<p>This script will update records with '0000-00-00' dates...</p>";

    // Check what date columns exist in datos table
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $dateColumn = null;
    if (in_array('created_at', $columns)) {
        $dateColumn = 'created_at';
    } elseif (in_array('fecha', $columns)) {
        $dateColumn = 'fecha';
    } elseif (in_array('date', $columns)) {
        $dateColumn = 'date';
    }

    if ($dateColumn) {
        echo "<p><strong>Found date column:</strong> $dateColumn</p>";

        // Update records with "0000-00-00" dates
        $sql = "UPDATE datos SET $dateColumn = NOW() WHERE $dateColumn = '0000-00-00' OR $dateColumn IS NULL";
        $stmt = $db->query($sql);
        $affectedRows = $stmt->rowCount();

        echo "<p style='color: green;'>✅ Updated $affectedRows records with proper dates</p>";

        // Show updated records
        $sql = "SELECT id, $dateColumn FROM datos WHERE $dateColumn != '0000-00-00' ORDER BY $dateColumn DESC LIMIT 10";
        $stmt = $db->query($sql);
        $records = $stmt->fetchAll();

        echo "<h2>Recent Records:</h2>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Date</th></tr>";
        foreach ($records as $record) {
            echo "<tr><td>{$record['id']}</td><td>{$record[$dateColumn]}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No date column found in datos table</p>";
        echo "<p>Available columns: " . implode(', ', $columns) . "</p>";
    }

    echo "<h2>Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Test the contact form to ensure new submissions have proper dates</li>";
    echo "<li>Check your admin panel to verify dates are now showing correctly</li>";
    echo "<li>Delete this fix_dates.php file after running it for security</li>";
    echo "</ol>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
