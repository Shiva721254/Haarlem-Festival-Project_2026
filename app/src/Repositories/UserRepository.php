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
    public function getAll(): array
    {
        $sql = 'SELECT * 
                FROM users ORDER BY LastName';
        
        $result = $this->getConnection()->query($sql);

        // 1. Fetch as a plain associative array (not a class yet)
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);

        // 2. Map the arrays to UserModel objects manually
        $users = [];

        foreach ($rows as $row) {
            $user = new UserModel();
            
            // Manual assignment
            $user->UserId = $row['UserId'];
            $user->FirstName = $row['FirstName'];
            $user->LastName = $row['LastName'];
            $user->Email = $row['Email'];
            
            // --- THE FIX ---
            // Convert the database string back into the Enum
            $user->Role = \App\Enums\UserRole::tryFrom($row['Role']);
            // ----------------

            $user->isVerified = (bool) $row['isVerified']; // Good practice to cast bools
            $user->isActive = (bool) $row['isActive'];

            $users[] = $user;
        }

        return $users;
    }

    // --- CRUD OPERATIONS ---
    public function create(UserModel $user): void
    {
        try{
            $sql = 'INSERT INTO users (FirstName, LastName, Email, Password, Role, isVerified, isActive, created_at)
                    VALUES (:FirstName, :LastName, :Email, :Password, :Role, :isVerified, :isActive, NOW())';

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':FirstName', $user->FirstName, PDO::PARAM_STR);
            $stmt->bindValue(':LastName', $user->LastName, PDO::PARAM_STR);
            $stmt->bindValue(':Email', $user->Email, PDO::PARAM_STR);
            $stmt->bindValue(':Password', $user->Password, PDO::PARAM_STR);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            
            // Access the scalar value (string or int) of the Enum BY USING ->value
            $roleValue = isset($user->Role) ? $user->Role->value : UserRole::Customer->value;
            $stmt->bindValue(':Role', $roleValue, PDO::PARAM_STR);

            $stmt->bindValue(':isVerified', $user->isVerified, PDO::PARAM_BOOL);
            $stmt->bindValue(':isActive', $user->isActive, PDO::PARAM_BOOL);

            $stmt->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                throw new DuplicateEntryException("This email is already registered.");
            }
            throw $e; // Rethrow if it's a different DB error
        }
    }

    public function getById(int $id): ?UserModel
    {
        $sql = 'SELECT UserId, FirstName, LastName, Email, Role, isVerified, isActive 
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

    public function update(UserModel $user): void
    {
        $sql = 'UPDATE users 
                SET FirstName = :FirstName, LastName = :LastName, Email = :Email, 
                    Role = :Role, updated_at = NOW()
                WHERE UserId = :UserId';

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->bindValue(':FirstName', $user->FirstName, PDO::PARAM_STR);
        $stmt->bindValue(':LastName', $user->LastName, PDO::PARAM_STR);
        $stmt->bindValue(':Email', $user->Email, PDO::PARAM_STR);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);

        $stmt->bindValue(':Role', $user->Role->value, PDO::PARAM_STR);
        $stmt->bindValue(':UserId', $user->UserId, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $sql = 'DELETE FROM users WHERE UserId = :UserId';

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue(':UserId', $id, PDO::PARAM_INT);

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