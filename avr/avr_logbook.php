<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/security.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_helper.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/config/db.php';

$logbook_alert = null;
$logbook_admin_bypass = isAdminLoggedIn();
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $id_or_rfid = trim($_POST['id_or_rfid'] ?? '');
    
    if (!empty($id_or_rfid)) {
        $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
        $stmt->bind_param("ss", $id_or_rfid, $id_or_rfid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $name = $student['name'];
            $grade_section = $student['grade'] . ' - ' . $student['section'];
            $today = date('Y-m-d');
            
            $ins = $conn->prepare("INSERT INTO log_attendance (Name, GradeSection, NoOfStudent, Date) VALUES (?, ?, 1, ?)");
            $ins->bind_param("sss", $name, $grade_section, $today);
            
            if ($ins->execute()) {
                $logbook_alert = ["type" => "success", "message" => "Welcome, " . htmlspecialchars($name) . "! Attendance logged at " . date('h:i A')];
            } else {
                $logbook_alert = ["type" => "error", "message" => "Database error. Please try again."];
            }
        } else {
            $logbook_alert = ["type" => "error", "message" => "Student not found or account not yet approved."];
        }
    } else {
        $logbook_alert = ["type" => "error", "message" => "Please scan your ID or RFID."];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logbook_alert = ["type" => "error", "message" => "Invalid session token. Please refresh and try again."];
}

$logbook_title = 'AVR';
$logbook_input_name = 'id_or_rfid';
$logbook_form_action = '';
$logbook_submit_label = 'Log Attendance';
$logbook_alert = $logbook_alert;
$logbook_search_url = null;
$logbook_accent = 'pink';
$logbook_standalone = true;
require dirname(__DIR__) . '/partials/logbook_unified.php';
