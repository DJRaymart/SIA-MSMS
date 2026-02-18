<?php

session_start(); 
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$categories = $conn->query("SELECT DISTINCT category FROM book WHERE category IS NOT NULL ORDER BY category")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reserve_book'])) {
    $book_id = $_POST['book_id'];
    
    try {
        
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT available_copies FROM book WHERE book_id = ? AND status = 'Available'");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
        
        if (!$book) {
            $reservation_error = "Book not found or not available.";
            $conn->rollBack();
        } elseif ($book['available_copies'] <= 0) {
            $reservation_error = "No copies available for reservation.";
            $conn->rollBack();
        } else {
            
            $stmt = $conn->prepare("
                SELECT COUNT(*) as active_count 
                FROM borrowing_transaction 
                WHERE user_id = ? AND status IN ('Borrowed', 'Reserved')
            ");
            $stmt->execute([$user_id]);
            $active_count = $stmt->fetch()['active_count'];
            
            if ($active_count >= 5) {
                $reservation_error = "You have reached the maximum borrowing limit (5 books).";
                $conn->rollBack();
            } else {
                
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count 
                    FROM borrowing_transaction 
                    WHERE user_id = ? AND book_id = ? AND status IN ('Reserved', 'Borrowed')
                ");
                $stmt->execute([$user_id, $book_id]);
                $existing = $stmt->fetch()['count'];
                
                if ($existing > 0) {
                    $reservation_error = "You already have a pending reservation for this book.";
                    $conn->rollBack();
                } else {
                    
                    $borrow_date = date('Y-m-d');
                    $due_date = date('Y-m-d', strtotime('+14 days'));
                    
                    $stmt = $conn->prepare("
                        INSERT INTO borrowing_transaction 
                        (book_id, user_id, borrow_date, due_date, status) 
                        VALUES (?, ?, ?, ?, 'Reserved')
                    ");
                    $stmt->execute([$book_id, $user_id, $borrow_date, $due_date]);
                    
                    $transaction_id = $conn->lastInsertId();

                    $stmt = $conn->prepare("UPDATE book SET available_copies = available_copies - 1 WHERE book_id = ?");
                    $stmt->execute([$book_id]);

                    $conn->commit();
                    
                    $reservation_success = "Book reserved successfully! Transaction ID: #$transaction_id. Visit the library to collect it within 3 days.";
                }
            }
        }
    } catch(PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $reservation_error = "Reservation failed: " . $e->getMessage();
    }
}

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

$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';

$query = "SELECT * FROM book WHERE status = 'Available' AND available_copies > 0";
$params = [];

if ($category_filter) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}

if ($search_query) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY title";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

