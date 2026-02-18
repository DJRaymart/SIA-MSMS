<?php

session_start();
include "../config/db.php";

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$sql = "SELECT i.*, loc.location_name 
        FROM inventory i 
        LEFT JOIN locations loc ON i.location_id = loc.location_id";

if (!empty($dateFrom) && !empty($dateTo)) {
    $sql .= " WHERE i.date_added BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $dateFrom, $dateTo);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) { $data[] = $row; }

header('Content-Type: application/json');
echo json_encode($data);