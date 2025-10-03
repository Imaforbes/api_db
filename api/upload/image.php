<?php

/**
 * Image Upload API Endpoint
 */

require_once '../../config/database.php';
require_once '../../config/response.php';
require_once '../../auth/session.php';

// Set CORS headers
CorsHandler::setHeaders();

// Check authentication
SessionManager::requireAuth();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        ApiResponse::error('No file uploaded or upload error', 400);
    }

    $file = $_FILES['image'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        ApiResponse::error('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.', 400);
    }

    // Validate file size (5MB max)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        ApiResponse::error('File too large. Maximum size is 5MB.', 400);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;

    // Create upload directory if it doesn't exist
    $uploadDir = '../../uploads/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadPath = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Generate public URL
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'], 3);
        $publicUrl = $baseUrl . '/uploads/images/' . $filename;

        ApiResponse::success([
            'filename' => $filename,
            'url' => $publicUrl,
            'size' => $file['size'],
            'type' => $fileType
        ], 'Image uploaded successfully');
    } else {
        ApiResponse::serverError('Failed to save file');
    }
} catch (Exception $e) {
    error_log("Image upload error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred during file upload');
}