$total_books = count($books);
$available_categories = count($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Library System</title>
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
        
        .stat-card.books {
            border-left-color: var(--primary);
        }
        
        .stat-card.categories {
            border-left-color: var(--success);
        }
        
        .stat-card.available {
            border-left-color: var(--warning);
        }
        
        .stat-card.borrowed {
            border-left-color: var(--danger);
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
        
        .stat-card.books .stat-icon {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }
        
        .stat-card.categories .stat-icon {
            background: linear-gradient(135deg, #34d399 0%, var(--success) 100%);
            color: white;
        }
        
        .stat-card.available .stat-icon {
            background: linear-gradient(135deg, #fbbf24 0%, var(--warning) 100%);
            color: white;
        }
        
        .stat-card.borrowed .stat-icon {
            background: linear-gradient(135deg, #f87171 0%, var(--danger) 100%);
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
        
        .search-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
        
        .book-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .book-cover {
            height: 200px;
            object-fit: cover;
            width: 100%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .book-card-body {
            padding: 1.25rem;
        }
        
        .book-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 48px;
        }
        
        .book-author {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        
        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.8rem;
        }
        
        .book-copies {
            color: var(--success);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .view-details-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .view-details-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }
        
        .modal-book-img {
            max-height: 300px;
            object-fit: contain;
            width: 100%;
            border-radius: 8px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            border-radius: 0 0 12px 12px;
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
        
        /* No image placeholder styles */
        .no-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #64748b;
        }
        
        .no-image-placeholder i {
            font-size: 3rem;
        }
        
        .no-image-placeholder.small i {
            font-size: 2rem;
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
                <a href="user_books.php" class="nav-link active">
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
                <h1>Browse Library Books 📚</h1>
                <p>Discover and reserve books from our collection</p>
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
            <div class="stat-card books">
                <div class="stat-icon">
                    <i class="bi bi-bookshelf"></i>
                </div>
                <div class="stat-value"><?php echo $total_books; ?></div>
                <div class="stat-label">Available Books</div>
                <small class="text-muted mt-2 d-block">Ready to borrow</small>
            </div>
            
            <div class="stat-card categories">
                <div class="stat-icon">
                    <i class="bi bi-tags"></i>
                </div>
                <div class="stat-value"><?php echo $available_categories; ?></div>
                <div class="stat-label">Categories</div>
                <small class="text-muted mt-2 d-block">Browse by topic</small>
            </div>
            
            <div class="stat-card available">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">Available</div>
                <div class="stat-label">Status</div>
                <small class="text-success mt-2 d-block">
                    <i class="bi bi-check me-1"></i> All books ready
                </small>
            </div>
            
            <div class="stat-card borrowed">
                <div class="stat-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stat-value"><?php echo $active_borrows; ?>/5</div>
                <div class="stat-label">Your Borrowings</div>
                <small class="text-muted mt-2 d-block">Active books</small>
            </div>
        </div>
        
        <?php if(isset($reservation_success)): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2" style="font-size: 1.2rem;"></i>
                <div><?php echo $reservation_success; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($reservation_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.2rem;"></i>
                <div><?php echo $reservation_error; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Search and Filter -->
        <div class="search-card mb-4">
            <h5 class="mb-3">Find Your Next Read</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" name="search" 
                               placeholder="Search by title, author, or ISBN..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <?php if($cat['category']): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                    <?php echo ($category_filter == $cat['category']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                </div>
            </form>
            
            <?php if($category_filter || $search_query): ?>
                <div class="mt-3">
                    <small class="text-muted">
                        Filtered by: 
                        <?php if($category_filter): ?>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($category_filter); ?></span>
                        <?php endif; ?>
                        <?php if($search_query): ?>
                            <span class="badge bg-secondary">Search: <?php echo htmlspecialchars($search_query); ?></span>
                        <?php endif; ?>
                        <a href="user_books.php" class="text-decoration-none ms-2">
                            <i class="bi bi-x-circle"></i> Clear filters
                        </a>
                    </small>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Books Grid -->
        <div class="row">
            <?php if(count($books) > 0): ?>
                <?php foreach($books as $book): 
                    
                    $borrow_date = date('Y-m-d');
                    $due_date = date('Y-m-d', strtotime('+14 days'));
                ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="book-card">
                        <?php if($book['image_filename'] && file_exists('uploads/books/' . $book['image_filename'])): ?>
                            <img src="uploads/books/<?php echo htmlspecialchars($book['image_filename']); ?>" 
                                 class="book-cover" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 onerror="this.onerror=null; this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                            <div class="book-cover no-image-placeholder d-none">
                                <i class="bi bi-book text-muted"></i>
                            </div>
                        <?php else: ?>
                            <div class="book-cover no-image-placeholder">
                                <i class="bi bi-book text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="book-card-body">
                            <h6 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            
                            <div class="book-meta">
                                <?php if($book['category']): ?>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($book['category']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Category</span>
                                <?php endif; ?>
                                <span class="book-copies">
                                    <i class="bi bi-check-circle me-1"></i><?php echo $book['available_copies']; ?> available
                                </span>
                            </div>
                            
                            <button type="button" class="view-details-btn" 
                                    data-book-id="<?php echo $book['book_id']; ?>"
                                    data-book-title="<?php echo htmlspecialchars($book['title']); ?>"
                                    data-book-author="<?php echo htmlspecialchars($book['author']); ?>"
                                    data-book-category="<?php echo htmlspecialchars($book['category']); ?>"
                                    data-book-isbn="<?php echo htmlspecialchars($book['isbn']); ?>"
                                    data-book-publisher="<?php echo htmlspecialchars($book['publisher']); ?>"
                                    data-book-year="<?php echo $book['publication_year']; ?>"
                                    data-book-edition="<?php echo htmlspecialchars($book['edition']); ?>"
                                    data-book-description="<?php echo htmlspecialchars($book['description']); ?>"
                                    data-book-image="<?php echo htmlspecialchars($book['image_filename']); ?>"
                                    data-book-copies="<?php echo $book['available_copies']; ?>"
                                    data-borrow-date="<?php echo $borrow_date; ?>"
                                    data-due-date="<?php echo $due_date; ?>">
                                <i class="bi bi-eye me-2"></i> View & Reserve
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-book" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <h4 class="mb-3">No Books Found</h4>
                        <p class="mb-4 text-muted">We couldn't find any books matching your search criteria.</p>
                        <a href="user_books.php" class="btn btn-primary">
                            <i class="bi bi-arrow-counterclockwise me-2"></i> Clear Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if(count($books) > 0): ?>
            <div class="text-center mt-4">
                <p class="text-dark">
                    Showing <?php echo count($books); ?> book(s) 
                    <?php if($category_filter): ?>in <?php echo htmlspecialchars($category_filter); ?><?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Book Details Modal -->
    <div class="modal fade" id="bookDetailsModal" tabindex="-1" aria-labelledby="bookDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookDetailsModalLabel">
                        <i class="bi bi-book me-2"></i>Book Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="modalCloseBtn"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Book Cover -->
                        <div class="col-md-4 text-center">
                            <div id="modalImageContainer">
                                <img id="modalBookImage" src="" class="modal-book-img mb-3 rounded shadow-sm" 
                                     alt="Book Cover"
                                     onerror="this.onerror=null; this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                <div id="modalNoImage" class="modal-book-img no-image-placeholder d-none">
                                    <i class="bi bi-book text-muted"></i>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Available Copies:</span>
                                        <span id="modalAvailableCopies" class="badge bg-success"></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge bg-success">Available for Reservation</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Book Details -->
                        <div class="col-md-8">
                            <h4 id="modalBookTitle" class="mb-2"></h4>
                            <p class="text-muted mb-3">
                                <i class="bi bi-person me-1"></i>by <span id="modalBookAuthor"></span>
                            </p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body py-2">
                                            <small class="text-muted">Category</small>
                                            <div id="modalBookCategory" class="fw-bold">N/A</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body py-2">
                                            <small class="text-muted">ISBN</small>
                                            <div id="modalBookISBN" class="fw-bold">N/A</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body py-2">
                                            <small class="text-muted">Publisher</small>
                                            <div id="modalBookPublisher" class="fw-bold">N/A</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body py-2">
                                            <small class="text-muted">Publication Year</small>
                                            <div id="modalBookYear" class="fw-bold">N/A</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body py-2">
                                        <small class="text-muted">Edition</small>
                                        <div id="modalBookEdition" class="fw-bold">N/A</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong class="d-block mb-2">Description:</strong>
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <p id="modalBookDescription" class="mb-0"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reservation Info -->
                            <div class="alert alert-info border-0" style="background: #f0f9ff;">
                                <h6 class="d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2"></i>Reservation Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Borrow Date</small>
                                        <div id="modalBorrowDate" class="fw-bold"></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Due Date</small>
                                        <div id="modalDueDate" class="fw-bold text-primary"></div>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-clock me-1"></i> Books must be picked up within 3 days of reservation.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="modalCancelBtn">Cancel</button>
                    
                    <!-- Reserve Form -->
                    <form method="POST" id="reserveForm">
                        <input type="hidden" name="book_id" id="modalBookId">
                        <button type="submit" name="confirm_reserve_book" class="btn btn-primary">
                            <i class="bi bi-bookmark-check me-2"></i> Confirm Reservation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Generate a simple SVG placeholder for book covers
    function generateBookPlaceholder(title = '', author = '') {
        const initials = title ? title.charAt(0).toUpperCase() : 'B';
        const svg = `
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#4f46e5;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#818cf8;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect width="100%" height="100%" fill="url(#gradient)"/>
                <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="white" font-family="Arial, sans-serif" font-size="48" font-weight="bold">
                    ${initials}
                </text>
                <text x="50%" y="85%" text-anchor="middle" fill="rgba(255,255,255,0.8)" font-family="Arial, sans-serif" font-size="14">
                    ${author ? author.substring(0, 15) + (author.length > 15 ? '...' : '') : 'Book Cover'}
                </text>
            </svg>`;
        return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Get all view details buttons
        const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
        const bookDetailsModal = new bootstrap.Modal(document.getElementById('bookDetailsModal'));
        
        // Variables to track current modal state
        let shouldRefreshOnClose = false;
        
        // Add click event to each button
        viewDetailsButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Reset refresh flag
                shouldRefreshOnClose = false;
                
                // Get data attributes
                const bookId = this.getAttribute('data-book-id');
                const title = this.getAttribute('data-book-title');
                const author = this.getAttribute('data-book-author');
                const category = this.getAttribute('data-book-category');
                const isbn = this.getAttribute('data-book-isbn');
                const publisher = this.getAttribute('data-book-publisher');
                const year = this.getAttribute('data-book-year');
                const edition = this.getAttribute('data-book-edition');
                const description = this.getAttribute('data-book-description');
                const image = this.getAttribute('data-book-image');
                const copies = this.getAttribute('data-book-copies');
                const borrowDate = this.getAttribute('data-borrow-date');
                const dueDate = this.getAttribute('data-due-date');
                
                // Set modal content
                document.getElementById('modalBookId').value = bookId;
                document.getElementById('modalBookTitle').textContent = title;
                document.getElementById('modalBookAuthor').textContent = author;
                document.getElementById('modalBookCategory').textContent = category || 'N/A';
                document.getElementById('modalBookISBN').textContent = isbn || 'N/A';
                document.getElementById('modalBookPublisher').textContent = publisher || 'N/A';
                document.getElementById('modalBookYear').textContent = year || 'N/A';
                document.getElementById('modalBookEdition').textContent = edition || 'N/A';
                document.getElementById('modalBookDescription').textContent = description || 'No description available.';
                document.getElementById('modalAvailableCopies').textContent = copies;
                
                // Set image
                const imgElement = document.getElementById('modalBookImage');
                const noImageElement = document.getElementById('modalNoImage');
                
                // Show image or placeholder
                if (image && image.trim() !== '') {
                    imgElement.classList.remove('d-none');
                    noImageElement.classList.add('d-none');
                    imgElement.src = 'uploads/books/' + image;
                } else {
                    imgElement.classList.add('d-none');
                    noImageElement.classList.remove('d-none');
                }
                
                // Format and set dates
                const formatDate = (dateString) => {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                };
                
                document.getElementById('modalBorrowDate').textContent = formatDate(borrowDate);
                document.getElementById('modalDueDate').textContent = formatDate(dueDate);
                
                // Update modal title
                document.getElementById('bookDetailsModalLabel').innerHTML = 
                    `<i class="bi bi-book me-2"></i>${title}`;
                
                // Show modal
                bookDetailsModal.show();
            });
        });
        
        // Handle reserve form submission
        document.getElementById('reserveForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission until confirmed
            
            const copies = parseInt(document.getElementById('modalAvailableCopies').textContent);
            const title = document.getElementById('modalBookTitle').textContent;
            const borrowDate = document.getElementById('modalBorrowDate').textContent;
            const dueDate = document.getElementById('modalDueDate').textContent;
            
            if (copies <= 0) {
                alert("Sorry, this book is no longer available for reservation.");
                return;
            }
            
            // Create confirmation modal HTML
            const confirmModalHTML = `
                <div class="modal fade" id="confirmModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-primary">
                                    <i class="bi bi-bookmark-check me-2"></i>Confirm Reservation
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" id="confirmModalCloseBtn"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="bi bi-question-circle text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="text-center mb-3">${title}</h6>
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Borrow Date</small>
                                        <div class="fw-bold">${borrowDate}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Due Date</small>
                                        <div class="fw-bold text-primary">${dueDate}</div>
                                    </div>
                                </div>
                                <div class="alert alert-info" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Books must be picked up within 3 days of reservation.
                                </div>
                                <p class="text-center mb-0">Are you sure you want to reserve this book?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" id="confirmCancelBtn" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-primary" id="confirmReserveBtn">
                                    <i class="bi bi-check-circle me-2"></i>Yes, Reserve
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            
            // Remove existing modal if exists
            const existingModal = document.getElementById('confirmModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', confirmModalHTML);
            
            // Show confirmation modal
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            confirmModal.show();
            
            // Handle confirm button click
            document.addEventListener('click', function confirmReserveHandler(e) {
                if (e.target && e.target.id === 'confirmReserveBtn') {
                    // Submit the original form
                    const originalForm = document.getElementById('reserveForm');
                    
                    // Create a new form element to submit
                    const tempForm = document.createElement('form');
                    tempForm.method = 'POST';
                    tempForm.style.display = 'none';
                    
                    const bookIdInput = document.createElement('input');
                    bookIdInput.type = 'hidden';
                    bookIdInput.name = 'book_id';
                    bookIdInput.value = document.getElementById('modalBookId').value;
                    
                    const submitInput = document.createElement('input');
                    submitInput.type = 'hidden';
                    submitInput.name = 'confirm_reserve_book';
                    submitInput.value = '1';
                    
                    tempForm.appendChild(bookIdInput);
                    tempForm.appendChild(submitInput);
                    document.body.appendChild(tempForm);
                    tempForm.submit();
                    
                    // Remove event listener
                    document.removeEventListener('click', confirmReserveHandler);
                }
            });
            
            // Handle confirm modal cancel button click
            document.addEventListener('click', function confirmCancelHandler(e) {
                if (e.target && (e.target.id === 'confirmCancelBtn' || e.target.id === 'confirmModalCloseBtn')) {
                    // Set flag to refresh on close
                    shouldRefreshOnClose = true;
                    
                    // Close all modals and refresh page
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                    
                    // Remove event listener
                    document.removeEventListener('click', confirmCancelHandler);
                }
            });
            
            // Handle confirm modal hidden event (when closed via backdrop click or ESC)
            const confirmModalElement = document.getElementById('confirmModal');
            if (confirmModalElement) {
                confirmModalElement.addEventListener('hidden.bs.modal', function () {
                    if (shouldRefreshOnClose) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 100);
                    }
                });
            }
        });
        
        // Handle main modal cancel button
        document.getElementById('modalCancelBtn').addEventListener('click', function() {
            shouldRefreshOnClose = true;
            setTimeout(() => {
                window.location.reload();
            }, 100);
        });
        
        // Handle main modal close button (X)
        document.getElementById('modalCloseBtn').addEventListener('click', function() {
            shouldRefreshOnClose = true;
            setTimeout(() => {
                window.location.reload();
            }, 100);
        });
        
        // Handle main modal hidden event (when closed via backdrop click or ESC)
        const bookDetailsModalElement = document.getElementById('bookDetailsModal');
        if (bookDetailsModalElement) {
            bookDetailsModalElement.addEventListener('hidden.bs.modal', function () {
                if (shouldRefreshOnClose) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                }
            });
        }

        // Add animation to book cards
        const bookCards = document.querySelectorAll('.book-card');
        bookCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
        
        // Fix broken images on page load
        document.querySelectorAll('img[src*="via.placeholder.com"]').forEach(img => {
            img.style.display = 'none';
            if (img.parentElement) {
                const placeholder = document.createElement('div');
                placeholder.className = 'no-image-placeholder';
                placeholder.innerHTML = '<i class="bi bi-book text-muted"></i>';
                img.parentElement.appendChild(placeholder);
            }
        });
    });
    </script>
</body>
</html>