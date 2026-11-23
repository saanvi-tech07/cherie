<?php
/**
 * CHÉRIE — Database Connection Manager (PDO)
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

// Database Credentials (Default for XAMPP on Windows)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'cherie_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;
    private static bool $connectionAttempted = false;
    private static ?string $lastError = null;

    /**
     * Get singleton PDO instance
     */
    public static function getConnection(): ?PDO {
        if (self::$instance === null && !self::$connectionAttempted) {
            self::$connectionAttempted = true;
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                self::$lastError = $e->getMessage();
                self::$instance = null;
            }
        }
        return self::$instance;
    }

    /**
     * Check if connection is active
     */
    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }

    /**
     * Get last connection error message
     */
    public static function getLastError(): ?string {
        return self::$lastError;
    }

    /**
     * Helper to run prepared queries with parameters
     */
    public static function query(string $sql, array $params = []): ?PDOStatement {
        $pdo = self::getConnection();
        if (!$pdo) {
            return null;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Helper to fetch single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        if (!$stmt) return null;
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Helper to fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        if (!$stmt) return [];
        return $stmt->fetchAll();
    }

    /**
     * Helper to insert and get last inserted ID
     */
    public static function insert(string $sql, array $params = []): string|false {
        $pdo = self::getConnection();
        if (!$pdo) return false;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }

    /**
     * Helper to execute update/delete and get affected rows
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }
}
