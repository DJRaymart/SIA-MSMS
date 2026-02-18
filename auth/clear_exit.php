<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['booking_verified_student'], $_SESSION['avr_verified_student'], $_SESSION['clinic_verified_student']);
$redirect = $_GET['redirect'] ?? '/';
if (!defined('BASE_URL') && file_exists(__DIR__ . '/path_config_loader.php')) {
    require_once __DIR__ . '/path_config_loader.php';
}
$base = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/';
if (strpos($redirect, '/') !== 0 && strpos($redirect, 'http') !== 0) {
    $redirect = $base . ltrim($redirect, '/');
}
header('Location: ' . $redirect);
exit;
