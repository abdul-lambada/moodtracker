<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    private static ?PDO $pdo = null;

    public static function instance(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = config('app.db');
        if (!is_array($config)) {
            throw new RuntimeException('Database configuration not found.');
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            // Ensure connection truly uses utf8mb4 on environments where DSN charset is ignored
            // This is important for storing and reading emoji characters correctly
            self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            throw new RuntimeException('Failed to connect to database: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        return self::$pdo;
    }
}
