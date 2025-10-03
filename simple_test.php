<?php

/**
 * Simple Test - Check messages without authentication
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Messages Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;}</style>";

try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connected</p>";

    // Check datos table
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $count = $stmt->fetch()['total'];
    echo "<p><strong>Messages in datos table:</strong> $count</p>";

    if ($count > 0) {
        // Show all messages
        $stmt = $db->query("SELECT * FROM datos ORDER BY id DESC");
        $messages = $stmt->fetchAll();

        echo "<h2>All Messages:</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";

        foreach ($messages as $msg) {
            $name = $msg['name'] ?? $msg['nombre'] ?? 'N/A';
            $email = $msg['email'] ?? $msg['correo'] ?? 'N/A';
            $message = $msg['message'] ?? $msg['mensaje'] ?? $msg['comentario'] ?? 'N/A';
            $date = $msg['created_at'] ?? $msg['fecha'] ?? $msg['date'] ?? 'N/A';

            echo "<tr>";
            echo "<td>{$msg['id']}</td>";
            echo "<td>" . htmlspecialchars($name) . "</td>";
            echo "<td>" . htmlspecialchars($email) . "</td>";
            echo "<td>" . htmlspecialchars(substr($message, 0, 50)) . "...</td>";
            echo "<td>$date</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No messages found</p>";
        echo "<p><a href='create_test_message.php'>Create test message</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
