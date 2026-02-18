<?php
require_once __DIR__ . '/../models/User.php';

class UserController{
    private $model;

    public function __construct() {
        $this->model = new User();
    }

    public function index() {
        echo json_encode($this->model->all());
    }

    public function show($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "student id required"]);
            return;
        }

        $user = $this->model->show($id);

        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "student id not found"]);
        }
    }
}

?>