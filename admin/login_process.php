<?php
if (!defined('BASE_URL')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
header("Location: " . $baseS . "login.php?type=admin");
exit();
