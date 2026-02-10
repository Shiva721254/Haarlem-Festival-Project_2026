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
            $dsn = 'mysql:host=' . Config::dbHost()
                 . ';port=' . Config::dbPort()
                 . ';dbname=' . Config::dbName()
                 . ';charset=utf8mb4';

            self::$connection = new PDO(
                $dsn,
                Config::dbUser(),
                Config::dbPass(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

        } catch (PDOException $ex) {
            throw new Exception('Database connection failed');
        }
    }
}
