<?php

error_reporting(0); 
header('Content-Type: application/json');

include "../config/db.php";

$ids_json = isset($_POST['item_ids']) ? $_POST['item_ids'] : '[]';
$ids = json_decode($ids_json, true);

if (!empty($ids) && is_array($ids)) {
    $idList = implode(',', array_map('intval', $ids));
    $query = "DELETE FROM inventory WHERE item_id IN ($idList)";
    
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => count($ids) . ' items purged.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No IDs received.']);
}
exit;