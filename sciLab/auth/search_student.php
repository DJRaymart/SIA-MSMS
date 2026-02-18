<?php
include "../config/db.php";

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    exit;
}

$q = $_GET['q'];

$stmt = $conn->prepare("
    SELECT student_id, name
    FROM students
    WHERE (student_id LIKE CONCAT(?, '%') OR name LIKE CONCAT(?, '%'))
    AND (account_status = 'approved' OR account_status IS NULL OR account_status = '')
    LIMIT 5
");

if ($stmt) {
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='px-4 py-3 text-gray-500'>No results found.</div>";
    }

    while ($row = $result->fetch_assoc()) {
        $upperName = strtoupper($row['name']); 

        $dataAttr = htmlspecialchars(json_encode([
            'id' => $row['student_id'],
            'name' => $row['name']
        ]), ENT_QUOTES, 'UTF-8');

        $displayId = htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8');
        $displayName = htmlspecialchars($upperName, ENT_QUOTES, 'UTF-8');

        echo "
            <div class='px-4 py-3 hover:bg-gray-100 cursor-pointer select-student'
                 data-student='{$dataAttr}'>
                <strong>{$displayId}</strong> – {$displayName}
            </div>
        ";
    }
    $stmt->close();
}