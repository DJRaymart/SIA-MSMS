<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../routes/api.php';

$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri  = explode('/', trim($path, '/'));
$resource = end($uri);

$id = $_GET['id'] ?? null;
$sId = $_GET['student_id'] ?? $_GET['rfid'] ?? null;

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($routes[$resource])) {
    http_response_code(404);
    echo json_encode(["error" => "API not found"]);
    exit;
}

$controller = new $routes[$resource]();

switch ($method) {
    case 'GET':
        if ($id) {
            $controller->show((int)$id); 

        } elseif ($sId) {
            $controller->show($sId); 
        } else {
            $controller->index();        
        }
        break;

    case 'POST':

        $controller->store($data);
        break;

    case 'PUT':
        if($id){
            $controller->update((int)$id, $data);

        }
        elseif ($sId){
            $controller->update($sId, $data);
        }
        break;

    case 'DELETE':
        $controller->destroy($id);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
}
