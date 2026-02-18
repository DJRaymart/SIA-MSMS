<?php

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $conn->prepare("
    SELECT COUNT(*) as active_borrows 
    FROM borrowing_transaction 
    WHERE user_id = ? AND status IN ('Borrowed', 'Reserved')
");
$stmt->execute([$user_id]);
$active_borrows = $stmt->fetch()['active_borrows'];

$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_borrowed,
        SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as total_returned,
        SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as total_overdue,
        SUM(total_penalty) as total_penalties
    FROM borrowing_transaction 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    
    try {
        
        $stmt = $conn->prepare("
            UPDATE tbl_users 
            SET full_name = ?, email = ?, contact_number = ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$full_name, $email, $contact_number, $user_id]);
        
        $_SESSION['full_name'] = $full_name;
        $success = "Profile updated successfully!";

        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Library System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --sidebar-width: 280px;
        }
        
        body {
            background-image: url('user_bg.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark) 0%, #0f172a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 12px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-brand h4 {
            font-weight: 700;
            margin: 0;
            color: white;
        }
        
        .sidebar-user {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
            color: white;
            font-weight: bold;
        }
        
        .sidebar-nav {
            padding: 1.5rem 0;
        }
        
        .nav-item {
            margin: 0.3rem 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--primary-light);
        }
        
        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--primary);
        }
        
        .nav-icon {
            width: 24px;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-text h1 {
            font-size: 1.8rem;
            margin-bottom: 0.3rem;
            color: var(--dark);
        }
        
        .welcome-text p {
            color: var(--secondary);
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0;
        }
        
        .card-header h5 {
            margin: 0;
            color: var(--dark);
            font-weight: 600;
        }
        
        .profile-header-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            border: 3px solid rgba(255,255,255,0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center;
            border-top: 4px solid var(--primary);
        }
        
        .stat-card.total {
            border-top-color: var(--primary);
        }
        
        .stat-card.returned {
            border-top-color: var(--success);
        }
        
        .stat-card.overdue {
            border-top-color: var(--danger);
        }
        
        .stat-card.penalty {
            border-top-color: #8b5cf6;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: var(--dark);
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--secondary);
            font-weight: 500;
        }
        
        .info-value {
            color: var(--dark);
            font-weight: 600;
        }
        
        .profile-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f5f9;
        }
        
        /* RFID display styling */
        .rfid-display {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: #2d3748;
            background: #f7fafc;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .grade-section-display {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #2d3748;
            background: #f7fafc;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="bi bi-book-half me-2"></i>Library System</h4>
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
            <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['institutional_id']); ?></p>
            <?php if(!empty($user['rfid_number'])): ?>
                <p class="text-muted small mb-0">
                    <i class="bi bi-credit-card me-1"></i>
                    RFID: <?php echo htmlspecialchars($user['rfid_number']); ?>
                </p>
            <?php endif; ?>
            <span class="badge bg-primary mt-2">User Account</span>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="user_dashboard.php" class="nav-link">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="user_books.php" class="nav-link">
                    <i class="nav-icon bi bi-book"></i>
                    <span>Browse Books</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="user_borrowed.php" class="nav-link">
                    <i class="nav-icon bi bi-bag-check"></i>
                    <span>My Borrowings</span>
                    <?php if($active_borrows > 0): ?>
                        <span class="badge bg-primary ms-auto"><?php echo $active_borrows; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav-item">
                <a href="user_history.php" class="nav-link">
                    <i class="nav-icon bi bi-clock-history"></i>
                    <span>Borrowing History</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="user_profile.php" class="nav-link active">
                    <i class="nav-icon bi bi-person"></i>
                    <span>My Profile</span>
                </a>
            </div>
            <div class="nav-item mt-4">
                <a href="user_logout.php" class="nav-link text-danger">
                    <i class="nav-icon bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="welcome-text">
                <h1>My Profile 👤</h1>
                <p>Manage your personal information and account settings</p>
            </div>
            <div class="date-display">
                <span class="badge bg-light text-dark">
                    <i class="bi bi-calendar me-1"></i>
                    <?php echo date('F j, Y'); ?>
                </span>
            </div>
        </div>
        
        <!-- Profile Header -->
        <div class="profile-header-card">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h3 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p class="mb-0 opacity-75">
                <i class="bi bi-person-badge me-1"></i>
                <?php echo htmlspecialchars($user['institutional_id']); ?>
                • <?php echo htmlspecialchars($user['user_type']); ?>
            </p>
            <?php if(!empty($user['grade_section'])): ?>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-mortarboard me-1"></i>
                    <?php echo htmlspecialchars($user['grade_section']); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-value"><?php echo $stats['total_borrowed']; ?></div>
                <div class="stat-label">Total Books Borrowed</div>
                <small class="text-muted">All time</small>
            </div>
            
            <div class="stat-card returned">
                <div class="stat-value"><?php echo $stats['total_returned']; ?></div>
                <div class="stat-label">Successfully Returned</div>
                <small class="text-success">
                    <?php 
                    $return_rate = $stats['total_borrowed'] > 0 ? round(($stats['total_returned'] / $stats['total_borrowed']) * 100) : 0;
                    echo $return_rate . '% completion';
                    ?>
                </small>
            </div>
            
            <div class="stat-card overdue">
                <div class="stat-value"><?php echo $stats['total_overdue']; ?></div>
                <div class="stat-label">Overdue Instances</div>
                <small class="text-muted">Past records</small>
            </div>
            
            <div class="stat-card penalty">
                <div class="stat-value">₱<?php echo number_format($stats['total_penalties'], 2); ?></div>
                <div class="stat-label">Total Penalties</div>
                <?php if($stats['total_penalties'] > 0): ?>
                    <small class="text-danger">Action needed</small>
                <?php else: ?>
                    <small class="text-success">All clear</small>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Bootstrap Alerts -->
        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Profile Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="profileForm">
                            <div class="profile-section-title">Personal Details</div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    <small class="text-muted">Your complete name as registered</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Student/Employee ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['institutional_id']); ?>" disabled>
                                        <span class="input-group-text">
                                            <i class="bi bi-lock text-muted"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">Cannot be changed</small>
                                </div>
                            </div>
                            
                            <div class="profile-section-title">Identification</div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">RFID Number</label>
                                    <div class="rfid-display">
                                        <?php 
                                        if(!empty($user['rfid_number'])) {
                                            echo htmlspecialchars($user['rfid_number']);
                                        } else {
                                            echo '<span class="text-muted">Not assigned</span>';
                                        }
                                        ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Library card identification number
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Grade/Section</label>
                                    <div class="grade-section-display">
                                        <?php 
                                        if(!empty($user['grade_section'])) {
                                            echo htmlspecialchars($user['grade_section']);
                                        } else {
                                            echo '<span class="text-muted">Not specified</span>';
                                        }
                                        ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Your class/grade information
                                    </small>
                                </div>
                            </div>
                            
                            <div class="profile-section-title">Contact Information</div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope text-muted"></i>
                                        </span>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                               placeholder="your.email@example.com">
                                    </div>
                                    <small class="text-muted">For notifications and updates</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-phone text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control" name="contact_number" 
                                               value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>"
                                               placeholder="+1 (123) 456-7890">
                                    </div>
                                    <small class="text-muted">For urgent notifications</small>
                                </div>
                            </div>
                            
                            <div class="profile-section-title">Account Type</div>
                            
                            <div class="mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="form-control" style="background-color: #f8fafc;">
                                            <small class="text-muted d-block">User Type</small>
                                            <span class="fw-bold"><?php echo htmlspecialchars($user['user_type']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-control" style="background-color: #f8fafc;">
                                            <small class="text-muted d-block">Account Status</small>
                                            <span class="badge bg-<?php echo $user['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo $user['status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                                    </button>
                                </div>
                                <small class="text-muted">Last updated: 
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Account Info -->
            <div class="col-lg-4">
                <!-- Account Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Account Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <span class="info-label">Account Balance</span>
                            <span class="info-value <?php echo $user['balance'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                ₱<?php echo number_format($user['balance'], 2); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Member Since</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Active Borrowings</span>
                            <span class="info-value"><?php echo $active_borrows; ?> / 5</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">RFID Number</span>
                            <span class="info-value">
                                <?php 
                                if(!empty($user['rfid_number'])) {
                                    echo '<span class="badge bg-info text-white">' . htmlspecialchars($user['rfid_number']) . '</span>';
                                } else {
                                    echo '<span class="text-muted">N/A</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Grade/Section</span>
                            <span class="info-value">
                                <?php 
                                if(!empty($user['grade_section'])) {
                                    echo '<span class="badge bg-secondary text-white">' . htmlspecialchars($user['grade_section']) . '</span>';
                                } else {
                                    echo '<span class="text-muted">N/A</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Status</span>
                            <span class="info-value">
                                <span class="badge bg-<?php echo $user['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                    <?php echo $user['status']; ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <?php if($user['balance'] > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
                            <div>
                                <strong>Outstanding Balance</strong>
                                <p class="mb-0">You have a balance of ₱<?php echo number_format($user['balance'], 2); ?>. 
                                Please visit the library to settle your penalties.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if($active_borrows >= 5): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill me-2 flex-shrink-0"></i>
                            <div>
                                <strong>Borrowing Limit Reached</strong>
                                <p class="mb-0">You have reached the maximum borrowing limit of 5 books.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-4 pt-3 border-top text-center text-dark">
            <p class="large">
                For security reasons, RFID, Grade/Section, and password changes must be done at the library help desk. 
                Contact support for any account-related issues.
            </p>
        </footer>
    </div>
    
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#profileForm');
    const contactInput = document.querySelector('input[name="contact_number"]');
    
    // Form submission confirmation
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent form submission until confirmed
        
        const fullName = document.querySelector('input[name="full_name"]').value.trim();
        const emailInput = document.querySelector('input[name="email"]');
        const contactInput = document.querySelector('input[name="contact_number"]');
        
        if (!fullName) {
            showBootstrapAlert('Please enter your full name.', 'warning');
            return;
        }
        
        if (emailInput.value && !isValidEmail(emailInput.value)) {
            showBootstrapAlert('Please enter a valid email address.', 'warning');
            return;
        }
        
        if (contactInput.value && !isValidPhone(contactInput.value)) {
            showBootstrapAlert('Please enter a valid contact number (at least 10 digits).', 'warning');
            return;
        }
        
        // Show Bootstrap confirmation modal instead of native confirm()
        const confirmModalHTML = `
            <div class="modal fade" id="profileConfirmModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-primary">
                                <i class="bi bi-question-circle me-2"></i>Confirm Changes
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-person-check text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="mb-3">Profile Update Confirmation</h6>
                            <p>Are you sure you want to save changes to your profile?</p>
                            <p class="text-muted small">Note: RFID and Grade/Section cannot be changed here.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary" id="confirmProfileSaveBtn">
                                <i class="bi bi-check-circle me-2"></i>Yes, Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        
        // Add modal to body if not exists
        if (!document.getElementById('profileConfirmModal')) {
            document.body.insertAdjacentHTML('beforeend', confirmModalHTML);
        }
        
        // Show confirmation modal
        const confirmModal = new bootstrap.Modal(document.getElementById('profileConfirmModal'));
        confirmModal.show();
        
        // Handle confirm button click
        document.getElementById('confirmProfileSaveBtn').addEventListener('click', function() {
            // Close confirmation modal
            confirmModal.hide();
            
            // Remove the submit event listener to prevent infinite loop
            form.removeEventListener('submit', arguments.callee);
            
            // Submit the form
            form.submit();
        });
    });
    
    // Contact number input validation
    if (contactInput) {
        contactInput.addEventListener('input', function(e) {
            // Allow only numbers, spaces, parentheses, and dashes
            this.value = this.value.replace(/[^\d\s\-+()]/g, '');
        });
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function isValidPhone(phone) {
        // Simple phone validation - at least 10 digits
        const digits = phone.replace(/\D/g, '');
        return digits.length >= 10;
    }
    
    function showBootstrapAlert(message, type = 'warning') {
        // Remove any existing custom alerts
        const existingAlert = document.querySelector('.custom-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show custom-alert`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'warning' ? 'exclamation-triangle' : 'info-circle'}-fill me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert after stats grid
        const statsGrid = document.querySelector('.stats-grid');
        statsGrid.parentNode.insertBefore(alertDiv, statsGrid.nextSibling);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
</body>
</html>