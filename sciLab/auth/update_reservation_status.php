<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);

include "../auth/session_guard.php";
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please login.']);
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$id || !in_array($action, ['approve', 'decline'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters provided.']);
    exit();
}

$status = $action === 'approve' ? 'approved' : 'declined';
$admin = $_SESSION['admin_username'] ?? 'ADMIN';

$conn->begin_transaction();

try {
    
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE id=? FOR UPDATE");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $reservation = $stmt->get_result()->fetch_assoc();

    if (!$reservation) throw new Exception("Reservation not found.");
    if ($reservation['status'] !== 'pending') throw new Exception("Request already processed.");

    $stmtItems = $conn->prepare("
        SELECT ri.*, i.item_name, i.quantity AS stock_available
        FROM reservation_items ri
        INNER JOIN inventory i ON ri.item_id = i.item_id
        WHERE ri.reservation_id = ? FOR UPDATE
    ");
    $stmtItems->bind_param("i", $id);
    $stmtItems->execute();
    $itemsResult = $stmtItems->get_result();
    $items = [];

    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }

    if ($status === 'approved') {
        
        foreach ($items as $item) {
            if ($item['stock_available'] < $item['qty']) {
                throw new Exception("Insufficient stock for item: {$item['item_name']}");
            }
            $newQty = $item['stock_available'] - $item['qty'];
            $updateInv = $conn->prepare("UPDATE inventory SET quantity=? WHERE item_id=?");
            $updateInv->bind_param("ii", $newQty, $item['item_id']);
            $updateInv->execute();
        }
    }

    $update = $conn->prepare("UPDATE reservations SET status=?, noted_by=? WHERE id=?");
    $update->bind_param("ssi", $status, $admin, $id);
    $update->execute();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => $status === 'approved' ? 'Reservation approved successfully!' : 'Reservation declined successfully!',
        'reservation' => [
            'id' => $reservation['id'],
            'reference_no' => $reservation['reference_no'],
            'activity' => $reservation['activity'],
            'grade_section' => $reservation['grade_section'],
            'usage_date' => $reservation['usage_date'],
            'student_count' => $reservation['student_count'],
            'status' => $status,
            'items' => $items
        ]
    ]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
