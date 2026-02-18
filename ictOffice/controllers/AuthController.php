<?php
session_start();
require_once __DIR__ . '/../models/Auth.php';

class AuthController {
    private $model;

    public function __construct() {
        $this->model = new Auth();
    }

   public function store($data) {

    if($this->model->getUser($data)){
        $dataResponse = $this->model->getUser($data);
         $_SESSION['userFullName'] = $dataResponse['fullname'];
         $_SESSION['userRole'] =  $dataResponse['role'];
         http_response_code(200);

         echo json_encode(["message" => "Logged in successfully", "success" => true, "result" => $dataResponse]);
        
         return;
    }
       http_response_code(500);
      echo json_encode(["error" => "Invalid Username or Password", "success" => false]);
    }

}
