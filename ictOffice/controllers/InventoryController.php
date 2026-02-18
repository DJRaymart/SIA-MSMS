<?php
require_once __DIR__ . '/../models/Inventory.php';

class InventoryController {
    private $model;

    public function __construct() {
        $this->model = new Inventory();
    }

    public function index() {
        echo json_encode($this->model->all());
    }

    public function store($data) {
        if($this->model->create($data)){
            http_response_code(200);
            echo json_encode(["message" => "inventory added", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to add inventory", "success" => false]);
    }

    public function update($id, $data) {
        if($this->model->update($id, $data)){
            http_response_code(200);
            echo json_encode(["message" => "Inventory updated successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to update inventory", "success" => false]);
       
    }

    public function destroy($id) {
        if($this->model->delete($id)){
            http_response_code(200);
            echo json_encode(["message" => "Inventory deleted successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to delete inventory", "success" => false]);
    }

    public function show($id) {
        echo json_encode($this->model->find($id));
    }
}
