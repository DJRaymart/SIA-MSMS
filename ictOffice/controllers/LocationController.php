<?php
require_once __DIR__ . '/../models/Location.php';

class LocationController {
    private $model;

    public function __construct() {
        $this->model = new Location();
    }

    public function index() {
        echo json_encode($this->model->all());
    }

    public function store($data) {
         if($this->model->create($data)){
            http_response_code(200);
            echo json_encode(["message" => "location added successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to add location", "success" => false]);
    }

     public function update($id, $data) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Location ID required"]);
            return;
        }

       if($this->model->update($id, $data)){
            http_response_code(200);
            echo json_encode(["message" => "location updated successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to update location", "success" => false]);
    }

    public function destroy($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Category ID required"]);
            return;
        }

         if($this->model->delete($id)){
            http_response_code(200);
            echo json_encode(["message" => "location deleted successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to delete location", "success" => false]);
    }

}
