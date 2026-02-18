<?php
if (!defined('BASE_URL')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/student_auth.php';

StudentAuth::logout();

header("Location: " . $baseS . "login.php?type=student&logged_out=1");
exit();
