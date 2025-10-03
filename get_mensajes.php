<?php

/**
 * Get Messages API Endpoint
 * Retrieves contact form messages for admin
 */

require_once 'config/database.php';
require_once 'config/response.php';

// Set CORS headers
CorsHandler::setHeaders();

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    ApiResponse::unauthorized('Access denied. Please login first.');
}

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

    if ($hasName && $hasEmail && $hasMessage) {
        // Use existing datos table with mapped column names
        $nameColumn = in_array('name', $columns) ? 'name' : 'nombre';
        $emailColumn = in_array('email', $columns) ? 'email' : 'correo';
        $messageColumn = in_array('message', $columns) ? 'message' : (in_array('mensaje', $columns) ? 'mensaje' : 'comentario');
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : (in_array('fecha', $columns) ? 'fecha' : 'date');

        $sql = "SELECT id, $nameColumn as name, $emailColumn as email, $messageColumn as message, $dateColumn as created_at FROM datos ORDER BY $dateColumn DESC";
        $stmt = $db->query($sql);
        $messages = $stmt->fetchAll();
    } else {
        // Try contact_messages table
        $sql = "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC";
        $stmt = $db->query($sql);
        $messages = $stmt->fetchAll();
    }

    // Transform the data to match what the frontend expects
    $transformedMessages = array_map(function ($message) {
        return [
            'id' => $message['id'],
            'fecha' => $message['created_at'],
            'nombre' => $message['name'],
            'email' => $message['email'],
            'mensaje' => $message['message']
        ];
    }, $messages);

    ApiResponse::success($transformedMessages, 'Messages retrieved successfully');
} catch (Exception $e) {
    error_log("Get messages error: " . $e->getMessage());
    ApiResponse::serverError('Failed to retrieve messages');
}
