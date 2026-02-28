<?php
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

    public static function requireOwner($requiredUserId) {
        self::requireAuth();

        if ($_SESSION['UserId'] !== $requiredUserId && $_SESSION['Role']->value !== 'admin') {
            http_response_code(403);
            echo 'Unauthorized: You do not own this resource.';
            exit();
        }
    }

    public static function requireAdminOrOwner($requiredUserId) {
        self::requireAuth();

        $currentUserId = $_SESSION['UserId'];
        $currentUserRole = $_SESSION['Role']->value;

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