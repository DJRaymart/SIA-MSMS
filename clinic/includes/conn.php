<?php

$clinic_config = dirname(__DIR__, 2) . '/config/db.php';
if (!file_exists($clinic_config)) {
    die("Clinic: config/db.php not found.");
}
require_once $clinic_config;
if (!isset($conn) || !$conn) {
    die("Clinic: database connection failed.");
}

?>
