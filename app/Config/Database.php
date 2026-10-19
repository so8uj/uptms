<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    private static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    private static function dsn(bool $withDatabase): string
    {
        $dsn = 'mysql:host=' . env('DB_HOST', 'localhost') . ';port=' . env('DB_PORT', '3306');

        if ($withDatabase) {
            $dsn .= ';dbname=' . env('DB_NAME', 'uptms_db');
        }

        return $dsn . ';charset=utf8mb4';
    }

    public static function connect(): PDO
    {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    self::dsn(true),
                    env('DB_USER', 'root'),
                    env('DB_PASS', ''),
                    self::options()
                );
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                http_response_code(500);
                exit('Database connection failed.');
            }
        }

        return self::$connection;
    }

    /**
     * Used by migrate.php only: creates the database when it does not exist yet.
     */
    public static function createDatabaseIfMissing(): void
    {
        $pdo = new PDO(self::dsn(false), env('DB_USER', 'root'), env('DB_PASS', ''), self::options());
        $name = str_replace('`', '', env('DB_NAME', 'uptms_db'));
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
}
