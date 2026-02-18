<?php
if (!defined('BASE_URL')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_auth.php';

AdminAuth::logout();

header("Location: " . $baseS . "login.php?type=admin&success=" . urlencode("You have been logged out successfully"));
exit();
