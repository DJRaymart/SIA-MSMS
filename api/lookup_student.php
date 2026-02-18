<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!defined('APP_ROOT')) {
    require_once dirname(__DIR__) . '/auth/path_config_loader.php';
}
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/config/db.php';

$id = trim($_GET['id'] ?? $_GET['student_id'] ?? $_GET['rfid'] ?? '');

if (empty($id)) {
    echo json_encode(['found' => false, 'message' => 'ID or RFID required']);
    exit;
}

$stmt = $conn->prepare("
    SELECT student_id, name, grade, section, rfid_number
    FROM students
    WHERE (student_id = ? OR rfid_number = ?)
    AND (account_status = 'approved' OR account_status IS NULL OR account_status = '')
    LIMIT 1
");
$stmt->bind_param("ss", $id, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'found' => false,
        'message' => 'Student not found or account not yet approved.'
    ]);
    exit;
}

$row = $result->fetch_assoc();
$grade_section = $row['grade'] . ' - ' . $row['section'];

echo json_encode([
    'found' => true,
    'student_id' => $row['student_id'],
    'name' => $row['name'],
    'fullname' => $row['name'],
    'grade' => $row['grade'],
    'section' => $row['section'],
    'grade_section' => $grade_section,
    'rfid_number' => $row['rfid_number'] ?? null
]);
