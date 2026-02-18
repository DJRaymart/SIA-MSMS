<?php
if (defined('BASE_URL')) {
    return;
}
$path_config = dirname(__DIR__) . '/config/path_config.php';
if (is_file($path_config)) {
    require_once $path_config;
    return;
}
$app_root = dirname(__DIR__);
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $app_root);
}
if (!defined('BASE_URL')) {
    $doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    $real_doc = ($doc_root !== '' && @realpath($doc_root) !== false) ? rtrim(str_replace('\\', '/', realpath($doc_root)), '/') : '';
    $real_app = ($app_root !== '' && @realpath($app_root) !== false) ? rtrim(str_replace('\\', '/', realpath($app_root)), '/') : '';
    if ($real_doc !== '' && $real_app !== '' && strpos($real_app, $real_doc) === 0) {
        $base = substr($real_app, strlen($real_doc));
        $base = ($base === '' || $base === '/') ? '' : ('/' . trim($base, '/'));
    } else {
        $base = '';
    }
    define('BASE_URL', $base);
}
