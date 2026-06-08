<?php

/**
 * Simple forward-only database migration runner.
 *
 * Applies every *.sql file in database/migrations in filename order exactly
 * once, tracking applied files in a `migrations` table. Designed to be run
 * inside the php container where the .env vars and Composer autoloader are
 * already available:
 *
 *   docker compose run --rm php php database/migrate.php
 *
 * Filenames must sort correctly, so prefix them with a zero-padded number,
 * e.g. 001_create_users.sql, 002_create_events.sql.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Config;

$dsn = 'mysql:host=' . Config::dbHost()
     . ';port=' . Config::dbPort()
     . ';dbname=' . Config::dbName()
     . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, Config::dbUser(), Config::dbPass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Could not connect to database: {$e->getMessage()}\n");
    exit(1);
}

// Bookkeeping table for applied migrations.
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )'
);

$applied = $pdo->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$applied = array_flip($applied);

$files = glob(__DIR__ . '/migrations/*.sql');
sort($files, SORT_STRING);

if (!$files) {
    echo "No migration files found in database/migrations.\n";
    exit(0);
}

$ranAny = false;

foreach ($files as $file) {
    $name = basename($file);

    if (isset($applied[$name])) {
        continue;
    }

    echo "Applying {$name} ... ";
    $sql = file_get_contents($file);

    // Note: MySQL implicitly commits DDL (CREATE/ALTER) statements, so wrapping
    // schema migrations in a transaction is not meaningful. We run the file and
    // only record it as applied once it succeeds.
    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare('INSERT INTO migrations (filename) VALUES (:filename)');
        $stmt->execute(['filename' => $name]);
        echo "done\n";
        $ranAny = true;
    } catch (PDOException $e) {
        fwrite(STDERR, "failed\n  {$e->getMessage()}\n");
        exit(1);
    }
}

echo $ranAny ? "Migrations complete.\n" : "Nothing to migrate; database is up to date.\n";
