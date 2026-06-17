<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\UserModel;
use App\Repositories\Interfaces\IAccountRepository;

class AccountRepository extends Repository implements IAccountRepository
{
    public function getById(int $userId): ?UserModel
    {
        $row = $this->fetchOne(
            'SELECT UserId, Username, FirstName, LastName, Email, Role, isVerified, isActive,
                    profile_image, phone, address, created_at
             FROM users
             WHERE UserId = :id',
            ['id' => $userId]
        );

        return $row === null ? null : UserModel::fromDb($row);
    }

    public function updateProfileImage(int $userId, string $path): void
    {
        $this->execute(
            'UPDATE users SET profile_image = :path WHERE UserId = :id',
            ['path' => $path, 'id' => $userId]
        );
    }

    public function getOrderExportRows(int $userId): array
    {
        return $this->fetchAll(
            'SELECT id, invoice_number, status, total, created_at, paid_at
             FROM orders
             WHERE user_id = :uid
             ORDER BY created_at DESC',
            ['uid' => $userId]
        );
    }
}
