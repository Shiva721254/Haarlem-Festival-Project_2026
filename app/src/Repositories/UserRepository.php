<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IUserRepository;
use App\Models\UserModel;
use App\Enums\UserRole;
use App\CustomException\DuplicateEntryException;
use \PDO;

class UserRepository extends Repository implements IUserRepository
{
    /**
     * Users for the admin list, with optional search, role filter and sorting.
     *
     * @return UserModel[]
     */
    public function getAll(string $search = '', string $role = '', string $sort = 'LastName', string $dir = 'ASC'): array
    {
        // Whitelist sort column and direction to avoid SQL injection.
        $sortable = ['Username', 'FirstName', 'LastName', 'Email', 'Role', 'created_at'];
        if (!in_array($sort, $sortable, true)) {
            $sort = 'LastName';
        }
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(Username LIKE :q OR FirstName LIKE :q OR LastName LIKE :q OR Email LIKE :q)';
            $params['q'] = '%' . $search . '%';
        }
        if ($role !== '') {
            $where[] = 'Role = :role';
            $params['role'] = $role;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "SELECT UserId, Username, FirstName, LastName, Email, Role, isVerified, isActive, created_at
                FROM users {$whereSql} ORDER BY {$sort} {$dir}";

        $rows = $this->fetchAll($sql, $params);

        $users = [];
        foreach ($rows as $row) {
            $user = new UserModel();
            $user->UserId = (int) $row['UserId'];
            $user->Username = $row['Username'];
            $user->FirstName = $row['FirstName'];
            $user->LastName = $row['LastName'];
            $user->Email = $row['Email'];
            $user->Role = \App\Enums\UserRole::tryFrom($row['Role']);
            $user->isVerified = (bool) $row['isVerified'];
            $user->isActive = (bool) $row['isActive'];
            $user->created_at = $row['created_at'] ?? null;
            $users[] = $user;
        }

        return $users;
    }
    // --- CRUD OPERATIONS ---
    public function create(UserModel $user): void
    {
        try{
            $sql = 'INSERT INTO users (Username, FirstName, LastName, Email, Password, Role, isVerified, isActive)
                    VALUES (:Username, :FirstName, :LastName, :Email, :Password, :Role, :isVerified, :isActive)';

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':Username', $user->Username, PDO::PARAM_STR);
            $stmt->bindValue(':FirstName', $user->FirstName, PDO::PARAM_STR);
            $stmt->bindValue(':LastName', $user->LastName, PDO::PARAM_STR);
            $stmt->bindValue(':Email', $user->Email, PDO::PARAM_STR);
            $stmt->bindValue(':Password', $user->Password, PDO::PARAM_STR);
            
            // Access the scalar value (string or int) of the Enum BY USING ->value
            $roleValue = isset($user->Role) ? $user->Role->value : UserRole::Customer->value;
            $stmt->bindValue(':Role', $roleValue, PDO::PARAM_STR);

            $stmt->bindValue(':isVerified', $user->isVerified, PDO::PARAM_BOOL);
            $stmt->bindValue(':isActive', $user->isActive, PDO::PARAM_BOOL);

            $stmt->execute();
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
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':UserId', $id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? UserModel::fromDb($data) : null;
    }

    public function getByEmail(string $email): ?UserModel
    {
        $sql = 'SELECT * FROM users WHERE Email = :Email';

        $stmt = $this->getConnection()->prepare($sql);
        
        $stmt->bindValue(':Email', $email, PDO::PARAM_STR); 
        
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? UserModel::fromDb($data) : null;
    }

    public function getByUsername(string $username): ?UserModel
    {
        $sql = 'SELECT * FROM users WHERE Username = :Username';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':Username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? UserModel::fromDb($data) : null;
    }

    public function getByLoginIdentifier(string $identifier): ?UserModel
    {
        $sql = 'SELECT * FROM users WHERE Email = :identifier OR Username = :identifier';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? UserModel::fromDb($data) : null;
    }

