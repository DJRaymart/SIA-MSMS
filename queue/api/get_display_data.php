<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$data = ['now_serving' => null, 'next_in_line' => null, 'waiting_count' => 0, 'recent_called' => [], 'waiting_queue' => []];

try {
    $db = new Database();
    $conn = $db->getConnection();

    
    $stmt = $conn->query("
        SELECT c.*, cnt.name as counter_name 
        FROM customers c 
        LEFT JOIN counters cnt ON cnt.current_customer_id = c.id 
        WHERE c.status = 'serving' 
        ORDER BY c.called_at DESC 
        LIMIT 1
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['now_serving'] = $row ?: null;

    $stmt = $conn->query("
        SELECT * FROM customers 
        WHERE status = 'waiting' 
        ORDER BY created_at ASC 
        LIMIT 1
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['next_in_line'] = $row ?: null;

    $stmt = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status = 'waiting'");
    $data['waiting_count'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("
        SELECT queue_number, called_at 
        FROM customers 
        WHERE (status = 'completed' OR status = 'serving') AND called_at IS NOT NULL 
        ORDER BY called_at DESC 
        LIMIT 6
    ");
    $data['recent_called'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("
        SELECT queue_number 
        FROM customers 
        WHERE status = 'waiting' 
        ORDER BY created_at ASC 
        LIMIT 10
    ");
    $data['waiting_queue'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data['now_serving']) {
        $cn = $data['now_serving']['counter_name'] ?? null;
        if (empty($cn)) {
            $stmt = $conn->prepare("SELECT name FROM counters WHERE current_customer_id = ?");
            $stmt->execute([$data['now_serving']['id']]);
            $counter = $stmt->fetch(PDO::FETCH_ASSOC);
            $cn = (!empty($counter['name'])) ? $counter['name'] : 'Ask staff';
        }
        $data['now_serving']['counter_name'] = $cn;
        if (empty($data['now_serving']['service_type'])) {
            $data['now_serving']['service_type'] = 'general';
        }
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    $data['error'] = $e->getMessage();
    echo json_encode($data);
}
?>