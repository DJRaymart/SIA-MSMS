<?php
require_once __DIR__ . '/../config/database.php';

class Log {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function all() {
        return $this->db->query("SELECT * FROM ict_logs")
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data) {
      $stmt = $this->db->prepare(
            "INSERT INTO ict_logs (time_in,  user_id) VALUES (?,?)"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

      if (!$stmt->bind_param(
            "si",
            $data['timeIn'],
            $data['userId']
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    }

    public function update($id, $user) {
        $stmt = $this->db->prepare(
            "UPDATE ict_logs SET time_out = ? WHERE user_id = ?"
        );
         if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

      if (!$stmt->bind_param(
            "si",
            $user['timeOut'],
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
            "DELETE FROM ict_logs WHERE log_id = ?"
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
            "SELECT * FROM ict_logs A INNER JOIN ict_users B ON A.user_id = B.id WHERE B.student_id = ?"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param(
            "s",
            $id
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }

    public function updateTimeOut($logId, $timeOut) {
        $stmt = $this->db->prepare("UPDATE ict_logs SET time_out = ? WHERE log_id = ?");
        $stmt->bind_param("si", $timeOut, $logId);
        return $stmt->execute();
    }

    public  function findActiveLogByUserId($userId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ict_logs WHERE user_id = ? AND time_out IS NULL"
        );

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param(
            "i",
            $userId
        )) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }
}
