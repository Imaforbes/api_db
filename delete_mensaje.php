<?php

/**
 * Delete Message API Endpoint
 * Deletes a contact form message
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    // Get database connection
    $db = Database::getInstance();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    // Validate message ID
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        ApiResponse::error('Invalid message ID', 400);
    }

    $id = intval($input['id']);

    // Try to delete from datos table first
    $stmt = $db->query("DELETE FROM datos WHERE id = ?", [$id]);

    if ($stmt->rowCount() > 0) {
        ApiResponse::success(null, 'Message deleted successfully');
    } else {
        // Try contact_messages table
        $stmt = $db->query("DELETE FROM contact_messages WHERE id = ?", [$id]);

        if ($stmt->rowCount() > 0) {
            ApiResponse::success(null, 'Message deleted successfully');
        } else {
            ApiResponse::notFound('Message not found');
        }
    }
} catch (Exception $e) {
    error_log("Delete message error: " . $e->getMessage());
    ApiResponse::serverError('Failed to delete message');
}
