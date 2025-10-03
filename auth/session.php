<?php

/**
 * Session Management for Admin Authentication
 */

require_once __DIR__ . '/../config/database.php';

class SessionManager
{

    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isAuthenticated()
    {
        self::startSession();

        if (!isset($_SESSION['admin_user_id'])) {
            return false;
        }

        // For now, use simple session-based authentication
        // In a production environment, you might want to add session tokens to the database
        return true;
    }

    public static function login($username, $password)
    {
        $db = Database::getInstance();

        // Use the existing usuarios table
        $sql = "SELECT id, username, password_hash 
                FROM usuarios 
                WHERE username = ?";

        $stmt = $db->query($sql, [$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Set session variables
        self::startSession();
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = 'admin'; // Default role

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => 'admin'
        ];
    }

    public static function logout()
    {
        self::startSession();
        self::destroySession();
    }

    public static function destroySession()
    {
        self::startSession();
        session_destroy();
    }

    public static function getCurrentUser()
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        self::startSession();

        return [
            'id' => $_SESSION['admin_user_id'],
            'username' => $_SESSION['admin_username'],
            'role' => $_SESSION['admin_role']
        ];
    }

    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            require_once '../config/response.php';
            ApiResponse::unauthorized('Authentication required');
        }
    }

    public static function requireRole($requiredRole)
    {
        self::requireAuth();

        $user = self::getCurrentUser();
        if ($user['role'] !== $requiredRole && $user['role'] !== 'super_admin') {
            require_once '../config/response.php';
            ApiResponse::forbidden('Insufficient permissions');
        }
    }

    public static function cleanupExpiredSessions()
    {
        // No-op for now since we're using simple session-based auth
        return true;
    }
}
