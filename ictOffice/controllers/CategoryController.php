<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private $model;

    public function __construct() {
        $this->model = new Category();
    }

    public function index() {
        echo json_encode($this->model->all());
    }

   public function store($data) {
        if($this->model->create($data)){
            http_response_code(200);
            echo json_encode(["message" => "category added successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to add category", "success" => false]);
    }

     public function update($id, $data) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Category ID required"]);
            return;
        }

        if($this->model->update($id, $data)){
            http_response_code(200);
            echo json_encode(["message" => "category updated successfully", "success" => true]);
            return;
        }

        http_response_code(500);
        echo json_encode(["error" => "Failed to update category", "success" => false]);
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
            echo json_encode(["error" => "Category ID required"]);
            return;
        }

        $category = $this->model->show($id);
        if ($category) {
            echo json_encode($category);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Category not found"]);
        }
    }
    
}
