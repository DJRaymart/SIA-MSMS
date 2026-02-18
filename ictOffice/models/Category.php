<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function all() {
        return $this->db->query("SELECT * FROM ict_categories")
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function create($name) {
      $stmt = $this->db->prepare(
            "INSERT INTO ict_categories (category_name) VALUES (?)"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

      if (!$stmt->bind_param(
            "s", 
            $name['categoryName']
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    public function update($id, $name) {
        $stmt = $this->db->prepare(
            "UPDATE ict_categories SET category_name = ? WHERE ID = ?"
        );
         if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

      if (!$stmt->bind_param(
            "si",
            $name['categoryName'],
            $id
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    public function delete($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM ict_categories WHERE ID = ?"
        );

         if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param(
            "i",
            $id
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    public function show($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ict_categories WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
