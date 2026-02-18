<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'expired']);
} else {
    echo json_encode(['status' => 'active']);
}
exit();