<?php
namespace App\Services;

use App\Models\UserModel;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IUserService;
use App\CustomException\DuplicateEntryException;

class UserService implements IUserService
{
    private IUserRepository $userRepository;
    private MailService $mailService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->mailService = new MailService();
    }

    public function getAll(string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC'): array
    {
        return $this->userRepository->getAll($search, $role, $sort, $dir);
    }

    public function create(UserModel $user): void
    {
        $user->Username = $this->normalizeUsername($user->Username);
        if (empty($user->Password)) {
            throw new \Exception('Password is required for new users.');
        }

        $confirm = $_POST['PasswordConfirm'] ?? $_POST['password_confirm'] ?? '';
        if ($user->Password !== $confirm) {
            throw new \Exception('Passwords do not match.');
        }
        if (!$this->isValidUsername($user->Username)) {
            throw new \Exception('Username must be 3-30 characters and only contain letters, numbers, dots, underscores, or hyphens.');
        }
        if ($this->userRepository->getByUsername($user->Username)) {
            throw new DuplicateEntryException('Warning: Username already exists.');
        }
        if ($this->userRepository->getByEmail($user->Email)) {
            throw new DuplicateEntryException('Warning: Email already exists.');
        }

        $user->Password = password_hash($user->Password, PASSWORD_DEFAULT);
        $this->userRepository->create($user);
    }

    public function getById(int $id): ?UserModel
    {
        return $this->userRepository->getById($id);
    }

    public function update(UserModel $user): void
    {
        $user->Username = $this->normalizeUsername($user->Username);
        if (!$this->isValidUsername($user->Username)) {
            throw new \Exception('Username must be 3-30 characters and only contain letters, numbers, dots, underscores, or hyphens.');
        }
        $existingUsername = $this->userRepository->getByUsername($user->Username);
        if ($existingUsername && $existingUsername->UserId !== $user->UserId) {
            throw new DuplicateEntryException('This username is already in use by another account.');
        }
        $existingEmail = $this->userRepository->getByEmail($user->Email);
        if ($existingEmail && $existingEmail->UserId !== $user->UserId) {
            throw new DuplicateEntryException('This email is already in use by another account.');
        }

        $this->userRepository->update($user);
    }

    public function delete(int $id): void
    {
        $this->userRepository->delete($id);
    }

    public function deleteOwnAccount(int $userId): void
    {
        $this->userRepository->anonymize($userId);
    }

    public function sendConfirmEmail(): void
    {
    }

    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone = null, ?string $address = null): void
    {
        $username = $this->normalizeUsername($username);
        if (!$this->isValidUsername($username)) {
            throw new DuplicateEntryException('Username must be 3-30 characters and only contain letters, numbers, dots, underscores, or hyphens.');
        }
        $existingUsername = $this->userRepository->getByUsername($username);
        if ($existingUsername && $existingUsername->UserId !== $userId) {
            throw new DuplicateEntryException('This username is already in use by another account.');
        }
        $existing = $this->userRepository->getByEmail($email);
        if ($existing && $existing->UserId !== $userId) {
            throw new DuplicateEntryException('This email is already in use by another account.');
        }

        $this->userRepository->updateProfile($userId, $username, $firstName, $lastName, $email, $phone, $address);

        $message = "
            <h2>Your account was updated</h2>
            <p>Hi {$firstName}, your Haarlem Festival account details were just changed.</p>
            <p>If this wasn't you, please reset your password immediately.</p>
        ";
        $this->mailService->send($email, 'Your Haarlem Festival account was updated', $message);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return false;
        }

        $full = $this->userRepository->getByEmail($user->Email);
        if (!$full || !password_verify($currentPassword, $full->Password)) {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userRepository->updatePassword($userId, $hash);
        return true;
    }

    public function updateProfileImage(int $userId, string $path): void
    {
        $this->userRepository->updateProfileImage($userId, $path);
    }

    public function authenticate(string $identifier, string $password): ?UserModel
    {
        $user = $this->userRepository->getByLoginIdentifier(trim($identifier));
        if (!$user) {
            return null;
        }

        return password_verify($password, $user->Password) ? $user : null;
    }

    public function sendPasswordReset(string $email): bool
    {
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(16));
        $tokenHash = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', time() + 60 * 30);

        $this->userRepository->updateResetToken($user->UserId, $tokenHash, $expiry);

        $resetLink = "http://localhost/resetPassword?token=$token";
        $message = "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password. This link expires in 30 minutes.</p>
            <a href='{$resetLink}'>Reset Password</a>
        ";

        return $this->mailService->send($email, 'Reset your Haarlem Festival password', $message);
    }

    public function validateResetToken(string $token): ?UserModel
    {
        $hash = hash('sha256', $token);
        $user = $this->userRepository->findByResetToken($hash);

        if ($user && strtotime($user->reset_token_expires_at) > time()) {
            return $user;
        }

        return null;
    }

    public function resetUserPassword(UserModel $user, string $newPassword): void
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userRepository->updatePassword($user->UserId, $hashedPassword);
        $this->userRepository->updateResetToken($user->UserId, null, null);
    }

    public function completePasswordReset(string $token, string $password): bool
    {
        $user = $this->validateResetToken($token);
        if (!$user) {
            return false;
        }

        $this->resetUserPassword($user, $password);
        return true;
    }

    public function sendVerificationEmail(string $email): bool
    {
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            return false;
        }
        $token = bin2hex(random_bytes(16));
        $tokenHash = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', time() + 60 * 1440);

        $this->userRepository->updateVerifyToken($user->UserId, $tokenHash, $expiry);
        $resetLink = "http://localhost/verifyAccount?token=$token";
        $message = "
            <h2>Verification Account Request</h2>
            <p>Click the link below to verify your account. This link expires in 24 hours.</p>
            <a href='{$resetLink}'>Verify Account</a>
        ";
        return $this->mailService->send($email, 'Verify your Haarlem Festival account', $message);
    }

    public function validateVerificationToken(string $token): ?UserModel
    {
        $hash = hash('sha256', $token);
        $user = $this->userRepository->findByVerifyToken($hash);
        if ($user && strtotime($user->verification_token_expires_at) > time()) {
            return $user;
        }

        return null;
    }

    public function verifyUser(UserModel $user): void
    {
        $this->userRepository->verifyAccount($user->UserId);
        $this->userRepository->updateVerifyToken($user->UserId, null, null);
    }

    public function completeAccountVerification(string $token): bool
    {
        $user = $this->validateVerificationToken($token);
        if (!$user) {
            return false;
        }
        $this->verifyUser($user);
        return true;
    }

    private function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    private function isValidUsername(string $username): bool
    {
        return (bool)preg_match('/^[a-z0-9._-]{3,30}$/', $username);
    }
}
