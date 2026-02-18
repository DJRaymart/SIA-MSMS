<?php

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/admin_helper.php';
require_once __DIR__ . '/student_helper.php';

function isUserLoggedIn() {
    return isAdminLoggedIn() || isStudentLoggedIn();
}

function getLoginRedirectUrl($type = 'student') {
    if (!defined('BASE_URL')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
    $baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
    $currentUrl = $_SERVER['REQUEST_URI'];
    return $baseS . 'login.php?type=' . $type . '&redirect=' . urlencode($currentUrl);
}

function getButtonHref($targetUrl, $requireType = 'student') {
    
    if (isAdminLoggedIn()) {
        return $targetUrl;
    }

    if ($requireType === 'student' && isStudentLoggedIn()) {
        return $targetUrl;
    }

    if ($requireType === 'admin' && isAdminLoggedIn()) {
        return $targetUrl;
    }

    return getLoginRedirectUrl($requireType);
}

function getButtonDisabledAttr($requireType = 'student') {
    
    return ['class' => '', 'onclick' => ''];
}
