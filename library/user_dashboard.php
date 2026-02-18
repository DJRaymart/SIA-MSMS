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
    SELECT COUNT(*) as overdue_count 
    FROM borrowing_transaction 
    WHERE user_id = ? AND status = 'Overdue'
");
$stmt->execute([$user_id]);
$overdue_count = $stmt->fetch()['overdue_count'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total_borrowed 
    FROM borrowing_transaction 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$total_borrowed = $stmt->fetch()['total_borrowed'];

$stmt = $conn->prepare("
    SELECT bt.*, b.title, b.author 
    FROM borrowing_transaction bt
    JOIN book b ON bt.book_id = b.book_id
    WHERE bt.user_id = ? AND bt.status = 'Overdue'
    LIMIT 3
");
$stmt->execute([$user_id]);
$overdue_books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card.balance {
            border-left-color: var(--primary);
        }
        
        .stat-card.borrows {
            border-left-color: var(--success);
        }
        
        .stat-card.overdue {
            border-left-color: var(--danger);
        }
        
        .stat-card.total {
            border-left-color: var(--warning);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card.balance .stat-icon {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }
        
        .stat-card.borrows .stat-icon {
            background: linear-gradient(135deg, #34d399 0%, var(--success) 100%);
            color: white;
        }
        
        .stat-card.overdue .stat-icon {
            background: linear-gradient(135deg, #f87171 0%, var(--danger) 100%);
            color: white;
        }
        
        .stat-card.total .stat-icon {
            background: linear-gradient(135deg, #fbbf24 0%, var(--warning) 100%);
            color: white;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: var(--dark);
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
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
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            color: var(--secondary);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
        }
        
        .table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }
        
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
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
        
        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: #fefce8;
            border-left: 3px solid var(--warning);
        }
        
        .notification-item.danger {
            background: #fef2f2;
            border-left-color: var(--danger);
        }
        
        .notification-item.info {
            background: #eff6ff;
            border-left-color: var(--primary);
        }
        
        .notification-icon {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: var(--dark);
        }
        
        .notification-text {
            color: var(--secondary);
            font-size: 0.9rem;
            margin: 0;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s ease;
            margin-bottom: 0.8rem;
            text-align: center;
        }
        
        .quick-action-btn:hover {
            border-color: var(--primary);
            background: #f8fafc;
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        .quick-action-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
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
            <span class="badge bg-primary mt-2">User Account</span>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="user_dashboard.php" class="nav-link active">
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
                <a href="user_profile.php" class="nav-link">
                    <i class="nav-icon bi bi-person"></i>
                    <span>My Profile</span>
                </a>
            </div>
            <div class="nav-item mt-4">
                <a href="user_logout.php" class="nav-link text-danger" id="logoutLink">
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
                <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>! 👋</h1>
                <p>Track your library activities and manage your borrowings</p>
            </div>
            <div class="date-display">
                <span class="badge bg-light text-dark">
                    <i class="bi bi-calendar me-1"></i>
                    <?php echo date('F j, Y'); ?>
                </span>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card balance">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($user['balance'], 2); ?></div>
                <div class="stat-label">Account Balance</div>
                <?php if($user['balance'] > 0): ?>
                    <small class="text-danger mt-2 d-block">
                        <i class="bi bi-exclamation-circle me-1"></i> Payment required
                    </small>
                <?php endif; ?>
            </div>
            
            <div class="stat-card borrows">
                <div class="stat-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-value"><?php echo $active_borrows; ?></div>
                <div class="stat-label">Active Borrows</div>
                <small class="text-muted mt-2 d-block">Currently reading</small>
            </div>
            
            <div class="stat-card overdue">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?php echo $overdue_count; ?></div>
                <div class="stat-label">Overdue Books</div>
                <?php if($overdue_count > 0): ?>
                    <small class="text-danger mt-2 d-block">
                        <i class="bi bi-clock me-1"></i> Immediate action needed
                    </small>
                <?php endif; ?>
            </div>
            
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="bi bi-collection"></i>
                </div>
                <div class="stat-value"><?php echo $total_borrowed; ?></div>
                <div class="stat-label">Total Borrowed</div>
                <small class="text-muted mt-2 d-block">All time</small>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Activity -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Recent Borrowing Activity</h5>
                        <a href="user_history.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT bt.*, b.title, b.author
                            FROM borrowing_transaction bt
                            JOIN book b ON bt.book_id = b.book_id
                            WHERE bt.user_id = ?
                            ORDER BY bt.borrow_date DESC 
                            LIMIT 6
                        ");
                        $stmt->execute([$user_id]);
                        $recent_transactions = $stmt->fetchAll();
                        
                        if (count($recent_transactions) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Book</th>
                                            <th>Dates</th>
                                            <th>Status</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_transactions as $transaction): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($transaction['title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($transaction['author']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="d-block">
                                                    <strong>Borrowed:</strong> <?php echo date('M d, Y', strtotime($transaction['borrow_date'])); ?>
                                                </small>
                                                <small class="d-block">
                                                    <strong>Due:</strong> <?php echo date('M d, Y', strtotime($transaction['due_date'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = 'bg-secondary';
                                                switch($transaction['status']) {
                                                    case 'Reserved': $status_class = 'bg-warning'; break;
                                                    case 'Borrowed': $status_class = 'bg-info'; break;
                                                    case 'Overdue': $status_class = 'bg-danger'; break;
                                                    case 'Returned': $status_class = 'bg-success'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo $transaction['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($transaction['status'] === 'Overdue'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger pay-now-btn"
                                                            data-transaction-id="<?php echo $transaction['transaction_id']; ?>"
                                                            data-book-title="<?php echo htmlspecialchars($transaction['title']); ?>">
                                                        Pay Now
                                                    </button>
                                             
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-clock-history" style="font-size: 3rem; color: #cbd5e1;"></i>
                                </div>
                                <h5>No borrowing history yet</h5>
                                <p class="text-muted">Start exploring our library collection</p>
                                <a href="user_books.php" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i> Browse Books
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions & Notifications -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="user_books.php" class="quick-action-btn">
                                    <div>
                                        <i class="bi bi-search quick-action-icon"></i>
                                        <div>Browse Books</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="user_borrowed.php" class="quick-action-btn">
                                    <div>
                                        <i class="bi bi-bag-check quick-action-icon"></i>
                                        <div>My Borrowings</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="user_history.php" class="quick-action-btn">
                                    <div>
                                        <i class="bi bi-clock-history quick-action-icon"></i>
                                        <div>History</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="user_profile.php" class="quick-action-btn">
                                    <div>
                                        <i class="bi bi-person quick-action-icon"></i>
                                        <div>My Profile</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="card">
                    <div class="card-header">
                        <h5>Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($overdue_count > 0): ?>
                            <div class="notification-item danger">
                                <div class="notification-icon text-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">Overdue Books</div>
                                    <p class="notification-text">You have <?php echo $overdue_count; ?> overdue book(s) that need to be returned immediately.</p>
                                    <?php if (count($overdue_books) > 0): ?>
                                        <ul class="list-unstyled mt-2 mb-0 small">
                                            <?php foreach ($overdue_books as $book): ?>
                                                <li><i class="bi bi-dot"></i> <?php echo htmlspecialchars($book['title']); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user['balance'] > 0): ?>
                            <div class="notification-item">
                                <div class="notification-icon text-warning">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">Outstanding Balance</div>
                                    <p class="notification-text">You have a balance of $<?php echo number_format($user['balance'], 2); ?>. Please settle your dues.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($active_borrows >= 5): ?>
                            <div class="notification-item info">
                                <div class="notification-icon text-primary">
                                    <i class="bi bi-info-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">Borrowing Limit Reached</div>
                                    <p class="notification-text">You have reached the maximum borrowing limit (5 books).</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($overdue_count == 0 && $user['balance'] == 0 && $active_borrows < 5): ?>
                            <div class="text-center py-3">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                <p class="mt-2 text-muted">You're all caught up! No pending notifications.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-4 pt-3 border-top text-center text-dark">
                       <p class="large">Library System • Last login: <?php echo date('M d, Y H:i'); ?></p>
        </footer>
    </div>

    <!-- Bootstrap Modals -->
    
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-box-arrow-right text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Are you sure you want to logout?</h5>
                    <p class="text-muted">You will be redirected to the login page.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="user_logout.php" class="btn btn-primary">Yes, Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pay Now Confirmation Modal -->
    <div class="modal fade" id="payNowModal" tabindex="-1" aria-labelledby="payNowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payNowModalLabel">Pay Overdue Fee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to pay the overdue fee for this book?</p>
                    <p><strong>Book:</strong> <span id="payBookTitle"></span></p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        This action will mark the overdue fee as paid and update your account balance.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="user_pay.php" style="display: inline;">
                        <input type="hidden" name="transaction_id" id="payTransactionId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-credit-card"></i> Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Renew Book Confirmation Modal -->
    <div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renewModalLabel">Renew Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to renew this book?</p>
                    <p><strong>Book:</strong> <span id="renewBookTitle"></span></p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Renewing will extend the due date by 14 days from today.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="user_renew.php" style="display: inline;">
                        <input type="hidden" name="transaction_id" id="renewTransactionId">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise"></i> Confirm Renew
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modals
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            const payNowModal = new bootstrap.Modal(document.getElementById('payNowModal'));
            const renewModal = new bootstrap.Modal(document.getElementById('renewModal'));
            
            // Handle logout link click
            document.getElementById('logoutLink').addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.show();
            });
            
            // Handle pay now button clicks
            document.querySelectorAll('.pay-now-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const transactionId = this.getAttribute('data-transaction-id');
                    const bookTitle = this.getAttribute('data-book-title');
                    
                    // Set data in modal
                    document.getElementById('payBookTitle').textContent = bookTitle;
                    document.getElementById('payTransactionId').value = transactionId;
                    
                    // Show the modal
                    payNowModal.show();
                });
            });
            
            // Handle renew button clicks
            document.querySelectorAll('.renew-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const transactionId = this.getAttribute('data-transaction-id');
                    const bookTitle = this.getAttribute('data-book-title');
                    
                    // Set data in modal
                    document.getElementById('renewBookTitle').textContent = bookTitle;
                    document.getElementById('renewTransactionId').value = transactionId;
                    
                    // Show the modal
                    renewModal.show();
                });
            });
            
            // Add animation to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Initialize opacity for animation
            statCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });
        });
    </script>
</body>
</html>