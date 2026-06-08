<?php

namespace App\Controllers;

use App\Services\Interfaces\IUserService;
use App\Services\UserService;
use App\Framework\View;
use App\Framework\Flash;
use App\Middleware\AuthMiddleware;
use App\CustomException\DuplicateEntryException;

/**
 * Self-service account management for the logged-in user: edit name/email,
 * change password, and upload an optional profile picture.
 */
class AccountController
{
    private const AVATAR_DIR = __DIR__ . '/../../public/assets/uploads/avatars/';
    private const AVATAR_PUBLIC = '/assets/uploads/avatars/';
    private const ALLOWED_IMAGE = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024; // 2 MB

    private IUserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    // GET: /account
    public function show(): void
    {
        AuthMiddleware::requireAuth();
        $user = $this->userService->getById((int) $_SESSION['UserId']);
        View::render('Account/index', ['user' => $user], 'My Account');
    }

    // POST: /account
    public function update(): void
    {
        AuthMiddleware::requireAuth();
        $userId = (int) $_SESSION['UserId'];

        $firstName = trim($_POST['FirstName'] ?? '');
        $lastName  = trim($_POST['LastName'] ?? '');
        $email     = trim($_POST['Email'] ?? '');

        if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please provide a valid name and email address.');
            header('Location: /account');
            exit();
        }

        try {
            $this->userService->updateProfile($userId, $firstName, $lastName, $email);
            $_SESSION['FirstName'] = $firstName;
            Flash::success('Your profile has been updated.');
        } catch (DuplicateEntryException $e) {
            Flash::error($e->getMessage());
            header('Location: /account');
            exit();
        }

        $this->handlePasswordChange($userId);
        $this->handleAvatarUpload($userId);

        header('Location: /account');
        exit();
    }

    /**
     * Optional password change. Only acts when the user filled the fields.
     */
    private function handlePasswordChange(int $userId): void
    {
        $current = $_POST['CurrentPassword'] ?? '';
        $new     = $_POST['NewPassword'] ?? '';
        $confirm = $_POST['NewPasswordConfirm'] ?? '';

        if ($current === '' && $new === '' && $confirm === '') {
            return; // user did not want to change their password
        }

        if ($new !== $confirm) {
            Flash::error('New passwords do not match; password was not changed.');
            return;
        }
        if (strlen($new) < 8
            || !preg_match('/[A-Z]/', $new)
            || !preg_match('/[0-9]/', $new)
            || !preg_match('/[^A-Za-z0-9]/', $new)) {
            Flash::error('New password does not meet the requirements; password was not changed.');
            return;
        }

        if ($this->userService->changePassword($userId, $current, $new)) {
            Flash::success('Your password has been changed.');
        } else {
            Flash::error('Current password is incorrect; password was not changed.');
        }
    }

    /**
     * Optional profile picture upload with type and size validation.
     */
    private function handleAvatarUpload(int $userId): void
    {
        if (!isset($_FILES['ProfileImage']) || $_FILES['ProfileImage']['error'] === UPLOAD_ERR_NO_FILE) {
            return;
        }

        $file = $_FILES['ProfileImage'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Flash::error('Image upload failed; please try again.');
            return;
        }
        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            Flash::error('Image is too large (max 2 MB).');
            return;
        }

        // Trust the real MIME type, not the client-supplied name/extension.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED_IMAGE[$mime])) {
            Flash::error('Only JPG, PNG, or WEBP images are allowed.');
            return;
        }

        if (!is_dir(self::AVATAR_DIR)) {
            mkdir(self::AVATAR_DIR, 0775, true);
        }

        $filename = 'u' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED_IMAGE[$mime];
        if (!move_uploaded_file($file['tmp_name'], self::AVATAR_DIR . $filename)) {
            Flash::error('Could not save the uploaded image.');
            return;
        }

        $this->userService->updateProfileImage($userId, self::AVATAR_PUBLIC . $filename);
        Flash::success('Your profile picture has been updated.');
    }
}
