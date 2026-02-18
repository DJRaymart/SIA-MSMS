<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    if (!defined('BASE_URL')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
    $ict_public = (rtrim(BASE_URL, '/') === '' ? '' : rtrim(BASE_URL, '/')) . '/ictOffice/public';
    header("Location: " . $ict_public . "/?page=dashboard");
?>