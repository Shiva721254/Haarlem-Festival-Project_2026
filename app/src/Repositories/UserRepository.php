<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IUserRepository;
use App\Models\UserModel;
use App\Enums\UserRole;
use App\CustomException\DuplicateEntryException;

class UserRepository extends Repository implements IUserRepository
{
    /**
     * Users for the admin list, with optional search, role filter and sorting.
     *
     * @return UserModel[]
     */
    public function getAll(string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC'): array
    {
        [$sort, $dir] = $this->normalizeSort($sort, $dir);
        [$whereSql, $params] = $this->userFilters($search, $role);
        $sql = "SELECT UserId, Username, FirstName, LastName, Email, Role, isVerified, isActive, created_at
                FROM users {$whereSql} ORDER BY {$sort} {$dir}";
        return $this->mapUsers($this->fetchAll($sql, $params));
    }
    // --- CRUD OPERATIONS ---
    public function create(UserModel $user): void
    {
        try{
            $sql = 'INSERT INTO users (Username, FirstName, LastName, Email, Password, Role, isVerified, isActive)
                    VALUES (:Username, :FirstName, :LastName, :Email, :Password, :Role, :isVerified, :isActive)';
            $this->execute($sql, $this->userParams($user, false));
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                throw new DuplicateEntryException("This email or username is already registered.");
            }
            throw $e; // Rethrow if it's a different DB error
        }
    }

    public function getById(int $id): ?UserModel
    {
        $sql = 'SELECT UserId, Username, FirstName, LastName, Email, Role, isVerified, isActive, profile_image, phone, address, created_at
                FROM users WHERE UserId = :UserId';
        return $this->fetchUser($sql, ['UserId' => $id]);
    }

    public function getByEmail(string $email): ?UserModel
    {
        return $this->fetchUser('SELECT * FROM users WHERE Email = :Email', ['Email' => $email]);
    }

    public function getByUsername(string $username): ?UserModel
    {
        return $this->fetchUser('SELECT * FROM users WHERE Username = :Username', ['Username' => $username]);
    }

    public function getByLoginIdentifier(string $identifier): ?UserModel
    {
        $sql = 'SELECT * FROM users WHERE Email = :identifier OR Username = :identifier';
        return $this->fetchUser($sql, ['identifier' => $identifier]);
    }

    public function update(UserModel $user): void
    {
        $sql = 'UPDATE users 
                SET Username = :Username, FirstName = :FirstName, LastName = :LastName, Email = :Email, 
                    Role = :Role
                WHERE UserId = :UserId';
        $this->execute($sql, $this->userParams($user, true));
    }

    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone = null, ?string $address = null): void
    {
        $sql = 'UPDATE users SET Username = :Username, FirstName = :FirstName, LastName = :LastName, Email = :Email,
                    phone = :phone, address = :address
                WHERE UserId = :UserId';
        $this->execute($sql, [
            'Username' => $username, 'FirstName' => $firstName, 'LastName' => $lastName,
            'Email' => $email, 'phone' => $phone, 'address' => $address, 'UserId' => $userId,
        ]);
    }

    public function updateProfileImage(int $userId, string $path): void
    {
        $sql = 'UPDATE users SET profile_image = :path WHERE UserId = :id';
        $this->execute($sql, ['path' => $path, 'id' => $userId]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM users WHERE UserId = :UserId', ['UserId' => $id]);
    }

    /**
     * GDPR erasure: strip personal data but keep the row so linked transaction
     * records (orders, invoices) stay intact. The account is deactivated and
     * the email freed with a non-routable placeholder.
     */
    public function anonymize(int $userId): void
    {
        $this->execute($this->anonymizeSql(), ['id' => $userId]);
    }

    private function normalizeSort(string $sort, string $dir): array
    {
        $sortable = ['Username', 'FirstName', 'LastName', 'Email', 'Role', 'created_at'];
        $sort = in_array($sort, $sortable, true) ? $sort : 'LastName';
        return [$sort, strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC'];
    }

    private function userFilters(string $search, string $role): array
    {
        $where = $params = [];
        if ($search !== '') {
            $where[] = '(Username LIKE :q OR FirstName LIKE :q OR LastName LIKE :q OR Email LIKE :q)';
            $params['q'] = '%' . $search . '%';
        }
        if ($role !== '') {
            $where[] = 'Role = :role';
            $params['role'] = $role;
        }
        return [$where ? ('WHERE ' . implode(' AND ', $where)) : '', $params];
    }

    private function userParams(UserModel $user, bool $includeId): array
    {
        $params = ['Username' => $user->Username, 'FirstName' => $user->FirstName, 'LastName' => $user->LastName];
        $params += ['Email' => $user->Email, 'Role' => ($user->Role ?? UserRole::Customer)->value];
        if (!$includeId) {
            return $params + ['Password' => $user->Password, 'isVerified' => (int)$user->isVerified, 'isActive' => (int)$user->isActive];
        }
        return $params + ['UserId' => $user->UserId];
    }

    private function mapUsers(array $rows): array
    {
        return array_map(fn(array $row) => UserModel::fromDb($row), $rows);
    }

    private function fetchUser(string $sql, array $params): ?UserModel
    {
        $data = $this->fetchOne($sql, $params);
        return $data ? UserModel::fromDb($data) : null;
    }

    private function anonymizeSql(): string
    {
        return "UPDATE users SET FirstName = 'Deleted', LastName = 'User', Username = CONCAT('deleted', UserId),
                Email = CONCAT('deleted+', UserId, '@removed.invalid'), Password = '', profile_image = NULL,
                verification_token = NULL, verification_token_expires_at = NULL, reset_token_hash = NULL,
                reset_token_expires_at = NULL, isActive = 0, isVerified = 0 WHERE UserId = :id";
    }

    // --- RESET PASSWORD OPERATIONS ---
    public function updateResetToken(int $userId, ?string $hash, ?string $expiry): void
    {
        $sql = "UPDATE users SET reset_token_hash = :hash, reset_token_expires_at = :expiry
                WHERE UserId = :id";
        $this->execute($sql, ['hash' => $hash, 'expiry' => $expiry, 'id' => $userId]);
    }

    public function findByResetToken(string $hash): ?UserModel
    {
        return $this->fetchUser('SELECT * FROM users WHERE reset_token_hash = :hash', ['hash' => $hash]);
    }
    
    public function updatePassword(int $userId, string $passwordHash): void
    {
        $sql = 'UPDATE users SET Password = :password WHERE UserId = :id';

        $this->execute($sql, ['password' => $passwordHash, 'id' => $userId]);
    }

    // --- VERIFY ACCOUNT OPERATIONS ---
    public function updateVerifyToken(int $userId, ?string $hash, ?string $expiry): void
    {
        $sql = 'UPDATE users SET verification_token = :hash, verification_token_expires_at = :expiry 
                WHERE UserId = :id';
        $this->execute($sql, ['hash' => $hash, 'expiry' => $expiry, 'id' => $userId]);
    }

    public function findByVerifyToken(string $hash): ?UserModel
    {
        return $this->fetchUser('SELECT * FROM users WHERE verification_token = :hash', ['hash' => $hash]);
    }

    public function verifyAccount(int $userId): bool
    {
        return $this->execute('UPDATE users SET isVerified = 1 WHERE UserId = :id', ['id' => $userId]) > 0;
    }
}
