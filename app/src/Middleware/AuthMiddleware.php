<?php
namespace App\Middleware;

use App\Enums\UserRole;

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
    
    /** Require a logged-in user and return their id. */
    public static function userId(): int {
        self::requireAuth();
        return (int) $_SESSION['UserId'];
    }

    /** Drop the auth identity and rotate the session id, keeping the session itself. */
    public static function logout(): void {
        unset($_SESSION['UserId'], $_SESSION['Role'], $_SESSION['FirstName']);
        session_regenerate_id(true);
    }

    public static function requireAdmin() {
        self::requireAuth();
        if (self::currentRole() !== UserRole::Admin->value) {
            http_response_code(403);
            echo 'Access Denied';
            exit();
        }
    }

    public static function requireStaff() {
        self::requireAuth();
        if (!in_array(self::currentRole(), [UserRole::Admin->value, UserRole::Employee->value], true)) {
            http_response_code(403);
            echo 'Access Denied';
            exit();
        }
    }

    public static function requireOwner($requiredUserId) {
        self::requireAuth();

        if ($_SESSION['UserId'] !== $requiredUserId && self::currentRole() !== UserRole::Admin->value) {
            http_response_code(403);
            echo 'Unauthorized: You do not own this resource.';
            exit();
        }
    }

    public static function requireAdminOrOwner($requiredUserId) {
        self::requireAuth();

        $currentUserId = $_SESSION['UserId'];
        $currentUserRole = self::currentRole();

        $isAdmin = ($currentUserRole === UserRole::Admin->value);
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

    private static function currentRole(): ?string {
        $role = $_SESSION['Role'] ?? null;
        return is_object($role) && property_exists($role, 'value') ? $role->value : (is_string($role) ? $role : null);
    }
}
