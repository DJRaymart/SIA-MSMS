<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM customers");
    $total = $stmt ? (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'] : 0;
    
    $stmt = $conn->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("
        SELECT c.*, cust.name as current_customer_name 
        FROM counters c 
        LEFT JOIN customers cust ON c.current_customer_id = cust.id
    ");
    $counters = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    echo json_encode([
        'success' => true,
        'customers' => $customers,
        'counters' => $counters,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $total > 0 ? (int)ceil($total / $perPage) : 1
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'customers' => [],
        'counters' => [],
        'total' => 0,
        'page' => 1,
        'per_page' => $perPage,
        'total_pages' => 1
    ]);
}
?>