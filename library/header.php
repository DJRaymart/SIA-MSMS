<?php

if (!isset($_SESSION['librarian_id'])) {
    
    header("Location: login.php");
    exit();
}

$librarian_id = $_SESSION['librarian_id'];
$librarian_name = $_SESSION['full_name'];
$librarian_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        /* Your CSS styles here */
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; text-decoration: none; }
        .sidebar a:hover { background-color: #495057; text-decoration: none; }
        .sidebar .active { background-color: #007bff; }
        .main-content { padding: 20px; }
    </style>
</head>
<body style="background-image: url('bg.jpg'); background-repeat: no-repeat;
  background-size: cover;
  background-position: center;">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-sticky">
                    <h4 class="text-white text-center mb-4">
                        <i class="bi bi-book"></i> Library Admin
                    </h4>
                    <hr class="bg-light">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>" href="books.php">
                                <i class="bi bi-book"></i> Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>" href="transactions.php">
                                <i class="bi bi-arrow-left-right"></i> Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'log_book.php' ? 'active' : ''; ?>" href="log_book.php">
                                <i class="bi bi-arrow-left-right"></i> Log Book
                            </a>
                        </li>
                        <?php if ($librarian_role == 'Head'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'librarians.php' ? 'active' : ''; ?>" href="librarians.php">
                                <i class="bi bi-person-badge"></i> Librarians
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto col-lg-10 px-4 main-content">
                <nav class="navbar navbar-light bg-light mb-4" style="border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.1);">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">
                            <?php 
                            $page = basename($_SERVER['PHP_SELF']);
                            $titles = [
                                'index.php' => 'Dashboard',
                                'books.php' => 'Book Management',
                                'users.php' => 'Borrowers Management',
                                'log_book.php' => 'Transaction Management',
                                'transactions.php' => 'Transaction Management',
                                'librarians.php' => 'Librarian Management'
                            ];
                            echo $titles[$page] ?? 'Library Management';
                            ?>
                        </span>
                        <div class="d-flex align-items-center">
                            <span class="me-3">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo $librarian_name; ?> 
                                <span class="badge bg-primary"><?php echo $librarian_role; ?></span>
                            </span>
                        </div>
                    </div>
                </nav>