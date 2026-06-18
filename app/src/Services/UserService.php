<?php
namespace App\Services;

use App\Models\UserModel;
use App\Enums\UserRole;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IUserService;
use App\Services\Interfaces\IMailService;
use App\CustomException\DuplicateEntryException;

class UserService implements IUserService
{
    private IUserRepository $userRepository;
    private IMailService $mailService;

    public function __construct(IUserRepository $userRepository, IMailService $mailService)
    {
        $this->userRepository = $userRepository;
        $this->mailService = $mailService;
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
        $this->assertValidUsername($user->Username);
        $this->assertUnique($user->Username, $user->Email, (int)($user->UserId ?? 0));
        $user->Password = password_hash($user->Password, PASSWORD_DEFAULT);
        $this->userRepository->create($user);
    }

    public function createWithPasswordConfirmation(UserModel $user, string $confirm): void
    {
        if ($user->Password !== $confirm) {
            throw new \Exception('Passwords do not match.');
        }
        $this->create($user);
    }

    public function saveAdminUser(array $post): array
    {
        $user = $this->adminUserFromPost($post);
        try {
            $this->persistAdminUser($user, $post['PasswordConfirm'] ?? '');
            return ['ok' => true, 'user' => $user, 'error' => null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'user' => $user, 'error' => $e->getMessage()];
        }
    }

    private function adminUserFromPost(array $post): UserModel
    {
        $user = new UserModel();
        $user->UserId = isset($post['UserId']) ? (int)$post['UserId'] : 0;
        $user->Username = trim($post['Username'] ?? '');
        $user->FirstName = $post['FirstName'];
        $user->LastName = $post['LastName'];
        $user->Email = $post['Email'];
        $user->Password = $post['Password'] ?? $post['password'] ?? '';
        $user->Role = isset($post['Role']) ? UserRole::from($post['Role']) : UserRole::Customer;
        return $user;
    }

    private function createAdminUser(UserModel $user, string $confirm): void
    {
        $user->isActive = true;
        $user->isVerified = true;
        $this->createWithPasswordConfirmation($user, $confirm);
    }

    private function persistAdminUser(UserModel $user, string $confirm): void
    {
        if ($user->UserId > 0) {
            $this->update($user);
            return;
        }
        $this->createAdminUser($user, $confirm);
    }

    public function registrationCaptchaChallenge(): array
    {
        $a = random_int(2, 9);
        $b = random_int(2, 9);
        return ['question' => "$a + $b", 'answer' => $a + $b];
    }

    public function verifyRegistrationCaptcha(string $answer, ?int $expected): bool
    {
        return $expected !== null && (int)$answer === (int)$expected;
    }

    /**
     * Public sign-up: validate the fields, create a customer account and send
     * the verification email. Returns the outcome for the controller.
     *
     * @param array<string,string> $fields FirstName, LastName, Username, Email
     * @return array{ok:bool,error?:string}
     */
    public function registerCustomer(array $fields, string $password, string $confirm): array
    {
        $error = $this->validateRegistration($fields, $password, $confirm);
        if ($error !== null) {
            return ['ok' => false, 'error' => $error];
        }
        return $this->createCustomer($this->newCustomer($fields, $password));
    }

    private function newCustomer(array $fields, string $password): UserModel
    {
        $user = new UserModel();
        $user->Username   = $fields['Username'];
        $user->FirstName  = $fields['FirstName'];
        $user->LastName   = $fields['LastName'];
        $user->Email      = $fields['Email'];
        $user->Password   = $password;
        $user->Role       = UserRole::Customer; // public sign-ups are always customers
        $user->isVerified = false;
        $user->isActive   = true;
        return $user;
    }

    private function createCustomer(UserModel $user): array
    {
        try {
            $this->create($user);
            $this->sendVerificationEmail($user->Email);
            return ['ok' => true];
        } catch (DuplicateEntryException $e) {
            return ['ok' => false, 'error' => 'An account with this username or email already exists.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Something went wrong creating your account. Please try again.'];
        }
    }

    /**
     * Validate sign-up input. Returns an error message, or null if valid.
     *
     * @param array<string,string> $fields
     */
    private function validateRegistration(array $fields, string $password, string $confirm): ?string
    {
        if (($fields['FirstName'] ?? '') === '' || ($fields['LastName'] ?? '') === '') {
            return 'Please provide your first and last name.';
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $fields['Username'] ?? '')) {
            return 'Username must be 3-30 characters and only contain letters, numbers, dots, underscores, or hyphens.';
        }
        if (!filter_var($fields['Email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            return 'Please provide a valid email address.';
        }
        return $this->passwordError($password, $confirm);
    }

    private function passwordError(string $password, string $confirm): ?string
    {
        if ($password !== $confirm) {
            return 'Passwords do not match.';
        }
        if (!$this->isStrongPassword($password)) {
            return 'Password must be at least 8 characters and include a capital letter, a number, and a symbol.';
        }
        return null;
    }

    public function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }

