<?php
require_once 'db.php';

if (isset($_GET['check_user_ajax']) || isset($_POST['check_user_ajax'])) {
    $institutional_id = trim($_POST['institutional_id'] ?? $_GET['institutional_id'] ?? '');
    header('Content-Type: application/json');
    if (empty($institutional_id)) {
        echo json_encode(['success' => false, 'error' => 'Please enter a Student / Employee ID']);
        exit;
    }
    try {
        $user_sql = "SELECT user_id, institutional_id, full_name, status FROM tbl_users WHERE institutional_id = :institutional_id";
        $stmt = $conn->prepare($user_sql);
        $stmt->execute([':institutional_id' => $institutional_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'User not found with Student / Employee ID: ' . htmlspecialchars($institutional_id)]);
            exit;
        }
        if ($user['status'] != 'Active') {
            echo json_encode(['success' => false, 'error' => 'User account is not active. Status: ' . htmlspecialchars($user['status'])]);
            exit;
        }
        $borrow_sql = "SELECT COUNT(*) as active_borrows FROM borrowing_transaction WHERE user_id = :user_id AND return_date IS NULL AND status IN ('Borrowed', 'Reserved')";
        $bstmt = $conn->prepare($borrow_sql);
        $bstmt->execute([':user_id' => $user['user_id']]);
        $active = $bstmt->fetch(PDO::FETCH_ASSOC);
        $user['active_borrows'] = (int)($active['active_borrows'] ?? 0);
        if ($user['active_borrows'] >= 5) {
            echo json_encode(['success' => false, 'error' => 'User has reached the borrowing limit of 5 books.']);
            exit;
        }
        echo json_encode(['success' => true, 'user' => $user]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['search_books_ajax'])) {
    $search = trim($_GET['q'] ?? '');
    header('Content-Type: application/json; charset=utf-8');
    if (strlen($search) < 1) {
        echo json_encode(['results' => []]);
        exit;
    }
    try {
        $term = '%' . $search . '%';
        $isbn_digits = preg_replace('/[\s\-]/', '', $search);
        $has_isbn_digits = strlen($isbn_digits) >= 2;
        $statusCond = " AND (status IS NULL OR status NOT IN ('Lost', 'Damaged'))";
        $sql = "SELECT book_id, title, author, isbn, available_copies, number_of_copies FROM book 
                WHERE (title LIKE :t OR author LIKE :t OR isbn LIKE :t)";
        $params = [':t' => $term];
        if ($has_isbn_digits) {
            $sql .= " OR REPLACE(REPLACE(COALESCE(isbn,''), '-', ''), ' ', '') LIKE :isbn";
            $params[':isbn'] = '%' . $isbn_digits . '%';
        }
        $sql .= $statusCond . " ORDER BY available_copies DESC, title LIMIT 15";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($results) && $has_isbn_digits) {
            $stmt2 = $conn->prepare("SELECT book_id, title, author, isbn, available_copies, number_of_copies FROM book WHERE (isbn LIKE :t OR REPLACE(REPLACE(COALESCE(isbn,''), '-', ''), ' ', '') LIKE :isbn) " . trim($statusCond) . " ORDER BY title LIMIT 15");
            $stmt2->execute([':t' => $term, ':isbn' => '%' . $isbn_digits . '%']);
            $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode(['results' => $results]);
    } catch (PDOException $e) {
        echo json_encode(['results' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

require_once 'header_unified.php';

$action = $_GET['action'] ?? 'list';
$alert_message = '';
$alert_type = ''; 

if ($action == 'borrow') {
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_submit'])) {
        $book_id = $_POST['book_id'];
        $institutional_id = trim($_POST['institutional_id']);
        $borrow_days = intval($_POST['borrow_days']);
        $librarian_id = $_SESSION['librarian_id'] ?? 1; 
        
        try {
            $conn->beginTransaction();

            $user_sql = "SELECT user_id, full_name, status FROM tbl_users 
                        WHERE institutional_id = :institutional_id";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->execute([':institutional_id' => $institutional_id]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('User not found with Student / Employee ID: ' . htmlspecialchars($institutional_id));
            }
            
            if ($user['status'] != 'Active') {
                throw new Exception('User account is not active. Status: ' . htmlspecialchars($user['status']));
            }

            $book_sql = "SELECT book_id, title, available_copies FROM book 
                        WHERE book_id = :book_id 
                        AND available_copies > 0";
            $book_stmt = $conn->prepare($book_sql);
            $book_stmt->execute([':book_id' => $book_id]);
            $book = $book_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$book) {
                throw new Exception('Book not found or no available copies!');
            }

            $borrowing_limit = 5; 
            $current_borrows_sql = "SELECT COUNT(*) as active_borrows 
                                   FROM borrowing_transaction 
                                   WHERE user_id = :user_id 
                                   AND return_date IS NULL 
                                   AND status IN ('Borrowed', 'Reserved')";
            $current_borrows_stmt = $conn->prepare($current_borrows_sql);
            $current_borrows_stmt->execute([':user_id' => $user['user_id']]);
            $active_borrows = $current_borrows_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($active_borrows['active_borrows'] >= $borrowing_limit) {
                throw new Exception('User has reached the borrowing limit of ' . $borrowing_limit . ' books.');
            }

            $existing_borrow_sql = "SELECT transaction_id FROM borrowing_transaction 
                                   WHERE user_id = :user_id 
                                   AND book_id = :book_id 
                                   AND return_date IS NULL";
            $existing_borrow_stmt = $conn->prepare($existing_borrow_sql);
            $existing_borrow_stmt->execute([
                ':user_id' => $user['user_id'],
                ':book_id' => $book_id
            ]);
            
            if ($existing_borrow_stmt->fetch()) {
                throw new Exception('User already has this book borrowed!');
            }

            $borrow_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime("+$borrow_days days"));

            $transaction_sql = "INSERT INTO borrowing_transaction 
                               (book_id, user_id, librarian_id, borrow_date, due_date, status) 
                               VALUES (:book_id, :user_id, :librarian_id, :borrow_date, :due_date, 'Borrowed')";
            
            $transaction_stmt = $conn->prepare($transaction_sql);
            $transaction_stmt->execute([
                ':book_id' => $book_id,
                ':user_id' => $user['user_id'],
                ':librarian_id' => $librarian_id,
                ':borrow_date' => $borrow_date,
                ':due_date' => $due_date
            ]);
            
            $transaction_id = $conn->lastInsertId();

            $update_book_sql = "UPDATE book SET 
                               available_copies = available_copies - 1,
                               status = CASE WHEN (available_copies - 1) > 0 THEN 'Available' ELSE 'Borrowed' END
                               WHERE book_id = :book_id";
            
            $update_book_stmt = $conn->prepare($update_book_sql);
            $update_book_stmt->execute([':book_id' => $book_id]);
            
            $conn->commit();

            $alert_message = 'Book borrowed successfully!<br>';
            $alert_message .= '<strong>Book:</strong> ' . htmlspecialchars($book['title']) . '<br>';
            $alert_message .= '<strong>User:</strong> ' . htmlspecialchars($user['full_name']) . '<br>';
            $alert_message .= '<strong>Borrow Date:</strong> ' . $borrow_date . '<br>';
            $alert_message .= '<strong>Due Date:</strong> ' . $due_date . ' (' . $borrow_days . ' days)<br>';
            $alert_message .= '<strong>Transaction ID:</strong> ' . $transaction_id;
            $alert_type = 'success';

            $_POST = array(); 
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }

    $user_info = null;
    $user_error = '';
    $user_checked = false;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_user'])) {
        $institutional_id = trim($_POST['institutional_id'] ?? '');
        
        if (empty($institutional_id)) {
            $user_error = 'Please enter an Student / Employee ID';
        } else {
            try {
                $user_sql = "SELECT user_id, institutional_id, full_name, status FROM tbl_users 
                            WHERE institutional_id = :institutional_id";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->execute([':institutional_id' => $institutional_id]);
                $user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user_info) {
                    $user_error = 'User not found with Student / Employee ID: ' . htmlspecialchars($institutional_id);
                } else {
                    $user_checked = true;

                    $borrow_sql = "SELECT COUNT(*) as active_borrows 
                                 FROM borrowing_transaction 
                                 WHERE user_id = :user_id 
                                 AND return_date IS NULL 
                                 AND status IN ('Borrowed', 'Reserved')";
                    $borrow_stmt = $conn->prepare($borrow_sql);
                    $borrow_stmt->execute([':user_id' => $user_info['user_id']]);
                    $active_borrows = $borrow_stmt->fetch(PDO::FETCH_ASSOC);
                    $user_info['active_borrows'] = $active_borrows['active_borrows'];

                    if ($user_info['status'] != 'Active') {
                        $user_error = 'User account is not active. Status: ' . htmlspecialchars($user_info['status']);
                    } elseif ($user_info['active_borrows'] >= 5) {
                        $user_error = 'User has reached the borrowing limit of 5 books.';
                    }
                }
            } catch (PDOException $e) {
                $user_error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    $search_results = [];
    $search_query = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_book'])) {
        $search_query = trim($_POST['book_search'] ?? '');
        
        if (strlen($search_query) >= 2) {
            try {
                $search_term = '%' . $search_query . '%';
                $sql = "SELECT book_id, title, author, isbn, number_of_copies, available_copies, shelf_location
                        FROM book 
                        WHERE (title LIKE :search OR author LIKE :search OR isbn LIKE :search)
                        AND status != 'Lost' 
                        AND status != 'Damaged'
                        ORDER BY title
                        LIMIT 10";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([':search' => $search_term]);
                $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                
            }
        }
    }

    $selected_book = null;
    $book_id_from_url = $_GET['book_id'] ?? null;
    $book_id_from_post = $_POST['book_id'] ?? null;
    $selected_book_id = $book_id_from_post ?: $book_id_from_url;
    
    if ($selected_book_id) {
        try {
            $sql = "SELECT book_id, title, author, isbn, number_of_copies, available_copies, shelf_location
                    FROM book WHERE book_id = :book_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':book_id' => $selected_book_id]);
            $selected_book = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            
        }
    }
}

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];
    $publication_year = $_POST['publication_year'];
    $edition = $_POST['edition'];
    $number_of_copies = $_POST['number_of_copies'];
    $available_copies = $number_of_copies; 
    $shelf_location = $_POST['shelf_location'];
    $description = $_POST['description'] ?? '';
    $status = ($available_copies > 0) ? 'Available' : 'Borrowed';

    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';

    $image_filename = null;
    if (!$remove_image && isset($_FILES['book_image']) && $_FILES['book_image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['book_image']['type'];
        $file_size = $_FILES['book_image']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) { 
            $extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid('book_', true) . '.' . $extension;
            $upload_dir = 'uploads/books/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $target_file = $upload_dir . $image_filename;
            
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_file)) {
                
            } else {
                $image_filename = null;
            }
        }
    }
    
    try {
        $sql = "INSERT INTO book (title, author, isbn, category, publisher, publication_year, edition, 
                number_of_copies, available_copies, shelf_location, status, description, image_filename) 
                VALUES (:title, :author, :isbn, :category, :publisher, :publication_year, :edition, 
                :number_of_copies, :available_copies, :shelf_location, :status, :description, :image_filename)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn,
            ':category' => $category,
            ':publisher' => $publisher,
            ':publication_year' => $publication_year,
            ':edition' => $edition,
            ':number_of_copies' => $number_of_copies,
            ':available_copies' => $available_copies,
            ':shelf_location' => $shelf_location,
            ':status' => $status,
            ':description' => $description,
            ':image_filename' => $image_filename
        ]);
        
        $alert_message = 'Book added successfully!';
        $alert_type = 'success';
        $action = 'list'; 
    } catch (PDOException $e) {
        $alert_message = 'Error adding book: ' . $e->getMessage();
        $alert_type = 'danger';
    }
}

