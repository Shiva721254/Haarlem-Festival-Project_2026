<?php
namespace App\Framework;

use App\Config;
use Exception;
use PDO;
use PDOException;

class Repository
{
    private static ?PDO $connection = null;

    public function getConnection(): ?PDO
    {
        if (self::$connection === null) {
            $this->connect();
        }

        return self::$connection;
    }

    private function connect(): void
    {
        try {
            $connectionString = 'mysql:host=' . Config::DB_SERVER_NAME . 
                                ';dbname=' . Config::DB_NAME . 
                                ';charset=utf8mb4';

            self::$connection = new PDO(
                $connectionString,
                Config::DB_USERNAME,
                Config::DB_PASSWORD
            );
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $ex) {
            throw new Exception("Database connection failed: " . $ex->getMessage());
        }
    }
}