    public function getById(int $id): ?UserModel
    {
        return $this->userRepository->getById($id);
    }

    public function update(UserModel $user): void
    {
        $user->Username = $this->normalizeUsername($user->Username);
        $this->assertValidUsername($user->Username);
        $this->assertUnique($user->Username, $user->Email, $user->UserId);
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

    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone = null, ?string $address = null): void
    {
        $username = $this->normalizeUsername($username);
        $this->assertValidUsername($username);
        $this->assertUnique($username, $email, $userId);
        $this->userRepository->updateProfile($userId, $username, $firstName, $lastName, $email, $phone, $address);
        $this->mailService->send($email, 'Your Haarlem Festival account was updated', $this->accountUpdatedBody($firstName));
    }

    private function accountUpdatedBody(string $firstName): string
    {
        return "
            <h2>Your account was updated</h2>
            <p>Hi {$firstName}, your Haarlem Festival account details were just changed.</p>
            <p>If this wasn't you, please reset your password immediately.</p>
        ";
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        if (!$this->currentPasswordMatches($userId, $currentPassword)) {
            return false;
        }
        $this->userRepository->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        return true;
    }

    private function currentPasswordMatches(int $userId, string $currentPassword): bool
    {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return false;
        }
        $full = $this->userRepository->getByEmail($user->Email);
        return $full && password_verify($currentPassword, $full->Password);
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
        return $this->sendTokenEmail($email, 30, 'reset');
    }

    public function sendVerificationEmail(string $email): bool
    {
        return $this->sendTokenEmail($email, 1440, 'verify');
    }

    /** Generate a one-time token for the user and email them the matching link. */
    private function sendTokenEmail(string $email, int $minutes, string $kind): bool
    {
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            return false;
        }
        $token = $this->issueToken($user->UserId, $minutes, $kind);
        [$subject, $body] = $this->tokenEmailContent($kind, $token);
        return $this->mailService->send($email, $subject, $body);
    }

    /** Store a fresh hashed token (reset or verify) and return the raw token. */
    private function issueToken(int $userId, int $minutes, string $kind): string
    {
        $token = bin2hex(random_bytes(16));
        $hash = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', time() + 60 * $minutes);
        $kind === 'reset'
            ? $this->userRepository->updateResetToken($userId, $hash, $expiry)
            : $this->userRepository->updateVerifyToken($userId, $hash, $expiry);
        return $token;
    }

    /** @return array{0:string,1:string} subject and HTML body */
    private function tokenEmailContent(string $kind, string $token): array
    {
        if ($kind === 'reset') {
            return ['Reset your Haarlem Festival password', $this->resetEmailBody($token)];
        }
        return ['Verify your Haarlem Festival account', $this->verifyEmailBody($token)];
    }

    private function resetEmailBody(string $token): string
    {
        $link = "http://localhost/resetPassword?token=$token";
        return "
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password. This link expires in 30 minutes.</p>
            <a href='{$link}'>Reset Password</a>
        ";
    }

    private function verifyEmailBody(string $token): string
    {
        $link = "http://localhost/verifyAccount?token=$token";
        return "
            <h2>Verification Account Request</h2>
            <p>Click the link below to verify your account. This link expires in 24 hours.</p>
            <a href='{$link}'>Verify Account</a>
        ";
    }

    public function validateResetToken(string $token): ?UserModel
    {
        $user = $this->userRepository->findByResetToken(hash('sha256', $token));
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

    public function validateVerificationToken(string $token): ?UserModel
    {
        $user = $this->userRepository->findByVerifyToken(hash('sha256', $token));
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

    /** Validate the username format or throw (shared by create/update/profile). */
    private function assertValidUsername(string $username): void
    {
        if (!$this->isValidUsername($username)) {
            throw new DuplicateEntryException('Username must be 3-30 characters and only contain letters, numbers, dots, underscores, or hyphens.');
        }
    }

    /** Ensure the username and email aren't taken by a different account. */
    private function assertUnique(string $username, string $email, int $exceptUserId): void
    {
        $byUsername = $this->userRepository->getByUsername($username);
        if ($byUsername && $byUsername->UserId !== $exceptUserId) {
            throw new DuplicateEntryException('This username is already in use by another account.');
        }
        $byEmail = $this->userRepository->getByEmail($email);
        if ($byEmail && $byEmail->UserId !== $exceptUserId) {
            throw new DuplicateEntryException('This email is already in use by another account.');
        }
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
