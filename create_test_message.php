<?php

/**
 * Create Test Message
 * Insert a test message into the database to verify the system works
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    echo "<h1>Create Test Message</h1>";

    // Check if datos table exists and has the right structure
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns in datos table:</strong> " . implode(', ', $columns) . "</p>";

    // Determine column names
    $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
    $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
    $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
    $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

    echo "<p><strong>Using columns:</strong></p>";
    echo "<ul>";
    echo "<li>Name: $nameColumn</li>";
    echo "<li>Email: $emailColumn</li>";
    echo "<li>Message: $messageColumn</li>";
    echo "<li>Date: $dateColumn</li>";
    echo "</ul>";

    // Insert test message
    $testData = [
        $nameColumn => 'Test User',
        $emailColumn => 'test@example.com',
        $messageColumn => 'This is a test message to verify the dashboard is working correctly.',
    ];

    // Add date if column exists
    if (in_array($dateColumn, $columns)) {
        $testData[$dateColumn] = date('Y-m-d H:i:s');
    }

    $sql = "INSERT INTO datos (" . implode(', ', array_keys($testData)) . ") VALUES (" . implode(', ', array_fill(0, count($testData), '?')) . ")";

    echo "<p><strong>SQL Query:</strong> <code>$sql</code></p>";
    echo "<p><strong>Data:</strong> <pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre></p>";

    $stmt = $db->prepare($sql);
    $result = $stmt->execute(array_values($testData));

    if ($result) {
        echo "<p class='success'>✅ Test message created successfully!</p>";
        echo "<p>Message ID: " . $db->lastInsertId() . "</p>";

        // Verify the message was inserted
        $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
        $count = $stmt->fetch()['total'];
        echo "<p><strong>Total messages in database:</strong> $count</p>";

        // Show the latest message
        $stmt = $db->query("SELECT * FROM datos ORDER BY id DESC LIMIT 1");
        $latestMessage = $stmt->fetch();

        echo "<h3>Latest message:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($latestMessage as $field => $value) {
            echo "<tr><td>$field</td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";

        echo "<p><a href='debug_dashboard_complete.php'>Check dashboard debug</a></p>";
        echo "<p><a href='../my-portfolio-react/'>Go to portfolio</a></p>";
    } else {
        echo "<p class='error'>❌ Failed to create test message</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
