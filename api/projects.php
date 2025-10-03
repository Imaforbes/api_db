<?php

/**
 * Projects API Endpoint
 * Handles CRUD operations for portfolio projects
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

// Also set CORS headers using the handler (as backup)
CorsHandler::setHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            handleGetProjects($db);
            break;
        case 'POST':
            handleCreateProject($db);
            break;
        case 'PUT':
            handleUpdateProject($db);
            break;
        case 'DELETE':
            handleDeleteProject($db);
            break;
        default:
            ApiResponse::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Projects API error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred while processing the request');
}

function handleGetProjects($db)
{
    $projectId = intval($_GET['id'] ?? 0);

    if ($projectId > 0) {
        // Get single project
        $sql = "SELECT * FROM projects WHERE id = ? AND status = 'published'";
        $stmt = $db->query($sql, [$projectId]);
        $project = $stmt->fetch();

        if (!$project) {
            ApiResponse::notFound('Project not found');
        }

        // Decode JSON fields
        if ($project['technologies']) {
            $project['technologies'] = json_decode($project['technologies'], true);
        }

        ApiResponse::success($project, 'Project retrieved successfully');
    } else {
        // Get all projects
        $featured = $_GET['featured'] ?? null;
        $limit = min(100, max(1, intval($_GET['limit'] ?? 20)));

        $whereConditions = ["status = 'published'"];
        $params = [];

        if ($featured === 'true') {
            $whereConditions[] = "featured = 1";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        $sql = "SELECT id, title, description, short_description, image_url, 
                       technologies, github_url, live_url, featured, sort_order, created_at
                FROM projects 
                {$whereClause} 
                ORDER BY sort_order ASC, created_at DESC 
                LIMIT ?";

        $params[] = $limit;

        $stmt = $db->query($sql, $params);
        $projects = $stmt->fetchAll();

        // Decode JSON fields for each project
        foreach ($projects as &$project) {
            if ($project['technologies']) {
                $project['technologies'] = json_decode($project['technologies'], true);
            }
        }

        ApiResponse::success($projects, 'Projects retrieved successfully');
    }
}

function handleCreateProject($db)
{
    // Check authentication for admin operations
    require_once '../auth/session.php';
    if (!SessionManager::isAuthenticated()) {
        ApiResponse::unauthorized('Authentication required');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    // Validate required fields
    $errors = [];

    if (empty($input['title'])) {
        $errors['title'] = 'Title is required';
    }

    if (empty($input['description'])) {
        $errors['description'] = 'Description is required';
    }

    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }

    // Sanitize and validate input
    $title = InputValidator::sanitizeString($input['title'], 200);
    $description = InputValidator::sanitizeText($input['description'], 5000);
    $shortDescription = InputValidator::sanitizeString($input['short_description'] ?? '', 500);
    $imageUrl = InputValidator::sanitizeString($input['image_url'] ?? '', 500);
    $githubUrl = InputValidator::sanitizeString($input['github_url'] ?? '', 500);
    $liveUrl = InputValidator::sanitizeString($input['live_url'] ?? '', 500);
    $technologies = $input['technologies'] ?? [];
    $featured = isset($input['featured']) ? (bool)$input['featured'] : false;
    $status = $input['status'] ?? 'draft';
    $sortOrder = intval($input['sort_order'] ?? 0);

    // Validate status
    if (!in_array($status, ['draft', 'published', 'archived'])) {
        $errors['status'] = 'Invalid status value';
    }

    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }

    // Validate URLs if provided
    if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $errors['image_url'] = 'Invalid image URL';
    }

    if ($githubUrl && !filter_var($githubUrl, FILTER_VALIDATE_URL)) {
        $errors['github_url'] = 'Invalid GitHub URL';
    }

    if ($liveUrl && !filter_var($liveUrl, FILTER_VALIDATE_URL)) {
        $errors['live_url'] = 'Invalid live URL';
    }

    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }

    // Insert project
    $sql = "INSERT INTO projects (title, description, short_description, image_url, 
                                technologies, github_url, live_url, featured, status, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $technologiesJson = json_encode($technologies);

    $stmt = $db->query($sql, [
        $title,
        $description,
        $shortDescription,
        $imageUrl,
        $technologiesJson,
        $githubUrl,
        $liveUrl,
        $featured,
        $status,
        $sortOrder
    ]);

    if ($stmt->rowCount() > 0) {
        $projectId = $db->lastInsertId();

        ApiResponse::success([
            'id' => $projectId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'created_at' => date('c')
        ], 'Project created successfully', 201);
    } else {
        ApiResponse::serverError('Failed to create project');
    }
}

function handleUpdateProject($db)
{
    // Check authentication
    require_once '../auth/session.php';
    if (!SessionManager::isAuthenticated()) {
        ApiResponse::unauthorized('Authentication required');
    }

    $projectId = intval($_GET['id'] ?? 0);

    if (!$projectId) {
        ApiResponse::error('Project ID is required', 400);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }

    // Build update query
    $updateFields = [];
    $params = [];

    $allowedFields = [
        'title',
        'description',
        'short_description',
        'image_url',
        'technologies',
        'github_url',
        'live_url',
        'featured',
        'status',
        'sort_order'
    ];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "{$field} = ?";

            if ($field === 'technologies') {
                $params[] = json_encode($input[$field]);
            } elseif ($field === 'featured') {
                $params[] = (bool)$input[$field];
            } elseif ($field === 'sort_order') {
                $params[] = intval($input[$field]);
            } else {
                $params[] = $input[$field];
            }
        }
    }

    if (empty($updateFields)) {
        ApiResponse::error('No valid fields to update', 400);
    }

    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $projectId;

    $sql = "UPDATE projects SET " . implode(', ', $updateFields) . " WHERE id = ?";

    $stmt = $db->query($sql, $params);

    if ($stmt->rowCount() > 0) {
        ApiResponse::success(null, 'Project updated successfully');
    } else {
        ApiResponse::notFound('Project not found');
    }
}

function handleDeleteProject($db)
{
    // Check authentication
    require_once '../auth/session.php';
    if (!SessionManager::isAuthenticated()) {
        ApiResponse::unauthorized('Authentication required');
    }

    $projectId = intval($_GET['id'] ?? 0);

    if (!$projectId) {
        ApiResponse::error('Project ID is required', 400);
    }

    $sql = "DELETE FROM projects WHERE id = ?";
    $stmt = $db->query($sql, [$projectId]);

    if ($stmt->rowCount() > 0) {
        ApiResponse::success(null, 'Project deleted successfully');
    } else {
        ApiResponse::notFound('Project not found');
    }
}
