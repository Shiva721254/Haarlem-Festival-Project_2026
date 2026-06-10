<?php

namespace App\Controllers;

use App\Services\AccountService;
use App\Services\Interfaces\IAccountService;
use App\Framework\View;
use App\Framework\Flash;
use App\Framework\ImageUpload;
use App\Middleware\AuthMiddleware;

/**
 * Self-service account management for the logged-in user. The controller only
 * reads the request, delegates to AccountService, flashes and redirects.
 */
class AccountController
{
    private IAccountService $accountService;

    public function __construct()
    {
        $this->accountService = new AccountService();
    }

    // GET: /account
    public function show(): void
    {
        AuthMiddleware::requireAuth();
        $user = $this->accountService->getById((int) $_SESSION['UserId']);
        View::render('Account/index', ['user' => $user], 'My Account');
    }

    // POST: /account
    public function update(): void
    {
        AuthMiddleware::requireAuth();
        $userId = (int) $_SESSION['UserId'];

        $firstName = trim($_POST['FirstName'] ?? '');
        $profile = $this->accountService->updateProfile(
            $userId,
            trim($_POST['Username'] ?? ''),
            $firstName,
            trim($_POST['LastName'] ?? ''),
            trim($_POST['Email'] ?? ''),
            trim($_POST['Phone'] ?? '') ?: null,
            trim($_POST['Address'] ?? '') ?: null
        );

        if (!$profile['ok']) {
            Flash::error($profile['message']);
            header('Location: /account');
            exit();
        }
        $_SESSION['FirstName'] = $firstName;
        Flash::success($profile['message']);

        $this->flashPasswordChange($userId);
        $this->flashAvatarUpload($userId);

        header('Location: /account');
        exit();
    }

    // GET: /account/data — GDPR right of access.
    public function exportData(): void
    {
        AuthMiddleware::requireAuth();
        $payload = $this->accountService->buildDataExport((int) $_SESSION['UserId']);

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="my-haarlem-festival-data.json"');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }

    // POST: /account/delete — GDPR right to erasure.
    public function deleteAccount(): void
    {
        AuthMiddleware::requireAuth();
        $this->accountService->deleteAccount((int) $_SESSION['UserId']);

        // Log out: drop the auth identity and rotate the session id, but keep
        // the session so the confirmation message survives the redirect.
        unset($_SESSION['UserId'], $_SESSION['Role'], $_SESSION['FirstName']);
        session_regenerate_id(true);

        Flash::success('Your account has been deleted and your personal data removed.');
        header('Location: /');
        exit();
    }

    private function flashPasswordChange(int $userId): void
    {
        $result = $this->accountService->changePassword(
            $userId,
            $_POST['CurrentPassword'] ?? '',
            $_POST['NewPassword'] ?? '',
            $_POST['NewPasswordConfirm'] ?? ''
        );
        if ($result !== null) {
            $result['ok'] ? Flash::success($result['message']) : Flash::error($result['message']);
        }
    }

    private function flashAvatarUpload(int $userId): void
    {
        $result = ImageUpload::handle('ProfileImage', 'avatars');
        if (!$result['ok']) {
            Flash::error($result['message']);
        } elseif (isset($result['path'])) {
            $this->accountService->updateProfileImage($userId, $result['path']);
            Flash::success('Your profile picture has been updated.');
        }
    }
}
