<?php

$config = dirname(__DIR__, 2) . '/config/db.php';
if (!file_exists($config)) {
    die("Clinic: config/db.php not found.");
}
require_once $config;
if (!isset($conn) || !$conn) {
    die("Clinic: database connection failed.");
}
?>
