<?php
header('Content-Type: application/json');
include '../config.php';

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
    
    $action = $data['action'] ?? '';
    $db = new Database();
    $conn = $db->getConnection();
    
    switch ($action) {
        case 'create':
            $name = $data['name'] ?? '';
            $serviceTypes = $data['service_types'] ?? '[]';
            $isOnline = isset($data['is_online']) ? (int)$data['is_online'] : 1;
            
            if (empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Counter name is required']);
                exit;
            }
            
            $stmt = $conn->prepare("INSERT INTO counters (name, service_types, is_online) VALUES (?, ?, ?)");
            $stmt->execute([$name, $serviceTypes, $isOnline]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Counter created successfully',
                'id' => $conn->lastInsertId()
            ]);
            break;
            
        case 'update':
            $id = $data['id'] ?? 0;
            $name = $data['name'] ?? '';
            $serviceTypes = $data['service_types'] ?? '[]';
            $isOnline = isset($data['is_online']) ? (int)$data['is_online'] : 1;
            
            if ($id <= 0 || empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Invalid counter ID or name']);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE counters SET name = ?, service_types = ?, is_online = ? WHERE id = ?");
            $stmt->execute([$name, $serviceTypes, $isOnline, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Counter updated successfully'
            ]);
            break;
            
        case 'toggle':
            $id = $data['id'] ?? 0;
            $isOnline = isset($data['is_online']) ? (int)$data['is_online'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid counter ID']);
                exit;
            }
            
            $stmt = $conn->prepare("UPDATE counters SET is_online = ? WHERE id = ?");
            $stmt->execute([$isOnline, $id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Counter status updated successfully'
            ]);
            break;
            
        case 'delete':
            $id = $data['id'] ?? 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid counter ID']);
                exit;
            }

            $stmt = $conn->prepare("SELECT current_customer_id FROM counters WHERE id = ?");
            $stmt->execute([$id]);
            $counter = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($counter && $counter['current_customer_id']) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete counter that is currently serving a customer']);
                exit;
            }

            $stmt = $conn->prepare("UPDATE counters SET current_customer_id = NULL WHERE id = ?");
            $stmt->execute([$id]);

            $stmt = $conn->prepare("DELETE FROM counters WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Counter deleted successfully'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
