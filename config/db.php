<?php

require_once __DIR__ . '/db_config.php';

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$dbname = defined('DB_NAME') ? DB_NAME : 'msms_db';
$username = defined('DB_USER') ? DB_USER : 'root';
$password = defined('DB_PASSWORD') ? DB_PASSWORD : '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new RuntimeException("MySQL Connection failed.");
    }
    $conn->set_charset("utf8mb4");
} catch(Exception $e) {
    error_log('MySQLi connection failure: ' . $e->getMessage());
    throw new RuntimeException("Database connection error.");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log('PDO connection failure: ' . $e->getMessage());
    throw new RuntimeException("Database connection error.");
}
?>
