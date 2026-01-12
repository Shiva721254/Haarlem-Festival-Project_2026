<?php
// app/src/Middleware/AuthMiddleware.php
namespace App\Middleware;

class AuthMiddleware {
    public static function requireAuth() {
        // Ensure session is started if not already
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['UserId'])) {
            header('Location: /showLogin');
            exit();
        }
    }
    
    public static function requireAdmin() {
        self::requireAuth();
        if ($_SESSION['Role']->value !== 'admin') {
            http_response_code(403);
            echo 'Access Denied';
            exit();
        }
    }

    /**
     * Verifies if the logged-in user is the owner of the resource.
     * Use this for routes like /profile/edit/{id}
     */
    public static function requireOwner($requiredUserId) {
        self::requireAuth();

        // Check if the logged-in user matches the ID of the resource owner
        // or optionally allow admins to bypass this check
        if ($_SESSION['UserId'] !== $requiredUserId && $_SESSION['Role']->value !== 'admin') {
            http_response_code(403);
            echo 'Unauthorized: You do not own this resource.';
            exit();
        }
    }

    // app/src/Middleware/AuthMiddleware.php

    public static function requireAdminOrOwner($requiredUserId) {
        self::requireAuth();

        $currentUserId = $_SESSION['UserId'];
        $currentUserRole = $_SESSION['Role']->value;

        // Logic: ALLOW if user is admin OR if user is the owner
        $isAdmin = ($currentUserRole === 'admin');
        $isOwner = ($currentUserId == $requiredUserId);

        if (!$isAdmin && !$isOwner) {
            http_response_code(403);
            echo 'Access Denied: You do not have permission to modify this user.';
            exit();
        }
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(): bool {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        return !empty($token) && hash_equals($sessionToken, $token);
    }
}