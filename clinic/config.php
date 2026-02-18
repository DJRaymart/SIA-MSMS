<?php

if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/auth/path_config_loader.php';
}
if (!defined('CLINIC_BASE')) {
    define('CLINIC_BASE', (BASE_URL === '' ? '' : BASE_URL) . '/clinic');
}
