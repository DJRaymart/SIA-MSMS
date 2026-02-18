<?php
include 'conn.php';
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['rfid'])) {
    $rfid = trim((string) $_GET['rfid']);
    if ($rfid === '') {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, grade_section FROM student_records WHERE rfid = ? LIMIT 1");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'id' => $row['id'],
            'name' => $row['name'],
            'grade_section' => $row['grade_section']
        ]);
    } else {
        echo json_encode([]);
    }
}
?>