if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $publisher = $_POST['publisher'];
    $publication_year = $_POST['publication_year'];
    $edition = $_POST['edition'];
    $new_number_of_copies = $_POST['number_of_copies'];
    $shelf_location = $_POST['shelf_location'];
    $status = $_POST['status'];
    $description = $_POST['description'] ?? '';
    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';

    $image_filename = $_POST['current_image'] ?? null;
    
    if ($remove_image) {
        
        if (!empty($image_filename)) {
            $old_image = 'uploads/books/' . $image_filename;
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }
        $image_filename = null;
    } elseif (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['book_image']['type'];
        $file_size = $_FILES['book_image']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) { 
            $extension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid('book_', true) . '.' . $extension;
            $upload_dir = 'uploads/books/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $target_file = $upload_dir . $image_filename;
            
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $target_file)) {
                
                if (!empty($_POST['current_image'])) {
                    $old_image = 'uploads/books/' . $_POST['current_image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
            } else {
                $image_filename = $_POST['current_image'] ?? null;
            }
        }
    }
    
    try {
        
        $sql = "SELECT number_of_copies, available_copies FROM book WHERE book_id = :book_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':book_id' => $book_id]);
        $current_book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current_book) {
            throw new Exception('Book not found!');
        }
        
        $current_number_of_copies = $current_book['number_of_copies'];
        $current_available_copies = $current_book['available_copies'];

        if ($new_number_of_copies > $current_number_of_copies) {
            
            $difference = $new_number_of_copies - $current_number_of_copies;
            $new_available_copies = $current_available_copies + $difference;
        } elseif ($new_number_of_copies < $current_number_of_copies) {

            $difference = $current_number_of_copies - $new_number_of_copies;
            $new_available_copies = max(0, $current_available_copies - $difference);
        } else {
            
            $new_available_copies = $current_available_copies;
        }

        $auto_status = ($new_available_copies > 0) ? 'Available' : 'Borrowed';
        
        $final_status = ($status == 'Lost' || $status == 'Damaged') ? $status : $auto_status;

        $sql = "UPDATE book SET 
                title = :title,
                author = :author,
                isbn = :isbn,
                category = :category,
                publisher = :publisher,
                publication_year = :publication_year,
                edition = :edition,
                number_of_copies = :number_of_copies,
                available_copies = :available_copies,
                shelf_location = :shelf_location,
                status = :status,
                description = :description,
                image_filename = :image_filename
                WHERE book_id = :book_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn,
            ':category' => $category,
            ':publisher' => $publisher,
            ':publication_year' => $publication_year,
            ':edition' => $edition,
            ':number_of_copies' => $new_number_of_copies,
            ':available_copies' => $new_available_copies,
            ':shelf_location' => $shelf_location,
            ':status' => $final_status,
            ':description' => $description,
            ':image_filename' => $image_filename,
            ':book_id' => $book_id
        ]);
        
        $alert_message = 'Book updated successfully!';
        $alert_type = 'success';
        $action = 'list'; 
    } catch (PDOException $e) {
        $alert_message = 'Error updating book: ' . $e->getMessage();
        $alert_type = 'danger';
    } catch (Exception $e) {
        $alert_message = 'Error: ' . $e->getMessage();
        $alert_type = 'danger';
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $book_id = $_GET['id'];
    
    try {
        
        $check_sql = "SELECT title, available_copies, number_of_copies FROM book WHERE book_id = :book_id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([':book_id' => $book_id]);
        $book = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($book) {
            
            if ($book['available_copies'] < $book['number_of_copies']) {
                $alert_message = 'Cannot delete "' . htmlspecialchars($book['title']) . '"! There are borrowed copies. Available: ' . $book['available_copies'] . ' / Total: ' . $book['number_of_copies'];
                $alert_type = 'warning';
            } else {
                
                $sql = "SELECT image_filename FROM book WHERE book_id = :book_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':book_id' => $book_id]);
                $book_image = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($book_image && $book_image['image_filename']) {
                    $image_path = 'uploads/books/' . $book_image['image_filename'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                $sql = "DELETE FROM book WHERE book_id = :book_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':book_id' => $book_id]);
                
                $alert_message = 'Book "' . htmlspecialchars($book['title']) . '" deleted successfully!';
                $alert_type = 'success';
            }
        } else {
            $alert_message = 'Book not found!';
            $alert_type = 'danger';
        }

        $action = 'list';
    } catch (PDOException $e) {
        $alert_message = 'Error deleting book: ' . $e->getMessage();
        $alert_type = 'danger';
        $action = 'list';
    }
}
?>

<div class="container-fluid">
    <?php if ($alert_message): ?>
        <div class="mb-6 p-4 rounded-lg border <?php 
            echo $alert_type == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 
            ($alert_type == 'danger' ? 'bg-red-50 border-red-200 text-red-800' : 
            ($alert_type == 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' : 
            'bg-blue-50 border-blue-200 text-blue-800')); 
        ?>">
            <?php if ($alert_type == 'success' && strpos($alert_message, '<br>') !== false): ?>
                <?php echo $alert_message; ?>
            <?php else: ?>
                <?php echo htmlspecialchars($alert_message); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            Book Management
        </h2>
        <div class="flex gap-3">
            <button onclick="openAddBookModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="text-white">Add New Book</span>
            </button>
            <button onclick="openBorrowBookModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                </svg>
                Borrow Book for User
            </button>
        </div>
    </div>

    <?php if ($action == 'borrow'): ?>
        <!-- Borrow Book Form -->
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-arrow-up-circle"></i> Borrow Book for User</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="books.php?action=borrow" id="borrowForm">
                    <div class="row">
                        <!-- User Section -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">User Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="institutional_id" class="form-label">User Student / Employee ID *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="institutional_id" name="institutional_id" 
                                                   placeholder="Enter user's Student / Employee ID" 
                                                   value="<?php echo htmlspecialchars($_POST['institutional_id'] ?? ''); ?>" required>
                                            <button type="submit" name="check_user" class="btn btn-outline-info">
                                                <i class="bi bi-search"></i> Check User
                                            </button>
                                        </div>
                                        <div class="form-text">Enter the user's institutional/student ID</div>
                                    </div>
                                    
                                    <?php if ($user_checked && $user_info): ?>
                                        <div id="userInfoSection">
                                            <div class="alert alert-success">
                                                <h6><i class="bi bi-person-check"></i> User Found</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Name:</strong> <?php echo htmlspecialchars($user_info['full_name']); ?><br>
                                                        <strong>ID:</strong> <?php echo htmlspecialchars($user_info['institutional_id']); ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Status:</strong> 
                                                        <span class="badge bg-<?php echo $user_info['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                            <?php echo htmlspecialchars($user_info['status']); ?>
                                                        </span><br>
                                                        <strong>Active Borrowings:</strong> <?php echo $user_info['active_borrows']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($user_error): ?>
                                        <div class="alert alert-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <?php echo htmlspecialchars($user_error); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Book Section -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Book Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="book_search" class="form-label">Search Book *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="book_search" name="book_search"
                                                   placeholder="Search by title, author, or ISBN"
                                                   value="<?php echo htmlspecialchars($search_query); ?>">
                                            <button type="submit" name="search_book" class="btn btn-outline-primary">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                        </div>
                                        <div class="form-text">Start typing to search for books</div>
                                    </div>
                                    
                                    <?php if (!empty($search_results)): ?>
                                        <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                                            <h6>Search Results:</h6>
                                            <?php foreach ($search_results as $book): ?>
                                                <div class="card mb-2">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                                                <small class="text-muted">
                                                                    <?php echo htmlspecialchars($book['author']); ?> | 
                                                                    ISBN: <?php echo htmlspecialchars($book['isbn']); ?>
                                                                </small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge <?php echo $book['available_copies'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                                    <?php echo $book['available_copies']; ?>/<?php echo $book['number_of_copies']; ?>
                                                                </span><br>
                                                                <button type="button" class="btn btn-sm btn-primary mt-1 select-book-btn" 
                                                                        data-book-id="<?php echo $book['book_id']; ?>"
                                                                        data-book-title="<?php echo htmlspecialchars($book['title']); ?>"
                                                                        data-book-author="<?php echo htmlspecialchars($book['author']); ?>"
                                                                        data-book-isbn="<?php echo htmlspecialchars($book['isbn']); ?>"
                                                                        data-book-available="<?php echo $book['available_copies']; ?>"
                                                                        data-book-total="<?php echo $book['number_of_copies']; ?>"
                                                                        data-book-shelf="<?php echo htmlspecialchars($book['shelf_location']); ?>">
                                                                    Select
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($search_query && empty($search_results)): ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> No books found matching your search.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($selected_book): ?>
                                        <div id="selectedBookSection">
                                            <div class="alert alert-primary">
                                                <h6><i class="bi bi-book"></i> Selected Book</h6>
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <strong>Title:</strong> <?php echo htmlspecialchars($selected_book['title']); ?><br>
                                                        <strong>Author:</strong> <?php echo htmlspecialchars($selected_book['author']); ?><br>
                                                        <strong>ISBN:</strong> <?php echo htmlspecialchars($selected_book['isbn']); ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>Available:</strong> 
                                                        <span class="badge <?php echo $selected_book['available_copies'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?php echo $selected_book['available_copies']; ?>
                                                        </span>/<?php echo $selected_book['number_of_copies']; ?><br>
                                                        <strong>Shelf:</strong> <?php echo htmlspecialchars($selected_book['shelf_location']); ?>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="book_id" value="<?php echo $selected_book['book_id']; ?>">
                                            </div>
                                            
                                            <?php if ($selected_book['available_copies'] <= 0): ?>
                                                <div class="alert alert-danger">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                    This book has no available copies!
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($selected_book_id && !$selected_book): ?>
                                        <div class="alert alert-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Selected book not found or has been removed.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Borrow Details -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">Borrowing Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="borrow_days" class="form-label">Borrowing Period (Days) *</label>
                                        <select class="form-control" id="borrow_days" name="borrow_days" required>
                                            <option value="7" <?php echo ($_POST['borrow_days'] ?? '') == '7' ? 'selected' : ''; ?>>7 days (1 week)</option>
                                            <option value="14" <?php echo ($_POST['borrow_days'] ?? '14') == '14' ? 'selected' : ''; ?>>14 days (2 weeks)</option>
                                            <option value="21" <?php echo ($_POST['borrow_days'] ?? '') == '21' ? 'selected' : ''; ?>>21 days (3 weeks)</option>
                                            <option value="30" <?php echo ($_POST['borrow_days'] ?? '') == '30' ? 'selected' : ''; ?>>30 days (1 month)</option>
                                        </select>
                                        <div class="form-text">Standard borrowing period is 14 days</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Dates</label>
                                        <div class="alert alert-light">
                                            <?php
                                            $borrow_days = $_POST['borrow_days'] ?? 14;
                                            $borrow_date = date('Y-m-d');
                                            $due_date = date('Y-m-d', strtotime("+$borrow_days days"));
                                            ?>
                                            <strong>Borrow Date:</strong> <?php echo $borrow_date; ?><br>
                                            <strong>Due Date:</strong> <?php echo $due_date; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Important:</strong> 
                                <ul class="mb-0">
                                    <li>Maximum borrowing limit: 5 books per user</li>
                                    <li>User must have an active account</li>
                                    <li>Book must be available (not all copies borrowed)</li>
                                    <li>User cannot borrow the same book multiple times</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="books.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" name="borrow_submit" class="btn btn-success" id="borrowSubmitBtn"
                                <?php echo (!$user_info || !$selected_book || $selected_book['available_copies'] <= 0) ? 'disabled' : ''; ?>>
                            <i class="bi bi-check-circle"></i> Confirm Borrowing
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add click events to select book buttons
            document.querySelectorAll('.select-book-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const bookId = this.getAttribute('data-book-id');
                    
                    // Create a hidden form to submit the selection
                    const form = document.getElementById('borrowForm');
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'book_id';
                    hiddenInput.value = bookId;
                    form.appendChild(hiddenInput);
                    
                    // Also keep the institutional ID
                    const userIdInput = document.createElement('input');
                    userIdInput.type = 'hidden';
                    userIdInput.name = 'institutional_id';
                    userIdInput.value = document.getElementById('institutional_id').value;
                    form.appendChild(userIdInput);
                    
                    // Submit the form
                    form.submit();
                });
            });
            
            // Update due date when borrow days change
            document.getElementById('borrow_days').addEventListener('change', function() {
                // This will happen on form submission
            });
        });
        </script>
        
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <!-- Add/Edit Book Form -->
        <div class="card">
            <div class="card-header">
                <h4><?php echo $action == 'add' ? 'Add New Book' : 'Edit Book'; ?></h4>
            </div>
            <div class="card-body">
                <?php
                $book = null;
                if ($action == 'edit' && isset($_GET['id'])) {
                    $book_id = $_GET['id'];
                    
                    try {
                        $sql = "SELECT * FROM book WHERE book_id = :book_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([':book_id' => $book_id]);
                        $book = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$book) {
                            $alert_message = 'Book not found!';
                            $alert_type = 'danger';
                            $action = 'list';
                        }
                    } catch (PDOException $e) {
                        $alert_message = 'Error fetching book: ' . $e->getMessage();
                        $alert_type = 'danger';
                        $action = 'list';
                    }
                }

                if ($action == 'list') {
                    
                } else {
                ?>
                <form method="POST" action="books.php?action=<?php echo $action; ?>" enctype="multipart/form-data" id="bookForm">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book['book_id'] ?? ''); ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($book['image_filename'] ?? ''); ?>">
                        
                        <?php if (isset($book['available_copies']) && $book['available_copies'] < $book['number_of_copies']): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Note:</strong> This book has borrowed copies. 
                                Currently borrowed: <?php echo ($book['number_of_copies'] - $book['available_copies']); ?> copy/copies.
                                Available: <?php echo $book['available_copies']; ?>/<?php echo $book['number_of_copies']; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($book['title'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="author" class="form-label">Author *</label>
                                        <input type="text" class="form-control" id="author" name="author" 
                                               value="<?php echo htmlspecialchars($book['author'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" 
                                               value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <input type="text" class="form-control" id="category" name="category" 
                                               value="<?php echo htmlspecialchars($book['category'] ?? ''); ?>"
                                               placeholder="e.g., Fiction, Science, History, Biography">
                                        <div class="form-text">
                                            Examples: Fiction, Non-Fiction, Science, Technology, History, Biography, Reference
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="publisher" class="form-label">Publisher</label>
                                        <input type="text" class="form-control" id="publisher" name="publisher" 
                                               value="<?php echo htmlspecialchars($book['publisher'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="publication_year" class="form-label">Publication Year</label>
                                        <input type="number" class="form-control" id="publication_year" name="publication_year" 
                                               min="1900" max="<?php echo date('Y'); ?>" 
                                               value="<?php echo htmlspecialchars($book['publication_year'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edition" class="form-label">Edition</label>
                                        <input type="text" class="form-control" id="edition" name="edition" 
                                               value="<?php echo htmlspecialchars($book['edition'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="number_of_copies" class="form-label">Number of Copies *</label>
                                        <input type="number" class="form-control" id="number_of_copies" name="number_of_copies" 
                                               min="1" value="<?php echo htmlspecialchars($book['number_of_copies'] ?? '1'); ?>" required>
                                        <?php if ($action == 'edit'): ?>
                                            <div class="form-text">
                                                Current available: <?php echo htmlspecialchars($book['available_copies'] ?? '0'); ?> copy/copies
                                            </div>
                                        <?php else: ?>
                                            <div class="form-text">
                                                Initially, all copies will be available for borrowing
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="shelf_location" class="form-label">Shelf Location</label>
                                        <input type="text" class="form-control" id="shelf_location" name="shelf_location" 
                                               value="<?php echo htmlspecialchars($book['shelf_location'] ?? ''); ?>">
                                    </div>
                                </div>
                                <?php if ($action == 'edit'): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="Available" <?php echo ($book['status'] ?? '') == 'Available' ? 'selected' : ''; ?>>Available</option>
                                            <option value="Borrowed" <?php echo ($book['status'] ?? '') == 'Borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                                            <option value="Lost" <?php echo ($book['status'] ?? '') == 'Lost' ? 'selected' : ''; ?>>Lost</option>
                                            <option value="Damaged" <?php echo ($book['status'] ?? '') == 'Damaged' ? 'selected' : ''; ?>>Damaged</option>
                                        </select>
                                        <div class="form-text">
                                            Note: Status will auto-change to "Borrowed" when all copies are borrowed, 
                                            unless manually set to Lost/Damaged
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Book Description/Insight</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Enter a brief description or insight about the book..."><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
                                <div class="form-text">Maximum 1000 characters.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="book_image" class="form-label">Book Cover Image</label>
                                <input type="file" class="form-control" id="book_image" name="book_image" 
                                       accept="image/jpeg, image/jpg, image/png, image/gif, image/webp">
                                <div class="form-text">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP.</div>
                                
                                <?php if ($action == 'edit' && !empty($book['image_filename'])): ?>
                                    <div class="mt-3">
                                        <p class="mb-1">Current Image:</p>
                                        <img src="uploads/books/<?php echo htmlspecialchars($book['image_filename']); ?>" 
                                             alt="Book Cover" 
                                             class="img-thumbnail" 
                                             style="max-height: 200px;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                            <label class="form-check-label" for="remove_image">
                                                Remove current image
                                            </label>
                                        </div>
                                    </div>
                                <?php elseif ($action == 'edit'): ?>
                                    <div class="mt-3">
                                        <p class="text-muted">No book cover image uploaded.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="books.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmSubmitModal">
                            <?php echo $action == 'add' ? 'Add Book' : 'Update Book'; ?>
                        </button>
                    </div>
                </form>
                <?php } ?>
            </div>
        </div>

        <!-- Submit Confirmation Modal -->
        <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmSubmitModalLabel">Confirm Submission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <?php echo $action == 'add' ? 'add' : 'update'; ?> this book?</p>
                        <?php if ($action == 'edit' && isset($book) && !empty($book['title'])): ?>
                            <div class="alert alert-info">
                                <strong>Book:</strong> <?php echo htmlspecialchars($book['title']); ?><br>
                                <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Yes, <?php echo $action == 'add' ? 'Add Book' : 'Update Book'; ?></button>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Books List -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="booksTable" class="data-table w-full">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ISBN</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Copies (Available/Total)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php
                        try {
                            $stmt = $conn->query("SELECT * FROM book ORDER BY book_id DESC");
                            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($books)):
                        ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No books found</p>
                                    <p class="text-sm">Add your first book to get started</p>
                                </div>
                            </td>
                        </tr>
                        <?php
                            else:
                                foreach ($books as $row): 
                                    $status_class = [
                                        'Available' => 'bg-green-100 text-green-800',
                                        'Borrowed' => 'bg-yellow-100 text-yellow-800',
                                        'Lost' => 'bg-red-100 text-red-800',
                                        'Damaged' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $available_copies = $row['available_copies'] ?? 0;
                                    $total_copies = $row['number_of_copies'];
                                    $borrowed_copies = $total_copies - $available_copies;
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['book_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($row['image_filename'])): ?>
                                    <img src="uploads/books/<?php echo htmlspecialchars($row['image_filename']); ?>" 
                                         alt="Book Cover" 
                                         class="w-12 h-16 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-12 h-16 bg-slate-100 rounded flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                <div class="font-medium"><?php echo htmlspecialchars($row['title']); ?></div>
                                <?php if (!empty($row['description'])): ?>
                                    <div class="text-xs text-slate-500 mt-1">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Has description
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['author']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($row['isbn']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($row['category']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-green-600 font-medium"><?php echo $available_copies; ?></span> / 
                                <span class="text-slate-600"><?php echo $total_copies; ?></span>
                                <?php if ($borrowed_copies > 0): ?>
                                    <div class="text-xs text-slate-500 mt-1">
                                        <?php echo $borrowed_copies; ?> borrowed
                                    </div>
                                <?php else: ?>
                                    <div class="text-xs text-green-600 mt-1">All available</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_class[$row['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="books.php?action=edit&id=<?php echo $row['book_id']; ?>" 
                                       class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button onclick="openBorrowBookModal(<?php echo $row['book_id']; ?>)" 
                                            class="text-green-600 hover:text-green-900" title="Borrow this book">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                        </svg>
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo $row['book_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>', '<?php echo htmlspecialchars(addslashes($row['author'])); ?>', <?php echo $available_copies; ?>, <?php echo $total_copies; ?>)" 
                                            class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            endif;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='9' class='px-6 py-4 text-center text-red-600'>Error loading books: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add New Book Modal -->
        <div id="addBookModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[85vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-900 leading-tight">Add New Book</h3>
                    <button onclick="closeAddBookModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="books.php?action=add" enctype="multipart/form-data" id="addBookForm" class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label for="add_title" class="block text-sm font-medium text-slate-700">Title *</label>
                            <input type="text" id="add_title" name="title" required class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_author" class="block text-sm font-medium text-slate-700">Author *</label>
                            <input type="text" id="add_author" name="author" required class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_isbn" class="block text-sm font-medium text-slate-700">ISBN</label>
                            <input type="text" id="add_isbn" name="isbn" class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_category" class="block text-sm font-medium text-slate-700">Category</label>
                            <input type="text" id="add_category" name="category" placeholder="e.g., Fiction, Science" class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_publisher" class="block text-sm font-medium text-slate-700">Publisher</label>
                            <input type="text" id="add_publisher" name="publisher" class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_publication_year" class="block text-sm font-medium text-slate-700">Publication Year</label>
                            <input type="number" id="add_publication_year" name="publication_year" min="1900" max="<?php echo date('Y'); ?>" class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_edition" class="block text-sm font-medium text-slate-700">Edition</label>
                            <input type="text" id="add_edition" name="edition" class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1">
                            <label for="add_number_of_copies" class="block text-sm font-medium text-slate-700">Number of Copies *</label>
                            <input type="number" id="add_number_of_copies" name="number_of_copies" min="1" value="1" required class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label for="add_shelf_location" class="block text-sm font-medium text-slate-700">Shelf Location</label>
                            <input type="text" id="add_shelf_location" name="shelf_location" class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label for="add_description" class="block text-sm font-medium text-slate-700">Book Description</label>
                            <textarea id="add_description" name="description" rows="4" placeholder="Enter a brief description..." class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label for="add_book_image" class="block text-sm font-medium text-slate-700">Book Cover Image</label>
                            <input type="file" id="add_book_image" name="book_image" accept="image/jpeg, image/jpg, image/png, image/gif, image/webp" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Max size: 5MB. Allowed: JPG, PNG, GIF, WebP.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeAddBookModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">Add Book</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Borrow Book Modal -->
        <div id="borrowBookModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[85vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-900 leading-tight">Borrow Book for User</h3>
                    <button onclick="closeBorrowBookModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="books.php?action=borrow" id="borrowBookForm" class="p-5">
                    <input type="hidden" id="borrow_book_id" name="book_id" value="">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Book *</label>
                            <div class="flex gap-2 mb-2">
                                <input type="text" id="borrow_book_search" placeholder="Search by title, author, or ISBN" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <button type="button" onclick="searchBooksForBorrow()" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg">Search</button>
                            </div>
                            <div id="borrowBookResults" class="max-h-32 overflow-y-auto border border-slate-200 rounded-lg hidden"></div>
                            <div id="borrowBookSelected" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 hidden"></div>
                        </div>
                        <div>
                            <label for="borrow_institutional_id" class="block text-sm font-medium text-slate-700 mb-2">User Student / Employee ID *</label>
                            <div class="flex gap-2">
                                <input type="text" id="borrow_institutional_id" name="institutional_id" placeholder="Enter user's Student / Employee ID" required class="flex-1 px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" onclick="checkUser()" class="px-4 py-3 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-lg transition-colors">Check User</button>
                            </div>
                            <div id="userInfoSection" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="font-semibold text-green-800 mb-2">User Found</div>
                                <div id="userInfoContent"></div>
                            </div>
                            <div id="userErrorSection" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800"></div>
                        </div>
                        <div>
                            <label for="borrow_days" class="block text-sm font-medium text-slate-700 mb-2">Borrowing Period (Days) *</label>
                            <select id="borrow_days" name="borrow_days" required class="w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="7">7 days (1 week)</option>
                                <option value="14" selected>14 days (2 weeks)</option>
                                <option value="21">21 days (3 weeks)</option>
                                <option value="30">30 days (1 month)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeBorrowBookModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" name="borrow_submit" id="borrowSubmitBtn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Confirm Borrowing</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[85vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-900">Confirm Deletion</h3>
                    <button onclick="closeDeleteModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-5">
                    <p class="text-slate-700 mb-4">Are you sure you want to delete the following book?</p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="font-semibold text-slate-900" id="deleteBookTitle"></div>
                        <div class="text-sm text-slate-600 mt-2">
                            <strong>Author:</strong> <span id="deleteBookAuthor"></span><br>
                            <strong>Copies:</strong> <span id="deleteBookCopies"></span>
                        </div>
                    </div>
                    <div id="borrowedWarning" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <strong>Warning:</strong> This book has borrowed copies and cannot be deleted!
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">Delete Book</a>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once 'footer_unified.php'; ?>

<script>
// Modal functions - must be global for onclick handlers
function openAddBookModal() {
    document.getElementById('addBookModal').classList.remove('hidden');
    document.getElementById('addBookModal').classList.add('flex');
}

function closeAddBookModal() {
    document.getElementById('addBookModal').classList.add('hidden');
    document.getElementById('addBookModal').classList.remove('flex');
    document.getElementById('addBookForm').reset();
}

function openBorrowBookModal(bookId = null) {
    const bookInput = document.getElementById('borrow_book_id');
    const selectedDiv = document.getElementById('borrowBookSelected');
    const resultsDiv = document.getElementById('borrowBookResults');
    if (bookId) {
        bookInput.value = bookId;
        selectedDiv.classList.remove('hidden');
        selectedDiv.innerHTML = 'Book #' + bookId + ' selected (from table)';
    } else {
        bookInput.value = '';
        selectedDiv.classList.add('hidden');
        selectedDiv.innerHTML = '';
    }
    resultsDiv.classList.add('hidden');
    resultsDiv.innerHTML = '';
    document.getElementById('borrow_book_search').value = '';
    document.getElementById('userInfoSection').classList.add('hidden');
    document.getElementById('userErrorSection').classList.add('hidden');
    document.getElementById('borrowSubmitBtn').disabled = true;
    document.getElementById('borrowBookModal').classList.remove('hidden');
    document.getElementById('borrowBookModal').classList.add('flex');
}

function closeBorrowBookModal() {
    document.getElementById('borrowBookModal').classList.add('hidden');
    document.getElementById('borrowBookModal').classList.remove('flex');
    document.getElementById('borrowBookForm').reset();
    document.getElementById('borrow_book_id').value = '';
    document.getElementById('borrowBookSelected').classList.add('hidden');
    document.getElementById('borrowBookResults').classList.add('hidden');
    document.getElementById('userInfoSection').classList.add('hidden');
    document.getElementById('userErrorSection').classList.add('hidden');
}

function searchBooksForBorrow() {
    const q = document.getElementById('borrow_book_search').value.trim();
    const resultsDiv = document.getElementById('borrowBookResults');
    if (q.length < 2) {
        resultsDiv.classList.add('hidden');
        return;
    }
    resultsDiv.innerHTML = '<div class="p-2 text-slate-500 text-sm">Searching...</div>';
    resultsDiv.classList.remove('hidden');
    fetch('books.php?search_books_ajax=1&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                resultsDiv.innerHTML = '<div class="p-2 text-amber-600 text-sm">Search error. Try title or author.</div>';
                return;
            }
            if (!data.results || data.results.length === 0) {
                resultsDiv.innerHTML = '<div class="p-2 text-slate-500 text-sm">No books found. Try title, author, or full ISBN.</div>';
                return;
            }
            let html = '';
            data.results.forEach(function(b) {
                var avail = parseInt(b.available_copies) || 0;
                var canSelect = avail > 0;
                var tit = String(b.title || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;');
                var rowClass = canSelect ? 'hover:bg-slate-50 cursor-pointer' : 'bg-slate-50 cursor-not-allowed opacity-75';
                var actionHtml = canSelect ? '<span class="text-blue-600 text-xs">Select</span>' : '<span class="text-slate-400 text-xs">Unavailable</span>';
                html += '<div class="p-2 border-b border-slate-100 flex justify-between items-center ' + rowClass + '" data-book-id="' + b.book_id + '" data-book-title="' + tit + '" data-can-select="' + (canSelect ? '1' : '0') + '"><div class="text-sm"><strong>' + (b.title || '').replace(/</g,'&lt;') + '</strong><br><span class="text-slate-500">' + (b.author || '').replace(/</g,'&lt;') + ' · ' + avail + '/' + b.number_of_copies + ' avail</span></div>' + actionHtml + '</div>';
            });
            resultsDiv.innerHTML = html;
            resultsDiv.querySelectorAll('[data-book-id]').forEach(function(el) {
                el.addEventListener('click', function() {
                    if (this.getAttribute('data-can-select') === '1') {
                        selectBookForBorrow(parseInt(this.getAttribute('data-book-id')), this.getAttribute('data-book-title') || '');
                    }
                });
            });
        })
        .catch(() => {
            resultsDiv.innerHTML = '<div class="p-2 text-red-600 text-sm">Error searching</div>';
        });
}

function selectBookForBorrow(bookId, bookTitle) {
    document.getElementById('borrow_book_id').value = bookId;
    var sel = document.getElementById('borrowBookSelected');
    sel.textContent = 'Selected: ' + (bookTitle || '');
    sel.classList.remove('hidden');
    document.getElementById('borrowBookResults').classList.add('hidden');
    document.getElementById('borrow_book_search').value = '';
    var userOk = document.getElementById('userInfoSection') && !document.getElementById('userInfoSection').classList.contains('hidden');
    if (userOk) document.getElementById('borrowSubmitBtn').disabled = false;
}

function openDeleteModal(bookId, bookTitle, bookAuthor, availableCopies, totalCopies) {
    const borrowedCopies = totalCopies - availableCopies;
    
    document.getElementById('deleteBookTitle').textContent = bookTitle;
    document.getElementById('deleteBookAuthor').textContent = bookAuthor;
    document.getElementById('deleteBookCopies').textContent = availableCopies + ' / ' + totalCopies;
    
    const borrowedWarning = document.getElementById('borrowedWarning');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    if (borrowedCopies > 0) {
        borrowedWarning.classList.remove('hidden');
        confirmDeleteBtn.style.display = 'none';
    } else {
        borrowedWarning.classList.add('hidden');
        confirmDeleteBtn.style.display = 'block';
        confirmDeleteBtn.href = 'books.php?action=delete&id=' + bookId;
    }
    
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
}

function checkUser() {
    const institutionalId = document.getElementById('borrow_institutional_id').value.trim();
    const userInfoSection = document.getElementById('userInfoSection');
    const userErrorSection = document.getElementById('userErrorSection');
    const userInfoContent = document.getElementById('userInfoContent');
    const submitBtn = document.getElementById('borrowSubmitBtn');
    
    if (!institutionalId) {
        userErrorSection.textContent = 'Please enter a Student / Employee ID';
        userErrorSection.classList.remove('hidden');
        userInfoSection.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = true;
        return;
    }
    
    userErrorSection.classList.add('hidden');
    userInfoSection.classList.remove('hidden');
    userInfoContent.innerHTML = '<div class="text-sm animate-pulse">Checking user...</div>';
    if (submitBtn) submitBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('check_user_ajax', '1');
    formData.append('institutional_id', institutionalId);
    
    fetch('books.php?check_user_ajax=1', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                const u = data.user;
                userErrorSection.classList.add('hidden');
                userInfoContent.innerHTML = '<div class="text-sm"><strong>Name:</strong> ' + (u.full_name || '') + '<br><strong>ID:</strong> ' + (u.institutional_id || '') + '<br><strong>Status:</strong> ' + (u.status || '') + '<br><strong>Active borrows:</strong> ' + (u.active_borrows || 0) + ' / 5</div>';
                if (submitBtn && document.getElementById('borrow_book_id').value) submitBtn.disabled = false;
                else if (submitBtn) submitBtn.disabled = true;
            } else {
                userInfoSection.classList.add('hidden');
                userErrorSection.textContent = data.error || 'User not found';
                userErrorSection.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
            }
        })
        .catch(() => {
            userInfoSection.classList.add('hidden');
            userErrorSection.textContent = 'Error checking user. Please try again.';
            userErrorSection.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = true;
        });
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                if (this.id === 'addBookModal') closeAddBookModal();
                if (this.id === 'borrowBookModal') closeBorrowBookModal();
                if (this.id === 'deleteModal') closeDeleteModal();
            }
        });
    });
});
</script>