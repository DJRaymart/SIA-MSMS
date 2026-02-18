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
    SELECT bt.*, b.title, b.author, b.image_filename, b.category, b.isbn
    FROM borrowing_transaction bt
    JOIN book b ON bt.book_id = b.book_id
    WHERE bt.user_id = ? 
    ORDER BY bt.borrow_date DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_borrowed,
        SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) as total_returned,
        SUM(CASE WHEN status = 'Overdue' THEN 1 ELSE 0 END) as total_overdue,
        SUM(CASE WHEN status = 'Borrowed' THEN 1 ELSE 0 END) as currently_borrowed,
        SUM(total_penalty) as total_penalties,
        SUM(CASE WHEN status = 'Returned' THEN total_penalty ELSE 0 END) as paid_penalties,
        SUM(CASE WHEN status IN ('Borrowed', 'Overdue') THEN total_penalty ELSE 0 END) as pending_penalties
    FROM borrowing_transaction 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$successful_returns = $stats['total_returned'];
$completion_rate = $stats['total_borrowed'] > 0 ? round(($successful_returns / $stats['total_borrowed']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing History - Library System</title>
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
        
        .stat-card.returned {
            border-left-color: var(--success);
        }
        
        .stat-card.overdue {
            border-left-color: var(--danger);
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
        
        .stat-card.returned .stat-icon {
            background: linear-gradient(135deg, #34d399 0%, var(--success) 100%);
            color: white;
        }
        
        .stat-card.overdue .stat-icon {
            background: linear-gradient(135deg, #f87171 0%, var(--danger) 100%);
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
        
        .history-item {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .history-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .history-item.reserved {
            border-left-color: #f59e0b;
            background: linear-gradient(to right, #fffbeb 0%, white 30%);
        }
        
        .history-item.borrowed {
            border-left-color: #3b82f6;
            background: linear-gradient(to right, #eff6ff 0%, white 30%);
        }
        
        .history-item.overdue {
            border-left-color: var(--danger);
            background: linear-gradient(to right, #fef2f2 0%, white 30%);
        }
        
        .history-item.returned {
            border-left-color: var(--success);
            background: linear-gradient(to right, #f0fdf4 0%, white 30%);
        }
        
        .book-cover {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .book-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
        .book-author {
            color: var(--secondary);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .book-category {
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 500;
        }
        
        .date-info {
            font-size: 0.85rem;
            color: var(--dark);
        }
        
        .date-label {
            font-size: 0.75rem;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        
        .penalty-amount {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .penalty-amount.paid {
            color: var(--success);
        }
        
        .penalty-amount.pending {
            color: var(--danger);
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
        
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .filter-btn {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            color: var(--secondary);
            background: white;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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
                <a href="user_borrowed.php" class="nav-link">
                    <i class="nav-icon bi bi-bag-check"></i>
                    <span>My Borrowings</span>
                    <?php if($active_borrows > 0): ?>
                        <span class="badge bg-primary ms-auto"><?php echo $active_borrows; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav-item">
                <a href="user_history.php" class="nav-link active">
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
                <h1>Borrowing History 📚</h1>
                <p>Track all your library transactions and reading journey</p>
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
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_borrowed']; ?></div>
                <div class="stat-label">Total Books Borrowed</div>
                <small class="text-muted mt-2 d-block">All time history</small>
            </div>
            
            <div class="stat-card returned">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_returned']; ?></div>
                <div class="stat-label">Successfully Returned</div>
                <small class="text-success mt-2 d-block">
                    <i class="bi bi-check me-1"></i> <?php echo $completion_rate; ?>% completion
                </small>
            </div>
            
            <div class="stat-card overdue">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_overdue']; ?></div>
                <div class="stat-label">Overdue Instances</div>
                <small class="text-muted mt-2 d-block">Past overdue records</small>
            </div>
            
            <div class="stat-card penalty">
                <div class="stat-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($stats['total_penalties'], 2); ?></div>
                <div class="stat-label">Total Penalties</div>
                <small class="text-muted mt-2 d-block">
                    <?php if($stats['paid_penalties'] > 0): ?>
                        <span class="text-success">Paid: ₱<?php echo number_format($stats['paid_penalties'], 2); ?></span>
                    <?php endif; ?>
                    <?php if($stats['pending_penalties'] > 0): ?>
                        <span class="text-danger ms-2">Pending: ₱ <?php echo number_format($stats['pending_penalties'], 2); ?></span>
                    <?php endif; ?>
                </small>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="d-flex flex-wrap gap-2">
                <button class="filter-btn active" data-filter="all">All History</button>
                <button class="filter-btn" data-filter="returned">Returned</button>
                <button class="filter-btn" data-filter="overdue">Overdue</button>
                <button class="filter-btn" data-filter="borrowed">Currently Borrowed</button>
                <button class="filter-btn" data-filter="reserved">Reserved</button>
            </div>
        </div>
        
        <!-- History List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Your Borrowing History</h5>
                <span class="badge bg-primary"><?php echo count($history); ?> transactions</span>
            </div>
            <div class="card-body">
                <?php if(count($history) > 0): ?>
                    <?php foreach($history as $record): 
                        
                        $borrow_date_formatted = date('M d, Y', strtotime($record['borrow_date']));
                        $due_date_formatted = date('M d, Y', strtotime($record['due_date']));
                        $return_date_formatted = $record['return_date'] ? date('M d, Y', strtotime($record['return_date'])) : 'Not returned';

                        $status_class = strtolower($record['status']);
                        $status_icon = '';
                        $status_color = '';
                        
                        switch($record['status']) {
                            case 'Reserved': 
                                $status_icon = 'bi-bookmark';
                                $status_color = 'warning';
                                break;
                            case 'Borrowed': 
                                $status_icon = 'bi-book';
                                $status_color = 'info';
                                break;
                            case 'Overdue': 
                                $status_icon = 'bi-exclamation-triangle';
                                $status_color = 'danger';
                                break;
                            case 'Returned': 
                                $status_icon = 'bi-check-circle';
                                $status_color = 'success';
                                break;
                        }

                        $penalty_class = $record['total_penalty'] > 0 ? 'pending' : 'paid';
                        if ($record['status'] == 'Returned' && $record['total_penalty'] > 0) {
                            $penalty_class = 'paid';
                        }
                    ?>
                    <div class="history-item <?php echo $status_class; ?>" data-status="<?php echo strtolower($record['status']); ?>">
                        <div class="row align-items-center">
                            <!-- Book Info -->
                            <div class="col-lg-3 mb-3 mb-lg-0">
                                <div class="d-flex">
                                    <?php if($record['image_filename'] && file_exists("uploads/books/" . $record['image_filename'])): ?>
                                        <img src="uploads/books/<?php echo htmlspecialchars($record['image_filename']); ?>" 
                                             class="book-cover me-3" 
                                             alt="<?php echo htmlspecialchars($record['title']); ?>">
                                    <?php else: ?>
                                        <div class="book-cover bg-light d-flex align-items-center justify-content-center me-3">
                                            <i class="bi bi-book text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="book-title"><?php echo htmlspecialchars($record['title']); ?></div>
                                        <div class="book-author"><?php echo htmlspecialchars($record['author']); ?></div>
                                        <?php if($record['category']): ?>
                                            <div class="book-category"><?php echo htmlspecialchars($record['category']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dates -->
                            <div class="col-lg-4 mb-3 mb-lg-0">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="date-label">Borrowed</div>
                                        <div class="date-info"><?php echo $borrow_date_formatted; ?></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="date-label">Due Date</div>
                                        <div class="date-info"><?php echo $due_date_formatted; ?></div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="date-label">Returned</div>
                                        <div class="date-info">
                                            <?php echo $return_date_formatted; ?>
                                            <?php if($record['return_date']): ?>
                                                <small class="text-success d-block">
                                                    <i class="bi bi-check-circle"></i> On time
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-lg-2 mb-3 mb-lg-0">
                                <span class="badge bg-<?php echo $status_color; ?>">
                                    <i class="bi <?php echo $status_icon; ?> me-1"></i><?php echo $record['status']; ?>
                                </span>
                                <?php if($record['status'] == 'Returned'): ?>
                                    <div class="mt-1">
                                        <small class="text-muted">Transaction #<?php echo $record['transaction_id']; ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Penalty -->
                            <div class="col-lg-3">
                                <?php if($record['total_penalty'] > 0): ?>
                                    <div class="penalty-amount <?php echo $penalty_class; ?>">
                                        <i class="bi bi-cash-coin me-1"></i>₱<?php echo number_format($record['total_penalty'], 2); ?>
                                        <small class="d-block text-muted">
                                            <?php echo $penalty_class == 'paid' ? 'Paid' : 'Pending'; ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>No penalty
                                        <small class="d-block text-muted">Cleared</small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($record['status'] == 'Overdue' && $record['total_penalty'] > 0): ?>
                                    <button class="btn btn-sm btn-outline-danger mt-2 w-100" 
                                            onclick="payPenalty(<?php echo $record['transaction_id']; ?>)">
                                        <i class="bi bi-credit-card me-1"></i>Pay Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h4 class="mb-3">No Borrowing History Yet</h4>
                        <p class="text-muted mb-4">Start your reading journey by borrowing books from our library collection.</p>
                        <a href="user_books.php" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Browse Books
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pagination/Summary -->
        <?php if(count($history) > 0): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-dark">
                    <large>Showing <?php echo count($history); ?> transactions from your borrowing history</large>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="exportHistory()" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);  color: white; ">
                        <i class="bi bi-download me-2"></i>Export History
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <footer class="mt-4 pt-3 border-top text-center text-dark">
            <p class="large">
                Your reading history helps us recommend better books for you. 
                Need help understanding your history? Visit the library for concerns.
            </p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation to history items
        const historyItems = document.querySelectorAll('.history-item');
        historyItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 30);
        });
        
        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const historyItemsAll = document.querySelectorAll('.history-item');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                
                // Show/hide items based on filter
                historyItemsAll.forEach(item => {
                    if (filter === 'all') {
                        item.style.display = 'block';
                    } else {
                        const itemStatus = item.getAttribute('data-status');
                        if (itemStatus === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                    
                    // Add animation when showing items
                    setTimeout(() => {
                        if (item.style.display === 'block') {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }
                    }, 50);
                });
            });
        });
    });
    
    function payPenalty(transactionId) {
        // In a real application, you would redirect to a payment page or open a payment modal
        alert('Payment functionality would be implemented here.\n\nTransaction ID: #' + transactionId + '\n\nYou would be redirected to a secure payment gateway.');
        // window.location.href = 'payment.php?id=' + transactionId;
    }
    
    function exportHistory() {
        // Show loading/processing state
        const exportBtn = event?.target || document.querySelector('button[onclick*="exportHistory"]');
        const originalText = exportBtn?.innerHTML || '';
        
        if (exportBtn) {
            exportBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Preparing Export...';
            exportBtn.disabled = true;
        }
        
        try {
            // Create CSV content
            const rows = [
                ['Library Borrowing History Report'],
                [''],
                ['User:', '<?php echo addslashes($user["full_name"]); ?>'],
                ['Student ID:', '<?php echo addslashes($user["institutional_id"]); ?>'],
                ['Generated:', new Date().toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })],
                [''],
                ['Transaction ID', 'Book Title', 'Author', 'Category', 'Borrow Date', 'Due Date', 'Return Date', 'Status', 'Penalty ($)', 'Return Status']
            ];
            
            // Add data rows from PHP data
            <?php foreach($history as $record): ?>
            rows.push([
                <?php echo $record['transaction_id']; ?>,
                "<?php echo addslashes($record['title']); ?>",
                "<?php echo addslashes($record['author']); ?>",
                "<?php echo addslashes($record['category'] ?? 'N/A'); ?>",
                "<?php echo $record['borrow_date']; ?>",
                "<?php echo $record['due_date']; ?>",
                "<?php echo $record['return_date'] ?: 'Not returned'; ?>",
                "<?php echo $record['status']; ?>",
                "<?php echo number_format($record['total_penalty'], 2); ?>",
                "<?php 
                    if (!$record['return_date']) {
                        echo 'Not Returned';
                    } elseif ($record['return_date'] <= $record['due_date']) {
                        echo 'On Time';
                    } else {
                        echo 'Late';
                    }
                ?>"
            ]);
            <?php endforeach; ?>
            
            // Add summary section
            rows.push(['']);
            rows.push(['Summary Statistics']);
            rows.push(['Total Books:', <?php echo count($history); ?>]);
            rows.push(['Returned Books:', <?php echo $stats['total_returned']; ?>]);
            rows.push(['Overdue Instances:', <?php echo $stats['total_overdue']; ?>]);
            rows.push(['Total Penalties:', '$<?php echo number_format($stats['total_penalties'], 2); ?>']);
            
            // Convert to CSV string
            let csvContent = '';
            rows.forEach(row => {
                csvContent += row.map(cell => {
                    // Escape quotes and wrap in quotes if contains comma
                    if (typeof cell === 'string' && (cell.includes(',') || cell.includes('"') || cell.includes('\n'))) {
                        return '"' + cell.replace(/"/g, '""') + '"';
                    }
                    return cell;
                }).join(",") + "\r\n";
            });
            
            // Create Blob and download
            const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            
            link.setAttribute("href", url);
            link.setAttribute("download", "library_history_<?php echo date('Y-m-d'); ?>.csv");
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up
            setTimeout(() => URL.revokeObjectURL(url), 100);
            
            // Show success message
            if (exportBtn) {
                exportBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Exported!';
                setTimeout(() => {
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                }, 1500);
            } else {
                alert('Export completed successfully! Check your downloads folder.');
            }
            
        } catch (error) {
            console.error('Export failed:', error);
            
            if (exportBtn) {
                exportBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>Export Failed';
                exportBtn.classList.add('btn-danger');
                setTimeout(() => {
                    exportBtn.innerHTML = originalText;
                    exportBtn.disabled = false;
                    exportBtn.classList.remove('btn-danger');
                }, 2000);
            }
            
            alert('Export failed. Please try again or contact support.');
        }
    }
</script>
</body>
</html>