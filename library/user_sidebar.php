<?php

?>
<div class="col-md-3 col-lg-2 px-0">
    <div class="sidebar bg-dark text-white min-vh-100">
        <div class="p-3">
            <h5 class="text-center"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h5>
            <p class="text-center text-muted small"><?php echo htmlspecialchars($_SESSION['institutional_id']); ?></p>
        </div>
        
        <nav class="nav flex-column p-3">
            <a href="user_dashboard.php" class="nav-link text-white">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="user_books.php" class="nav-link text-white">
                <i class="bi bi-book me-2"></i> Browse Books
            </a>
            <a href="user_borrowed.php" class="nav-link text-white">
                <i class="bi bi-bag-check me-2"></i> My Borrowings
            </a>
            <a href="user_history.php" class="nav-link text-white">
                <i class="bi bi-clock-history me-2"></i> Borrowing History
            </a>
            <a href="user_profile.php" class="nav-link text-white">
                <i class="bi bi-person me-2"></i> My Profile
            </a>
            <hr class="text-white">
            <a href="user_logout.php" class="nav-link text-white">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>
</div>