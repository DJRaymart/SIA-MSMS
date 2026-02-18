<?php
if (!defined('BASE_URL')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
require_once __DIR__ . '/admin_auth.php';

if (!AdminAuth::isLoggedIn()) {
    
    $currentUrl = $_SERVER['REQUEST_URI'];
    header("Location: " . $baseS . "login.php?type=admin&redirect=" . urlencode($currentUrl) . "&error=" . urlencode("Please login to access this page"));
    exit();
}
