<?php

require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $institutional_id = trim($_POST['institutional_id']);

    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE institutional_id = ?");
    $stmt->execute([$institutional_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if (isset($user['status']) && $user['status'] !== 'Active') {
            $error = "Your account is not active. Please contact the library.";
        } else {
            
            try {
                $log_stmt = $conn->prepare("
                    INSERT INTO log_book 
                    (user_id, full_name, institutional_id, rfid_number, grade_section, email, user_type, login_at) 
                    VALUES (:user_id, :full_name, :institutional_id, :rfid_number, :grade_section, :email, :user_type, NOW())
                ");
                
                $log_stmt->execute([
                    ':user_id' => $user['user_id'],
                    ':full_name' => $user['full_name'] ?? '',
                    ':institutional_id' => $user['institutional_id'],
                    ':rfid_number' => $user['rfid_number'] ?? null,
                    ':grade_section' => $user['grade_section'] ?? null,
                    ':email' => $user['email'] ?? '',
                    ':user_type' => $user['user_type'] ?? 'User'
                ]);

                $log_id = $conn->lastInsertId();

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['institutional_id'] = $user['institutional_id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['log_id'] = $log_id;
                $_SESSION['rfid_number'] = $user['rfid_number'] ?? null;
                $_SESSION['grade_section'] = $user['grade_section'] ?? null;

                if (isset($user['balance'])) {
                    $_SESSION['balance'] = $user['balance'];
                }
                if (isset($user['email'])) {
                    $_SESSION['email'] = $user['email'];
                }

                if (isset($user['user_id'])) {
                    $update_stmt = $conn->prepare("
                        UPDATE tbl_users 
                        SET last_login = NOW() 
                        WHERE user_id = ?
                    ");
                    $update_stmt->execute([$user['user_id']]);
                }

                header('Location: user_dashboard.php');
                exit();
                
            } catch (PDOException $e) {
                
                error_log("Login logging failed: " . $e->getMessage());

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['institutional_id'] = $user['institutional_id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['rfid_number'] = $user['rfid_number'] ?? null;
                $_SESSION['grade_section'] = $user['grade_section'] ?? null;
                
                header('Location: user_dashboard.php');
                exit();
            }
        }
    } else {
        
        try {
            $log_stmt = $conn->prepare("
                INSERT INTO log_book 
                (user_id, full_name, institutional_id, rfid_number, grade_section, user_type, login_at) 
                VALUES (0, 'Unknown', :institutional_id, NULL, NULL, 'Failed Login', NOW())
            ");
            $log_stmt->execute([':institutional_id' => $institutional_id]);
        } catch (PDOException $e) {
            error_log("Failed login logging error: " . $e->getMessage());
        }
        
        $error = "User not found with Student / Employee ID: " . htmlspecialchars($institutional_id) . 
                 ". Please contact the library to register your account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-image: url('export.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .login-container { 
            max-width: 400px;
            width: 100%;
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card { 
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: #2c3e50;
            padding: 1.5rem;
        }
        .btn-primary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .form-control {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            text-align: center;
        }
        .logo i {
            color: #667eea;
            background: white;
            padding: 10px;
            border-radius: 10px;
            margin-right: 10px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header text-center text-white">
                <div class="logo">
                    <i class="bi bi-book"></i> Library
                </div>
                <h4>User Access Portal</h4>
            </div>
            <div class="card-body p-4">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Enter Your Student / Employee ID</label>
                        <input type="text" class="form-control" name="institutional_id" 
                               placeholder="e.g., 2023-00123 or EMP-00123" 
                               required
                               autofocus
                               autocomplete="off">
                        <div class="form-text">
                            Enter your student ID, employee ID
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Access My Account
                    </button>
                    
                </form>
                
                <div class="info-box mt-4">
                    <h6><i class="bi bi-info-circle me-2"></i> How to access:</h6>
                    <ul class="mb-0 ps-3">
                        <li>Enter your Student / Employee ID to access your account</li>
                        <li>Your account must be pre-registered by the library</li>
                        <li>View your borrowings, history, and profile</li>
                        <li>Reserve books online</li>
                        <li>All login activity is recorded in the system</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Auto-format institutional ID
    document.querySelector('input[name="institutional_id"]').addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase();
        // Remove any spaces
        value = value.replace(/\s+/g, '');
        // Auto-add dash after certain patterns
        if (value.match(/^[A-Z]{3}\d+$/)) {
            value = value.replace(/([A-Z]{3})(\d+)/, '$1-$2');
        }
        e.target.value = value;
    });
    </script>
</body>
</html>