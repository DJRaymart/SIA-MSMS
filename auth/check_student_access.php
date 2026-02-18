<?php

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/admin_helper.php';
require_once __DIR__ . '/student_auth.php';

if (!isAdminLoggedIn()) {
    
    StudentAuth::requireLogin();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    exit('Invalid or missing CSRF token.');
}
