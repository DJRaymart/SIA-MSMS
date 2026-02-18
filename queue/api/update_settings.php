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
    
    $id = $data['id'] ?? 1;
    $companyName = $data['company_name'] ?? '';
    $welcomeMessage = $data['welcome_message'] ?? '';
    $refreshInterval = isset($data['refresh_interval']) ? (int)$data['refresh_interval'] : 10;
    
    if (empty($companyName)) {
        echo json_encode(['success' => false, 'message' => 'Company name is required']);
        exit;
    }
    
    if ($refreshInterval < 5 || $refreshInterval > 60) {
        echo json_encode(['success' => false, 'message' => 'Refresh interval must be between 5 and 60 seconds']);
        exit;
    }
    
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id FROM display_settings WHERE id = ?");
    $stmt->execute([$id]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        
        $stmt = $conn->prepare("UPDATE display_settings SET company_name = ?, welcome_message = ?, refresh_interval = ? WHERE id = ?");
        $stmt->execute([$companyName, $welcomeMessage, $refreshInterval, $id]);
    } else {
        
        $stmt = $conn->prepare("INSERT INTO display_settings (company_name, welcome_message, refresh_interval) VALUES (?, ?, ?)");
        $stmt->execute([$companyName, $welcomeMessage, $refreshInterval]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
