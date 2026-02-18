<?php

$ict_config = dirname(__DIR__) . '/../config/db.php';
if (!file_exists($ict_config)) {
    die("ICT database connection failed: config/db.php not found.");
}
require_once $ict_config;
if (!isset($conn) || !$conn) {
    die("ICT database connection failed: MSMS config not loaded.");
}
class Database {
    public static function connect() {
        global $conn;
        return $conn;
    }
}
