<?php

if (!defined('BASE_URL')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/admin_auth.php';

if (!AdminAuth::isLoggedIn()) {
    
    $currentUrl = $_SERVER['REQUEST_URI'];
    $redirectUrl = $baseS . 'login.php?type=admin&redirect=' . urlencode($currentUrl) . '&error=' . urlencode('Admin access required. Please login to continue.');
    
    header("Location: " . $redirectUrl);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    exit('Invalid or missing CSRF token.');
}
