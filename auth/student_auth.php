<?php

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/security.php';
require_once dirname(__DIR__) . '/config/db.php';

class StudentAuth {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function register($student_id, $name, $email, $grade, $section, $password) {
        $student_id = trim($student_id);
        $name = trim($name);
        $email = trim($email);
        $grade = (int) $grade;
        $section = trim($section);

        if (empty($student_id) || empty($name) || empty($section) || $grade < 1 || $grade > 12 || empty($password)) {
            return ['success' => false, 'message' => 'Please fill in all fields correctly. Grade must be 1-12.'];
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        
        $stmt = $this->conn->prepare("SELECT id, account_status FROM students WHERE student_id = ? LIMIT 1");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $status = $row['account_status'] ?? 'approved';
            if ($status === 'pending') {
                return ['success' => false, 'message' => 'Your account is pending approval. Please wait for admin verification.'];
            }
            if ($status === 'approved') {
                return ['success' => false, 'message' => 'Student ID already registered. Use login instead.'];
            }
            if ($status === 'rejected') {
                return ['success' => false, 'message' => 'Your previous registration was not approved. Contact the school for assistance.'];
            }
            return ['success' => false, 'message' => 'Student ID already registered.'];
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $status = 'pending';
        $stmt = $this->conn->prepare("INSERT INTO students (student_id, name, email, grade, section, password, account_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisss", $student_id, $name, $email, $grade, $section, $password_hash, $status);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration submitted. Your account is pending admin verification. You will be able to log in once approved.'];
        }
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    
    public function checkStudentExists($student_id) {
        $student_id = trim($student_id);
        if (empty($student_id)) return null;
        $stmt = $this->conn->prepare("SELECT id, student_id, name, grade, section, password, account_status FROM students WHERE student_id = ? LIMIT 1");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    
    public function setPassword($student_id, $password) {
        if (empty($password) || strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        $stmt = $this->conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $hash, $student_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Failed to set password.'];
    }

    public function login($student_id, $password) {
        $student = $this->checkStudentExists($student_id);
        if (!$student) {
            return ['success' => false, 'message' => 'Invalid Student ID'];
        }

        $status = $student['account_status'] ?? 'approved';
        if ($status === 'pending') {
            return ['success' => false, 'message' => 'Your account is pending approval. An admin must verify you are a student of the school before you can log in.'];
        }
        if ($status === 'rejected') {
            return ['success' => false, 'message' => 'Your registration was not approved. Contact the school for assistance.'];
        }

        $stored_password = $student['password'] ?? null;

        
        if (empty($stored_password)) {
            return ['success' => false, 'message' => 'password_required', 'student' => $student, 'first_time' => true];
        }

        
        if (!password_verify($password, $stored_password)) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }

        $this->setSession($student);
        return ['success' => true, 'student' => $student];
    }

    
    public function firstTimeLogin($student_id, $password) {
        $result = $this->setPassword($student_id, $password);
        if (!$result['success']) {
            return $result;
        }
        $student = $this->checkStudentExists($student_id);
        if ($student) {
            $this->setSession($student);
            return ['success' => true, 'student' => $student];
        }
        return ['success' => false, 'message' => 'Login failed.'];
    }

    private function setSession($student) {
        session_regenerate_id(true);
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['student_grade'] = $student['grade'];
        $_SESSION['student_section'] = $student['section'];
        $_SESSION['student_rfid'] = $student['rfid_number'] ?? null;
    }

    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true;
    }

    public static function getStudentInfo() {
        if (self::isLoggedIn()) {
            return [
                'student_id' => $_SESSION['student_id'] ?? null,
                'name' => $_SESSION['student_name'] ?? null,
                'grade' => $_SESSION['student_grade'] ?? null,
                'section' => $_SESSION['student_section'] ?? null,
                'rfid_number' => $_SESSION['student_rfid'] ?? null
            ];
        }
        return null;
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['student_logged_in']);
        unset($_SESSION['student_id']);
        unset($_SESSION['student_name']);
        unset($_SESSION['student_grade']);
        unset($_SESSION['student_section']);
        unset($_SESSION['student_rfid']);
    }

    public static function requireLogin($redirectUrl = null) {
        if (!self::isLoggedIn()) {
            if ($redirectUrl === null) {
                if (!defined('BASE_URL')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
                $baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
                $redirectUrl = $baseS . 'login.php?type=student';
            }
            $_SESSION['student_redirect_after_login'] = msms_safe_redirect($_SERVER['REQUEST_URI'] ?? '', $baseS ?? '/');
            header("Location: " . msms_safe_redirect($redirectUrl, '/'));
            exit();
        }
    }
}
