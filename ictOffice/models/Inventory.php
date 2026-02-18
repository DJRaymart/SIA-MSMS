<?php
require_once __DIR__ . '/../config/database.php';

class Inventory {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

   public function all() {
    $sql = "
        SELECT i.*, 
               c.category_name AS category, 
               l.location_name AS location 
        FROM ict_inventory i 
        LEFT JOIN ict_categories c ON i.category_id = c.ID  
        LEFT JOIN ict_locations l ON i.location_id = l.location_id
    ";

    $result = $this->db->query($sql);

    if (!$result) {
        die($this->db->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO ict_inventory (item_name, description, category_id, quantity, model_no, date_added, location_id, remarks)
            VALUES (?,?,?,?,?,?,?,?)"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

      if (!$stmt->bind_param(
            "ssiissis",
            $data['item_name'],
            $data['description'],
            $data['category_id'],
            $data['quantity'],
            $data['model_no'],
            $data['date_added'],
            $data['location_id'],
            $data['remarks']
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE ict_inventory 
            SET item_name=?, description=?, category_id=?, quantity=?, model_no=?, date_added=?, location_id=?, remarks=? 
            WHERE item_id=?"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param(
            "ssiissisi",
            $data['item_name'],
            $data['description'],
            $data['category_id'],
            $data['quantity'],
            $data['model_no'],
            $data['date_added'],
            $data['location_id'],
            $data['remarks'],
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
        $stmt = $this->db->prepare("DELETE FROM ict_inventory WHERE item_id=?");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param("i", $id)) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM ict_inventory WHERE item_id=?");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param("i", $id)) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("get_result failed: " . $stmt->error);
        }

        return $result->fetch_assoc();
    }

}
