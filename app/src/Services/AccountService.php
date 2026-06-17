<?php
namespace App\Services;

use App\Models\UserModel;
use App\Repositories\AccountRepository;
use App\Repositories\Interfaces\IAccountRepository;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IUserService;
use App\CustomException\DuplicateEntryException;
use App\Framework\ImageUpload;

/**
 * Account self-service business logic: profile/password updates, GDPR data
 * export and erasure. Keeps this logic out of the controller.
 */
class AccountService implements IAccountService
{
    private IAccountRepository $accountRepository;
    private IUserService $userService;

    public function __construct(IAccountRepository $accountRepository, IUserService $userService)
    {
        $this->accountRepository = $accountRepository;
        $this->userService = $userService;
    }

    public function getById(int $userId): ?UserModel
    {
        return $this->accountRepository->getById($userId);
    }

    public function updateFromRequest(int $userId, array $post): array
    {
        $firstName = trim($post['FirstName'] ?? '');
        $profile = $this->profileUpdate($userId, $post, $firstName);
        if (!$profile['ok']) {
            return ['ok' => false, 'messages' => [$profile], 'firstName' => null];
        }
        $messages = $this->optionalUpdates($userId, $post, $profile);
        return ['ok' => true, 'messages' => $messages, 'firstName' => $firstName];
    }

    private function profileUpdate(int $userId, array $post, string $firstName): array
    {
        return $this->updateProfile(
            $userId,
            trim($post['Username'] ?? ''),
            $firstName,
            trim($post['LastName'] ?? ''),
            trim($post['Email'] ?? ''),
            trim($post['Phone'] ?? '') ?: null,
            trim($post['Address'] ?? '') ?: null
        );
    }

    /** Apply the optional password and avatar changes, collecting their messages. */
    private function optionalUpdates(int $userId, array $post, array $profile): array
    {
        $messages = [$profile];
        $password = $this->changePassword($userId, $post['CurrentPassword'] ?? '', $post['NewPassword'] ?? '', $post['NewPasswordConfirm'] ?? '');
        if ($password !== null) {
            $messages[] = $password;
        }
        $image = $this->uploadProfileImage($userId);
        if ($image !== null) {
            $messages[] = $image;
        }
        return $messages;
    }

    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone, ?string $address): array
    {
        if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Please provide a valid name and email address.'];
        }
        try {
            $this->userService->updateProfile($userId, $username, $firstName, $lastName, $email, $phone, $address);
            return ['ok' => true, 'message' => 'Your profile has been updated.'];
        } catch (DuplicateEntryException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function changePassword(int $userId, string $current, string $new, string $confirm): ?array
    {
        if ($current === '' && $new === '' && $confirm === '') {
            return null; // user did not want to change their password
        }
        $error = $this->passwordChangeError($new, $confirm);
        if ($error !== null) {
            return ['ok' => false, 'message' => $error];
        }
        return $this->applyPasswordChange($userId, $current, $new);
    }

    private function passwordChangeError(string $new, string $confirm): ?string
    {
        if ($new !== $confirm) {
            return 'New passwords do not match; password was not changed.';
        }
        if (!$this->isStrongPassword($new)) {
            return 'New password does not meet the requirements; password was not changed.';
        }
        return null;
    }

    private function applyPasswordChange(int $userId, string $current, string $new): array
    {
        if ($this->userService->changePassword($userId, $current, $new)) {
            return ['ok' => true, 'message' => 'Your password has been changed.'];
        }
        return ['ok' => false, 'message' => 'Current password is incorrect; password was not changed.'];
    }

    public function uploadProfileImage(int $userId): ?array
    {
        $result = ImageUpload::handle('ProfileImage', 'avatars');
        if (!$result['ok']) {
            return ['ok' => false, 'message' => $result['message']];
        }
        if (!isset($result['path'])) {
            return null;
        }
        $this->accountRepository->updateProfileImage($userId, $result['path']);
        return ['ok' => true, 'message' => 'Your profile picture has been updated.'];
    }

    public function buildDataExport(int $userId): array
    {
        $user = $this->accountRepository->getById($userId);
        return [
            'exported_at' => date('c'),
            'account'     => $this->accountExport($user),
            'orders'      => $this->ordersExport($userId),
        ];
    }

    /** @return array<string,mixed> */
    private function accountExport(?UserModel $user): array
    {
        return [
            'first_name' => $user->FirstName ?? null,
            'last_name'  => $user->LastName ?? null,
            'username'   => $user->Username ?? null,
            'email'      => $user->Email ?? null,
            'role'       => $user->Role->value ?? null,
            'created_at' => $user->created_at ?? null,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function ordersExport(int $userId): array
    {
        return array_map(fn($order) => [
            'id'             => $order['id'],
            'invoice_number' => $order['invoice_number'],
            'status'         => $order['status'],
            'total'          => $order['total'],
            'created_at'     => $order['created_at'],
            'paid_at'        => $order['paid_at'],
        ], $this->accountRepository->getOrderExportRows($userId));
    }

    public function deleteAccount(int $userId): void
    {
        $this->userService->deleteOwnAccount($userId);
    }

    private function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }
}
