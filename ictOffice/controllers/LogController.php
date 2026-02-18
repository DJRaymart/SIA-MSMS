<?php
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/User.php';

class LogController {
    private $model;

    public function __construct() {
        $this->model = new Log();
    }

    public function index() {
        echo json_encode($this->model->all());
    }

    public function store($data) {
        $userId = $data['userId'];
        $currentTime = $data['currentTime'];

        $activeLog = $this->model->findActiveLogByUserId($userId);

        if ($activeLog) {
            
            if ($this->model->updateTimeOut($activeLog['log_id'], $currentTime)) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Logged out successfully"]);
                return;
            }
        } else {
            
            if ($this->model->create(['timeIn' => $currentTime, 'userId' => $userId])) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Logged in successfully"]);
                return;
            }
        }

        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Database error"]);
    }
    
     public function update($id, $data) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "user ID required"]);
            return;
        }

        if($this->model->update($id, $data)){
            http_response_code(200);
            echo json_encode(["message" => "log updated successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to update log", "success" => false]);
    }

    public function destroy($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Category ID required"]);
            return;
        }

      if($this->model->delete($id)){
            http_response_code(200);
            echo json_encode(["message" => "category deleted successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to delete category", "success" => false]);
    }

    public function show($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "student ID required"]);
            return;
        }

        $student = $this->model->show($id);

        if ($student) {
            echo json_encode($student);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "student not found"]);
        }
    }

    public function getActiveLogByUserId($userId) {
        if (!$userId) {
            http_response_code(400);
            echo json_encode(["error" => "user ID required"]);
            return;
        }

        $log = $this->model->findActiveLogByUserId($userId);

        if ($log) {
            echo json_encode($log);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No active log found for this user"]);
        }
    }
    
}
