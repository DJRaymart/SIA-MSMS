<?php
if (!defined('APP_ROOT')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$app_root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/check_admin_access.php';
require_once $app_root . '/config/db.php';

$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$baseS = $base === '' ? '/' : $base . '/';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$student_id = trim($_POST['student_id'] ?? $_GET['student_id'] ?? '');

if (!in_array($action, ['approve', 'reject']) || empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$new_status = $action === 'approve' ? 'approved' : 'rejected';
$stmt = $conn->prepare("UPDATE students SET account_status = ? WHERE student_id = ? AND account_status = 'pending'");
$stmt->bind_param("ss", $new_status, $student_id);
$stmt->execute();

if ($conn->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => $action === 'approve' ? 'Student approved. They can now log in.' : 'Registration rejected.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found or already processed.']);
}
