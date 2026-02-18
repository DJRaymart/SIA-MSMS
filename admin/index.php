<?php
session_start();
if (!defined('BASE_URL')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

header("Location: dashboard.php");
exit();
