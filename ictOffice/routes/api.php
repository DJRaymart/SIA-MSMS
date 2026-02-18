<?php
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/LocationController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/LogController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LogRecordController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

$routes = [
    "inventories"   => "InventoryController",
    "categories" => "CategoryController",
    "locations" => "LocationController",
    "users" => "UserController",
    "logs" => "LogController",
    "login" => "AuthController",
    "logbook-records" => "LogRecordController",
    "dashboard" => "DashboardController"
];
