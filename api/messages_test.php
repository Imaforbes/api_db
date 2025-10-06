<?php
/**
 * Messages Test API - Without Authentication
 * For testing purposes only
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/response.php';

try {
    $db = Database::getInstance();
    
    // Get query parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM datos";
    $countStmt = $db->query($countSql);
    $total = $countStmt->fetch()['total'];
    
    // Get messages
    $sql = "SELECT id, nombre as name, email, mensaje as message, fecha as created_at 
            FROM datos 
            ORDER BY fecha DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $db->query($sql, [$limit, $offset]);
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
    
    echo json_encode([
        'success' => true,
        'message' => 'Messages retrieved successfully (TEST MODE)',
        'data' => [
            'items' => $messages,
            'pagination' => $pagination
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving messages',
        'error' => $e->getMessage()
    ]);
}
