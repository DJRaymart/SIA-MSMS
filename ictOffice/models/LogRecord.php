<?php
require_once __DIR__ . '/../config/database.php';

class LogRecord {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

   public function all() {

    $sql = "SELECT 
                l.log_id, 
                l.time_in, 
                l.time_out, 
                l.user_id,
                u.fullname,
                u.grade_section
            FROM ict_logs l 
            INNER JOIN ict_users u ON l.user_id = u.id 
            ORDER BY l.log_id DESC";
            
    $result = $this->db->query($sql);

    if (!$result) {
        die("SQL Error: " . $this->db->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
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
}