<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/config/db.php';

$queue_pdo = null;
try {
    $h = isset($host) ? $host : 'localhost';
    $u = isset($username) ? $username : 'root';
    $p = isset($password) ? $password : '';
    $queue_pdo = new PDO("mysql:host=$h;dbname=queuing_system;charset=utf8mb4", $u, $p);
    $queue_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $queue_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    global $pdo;
    $queue_pdo = isset($pdo) ? $pdo : null;
}

class Database {
    public $conn;

    public function getConnection() {
        global $queue_pdo;
        if ($queue_pdo === null) {
            throw new RuntimeException('Queue database not available. Create database "queuing_system" and run queue/queuing_system.sql, or create queue tables in msms_db.');
        }
        $this->conn = $queue_pdo;
        return $this->conn;
    }
}
?>