<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    $customerId = (int)($data['customer_id'] ?? 0);
    $counterId = (int)($data['counter_id'] ?? 0);

    if ($customerId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("UPDATE customers SET status = 'serving', called_at = NOW() WHERE id = ?");
    $stmt->execute([$customerId]);

    
    if ($counterId <= 0) {
        $stmt = $conn->query("SELECT id FROM counters WHERE is_online = 1 ORDER BY id ASC LIMIT 1");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        $counterId = $row ? (int)$row['id'] : 0;
    }
    if ($counterId > 0) {
        $stmt = $conn->prepare("UPDATE counters SET current_customer_id = NULL WHERE current_customer_id = ?");
        $stmt->execute([$customerId]);
        $stmt = $conn->prepare("UPDATE counters SET current_customer_id = ? WHERE id = ?");
        $stmt->execute([$customerId, $counterId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Customer called successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>