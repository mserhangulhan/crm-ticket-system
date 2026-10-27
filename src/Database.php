<?php

namespace Crm\Ticket;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(?string $dsn = null): PDO
    {
        if (self::$pdo === null) {
            $connectionString = $dsn ?? getenv('DB_DSN') ?: 'sqlite::memory:';
            self::$pdo = new PDO($connectionString);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::initSchema(self::$pdo);
        }

        return self::$pdo;
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
        self::initSchema(self::$pdo);
    }

    public static function reset(): void
    {
        self::$pdo = null;
    }

    private static function initSchema(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                priority TEXT NOT NULL DEFAULT 'medium',
                status TEXT NOT NULL DEFAULT 'open',
                customer_email TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}
