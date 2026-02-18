<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$stats = ['waiting' => 0, 'serving' => 0, 'completed' => 0, 'today_total' => 0];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status = 'waiting' AND DATE(created_at) = CURDATE()");
    if ($stmt) { $row = $stmt->fetch(PDO::FETCH_ASSOC); $stats['waiting'] = (int)($row['count'] ?? 0); }
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status = 'serving' AND DATE(created_at) = CURDATE()");
    if ($stmt) { $row = $stmt->fetch(PDO::FETCH_ASSOC); $stats['serving'] = (int)($row['count'] ?? 0); }
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status = 'completed' AND DATE(created_at) = CURDATE()");
    if ($stmt) { $row = $stmt->fetch(PDO::FETCH_ASSOC); $stats['completed'] = (int)($row['count'] ?? 0); }
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = CURDATE()");
    if ($stmt) { $row = $stmt->fetch(PDO::FETCH_ASSOC); $stats['today_total'] = (int)($row['count'] ?? 0); }
} catch (Exception $e) {
    
}

echo json_encode(['success' => true, 'data' => $stats]);
?>