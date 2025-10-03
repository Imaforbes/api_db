<?php

/**
 * Test Authentication Flow
 * Check if login and session management is working
 */

session_start();

echo "<h1>Authentication Flow Test</h1>";

// Check if user is logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    echo "<p style='color: green;'>✅ User is logged in</p>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

    // Test the get_mensajes endpoint
    echo "<h2>Testing get_mensajes.php endpoint:</h2>";

    // Simulate the same logic as get_mensajes.php
    try {
        require_once 'config/database.php';
        $db = Database::getInstance();

        // Check datos table
        $stmt = $db->query("DESCRIBE datos");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $hasName = in_array('name', $columns) || in_array('nombre', $columns);
        $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
        $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);
        $hasDate = in_array('created_at', $columns) || in_array('fecha', $columns) || in_array('date', $columns);

        echo "<p><strong>Database compatibility:</strong></p>";
        echo "<ul>";
        echo "<li>Has name: " . ($hasName ? "Yes" : "No") . "</li>";
        echo "<li>Has email: " . ($hasEmail ? "Yes" : "No") . "</li>";
        echo "<li>Has message: " . ($hasMessage ? "Yes" : "No") . "</li>";
        echo "<li>Has date: " . ($hasDate ? "Yes" : "No") . "</li>";
        echo "</ul>";

        if ($hasName && $hasEmail && $hasMessage) {
            $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
            $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
            $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
            $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

            $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
            $stmt = $db->query($sql);
            $messages = $stmt->fetchAll();

            echo "<p><strong>Messages found:</strong> " . count($messages) . "</p>";

            if (count($messages) > 0) {
                echo "<h3>Sample messages:</h3>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>";
                foreach (array_slice($messages, 0, 3) as $message) {
                    echo "<tr>";
                    echo "<td>{$message['id']}</td>";
                    echo "<td>" . htmlspecialchars($message['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($message['email']) . "</td>";
                    echo "<td>" . htmlspecialchars(substr($message['message'], 0, 50)) . "...</td>";
                    echo "<td>{$message['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>⚠️ No messages found in database</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database structure not compatible</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>";
    echo "<p>This is why messages are not showing in the dashboard.</p>";
    echo "<p><a href='login.php'>Click here to login</a></p>";
}

echo "<h2>Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
