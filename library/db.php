<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/security.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/config/db.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && (isset($_SESSION['librarian_id']) || isset($_SESSION['user_id']))
    && !msms_verify_csrf_token($_POST['csrf_token'] ?? null)
) {
    http_response_code(419);
    exit('Invalid or missing CSRF token.');
}

$conn = $pdo;
?>