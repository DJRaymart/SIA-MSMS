<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function all() {
        return $this->db->query("SELECT * FROM ict_users")
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function create($name) {
      $stmt = $this->db->prepare(
            "INSERT INTO logs () VALUES (?)"
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
            "UPDATE categories SET category_name = ? WHERE ID = ?"
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
            "DELETE FROM categories WHERE ID = ?"
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
        if (empty($id)) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM ict_users WHERE student_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user) {
                return $user;
            }
        }
        
        $stmt2 = $this->db->prepare("
            SELECT student_id, name, grade, section
            FROM students
            WHERE (student_id = ? OR rfid_number = ?)
            AND (account_status = 'approved' OR account_status IS NULL OR account_status = '')
            LIMIT 1
        ");
        if (!$stmt2) {
            return null;
        }
        $stmt2->bind_param("ss", $id, $id);
        $stmt2->execute();
        $student = $stmt2->get_result()->fetch_assoc();
        if (!$student) {
            return null;
        }
        
        $grade_section = $student['grade'] . ' - ' . $student['section'];
        $student_id = $student['student_id'];
        $fullname = $student['name'];
        
        $ins = $this->db->prepare("
            INSERT INTO ict_users (student_id, fullname, grade_section)
            VALUES (?, ?, ?)
        ");
        if ($ins) {
            $ins->bind_param("sss", $student_id, $fullname, $grade_section);
            if ($ins->execute()) {
                $newId = $this->db->insert_id;
                return [
                    'id' => (int)$newId,
                    'student_id' => $student_id,
                    'fullname' => $fullname,
                    'full_name' => $fullname,
                    'grade_section' => $grade_section
                ];
            }
        }
        
        $ins2 = $this->db->prepare("
            INSERT INTO ict_users (student_id, fullname, grade_section, username, password)
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($ins2) {
            $username = $student_id;
            $password = password_hash('logbook-only-' . $student_id, PASSWORD_DEFAULT);
            $ins2->bind_param("sssss", $student_id, $fullname, $grade_section, $username, $password);
            if ($ins2->execute()) {
                $newId = $this->db->insert_id;
                return [
                    'id' => (int)$newId,
                    'student_id' => $student_id,
                    'fullname' => $fullname,
                    'full_name' => $fullname,
                    'grade_section' => $grade_section
                ];
            }
        }
        return null;
    }
}
