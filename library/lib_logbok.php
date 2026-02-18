<?php

session_start();
require_once dirname(__DIR__) . '/auth/security.php';
require_once 'db.php';
if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once dirname(__DIR__) . '/auth/admin_helper.php';

$logbook_alert = null;
$logbook_admin_bypass = isAdminLoggedIn();
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $id_or_rfid = trim($_POST['id_or_rfid'] ?? '');
    
    if (!empty($id_or_rfid)) {
        try {
            $user = null;
            $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE institutional_id = ? OR rfid_number = ?");
            $stmt->execute([$id_or_rfid, $id_or_rfid]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $student_stmt = $conn->prepare("SELECT student_id, name, grade, section, rfid_number FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
                $student_stmt->execute([$id_or_rfid, $id_or_rfid]);
                $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($student) {
                    $check_stmt = $conn->prepare("SELECT * FROM tbl_users WHERE institutional_id = ?");
                    $check_stmt->execute([$student['student_id']]);
                    $user = $check_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user) {
                        $grade_section = $student['grade'] . ' - ' . $student['section'];
                        $user_rfid = $student['rfid_number'] ?? $student['student_id'];
                        $full_name_unique = $student['name'] . ' [' . $student['student_id'] . ']';
                        $pw_hash = password_hash('lib-kiosk-' . $student['student_id'], PASSWORD_DEFAULT);
                        $ins_stmt = $conn->prepare("INSERT INTO tbl_users (full_name, institutional_id, email, contact_number, user_type, grade_section, rfid_number, password, status, balance, history) VALUES (?, ?, '', NULL, 'Student', ?, ?, ?, 'Active', 0, '')");
                        $ins_stmt->execute([$full_name_unique, $student['student_id'], $grade_section, $user_rfid, $pw_hash]);
                        $user = ['user_id' => $conn->lastInsertId(), 'full_name' => $student['name'], 'institutional_id' => $student['student_id'], 'rfid_number' => $user_rfid, 'grade_section' => $grade_section, 'email' => '', 'user_type' => 'Student', 'balance' => 0, 'status' => 'Active'];
                    }
                }
            }
            
            if ($user && ($user['status'] ?? 'Active') === 'Active') {
                $ins = $conn->prepare("INSERT INTO log_book (user_id, full_name, institutional_id, rfid_number, grade_section, email, user_type, login_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $ins->execute([$user['user_id'], $user['full_name'], $user['institutional_id'], $user['rfid_number'] ?? '', $user['grade_section'] ?? '', $user['email'] ?? '', $user['user_type'] ?? 'User']);
                $logbook_alert = ["type" => "success", "message" => "Welcome, " . htmlspecialchars($user['full_name']) . "! Logged at " . date('h:i A')];
            } elseif ($user) {
                $logbook_alert = ["type" => "error", "message" => "Your account is not active. Please contact the library."];
            } else {
                $logbook_alert = ["type" => "error", "message" => "Student not found or account not yet approved."];
            }
        } catch (PDOException $e) {
            $logbook_alert = ["type" => "error", "message" => "Database error. Please try again."];
        }
    } else {
        $logbook_alert = ["type" => "error", "message" => "Please scan your ID or RFID."];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logbook_alert = ["type" => "error", "message" => "Invalid session token. Please refresh and try again."];
}

if (isset($_GET['rfid'])) {
    $id = trim($_GET['rfid']);
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE institutional_id = ? OR rfid_number = ?");
    $stmt->execute([$id, $id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) {
        $s = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
        $s->execute([$id, $id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row) echo json_encode(['student_id' => $row['student_id'], 'fullname' => $row['name'], 'grade_section' => $row['grade'] . ' - ' . $row['section']]);
        else echo json_encode([]);
    } else {
        echo json_encode(['student_id' => $u['institutional_id'], 'fullname' => $u['full_name'], 'grade_section' => $u['grade_section']]);
    }
    exit;
}

$logbook_title = 'Library';
$logbook_input_name = 'id_or_rfid';
$logbook_form_action = '';
$logbook_submit_label = 'Log In';
$logbook_alert = $logbook_alert;
$logbook_search_url = null;
$logbook_accent = 'emerald';
$logbook_standalone = true;
require dirname(__DIR__) . '/partials/logbook_unified.php';
