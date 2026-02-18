<?php

require_once __DIR__ . '/db_config.php';

class Database
{
    
    private static $instance = null;

    private $pdo = null;

    private $mysqli = null;

    private static $dsn = '';

    private static $user = '';

    private static $pass = '';

    private static $dbname = '';

    private static $host = '';

    private function __construct()
    {
        self::$host   = defined('DB_HOST') ? DB_HOST : 'localhost';
        self::$dbname = defined('DB_NAME') ? DB_NAME : 'msms_db';
        self::$user   = defined('DB_USER') ? DB_USER : 'root';
        self::$pass   = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        self::$dsn    = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->getPdo();
    }

    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO(self::$dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException(
                    'Database connection failed: ' . $e->getMessage()
                );
            }
        }
        return $this->pdo;
    }

    public function getMysqli(): mysqli
    {
        if ($this->mysqli === null) {
            $this->mysqli = new mysqli(self::$host, self::$user, self::$pass, self::$dbname);
            if ($this->mysqli->connect_error) {
                throw new RuntimeException(
                    'MySQLi connection failed: ' . $this->mysqli->connect_error
                );
            }
            $this->mysqli->set_charset('utf8mb4');
        }
        return $this->mysqli;
    }

    public static function connect(): mysqli
    {
        return self::getInstance()->getMysqli();
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }
}
