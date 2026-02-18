<?php

include 'conn.php';
if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once dirname(__DIR__) . '/auth/session_init.php';
require_once dirname(__DIR__) . '/auth/security.php';
require_once dirname(__DIR__) . '/auth/admin_helper.php';

$logbook_alert = null;
$logbook_admin_bypass = isAdminLoggedIn();
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $id_or_rfid = trim($_POST['id_or_rfid'] ?? '');
    
    if (!empty($id_or_rfid)) {
        $stmt = $conn->prepare("
            SELECT student_id, name, grade, section FROM students
            WHERE (student_id = ? OR rfid_number = ?)
            AND (account_status = 'approved' OR account_status IS NULL OR account_status = '')
            LIMIT 1
        ");
        $stmt->bind_param("ss", $id_or_rfid, $id_or_rfid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $clinic_id = $row['student_id'];
            $name = $row['name'];
            $grade_section = $row['grade'] . ' - ' . $row['section'];
            $date = date('Y-m-d');
            $time = date('H:i');
            
            $ins = $conn->prepare("INSERT INTO clinic_log (clinic_id, name, grade_section, date, time) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("sssss", $clinic_id, $name, $grade_section, $date, $time);
            
            if ($ins->execute()) {
                $logbook_alert = ["type" => "success", "message" => "Visit logged at " . date('h:i A') . ". Welcome, " . htmlspecialchars($name) . "!"];
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

if (isset($_GET['rfid'])) {
    $id_or_rfid = $conn->real_escape_string($_GET['rfid']);
    $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
    $stmt->bind_param("ss", $id_or_rfid, $id_or_rfid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['student_id' => $row['student_id'], 'fullname' => $row['name'], 'grade_section' => $row['grade'] . ' - ' . $row['section']]);
    } else {
        echo json_encode([]);
    }
    exit;
}
if (isset($_GET['search_name'])) {
    $term = '%' . $conn->real_escape_string($_GET['search_name']) . '%';
    $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE name LIKE ? AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') ORDER BY name LIMIT 5");
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $out = [];
    while ($row = $result->fetch_assoc()) {
        $out[] = ['student_id' => $row['student_id'], 'fullname' => $row['name'], 'grade_section' => $row['grade'] . ' - ' . $row['section']];
    }
    echo json_encode($out);
    exit;
}

$logbook_title = 'Clinic';
$logbook_input_name = 'id_or_rfid';
$logbook_form_action = '';
$logbook_submit_label = 'Log Visit';
$logbook_alert = $logbook_alert;
$logbook_search_url = null;
$logbook_accent = 'teal';
$logbook_standalone = true;
require_once dirname(__DIR__) . '/partials/logbook_unified.php';
