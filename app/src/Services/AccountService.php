<?php
namespace App\Services;

use App\Models\UserModel;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IUserService;
use App\Services\Interfaces\IOrderService;
use App\CustomException\DuplicateEntryException;

/**
 * Account self-service business logic: profile/password updates, GDPR data
 * export and erasure. Keeps this logic out of the controller.
 */
class AccountService implements IAccountService
{
    private IUserService $userService;
    private IOrderService $orderService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->orderService = new OrderService();
    }

    public function getById(int $userId): ?UserModel
    {
        return $this->userService->getById($userId);
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
        if ($new !== $confirm) {
            return ['ok' => false, 'message' => 'New passwords do not match; password was not changed.'];
        }
        if (!$this->isStrongPassword($new)) {
            return ['ok' => false, 'message' => 'New password does not meet the requirements; password was not changed.'];
        }
        if ($this->userService->changePassword($userId, $current, $new)) {
            return ['ok' => true, 'message' => 'Your password has been changed.'];
        }
        return ['ok' => false, 'message' => 'Current password is incorrect; password was not changed.'];
    }

    public function updateProfileImage(int $userId, string $path): void
    {
        $this->userService->updateProfileImage($userId, $path);
    }

    public function buildDataExport(int $userId): array
    {
        $user = $this->userService->getById($userId);

        $orders = [];
        foreach ($this->orderService->getByUser($userId) as $order) {
            $orders[] = [
                'id'             => $order->id,
                'invoice_number' => $order->invoice_number,
                'status'         => $order->status,
                'total'          => $order->total,
                'created_at'     => $order->created_at,
                'paid_at'        => $order->paid_at,
            ];
        }

        return [
            'exported_at' => date('c'),
            'account'     => [
                'first_name' => $user->FirstName ?? null,
                'last_name'  => $user->LastName ?? null,
                'username'   => $user->Username ?? null,
                'email'      => $user->Email ?? null,
                'role'       => $user->Role->value ?? null,
                'created_at' => $user->created_at ?? null,
            ],
            'orders'      => $orders,
        ];
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
