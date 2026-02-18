<?php
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {
    private $model;

    public function __construct() {
        $this->model = new Dashboard();
    }

    public function index() {
        
        header('Content-Type: application/json');
        echo json_encode($this->model->getStats());
    }
}