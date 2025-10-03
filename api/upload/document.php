<?php

/**
 * Document Upload API Endpoint
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
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        ApiResponse::error('No file uploaded or upload error', 400);
    }

    $file = $_FILES['document'];

    // Validate file type
    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];

    $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        ApiResponse::error('Invalid file type. Only PDF, DOC, DOCX, and TXT files are allowed.', 400);
    }

    // Validate file size (10MB max)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        ApiResponse::error('File too large. Maximum size is 10MB.', 400);
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $fileExtension;

    // Create upload directory if it doesn't exist
    $uploadDir = '../../uploads/documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadPath = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Generate public URL
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'], 3);
        $publicUrl = $baseUrl . '/uploads/documents/' . $filename;

        ApiResponse::success([
            'filename' => $filename,
            'url' => $publicUrl,
            'size' => $file['size'],
            'type' => $file['type'],
            'extension' => $fileExtension
        ], 'Document uploaded successfully');
    } else {
        ApiResponse::serverError('Failed to save file');
    }
} catch (Exception $e) {
    error_log("Document upload error: " . $e->getMessage());
    ApiResponse::serverError('An error occurred during file upload');
}
