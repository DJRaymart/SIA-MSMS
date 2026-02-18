<?php
include "../config/db.php";
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status'=>'error','message'=>"Invalid request method."]);
    exit;
}

$activity   = strtoupper(trim($_POST['activity_title']));
$usage_date = $_POST['usage_datetime'];
$grade      = strtoupper(trim($_POST['grade_section']));
$count      = intval($_POST['student_count']);
$booked     = strtoupper(trim($_POST['booked_by']));
$noted      = strtoupper(trim($_POST['noted_by']));

if (empty($_POST['item_queue'])) {
    echo json_encode(['status'=>'error','message'=>"No items in the queue."]);
    exit;
}

$ref_no = $_POST['reference_no'] ?? "REF-".date("Ymd")."-".strtoupper(substr(uniqid(), -5));

$conn->begin_transaction();

try {
    
    $stmt = $conn->prepare("
        INSERT INTO reservations (reference_no, activity, usage_date, grade_section, student_count, booked_by, noted_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssiss", $ref_no, $activity, $usage_date, $grade, $count, $booked, $noted);
    $stmt->execute();
    $reservation_id = $conn->insert_id; 
    $stmt->close();

    $stmt_item = $conn->prepare("
        INSERT INTO reservation_items (reservation_id, item_id, qty) VALUES (?, ?, ?)
    ");
    $stmt_stock = $conn->prepare("
        UPDATE inventory SET quantity = quantity - ? WHERE item_id = ? AND quantity >= ?
    ");

    foreach ($_POST['item_queue'] as $queued_item) {
        $item_id  = intval($queued_item['id']);
        $item_qty = intval($queued_item['qty']);

        $stmt_stock->bind_param("iii", $item_qty, $item_id, $item_qty);
        $stmt_stock->execute();
        if ($stmt_stock->affected_rows === 0) {
            throw new Exception("Not enough stock for item ID $item_id");
        }

        $stmt_item->bind_param("iii", $reservation_id, $item_id, $item_qty);
        $stmt_item->execute();
    }

    $stmt_item->close();
    $stmt_stock->close();
    $conn->commit();

    $_SESSION['item_queue'] = [];
    echo json_encode(['status'=>'success','message'=>"Reservation saved! Reference No: $ref_no"]);

} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>"Database error: ".$e->getMessage()]);
}
