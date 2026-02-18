<?php

require_once __DIR__ . '/env_loader.php';

$local_config = __DIR__ . '/db_config.local.php';
if (is_file($local_config)) {
    require_once $local_config;
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'msms_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
}
