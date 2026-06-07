<?php
/**
 * Database Class - SQLite PDO
 */

class Database {
    private static ?PDO $instance = null;
    private static ?string $dbPath = null;

    public static function init(string $dbPath): void {
        self::$dbPath = $dbPath;
    }

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dbDir = dirname(self::$dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            self::$instance = new PDO('sqlite:' . self::$dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            self::$instance->exec('PRAGMA journal_mode=WAL');
            self::$instance->exec('PRAGMA foreign_keys=ON');
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$sets} WHERE {$where}";
        $stmt = self::query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        self::query($sql, $params);
        return self::query($sql, $params)->rowCount();
    }

    public static function createTables(): void {
        $db = self::getInstance();

        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                language TEXT DEFAULT 'fa',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME DEFAULT NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS servers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                version TEXT NOT NULL DEFAULT '1.20.1',
                type TEXT NOT NULL DEFAULT 'paper',
                port INTEGER NOT NULL,
                ram INTEGER NOT NULL DEFAULT 1024,
                max_players INTEGER DEFAULT 20,
                motd TEXT DEFAULT 'A Minecraft Server',
                gamemode TEXT DEFAULT 'survival',
                difficulty TEXT DEFAULT 'easy',
                whitelist INTEGER DEFAULT 0,
                status TEXT DEFAULT 'offline',
                pid INTEGER DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS server_versions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                version TEXT NOT NULL,
                type TEXT NOT NULL DEFAULT 'paper',
                installed INTEGER DEFAULT 0,
                path TEXT DEFAULT NULL,
                installed_at DATETIME DEFAULT NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL
            )
        ");
    }

    public static function isInstalled(): bool {
        try {
            $db = self::getInstance();
            $db->query("SELECT COUNT(*) FROM users");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
