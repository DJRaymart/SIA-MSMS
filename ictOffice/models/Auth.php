<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getUser($data){

        $stmt = $this->db->prepare(
            "SELECT * FROM ict_users WHERE username=?"
        );
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($data['password'], $user['password'])) {
            return $user;
        }

        return null;

    }

}
