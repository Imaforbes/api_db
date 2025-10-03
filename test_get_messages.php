<?php

/**
 * Test Get Messages Endpoint
 * Simple test to see if messages can be retrieved
 * Access: https://yourdomain.com/api_db/test_get_messages.php
 */

require_once 'config/database.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

try {
    $db = Database::getInstance();

    echo "<h1>Test Get Messages</h1>";

    // Check datos table
    $stmt = $db->query("SELECT COUNT(*) as total FROM datos");
    $count = $stmt->fetch();
    echo "<p><strong>Total records in datos:</strong> {$count['total']}</p>";

    if ($count['total'] > 0) {
        // Get columns
        $stmt = $db->query("DESCRIBE datos");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
        $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
        $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

        $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
        $stmt = $db->query($sql);
        $messages = $stmt->fetchAll();

        echo "<h2>Messages found:</h2>";
        echo "<pre>" . json_encode($messages, JSON_PRETTY_PRINT) . "</pre>";

        echo "<h2>Raw JSON (for testing):</h2>";
        echo "<textarea style='width: 100%; height: 200px;'>" . json_encode($messages) . "</textarea>";
    } else {
        echo "<p style='color: red;'>❌ No messages found in datos table</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
