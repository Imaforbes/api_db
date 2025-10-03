<?php

/**
 * Simple Get Messages (No Authentication)
 * For testing purposes only
 */

require_once 'config/database.php';
require_once 'config/response.php';

// Set CORS headers
CorsHandler::setHeaders();

try {
    $db = Database::getInstance();

    // Get messages from database
    $stmt = $db->query("DESCRIBE datos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $hasName = in_array('name', $columns) || in_array('nombre', $columns);
    $hasEmail = in_array('email', $columns) || in_array('correo', $columns);
    $hasMessage = in_array('message', $columns) || in_array('mensaje', $columns) || in_array('comentario', $columns);
    $hasDate = in_array('created_at', $columns) || in_array('fecha', $columns) || in_array('date', $columns);

    if ($hasName && $hasEmail && $hasMessage) {
        $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
        $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
        $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

        $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
        $stmt = $db->query($sql);
        $messages = $stmt->fetchAll();
    } else {
        $sql = "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC";
        $stmt = $db->query($sql);
        $messages = $stmt->fetchAll();
    }

    ApiResponse::success($messages, 'Messages retrieved successfully');
} catch (Exception $e) {
    error_log("Get messages error: " . $e->getMessage());
    ApiResponse::serverError('Failed to retrieve messages');
}
