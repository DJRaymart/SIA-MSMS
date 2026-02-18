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
    
    $customerId = $data['customer_id'] ?? 0;

    if ($customerId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }

    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE customers SET status = 'completed', completed_at = NOW() WHERE id = ?");
    $stmt->execute([$customerId]);
    
    $stmt = $conn->prepare("UPDATE counters SET current_customer_id = NULL WHERE current_customer_id = ?");
    $stmt->execute([$customerId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Customer completed successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>