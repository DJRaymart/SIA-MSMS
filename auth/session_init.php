<?php

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/path_config_loader.php';
}
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
