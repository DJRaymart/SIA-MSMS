<?php

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/student_auth.php';

function isStudentLoggedIn() {
    return StudentAuth::isLoggedIn();
}

function getStudentInfo() {
    return StudentAuth::getStudentInfo();
}

function requireStudentLogin() {
    StudentAuth::requireLogin();
}
