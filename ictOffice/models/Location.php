<?php
require_once __DIR__ . '/../config/database.php';

class Location {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

   public function all() {
        $sql = "SELECT * FROM ict_locations";
        $result = $this->db->query($sql);

        if (!$result) {
            die($this->db->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO ict_locations (location_name) VALUES (?)"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param(
            "s",
            $data['locationName']
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
            "UPDATE ict_locations 
            SET location_name=? 
            WHERE location_id=?"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param(
            "si",
            $data['locationName'],
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
        $stmt = $this->db->prepare("DELETE FROM ict_locations WHERE location_id=?");

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
        $stmt = $this->db->prepare("SELECT * FROM ict_locations WHERE location_id=?");

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
