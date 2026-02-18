<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');


register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = $err['message'] ?? 'Unknown';
        @file_put_contents(dirname(__DIR__) . '/queue_error.log', date('Y-m-d H:i:s') . " FATAL: $msg\n", FILE_APPEND);
        if (!headers_sent()) http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $msg]);
    }
});

require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

function sendResponse($success, $message, $queueNumber = null) {
    $payload = ['success' => $success, 'message' => $message];
    if ($queueNumber !== null) $payload['queue_number'] = $queueNumber;
    echo json_encode($payload);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    $name = trim($data['name'] ?? '');
    $serviceType = trim($data['service_type'] ?? '');

    if (empty($name) || empty($serviceType)) {
        sendResponse(false, 'Name and service type are required');
    }

    $db = new Database();
    $conn = $db->getConnection();

    $prefix = strtoupper(substr($serviceType, 0, 1));
    $likePattern = $prefix . '%';
    $insertStmt = $conn->prepare("INSERT INTO customers (queue_number, name, service_type) VALUES (?, ?, ?)");
    $maxAttempts = 20;
    $inserted = false;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        
        $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(queue_number, 2) AS UNSIGNED)) as last_num 
                               FROM customers WHERE queue_number LIKE ?");
        $stmt->execute([$likePattern]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextNum = ($result['last_num'] ?? 0) + 1;
        $queueNumber = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        try {
            $insertStmt->execute([$queueNumber, $name, $serviceType]);
            $inserted = true;
            break;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' || strpos($e->getMessage(), 'Duplicate') !== false) {
                continue; 
            }
            throw $e;
        }
    }

    if (!$inserted) {
        throw new Exception('Could not generate unique queue number. Please try again.');
    }
    
    sendResponse(true, 'Customer added successfully', $queueNumber);
    
} catch (Throwable $e) {
    http_response_code(500);
    $msg = $e->getMessage();
    @file_put_contents(dirname(__DIR__) . '/queue_error.log', date('Y-m-d H:i:s') . " add_customer: $msg\n", FILE_APPEND);
    sendResponse(false, 'Error: ' . $msg);
}