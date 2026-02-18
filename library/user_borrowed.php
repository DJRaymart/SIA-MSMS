<?php

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
    $transaction_id = $_POST['transaction_id'];
    
    try {
        
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT book_id FROM borrowing_transaction WHERE transaction_id = ? AND user_id = ? AND status = 'Reserved'");
        $stmt->execute([$transaction_id, $user_id]);
        $transaction = $stmt->fetch();
        
        if ($transaction) {
            $book_id = $transaction['book_id'];

            $stmt = $conn->prepare("DELETE FROM borrowing_transaction WHERE transaction_id = ? AND user_id = ? AND status = 'Reserved'");
            $stmt->execute([$transaction_id, $user_id]);

            $stmt = $conn->prepare("UPDATE book SET available_copies = available_copies + 1 WHERE book_id = ?");
            $stmt->execute([$book_id]);
            
            $conn->commit();
            $cancellation_success = "Reservation cancelled successfully!";
        } else {
            $cancellation_error = "Reservation not found or cannot be cancelled.";
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        $cancellation_error = "Cancellation failed: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $conn->prepare("
    SELECT COUNT(*) as active_borrows 
    FROM borrowing_transaction 
    WHERE user_id = ? AND status IN ('Borrowed', 'Reserved', 'Overdue')
");
$stmt->execute([$user_id]);
$active_borrows = $stmt->fetch()['active_borrows'];

$stmt = $conn->prepare("
    SELECT bt.*, b.title, b.author, b.image_filename, b.isbn, b.category
    FROM borrowing_transaction bt
    JOIN book b ON bt.book_id = b.book_id
    WHERE bt.user_id = ? AND bt.status IN ('Borrowed', 'Reserved', 'Overdue')
    ORDER BY 
        CASE bt.status 
            WHEN 'Overdue' THEN 1
            WHEN 'Borrowed' THEN 2
            WHEN 'Reserved' THEN 3
        END,
        bt.due_date ASC
");
$stmt->execute([$user_id]);
$borrowings = $stmt->fetchAll();

$total_borrowed = count($borrowings);
$overdue_count = count(array_filter($borrowings, function($b) { 
    return $b['status'] == 'Overdue'; 
}));
$reserved_count = count(array_filter($borrowings, function($b) { 
    return $b['status'] == 'Reserved'; 
}));
$total_penalty = array_sum(array_column($borrowings, 'total_penalty'));

$today = date('Y-m-d');
$near_due_date = date('Y-m-d', strtotime('+3 days'));
$near_due_count = 0;
foreach ($borrowings as $borrow) {
    if ($borrow['status'] == 'Borrowed' && $borrow['due_date'] <= $near_due_date && $borrow['due_date'] >= $today) {
        $near_due_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowings - Library System</title>
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
        
        .stat-card.total {
            border-left-color: var(--primary);
        }
        
        .stat-card.overdue {
            border-left-color: var(--danger);
        }
        
        .stat-card.reserved {
            border-left-color: var(--warning);
        }
        
        .stat-card.penalty {
            border-left-color: #8b5cf6;
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
        
        .stat-card.total .stat-icon {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }
        
        .stat-card.overdue .stat-icon {
            background: linear-gradient(135deg, #f87171 0%, var(--danger) 100%);
            color: white;
        }
        
        .stat-card.reserved .stat-icon {
            background: linear-gradient(135deg, #fbbf24 0%, var(--warning) 100%);
            color: white;
        }
        
        .stat-card.penalty .stat-icon {
            background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
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
        
        .borrowing-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .borrowing-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .borrowing-card.overdue {
            border-left-color: var(--danger);
            background: linear-gradient(to right, #fef2f2 0%, white 30%);
        }
        
        .borrowing-card.near-due {
            border-left-color: var(--warning);
            background: linear-gradient(to right, #fffbeb 0%, white 30%);
        }
        
        .borrowing-card.reserved {
            border-left-color: #f59e0b;
            background: linear-gradient(to right, #fffbeb 0%, white 30%);
        }
        
        .borrowing-card.borrowed {
            border-left-color: var(--success);
            background: linear-gradient(to right, #f0fdf4 0%, white 30%);
        }
        
        .book-cover {
            width: 80px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .book-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }
        
        .book-author {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .book-category {
            font-size: 0.8rem;
            color: var(--primary);
            font-weight: 500;
        }
        
        .due-date {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .due-date.overdue {
            color: var(--danger);
        }
        
        .due-date.near-due {
            color: var(--warning);
        }
        
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .btn-outline-warning {
            border: 1px solid var(--warning);
            color: var(--warning);
        }
        
        .btn-outline-warning:hover {
            background: var(--warning);
            color: white;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        /* Confirmation Modal Styles */
        .confirmation-modal .modal-dialog {
            max-width: 500px;
        }
        
        .confirmation-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .confirmation-success {
            color: var(--success);
        }
        
        .confirmation-warning {
            color: var(--warning);
        }
        
        .confirmation-danger {
            color: var(--danger);
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
                <a href="user_borrowed.php" class="nav-link active">
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
                <h1>My Current Borrowings 📖</h1>
                <p>Manage your borrowed and reserved books</p>
            </div>
            <div class="date-display">
                <span class="badge bg-light text-dark">
                    <i class="bi bi-calendar me-1"></i>
                    <?php echo date('F j, Y'); ?>
                </span>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if(isset($cancellation_success)): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2" style="font-size: 1.2rem;"></i>
                <div><?php echo $cancellation_success; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($cancellation_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.2rem;"></i>
                <div><?php echo $cancellation_error; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-value"><?php echo $total_borrowed; ?></div>
                <div class="stat-label">Active Borrowings</div>
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
                <?php else: ?>
                    <small class="text-success mt-2 d-block">
                        <i class="bi bi-check-circle me-1"></i> All books on time
                    </small>
                <?php endif; ?>
            </div>
            
            <div class="stat-card reserved">
                <div class="stat-icon">
                    <i class="bi bi-bookmark"></i>
                </div>
                <div class="stat-value"><?php echo $reserved_count; ?></div>
                <div class="stat-label">Reserved Books</div>
                <small class="text-muted mt-2 d-block">Ready for pickup</small>
            </div>
            
            <div class="stat-card penalty">
                <div class="stat-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="stat-value">$<?php echo number_format($total_penalty, 2); ?></div>
                <div class="stat-label">Total Penalties</div>
                <?php if($total_penalty > 0): ?>
                    <small class="text-danger mt-2 d-block">
                        <i class="bi bi-exclamation-circle me-1"></i> Payment required
                    </small>
                <?php else: ?>
                    <small class="text-success mt-2 d-block">
                        <i class="bi bi-check-circle me-1"></i> No penalties
                    </small>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Notifications -->
        <?php if($overdue_count > 0): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Urgent Action Required!</strong> You have <?php echo $overdue_count; ?> overdue book(s). 
                    Please return them immediately to avoid additional penalties.
                </div>
            </div>
        <?php endif; ?>
        
        <?php if($near_due_count > 0): ?>
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-clock-history me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Due Date Reminder:</strong> You have <?php echo $near_due_count; ?> book(s) due within 3 days. 
                    Please plan for their return.
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Borrowings List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Your Borrowings</h5>
                <span class="badge bg-primary"><?php echo $total_borrowed; ?> items</span>
            </div>
            <div class="card-body">
                <?php if(count($borrowings) > 0): ?>
                    <?php foreach($borrowings as $borrow): 
                        $today = date('Y-m-d');
                        $due_date = $borrow['due_date'];
                        $is_overdue = ($today > $due_date && $borrow['status'] == 'Borrowed');
                        $is_near_due = (!$is_overdue && $borrow['status'] == 'Borrowed' && $due_date <= $near_due_date);

                        $card_class = '';
                        if ($borrow['status'] == 'Overdue' || $is_overdue) {
                            $card_class = 'overdue';
                        } elseif ($borrow['status'] == 'Reserved') {
                            $card_class = 'reserved';
                        } elseif ($is_near_due) {
                            $card_class = 'near-due';
                        } else {
                            $card_class = 'borrowed';
                        }

                        $borrow_date_formatted = date('M d, Y', strtotime($borrow['borrow_date']));
                        $due_date_formatted = date('M d, Y', strtotime($due_date));
                    ?>
                    <div class="borrowing-card <?php echo $card_class; ?>" id="borrowing-<?php echo $borrow['transaction_id']; ?>">
                        <div class="row align-items-center">
                            <!-- Book Cover & Info -->
                            <div class="col-lg-3 mb-3 mb-lg-0">
                                <div class="d-flex">
                                    <?php if($borrow['image_filename']): ?>
                                        <img src="uploads/books/<?php echo htmlspecialchars($borrow['image_filename']); ?>" 
                                             class="book-cover me-3" 
                                             alt="<?php echo htmlspecialchars($borrow['title']); ?>"
                                             onerror="this.src='https://via.placeholder.com/80x100?text=No+Image';">
                                    <?php else: ?>
                                        <div class="book-cover bg-light d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-book text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="book-title"><?php echo htmlspecialchars($borrow['title']); ?></div>
                                        <div class="book-author"><?php echo htmlspecialchars($borrow['author']); ?></div>
                                        <?php if($borrow['category']): ?>
                                            <div class="book-category"><?php echo htmlspecialchars($borrow['category']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dates -->
                            <div class="col-lg-3 mb-3 mb-lg-0">
                                <div class="mb-2">
                                    <small class="text-muted d-block">Borrow Date</small>
                                    <div class="fw-bold"><?php echo $borrow_date_formatted; ?></div>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Due Date</small>
                                    <div class="due-date <?php echo $is_overdue ? 'overdue' : ($is_near_due ? 'near-due' : ''); ?> fw-bold">
                                        <?php echo $due_date_formatted; ?>
                                        <?php if($is_overdue): ?>
                                            <span class="badge bg-danger ms-2">OVERDUE</span>
                                        <?php elseif($is_near_due): ?>
                                            <span class="badge bg-warning ms-2">DUE SOON</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status & Penalty -->
                            <div class="col-lg-3 mb-3 mb-lg-0">
                                <div class="mb-3">
                                    <?php 
                                    $status_class = '';
                                    switch($borrow['status']) {
                                        case 'Reserved': 
                                            $status_class = 'bg-warning';
                                            $status_text = 'Reserved';
                                            $status_icon = 'bi-bookmark';
                                            break;
                                        case 'Borrowed': 
                                            $status_class = $is_overdue ? 'bg-danger' : 'bg-success';
                                            $status_text = $is_overdue ? 'Overdue' : 'Borrowed';
                                            $status_icon = $is_overdue ? 'bi-exclamation-triangle' : 'bi-book';
                                            break;
                                        case 'Overdue': 
                                            $status_class = 'bg-danger';
                                            $status_text = 'Overdue';
                                            $status_icon = 'bi-exclamation-triangle';
                                            break;
                                    }
                                    ?>
                                    <small class="text-muted d-block mb-1">Status</small>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <i class="bi <?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1">Penalty</small>
                                    <?php if($borrow['total_penalty'] > 0): ?>
                                        <div class="text-danger fw-bold">
                                            <i class="bi bi-cash-coin me-1"></i>₱<?php echo number_format($borrow['total_penalty'], 2); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>No penalty
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-lg-3">
                                <?php if($borrow['status'] == 'Reserved'): ?>
                                    
                                    <button class="btn btn-outline-secondary w-100 cancel-reservation-btn"
                                            data-transaction-id="<?php echo $borrow['transaction_id']; ?>"
                                            data-book-title="<?php echo htmlspecialchars($borrow['title']); ?>">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </button>
                                <?php elseif($borrow['status'] == 'Borrowed' || $borrow['status'] == 'Overdue'): ?>
                                    <!-- Just show a message for borrowed books -->
                                    <div class="text-center">
                                        <h3 class="text-muted d-block mb-2">Visit library desk for returns</h3>
                                        <button class="btn btn-outline-success w-100" disabled>
                                            <i class="bi bi-arrow-return-left me-1"></i>Return at Library
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <h4 class="mb-3">No Active Borrowings</h4>
                        <p class="text-muted mb-4">You don't have any borrowed or reserved books at the moment.</p>
                        <a href="user_books.php" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Browse Books
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-4 pt-3 border-top text-center text-dark">
                       <p class="large">Need help? Visit the help desk.</p>
        </footer>
    </div>
    
    <!-- Collection Info Modal -->
    <div class="modal fade" id="collectionModal" tabindex="-1" aria-labelledby="collectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="collectionModalLabel">Book Collection Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Your reserved book <strong id="collectionBookTitle"></strong> is ready for collection.</p>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Collection Details:</h6>
                        <ul class="mb-0">
                            <li>Bring your student ID or library card</li>
                            <li>Visit the main library desk</li>
                            <li>Provide transaction ID: <strong id="collectionTransactionId"></strong></li>
                            <li>Books must be collected within 3 days</li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i> Library Hours: Mon-Fri 8AM-6PM, Sat 9AM-11PM
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancel Reservation Confirmation Modal -->
    <div class="modal fade confirmation-modal" id="cancelReservationModal" tabindex="-1" aria-labelledby="cancelReservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="confirmation-icon confirmation-warning">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <h4 class="modal-title mb-3" id="cancelReservationModalLabel">Cancel Reservation</h4>
                    <p id="cancelReservationMessage" class="mb-4"></p>
                    <div class="alert alert-warning small mb-4">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Note: Cancellations cannot be undone.
                    </div>
                    
                    <!-- Hidden form for cancellation -->
                    <form method="POST" id="cancelReservationForm" style="display: none;">
                        <input type="hidden" name="transaction_id" id="cancelTransactionId">
                        <input type="hidden" name="cancel_reservation" value="1">
                    </form>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="min-width: 120px;">No, Keep It</button>
                        <button type="button" class="btn btn-danger" id="finalCancelReservationBtn" style="min-width: 120px;">
                            <i class="bi bi-check-lg me-2"></i> Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize variables
        const collectionModal = new bootstrap.Modal(document.getElementById('collectionModal'));
        const cancelReservationModal = new bootstrap.Modal(document.getElementById('cancelReservationModal'));
        
        let currentTransactionId = null;
        let currentBookTitle = null;
        
        // Add animation to borrowing cards
        const borrowingCards = document.querySelectorAll('.borrowing-card');
        borrowingCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
        
        // Collect Info Button Click
        document.querySelectorAll('.collect-btn').forEach(button => {
            button.addEventListener('click', function() {
                const transactionId = this.getAttribute('data-transaction-id');
                const bookTitle = this.getAttribute('data-book-title');
                
                document.getElementById('collectionBookTitle').textContent = bookTitle;
                document.getElementById('collectionTransactionId').textContent = '#' + transactionId;
                
                collectionModal.show();
            });
        });
        
        // Cancel Reservation Button Click
        document.querySelectorAll('.cancel-reservation-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentTransactionId = this.getAttribute('data-transaction-id');
                currentBookTitle = this.getAttribute('data-book-title');
                
                document.getElementById('cancelReservationMessage').textContent = 
                    `Are you sure you want to cancel your reservation for "${currentBookTitle}"?`;
                
                // Set transaction ID in hidden form
                document.getElementById('cancelTransactionId').value = currentTransactionId;
                
                cancelReservationModal.show();
            });
        });
        
        // Final Cancel Reservation Button Click
        document.getElementById('finalCancelReservationBtn').addEventListener('click', function() {
            // Submit the cancellation form
            document.getElementById('cancelReservationForm').submit();
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
    </script>
</body>
</html>