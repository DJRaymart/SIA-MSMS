<?php

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/security.php';
require_once dirname(__DIR__) . '/config/db.php';

class AdminAuth {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        $this->initializeAdminTable();
    }

    private function initializeAdminTable() {
        $sql = "CREATE TABLE IF NOT EXISTS msms_admins (
            admin_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            role ENUM('Super Admin', 'Admin') DEFAULT 'Admin',
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            INDEX idx_username (username),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->conn->query($sql);

        $result = $this->conn->query("SELECT COUNT(*) as count FROM msms_admins");
        $count = $result->fetch_assoc()['count'];
        
        if ($count == 0) {
            $bootstrapUsername = trim((string) getenv('MSMS_BOOTSTRAP_ADMIN_USER'));
            $bootstrapPasswordRaw = (string) getenv('MSMS_BOOTSTRAP_ADMIN_PASSWORD');
            $bootstrapName = trim((string) getenv('MSMS_BOOTSTRAP_ADMIN_NAME')) ?: 'System Administrator';
            $bootstrapEmail = trim((string) getenv('MSMS_BOOTSTRAP_ADMIN_EMAIL')) ?: 'admin@hcmi.com';

            if ($bootstrapUsername !== '' && $bootstrapPasswordRaw !== '') {
                $bootstrapPassword = password_hash($bootstrapPasswordRaw, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("INSERT INTO msms_admins (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, 'Super Admin', 'Active')");
                $stmt->bind_param("ssss", $bootstrapUsername, $bootstrapPassword, $bootstrapName, $bootstrapEmail);
                $stmt->execute();
            } else {
                error_log('Admin table is empty. Set MSMS_BOOTSTRAP_ADMIN_USER and MSMS_BOOTSTRAP_ADMIN_PASSWORD to auto-create a secure initial admin.');
            }
        }
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM msms_admins WHERE username = ? AND status = 'Active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                
                $updateStmt = $this->conn->prepare("UPDATE msms_admins SET last_login = NOW() WHERE admin_id = ?");
                $updateStmt->bind_param("i", $admin['admin_id']);
                $updateStmt->execute();

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                
                return ['success' => true, 'admin' => $admin];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
    }

    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public static function getAdminInfo() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'] ?? null,
                'username' => $_SESSION['admin_username'] ?? null,
                'name' => $_SESSION['admin_name'] ?? null,
                'email' => $_SESSION['admin_email'] ?? null,
                'role' => $_SESSION['admin_role'] ?? null
            ];
        }
        return null;
    }

    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_role']);
        
        session_destroy();
    }

    public static function requireLogin($redirectUrl = null) {
        if (!self::isLoggedIn()) {
            if ($redirectUrl === null) {
                if (!defined('BASE_URL')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
                $baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
                $redirectUrl = $baseS . 'login.php?type=admin';
            }
            header("Location: " . msms_safe_redirect($redirectUrl, '/'));
            exit();
        }
    }

    public function updateProfile($adminId, $fullName, $email) {
        $stmt = $this->conn->prepare("UPDATE msms_admins SET full_name = ?, email = ? WHERE admin_id = ?");
        $stmt->bind_param("ssi", $fullName, $email, $adminId);
        
        if ($stmt->execute()) {
            
            $_SESSION['admin_name'] = $fullName;
            $_SESSION['admin_email'] = $email;
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }

    public function changePassword($adminId, $currentPassword, $newPassword) {
        
        $stmt = $this->conn->prepare("SELECT password FROM msms_admins WHERE admin_id = ?");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($currentPassword, $admin['password'])) {
                
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE msms_admins SET password = ? WHERE admin_id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $adminId);
                
                if ($updateStmt->execute()) {
                    return ['success' => true, 'message' => 'Password changed successfully'];
                } else {
                    return ['success' => false, 'message' => 'Failed to change password'];
                }
            } else {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
        } else {
            return ['success' => false, 'message' => 'Admin not found'];
        }
    }
}
