<?php

/**
 * Debug Messages Script
 * Check what messages exist in the database
 * Access: https://yourdomain.com/api_db/debug_messages.php
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance();

    echo "<h1>Debug Messages Script</h1>";
    echo "<p>Checking what messages exist in the database...</p>";

    // Check datos table structure
    echo "<h2>1. Checking datos table structure:</h2>";
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Columns in datos table:</strong> " . implode(', ', $columns) . "</p>";

    // Check if datos table has messages
    echo "<h2>2. Checking datos table content:</h2>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $count = $stmt->fetch();
    echo "<p><strong>Total records in datos:</strong> {$count['total']}</p>";

    if ($count['total'] > 0) {
        // Show sample records
        $stmt = $db->query("SELECT * FROM datos ORDER BY id DESC LIMIT 5");
        $records = $stmt->fetchAll();

        echo "<h3>Sample records from datos table:</h3>";
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
    }

    // Check contact_messages table
    echo "<h2>3. Checking contact_messages table:</h2>";
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM contact_messages");
        $count = $stmt->fetch();
        echo "<p><strong>Total records in contact_messages:</strong> {$count['total']}</p>";

        if ($count['total'] > 0) {
            $stmt = $db->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5");
            $records = $stmt->fetchAll();

            echo "<h3>Sample records from contact_messages table:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Created At</th></tr>";

            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>{$record['id']}</td>";
                echo "<td>" . htmlspecialchars($record['name']) . "</td>";
                echo "<td>" . htmlspecialchars($record['email']) . "</td>";
                echo "<td>" . htmlspecialchars(substr($record['message'], 0, 50)) . "...</td>";
                echo "<td>{$record['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ contact_messages table doesn't exist or has issues: " . $e->getMessage() . "</p>";
    }

    // Test the get_mensajes.php logic
    echo "<h2>4. Testing get_mensajes.php logic:</h2>";

    // Check what columns exist in the datos table
    $hasName = in_array('name', $columns) || in_array('nombre', $columns);
    $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
    $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);
    $hasDate = in_array('created_at', $columns) || in_array('fecha', $columns) || in_array('date', $columns);

    echo "<p><strong>Column compatibility check:</strong></p>";
    echo "<ul>";
    echo "<li>Has name column: " . ($hasName ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has email column: " . ($hasEmail ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has message column: " . ($hasMessage ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has date column: " . ($hasDate ? "✅ Yes" : "❌ No") . "</li>";
    echo "</ul>";

    if ($hasName && $hasEmail && $hasMessage) {
        echo "<p style='color: green;'>✅ datos table is compatible with get_mensajes.php</p>";

        // Test the actual query that get_mensajes.php would use
        $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
        $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
        $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

        $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";

        echo "<p><strong>Query that would be executed:</strong></p>";
        echo "<code>$sql</code>";

        try {
            $stmt = $db->query($sql);
            $messages = $stmt->fetchAll();

            echo "<p><strong>Query result:</strong> " . count($messages) . " messages found</p>";

            if (count($messages) > 0) {
                echo "<h3>Messages that would be returned:</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";

                foreach ($messages as $message) {
                    echo "<tr>";
                    echo "<td>{$message['id']}</td>";
                    echo "<td>" . htmlspecialchars($message['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($message['email']) . "</td>";
                    echo "<td>" . htmlspecialchars(substr($message['message'], 0, 50)) . "...</td>";
                    echo "<td>{$message['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Query failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ datos table is NOT compatible with get_mensajes.php</p>";
    }

    echo "<h2>5. Recommendations:</h2>";
    echo "<ul>";
    if ($count['total'] == 0) {
        echo "<li>🔍 No messages found in datos table. Try submitting a test message through the contact form.</li>";
    }
    if (!$hasName || !$hasEmail || !$hasMessage) {
        echo "<li>🔧 The datos table structure doesn't match what get_mensajes.php expects.</li>";
    }
    echo "<li>📝 Check the browser console for any JavaScript errors when loading the admin panel.</li>";
    echo "<li>🔗 Test the get_mensajes.php endpoint directly: <a href='get_mensajes.php' target='_blank'>get_mensajes.php</a></li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
