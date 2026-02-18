<?php

require_once __DIR__ . '/admin_auth.php';

function isAdminLoggedIn() {
    return AdminAuth::isLoggedIn();
}

function getAdminInfo() {
    return AdminAuth::getAdminInfo();
}

function requireAdminLogin($redirectUrl = null) {
    AdminAuth::requireLogin($redirectUrl);
}

function isFeatureEnabled() {
    return isAdminLoggedIn();
}

function getDisabledClasses() {
    if (!isAdminLoggedIn()) {
        return 'opacity-50 cursor-not-allowed pointer-events-none';
    }
    return '';
}

function getDisabledAttribute() {
    if (!isAdminLoggedIn()) {
        return 'disabled';
    }
    return '';
}

function adminOnly($content) {
    if (isAdminLoggedIn()) {
        return $content;
    }
    return '';
}

function guestOnly($content) {
    if (!isAdminLoggedIn()) {
        return $content;
    }
    return '';
}