    public function update(UserModel $user): void
    {
        $sql = 'UPDATE users 
                SET Username = :Username, FirstName = :FirstName, LastName = :LastName, Email = :Email, 
                    Role = :Role
                WHERE UserId = :UserId';

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(':Username', $user->Username, PDO::PARAM_STR);
        $stmt->bindValue(':FirstName', $user->FirstName, PDO::PARAM_STR);
        $stmt->bindValue(':LastName', $user->LastName, PDO::PARAM_STR);
        $stmt->bindValue(':Email', $user->Email, PDO::PARAM_STR);

        $stmt->bindValue(':Role', $user->Role->value, PDO::PARAM_STR);
        $stmt->bindValue(':UserId', $user->UserId, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function updateProfile(int $userId, string $username, string $firstName, string $lastName, string $email, ?string $phone = null, ?string $address = null): void
    {
        $sql = 'UPDATE users SET Username = :Username, FirstName = :FirstName, LastName = :LastName, Email = :Email,
                    phone = :phone, address = :address
                WHERE UserId = :UserId';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':Username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':FirstName', $firstName, PDO::PARAM_STR);
        $stmt->bindValue(':LastName', $lastName, PDO::PARAM_STR);
        $stmt->bindValue(':Email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, $phone === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, $address === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':UserId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function updateProfileImage(int $userId, string $path): void
    {
        $sql = 'UPDATE users SET profile_image = :path WHERE UserId = :id';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $sql = 'DELETE FROM users WHERE UserId = :UserId';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':UserId', $id, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * GDPR erasure: strip personal data but keep the row so linked transaction
     * records (orders, invoices) stay intact. The account is deactivated and
     * the email freed with a non-routable placeholder.
     */
    public function anonymize(int $userId): void
    {
        $sql = "UPDATE users SET
                    FirstName = 'Deleted', LastName = 'User',
                    Username = CONCAT('deleted', UserId),
                    Email = CONCAT('deleted+', UserId, '@removed.invalid'),
                    Password = '', profile_image = NULL,
                    verification_token = NULL, verification_token_expires_at = NULL,
                    reset_token_hash = NULL, reset_token_expires_at = NULL,
                    isActive = 0, isVerified = 0
                WHERE UserId = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    // --- RESET PASSWORD OPERATIONS ---
    public function updateResetToken(int $userId, ?string $hash, ?string $expiry): void
    {
        $sql = "UPDATE users SET reset_token_hash = :hash, reset_token_expires_at = :expiry
                WHERE UserId = :id";
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['hash' => $hash, 'expiry' => $expiry, 'id' => $userId]);
    }

    public function findByResetToken(string $hash): ?UserModel
    {
        $sql = "SELECT * FROM users WHERE reset_token_hash = :hash";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['hash' => $hash]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? UserModel::fromDb($data) : null;  
    }
    
    public function updatePassword(int $userId, string $passwordHash): void
    {
        $sql = 'UPDATE users SET Password = :password WHERE UserId = :id';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':password', $passwordHash, \PDO::PARAM_STR);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
    }

    // --- VERIFY ACCOUNT OPERATIONS ---
    public function updateVerifyToken(int $userId, ?string $hash, ?string $expiry): void
    {
        $sql = 'UPDATE users SET verification_token = :hash, verification_token_expires_at = :expiry 
                WHERE UserId = :id';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['hash' => $hash, 'expiry' => $expiry, 'id' => $userId]);
    }

    public function findByVerifyToken(string $hash): ?UserModel
    {
        $sql = 'SELECT * FROM users WHERE verification_token = :hash';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(['hash' => $hash]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? UserModel::fromDb($data) : null;  
    }

    public function verifyAccount(int $userId): bool
    {
        $sql = 'UPDATE users SET isVerified = 1 WHERE UserId = :id';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
