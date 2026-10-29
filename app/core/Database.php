<?php

namespace App\Core;

require_once dirname(__DIR__) . '/config/messages.php';

use PDO;
use PDOException;

/**
 * Singleton Database wrapper quản lý kết nối PDO duy nhất tới MySQL.
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $configFile = dirname(__DIR__, 2) . '/config/config.php';
        if (!file_exists($configFile)) {
            $configFile = dirname(__DIR__, 2) . '/config/config.example.php';
        }

        $config = require $configFile;
        $db = $config['db'] ?? [];

        $host    = $db['host'] ?? '127.0.0.1';
        $port    = $db['port'] ?? 3306;
        $dbname  = $db['dbname'] ?? 'marvel_wms';
        $charset = $db['charset'] ?? 'utf8mb4';
        $user    = $db['username'] ?? 'root';
        $pass    = $db['password'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Không lộ mật khẩu trong chuỗi lỗi
            throw new PDOException("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    // Chặn clone và unserialize singleton
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getConnection(): PDO
    {
        return self::getInstance()->connection;
    }
}
