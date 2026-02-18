<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/security.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/admin_helper.php';
include "../config/db.php";

$logbook_alert = null;
$logbook_admin_bypass = isAdminLoggedIn();
date_default_timezone_set("Asia/Manila");

if ($_SERVER["REQUEST_METHOD"] === "POST" && msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $id_or_rfid = trim($_POST['student_id']);
    $today = date("Y-m-d");
    $now_time = date("h:i A");

    $student = $conn->prepare("
        SELECT student_id, name, grade, section FROM students
        WHERE (student_id = ? OR rfid_number = ?)
        AND (account_status = 'approved' OR account_status IS NULL OR account_status = '')
        LIMIT 1
    ");
    $student->bind_param("ss", $id_or_rfid, $id_or_rfid);
    $student->execute();
    $result = $student->get_result();

    if ($result->num_rows === 0) {
        $logbook_alert = ["type" => "error", "message" => "Identity not found or account not yet approved."];
    } else {
        $data = $result->fetch_assoc();
        $check = $conn->prepare("SELECT log_id FROM logs WHERE student_id = ? AND date = ? LIMIT 1");
        $check->bind_param("ss", $data['student_id'], $today);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $logbook_alert = ["type" => "error", "message" => "Duplicate entry detected for today."];
        } else {
            $stmt = $conn->prepare("INSERT INTO logs (student_id, name, grade, section, date, time) VALUES (?,?,?,?,?,?)");
            $sId = strtoupper($data['student_id']);
            $sName = strtoupper($data['name']);
            $sGrade = strtoupper($data['grade']);
            $sSec = strtoupper($data['section']);
            $stmt->bind_param("ssssss", $sId, $sName, $sGrade, $sSec, $today, $now_time);

            if ($stmt->execute()) {
                $logbook_alert = ["type" => "success", "message" => "Access authorized at $now_time"];
            } else {
                $logbook_alert = ["type" => "error", "message" => "Database write failure."];
            }
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $logbook_alert = ["type" => "error", "message" => "Invalid session token. Please refresh and try again."];
}

$logbook_title = 'Science Lab';
$logbook_input_name = 'student_id';
$logbook_form_action = '';
$logbook_submit_label = 'Authorize Access';
$logbook_search_url = '../auth/search_student.php';
$logbook_accent = 'blue';
$logbook_standalone = true;
require_once dirname(__DIR__, 2) . '/partials/logbook_unified.php';
