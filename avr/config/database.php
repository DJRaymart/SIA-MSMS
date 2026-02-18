<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/db.php';

function getDBConnection() {
    global $conn;
    return $conn;
}
?>

