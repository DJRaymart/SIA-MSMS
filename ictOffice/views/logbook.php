<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once dirname(__DIR__, 2) . '/auth/session_init.php';
require_once dirname(__DIR__, 2) . '/auth/security.php';
require_once dirname(__DIR__, 2) . '/auth/admin_helper.php';

$logbook_alert = null;
$logbook_admin_bypass = isAdminLoggedIn();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $logbook_alert = ["type" => "error", "message" => "Invalid session token. Please refresh and try again."];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['student_id'] ?? ''))) {
    $app_root = dirname(__DIR__, 2);
    require_once $app_root . '/ictOffice/config/database.php';
    require_once $app_root . '/ictOffice/models/User.php';
    require_once $app_root . '/ictOffice/models/Log.php';
    
    $id = trim($_POST['student_id']);
    $userModel = new User();
    $user = $userModel->show($id);
    
    if ($user) {
        $logModel = new Log();
        $activeLog = $logModel->findActiveLogByUserId($user['id']);
        $now = date('Y-m-d H:i:s');
        
        if ($activeLog) {
            $logModel->updateTimeOut($activeLog['log_id'], $now);
            $logbook_alert = ["type" => "success", "message" => "Logged out successfully."];
        } else {
            $logModel->create(['timeIn' => $now, 'userId' => $user['id']]);
            $logbook_alert = ["type" => "success", "message" => "Logged in successfully at " . date('h:i A') . "."];
        }
    } else {
        $logbook_alert = ["type" => "error", "message" => "Student not found or account not yet approved."];
    }
}

$ict_public = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/ictOffice/public';
$logbook_title = 'ICT Office';
$logbook_input_name = 'student_id';
$logbook_form_action = $ict_public . '/?page=logbook';
$logbook_submit_label = 'Log Attendance';
$logbook_alert = $logbook_alert ?? null;
$logbook_search_url = null;
$logbook_accent = 'cyan';
$logbook_standalone = true;
require dirname(__DIR__, 2) . '/partials/logbook_unified.php';
