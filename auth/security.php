<?php

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/path_config_loader.php';
}

function msms_csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function msms_verify_csrf_token(?string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function msms_safe_redirect(?string $candidate, string $fallback = '/'): string
{
    $base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
    $baseSlash = $base === '' ? '/' : $base . '/';
    $fallback = trim($fallback) !== '' ? $fallback : $baseSlash;

    $value = trim((string) $candidate);
    if ($value === '') {
        return $fallback;
    }

    if (strpos($value, "\r") !== false || strpos($value, "\n") !== false) {
        return $fallback;
    }

    if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $value) || str_starts_with($value, '//')) {
        return $fallback;
    }

    if ($value[0] === '?') {
        $sep = strpos($fallback, '?') !== false ? '&' : '?';
        return rtrim($fallback, '?&') . $sep . ltrim($value, '?');
    }

    if ($value[0] === '/') {
        return str_starts_with($value, $baseSlash) || $baseSlash === '/' ? $value : $fallback;
    }

    return $baseSlash . ltrim($value, '/');
}

function msms_render_csrf_auto_form_script(): void
{
    $token = htmlspecialchars(msms_csrf_token(), ENT_QUOTES, 'UTF-8');
    echo '<script>(function(){var t="' . $token . '";document.addEventListener("DOMContentLoaded",function(){var forms=document.querySelectorAll("form[method=\'post\'],form[method=\'POST\']");for(var i=0;i<forms.length;i++){var f=forms[i];if(f.querySelector("input[name=\'csrf_token\']")){continue;}var input=document.createElement("input");input.type="hidden";input.name="csrf_token";input.value=t;f.appendChild(input);}});})();</script>';
}
