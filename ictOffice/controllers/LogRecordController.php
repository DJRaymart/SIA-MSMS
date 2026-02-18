<?php
require_once __DIR__ . '/../models/LogRecord.php';

class LogRecordController {
    private $model;

    public function __construct() {
        $this->model = new LogRecord();
    }

    public function index() {
    
        echo json_encode($this->model->all());
    }

    public function destroy($id) {
        if($this->model->delete($id)){
            echo json_encode(["success" => true, "message" => "Log deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Failed to delete"]);
        }
    }
}