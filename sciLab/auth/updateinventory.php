<?php
include "../config/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item_id     = $_POST['item_id'] ?? null;
    $item_name   = $_POST['item_name'] ?? '';
    $quantity    = (int)($_POST['quantity'] ?? 1); 
    $description = $_POST['description'] ?? '';
    $model_no    = $_POST['model_no'] ?? '';
    $serial_no   = $_POST['serial_no'] ?? '';
    $remarks     = $_POST['remarks'] ?? '';
    $location_id = $_POST['location_id'] ?? null;
    $lab_id      = $_POST['lab_id'] ?? null;

    if (!$item_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid item ID']);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE inventory SET 
            item_name = ?, 
            quantity = ?, 
            description = ?, 
            model_no = ?, 
            serial_no = ?, 
            remarks = ?, 
            location_id = ?, 
            lab_id = ?
        WHERE item_id = ?
    ");

    $stmt->bind_param("sissssiii", $item_name, $quantity, $description, $model_no, $serial_no, $remarks, $location_id, $lab_id, $item_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully!']);
    } else {
        
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}