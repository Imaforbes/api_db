<?php

/**
 * Messages Management API Endpoint
 * Handles CRUD operations for contact messages (Admin only)
 */

// Set CORS headers FIRST, before any other output
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/response.php';
require_once '../auth/session.php';

// Also set CORS headers using the handler (as backup)
CorsHandler::setHeaders();

// Check authentication
if (!SessionManager::isAuthenticated()) {
    ApiResponse::unauthorized('Authentication required');
}

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            handleGetMessages($db);
            break;
        case 'PATCH':
            handleUpdateMessage($db);
            break;
        case 'DELETE':
            handleDeleteMessage($db);
            break;
        default:
            ApiResponse::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Messages API error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred while processing the request');
}

function handleGetMessages($db)
{
    // Get query parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
    $status = $_GET['status'] ?? null;
    $search = $_GET['search'] ?? null;

    $offset = ($page - 1) * $limit;

    // Build WHERE clause
    $whereConditions = [];
    $params = [];

    if ($status && in_array($status, ['new', 'read', 'replied', 'archived'])) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }

    if ($search) {
        $whereConditions[] = "(nombre LIKE ? OR email LIKE ? OR mensaje LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM datos {$whereClause}";
    $countStmt = $db->query($countSql, $params);
    $total = $countStmt->fetch()['total'];

    // Get messages
    $sql = "SELECT id, nombre as name, email, mensaje as message, fecha as created_at 
            FROM datos 
            {$whereClause} 
            ORDER BY fecha DESC 
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->query($sql, $params);
    $messages = $stmt->fetchAll();

    // Calculate pagination info
    $totalPages = ceil($total / $limit);
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;

    $pagination = [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'items_per_page' => $limit,
        'has_next' => $hasNext,
        'has_prev' => $hasPrev
    ];

    ApiResponse::paginated($messages, $pagination, 'Messages retrieved successfully');
}

function handleUpdateMessage($db)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    $messageId = intval($_GET['id'] ?? 0);
    if (!$messageId) {
        ApiResponse::error('Message ID is required', 400);
    }

    // Validate status if provided
    if (isset($input['status'])) {
        if (!in_array($input['status'], ['new', 'read', 'replied', 'archived'])) {
            ApiResponse::error('Invalid status value', 400);
        }
    }

    // Build update query
    $updateFields = [];
    $params = [];

    if (isset($input['status'])) {
        $updateFields[] = "status = ?";
        $params[] = $input['status'];
    }

    if (empty($updateFields)) {
        ApiResponse::error('No valid fields to update', 400);
    }

    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $messageId;

    $sql = "UPDATE contact_messages SET " . implode(', ', $updateFields) . " WHERE id = ?";

    $stmt = $db->query($sql, $params);

    if ($stmt->rowCount() > 0) {
        ApiResponse::success(null, 'Message updated successfully');
    } else {
        ApiResponse::notFound('Message not found');
    }
}

function handleDeleteMessage($db)
{
    $messageId = intval($_GET['id'] ?? 0);

    if (!$messageId) {
        ApiResponse::error('Message ID is required', 400);
    }

    // Use the datos table instead of contact_messages
    $sql = "DELETE FROM datos WHERE id = ?";
    $stmt = $db->query($sql, [$messageId]);

    if ($stmt->rowCount() > 0) {
        ApiResponse::success(null, 'Message deleted successfully');
    } else {
        ApiResponse::notFound('Message not found');
    }
}
