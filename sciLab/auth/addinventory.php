<?php
include "../config/db.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request."
    ]);
    exit;
}

$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

$remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';

$stmt = $conn->prepare("
    INSERT INTO inventory 
    (item_name, quantity, description, model_no, serial_no, date_added, remarks, location_id, lab_id) 
    VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?, ?)
");

$stmt->bind_param(
    "sissssii", 
    $_POST['item_name'],
    $quantity,
    $_POST['description'],
    $_POST['model_no'],
    $_POST['serial_no'],
    $remarks,
    $_POST['location_id'],
    $_POST['lab_id']
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Inventory item added successfully."
    ]);
} else {
    
    echo json_encode([
        "status" => "error", 
        "message" => "Database Error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();