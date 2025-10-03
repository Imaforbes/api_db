<?php

/**
 * Get Messages Test Endpoint (No Authentication)
 * For testing message retrieval
 */

require_once 'config/database.php';
require_once 'config/response.php';

// Set CORS headers
CorsHandler::setHeaders();

try {
    // Get database connection
    $db = Database::getInstance();

    // Get messages from database
    // First, check what columns exist in the datos table
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check if we can use the datos table
    $hasName = in_array('name', $columns) || in_array('nombre', $columns);
    $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
    $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);
    $hasDate = in_array('created_at', $columns) || in_array('fecha', $columns) || in_array('date', $columns);

    echo "<h1>Debug get_mensajes.php Logic</h1>";
    echo "<p><strong>Columns found:</strong> " . implode(', ', $columns) . "</p>";
    echo "<p><strong>Has name:</strong> " . ($hasName ? "Yes" : "No") . "</p>";
    echo "<p><strong>Has email:</strong> " . ($hasEmail ? "Yes" : "No") . "</p>";
    echo "<p><strong>Has message:</strong> " . ($hasMessage ? "Yes" : "No") . "</p>";
    echo "<p><strong>Has date:</strong> " . ($hasDate ? "Yes" : "No") . "</p>";

    if ($hasName && $hasEmail && $hasMessage) {
        // Use existing datos table with mapped column names
        $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
        $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
        $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

        $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
        echo "<p><strong>SQL Query:</strong> $sql</p>";

        $stmt = $db->query($sql);
        $messages = $stmt->fetchAll();

        echo "<p><strong>Messages found:</strong> " . count($messages) . "</p>";

        if (count($messages) > 0) {
            echo "<h2>Messages:</h2>";
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
        } else {
            echo "<p style='color: red;'>❌ No messages found</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ datos table is not compatible</p>";

        // Try contact_messages table
        try {
            $sql = "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC";
            echo "<p><strong>Trying contact_messages table...</strong></p>";
            echo "<p><strong>SQL Query:</strong> $sql</p>";

            $stmt = $db->query($sql);
            $messages = $stmt->fetchAll();

            echo "<p><strong>Messages found in contact_messages:</strong> " . count($messages) . "</p>";

            if (count($messages) > 0) {
                echo "<h2>Messages from contact_messages:</h2>";
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
            echo "<p style='color: red;'>❌ contact_messages table error: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
