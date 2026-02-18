<?php

if (isset($_GET['export_returned']) && $_GET['export_returned'] == 'excel') {
    try {
        
        require_once 'db.php';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="returned_books_history_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $sql = "SELECT 
                    t.transaction_id,
                    b.title as book_title,
                    b.author as book_author,
                    b.isbn,
                    u.full_name as borrower_name,
                    u.institutional_id,
                    u.rfid_number,
                    u.grade_section,
                    u.user_type,
                    l.full_name as librarian_name,
                    t.borrow_date,
                    t.due_date,
                    t.return_date,
                    t.late_penalty,
                    t.lost_penalty,
                    t.damage_penalty,
                    t.total_penalty,
                    t.status,
                    t.penalty_updated_at,
                    DATEDIFF(t.return_date, t.borrow_date) as days_borrowed,
                    CASE 
                        WHEN t.return_date > t.due_date THEN DATEDIFF(t.return_date, t.due_date)
                        ELSE 0 
                    END as days_late
                FROM borrowing_transaction t
                JOIN book b ON t.book_id = b.book_id
                JOIN tbl_users u ON t.user_id = u.user_id
                LEFT JOIN librarian l ON t.librarian_id = l.librarian_id
                WHERE t.status = 'Returned'
                ORDER BY t.return_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $returned_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<table border="1">';

        echo '<tr style="background-color: #4CAF50; color: white;">';
        echo '<th>Transaction ID</th>';
        echo '<th>Book Title</th>';
        echo '<th>Author</th>';
        echo '<th>ISBN</th>';
        echo '<th>Borrower Name</th>';
        echo '<th>Student / Employee ID</th>';
        echo '<th>RFID Number</th>';
        echo '<th>Grade/Section</th>';
        echo '<th>User Type</th>';
        echo '<th>Librarian</th>';
        echo '<th>Borrow Date</th>';
        echo '<th>Due Date</th>';
        echo '<th>Return Date</th>';
        echo '<th>Days Borrowed</th>';
        echo '<th>Days Late</th>';
        echo '<th>Late Penalty (₱)</th>';
        echo '<th>Lost Penalty (₱)</th>';
        echo '<th>Damage Penalty (₱)</th>';
        echo '<th>Total Penalty (₱)</th>';
        echo '<th>Status</th>';
        echo '<th>Penalty Updated At</th>';
        echo '</tr>';

        $total_late = 0;
        $total_lost = 0;
        $total_damage = 0;
        $grand_total = 0;
        
        foreach ($returned_books as $book) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($book['transaction_id']) . '</td>';
            echo '<td>' . htmlspecialchars($book['book_title']) . '</td>';
            echo '<td>' . htmlspecialchars($book['book_author']) . '</td>';
            echo '<td>' . htmlspecialchars($book['isbn']) . '</td>';
            echo '<td>' . htmlspecialchars($book['borrower_name']) . '</td>';
            echo '<td>' . htmlspecialchars($book['institutional_id']) . '</td>';
            echo '<td>' . htmlspecialchars($book['rfid_number'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($book['grade_section'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($book['user_type']) . '</td>';
            echo '<td>' . htmlspecialchars($book['librarian_name'] ?? 'N/A') . '</td>';
            echo '<td>' . date('Y-m-d', strtotime($book['borrow_date'])) . '</td>';
            echo '<td>' . date('Y-m-d', strtotime($book['due_date'])) . '</td>';
            echo '<td>' . date('Y-m-d', strtotime($book['return_date'])) . '</td>';
            echo '<td>' . $book['days_borrowed'] . '</td>';
            echo '<td>' . $book['days_late'] . '</td>';
            echo '<td>₱' . number_format($book['late_penalty'], 2) . '</td>';
            echo '<td>₱' . number_format($book['lost_penalty'], 2) . '</td>';
            echo '<td>₱' . number_format($book['damage_penalty'], 2) . '</td>';
            echo '<td>₱' . number_format($book['total_penalty'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($book['status']) . '</td>';
            echo '<td>' . ($book['penalty_updated_at'] ? date('Y-m-d H:i', strtotime($book['penalty_updated_at'])) : 'N/A') . '</td>';
            echo '</tr>';

            $total_late += $book['late_penalty'];
            $total_lost += $book['lost_penalty'];
            $total_damage += $book['damage_penalty'];
            $grand_total += $book['total_penalty'];
        }

        echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
        echo '<td colspan="15" align="right">TOTALS:</td>';
        echo '<td>₱' . number_format($total_late, 2) . '</td>';
        echo '<td>₱' . number_format($total_lost, 2) . '</td>';
        echo '<td>₱' . number_format($total_damage, 2) . '</td>';
        echo '<td>₱' . number_format($grand_total, 2) . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';
        
        echo '</table>';
        exit();
        
    } catch (PDOException $e) {
        
        header('Location: transactions.php?action=returned_history&error=' . urlencode($e->getMessage()));
        exit();
    }
}

require_once 'db.php';

$smtpUser = trim((string) getenv('MSMS_SMTP_USER'));
$smtpPass = trim((string) getenv('MSMS_SMTP_PASSWORD'));
$smtpFrom = trim((string) getenv('MSMS_SMTP_FROM')) ?: $smtpUser;
$smtpReplyTo = trim((string) getenv('MSMS_SMTP_REPLY_TO')) ?: $smtpUser;

$penalty_search_result = null;
$penalty_search_error = '';
$penalty_searched_institutional_id = trim($_POST['penalty_institutional_id'] ?? '');
$penalty_search_error_type = '';

if (isset($_POST['search_penalty_user_submit']) && !empty($_POST['ajax'])) {
    if (empty($penalty_searched_institutional_id)) {
        $penalty_search_error = 'Please enter a Student / Employee ID';
        $penalty_search_error_type = 'danger';
    } else {
        try {
            $sql = "SELECT user_id, institutional_id, full_name, rfid_number, grade_section, user_type, status, balance FROM tbl_users WHERE institutional_id = :institutional_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':institutional_id' => $penalty_searched_institutional_id]);
            $penalty_search_result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$penalty_search_result) {
                $penalty_search_error = 'User not found with Student / Employee ID: ' . htmlspecialchars($penalty_searched_institutional_id);
                $penalty_search_error_type = 'warning';
            }
        } catch (PDOException $e) {
            $penalty_search_error = 'Database error: ' . $e->getMessage();
            $penalty_search_error_type = 'danger';
        }
    }
    header('Content-Type: text/html; charset=utf-8');
    if (!empty($penalty_search_error)) {
        echo '<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">' . htmlspecialchars($penalty_search_error) . '</div>';
        exit;
    }
    if (empty($penalty_search_result)) {
        echo '<div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-sm">Enter a Student / Employee ID and click Search.</div>';
        exit;
    }
    $user_id = $penalty_search_result['user_id'];
    $current_balance = $penalty_search_result['balance'] ?? 0;
    $sql = "SELECT t.*, b.title, b.author, u.institutional_id, u.full_name as borrower_name, u.rfid_number, u.grade_section
            FROM borrowing_transaction t
            JOIN book b ON t.book_id = b.book_id
            JOIN tbl_users u ON t.user_id = u.user_id
            WHERE t.user_id = :user_id AND t.return_date IS NULL AND t.status IN ('Borrowed', 'Overdue')
            ORDER BY t.due_date";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $active_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 mb-4">';
    echo '<strong>' . htmlspecialchars($penalty_search_result['full_name']) . '</strong> — ID: ' . htmlspecialchars($penalty_search_result['institutional_id']) . ' | Balance: ₱' . number_format($current_balance, 2) . '</div>';
    if (empty($active_transactions)) {
        $returned_sql = "SELECT t.*, b.title FROM borrowing_transaction t JOIN book b ON t.book_id = b.book_id WHERE t.user_id = :user_id AND t.total_penalty > 0 ORDER BY t.transaction_id DESC";
        $rstmt = $conn->prepare($returned_sql);
        $rstmt->execute([':user_id' => $user_id]);
        $returned_with_penalties = $rstmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($returned_with_penalties)) {
            echo '<div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm mb-4">Returned books with penalties (view only):</div>';
            foreach ($returned_with_penalties as $rt) {
                $rt_total = number_format((float)($rt['total_penalty'] ?? 0), 2);
                echo '<div class="border border-slate-200 rounded-lg p-4 mb-4 bg-white flex justify-between items-center">';
                echo '<div><span class="font-semibold text-slate-900">Txn #' . (int)$rt['transaction_id'] . '</span> — ' . htmlspecialchars($rt['title'] ?? '') . ' <span class="text-slate-600">(₱' . $rt_total . ')</span></div>';
                echo '<a href="generate_penalty_receipt.php?transaction_id=' . (int)$rt['transaction_id'] . '" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs">Receipt</a>';
                echo '</div>';
            }
        } else {
            echo '<div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-sm">No borrowed/overdue books or penalty transactions for this user.</div>';
        }
        exit;
    }
    foreach ($active_transactions as $transaction) {
        $tid = $transaction['transaction_id'];
        $late = isset($transaction['late_penalty']) ? number_format((float)$transaction['late_penalty'], 2) : '0.00';
        $lost = isset($transaction['lost_penalty']) ? number_format((float)$transaction['lost_penalty'], 2) : '0.00';
        $damage = isset($transaction['damage_penalty']) ? number_format((float)$transaction['damage_penalty'], 2) : '0.00';
        $total = isset($transaction['total_penalty']) ? number_format((float)$transaction['total_penalty'], 2) : '0.00';
        echo '<div class="border border-slate-200 rounded-lg p-4 mb-4 bg-white">';
        echo '<div class="font-semibold text-slate-900 mb-2">Transaction #' . $tid . ' — ' . htmlspecialchars($transaction['title']) . '</div>';
        echo '<form class="penalty-save-form" method="POST" action="transactions.php" data-transaction-id="' . $tid . '">';
        echo '<input type="hidden" name="save_penalty_submit" value="1">';
        echo '<input type="hidden" name="transaction_id" value="' . $tid . '">';
        echo '<input type="hidden" name="penalty_institutional_id" value="' . htmlspecialchars($penalty_searched_institutional_id) . '">';
        echo '<input type="hidden" name="ajax" value="1">';
        echo '<div class="grid grid-cols-3 gap-4 mb-4">';
        echo '<div><label class="block text-xs font-medium text-slate-600 mb-1">Late (₱)</label><input type="number" name="late_penalty" value="' . $late . '" step="0.01" min="0" class="penalty-input w-full px-3 py-2 border rounded-lg" data-tid="' . $tid . '"></div>';
        echo '<div><label class="block text-xs font-medium text-slate-600 mb-1">Lost (₱)</label><input type="number" name="lost_penalty" value="' . $lost . '" step="0.01" min="0" class="penalty-input w-full px-3 py-2 border rounded-lg" data-tid="' . $tid . '"></div>';
        echo '<div><label class="block text-xs font-medium text-slate-600 mb-1">Damage (₱)</label><input type="number" name="damage_penalty" value="' . $damage . '" step="0.01" min="0" class="penalty-input w-full px-3 py-2 border rounded-lg" data-tid="' . $tid . '"></div>';
        echo '</div>';
        echo '<div class="flex justify-between items-center"><span class="text-sm font-semibold text-slate-700">Total: ₱<span class="penalty-total" data-tid="' . $tid . '">' . $total . '</span></span><button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm">Save Penalty</button></div>';
        echo '</form></div>';
    }
    exit;
}

if (isset($_POST['save_penalty_submit']) && !empty($_POST['ajax'])) {
    $transaction_id = $_POST['transaction_id'] ?? null;
    $late_penalty = floatval($_POST['late_penalty'] ?? 0);
    $lost_penalty = floatval($_POST['lost_penalty'] ?? 0);
    $damage_penalty = floatval($_POST['damage_penalty'] ?? 0);
    $total_penalty = $late_penalty + $lost_penalty + $damage_penalty;
    $penalty_institutional_id = $_POST['penalty_institutional_id'] ?? '';
    try {
        $conn->beginTransaction();
        $get_user_sql = "SELECT user_id FROM borrowing_transaction WHERE transaction_id = ?";
        $stmt = $conn->prepare($get_user_sql);
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transaction) {
            throw new Exception('Transaction not found!');
        }
        $user_id = $transaction['user_id'];
        $update_sql = "UPDATE borrowing_transaction SET late_penalty = ?, lost_penalty = ?, damage_penalty = ?, total_penalty = ?, penalty_updated_at = NOW() WHERE transaction_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([$late_penalty, $lost_penalty, $damage_penalty, $total_penalty, $transaction_id]);
        $get_balance_sql = "SELECT balance FROM tbl_users WHERE user_id = ?";
        $balance_stmt = $conn->prepare($get_balance_sql);
        $balance_stmt->execute([$user_id]);
        $user_data = $balance_stmt->fetch(PDO::FETCH_ASSOC);
        $current_balance = $user_data['balance'] ?? 0;
        $new_balance = $current_balance - $total_penalty;
        $update_balance_sql = "UPDATE tbl_users SET balance = ? WHERE user_id = ?";
        $update_balance_stmt = $conn->prepare($update_balance_sql);
        $update_balance_stmt->execute([$new_balance, $user_id]);
        $conn->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Penalty saved.', 'institutional_id' => $penalty_institutional_id]);
        exit;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

require_once 'header_unified.php';

$action = $_GET['action'] ?? 'list';
$alert_message = '';
$alert_type = '';

$penalty_search_result = null;
$penalty_search_error = '';
$penalty_searched_institutional_id = $penalty_searched_institutional_id ?? '';
$penalty_search_error_type = '';

$users_with_penalties = [];
try {
    $penalty_users_sql = "SELECT DISTINCT u.user_id, u.institutional_id, u.full_name, u.grade_section, u.balance,
                          (SELECT t.transaction_id FROM borrowing_transaction t 
                           WHERE t.user_id = u.user_id AND t.total_penalty > 0 
                           ORDER BY t.transaction_id DESC LIMIT 1) as receipt_txn_id
                          FROM tbl_users u
                          WHERE u.status = 'Active'
                          AND (u.balance < 0 OR EXISTS (
                              SELECT 1 FROM borrowing_transaction t 
                              WHERE t.user_id = u.user_id AND t.total_penalty > 0
                          ))
                          ORDER BY u.balance ASC";
    $stmt = $conn->prepare($penalty_users_sql);
    $stmt->execute();
    $users_with_penalties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users_with_penalties = [];
}

if ($action == 'manage_reservations' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_reservation'])) {
        $transaction_id = $_POST['transaction_id'];
        
        try {
            $conn->beginTransaction();

            $sql = "SELECT bt.*, b.title as book_title, b.available_copies, 
                           u.user_id, u.full_name, u.email, u.institutional_id
                    FROM borrowing_transaction bt
                    JOIN book b ON bt.book_id = b.book_id
                    JOIN tbl_users u ON bt.user_id = u.user_id
                    WHERE bt.transaction_id = ? AND bt.status = 'Reserved'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$transaction_id]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                throw new Exception('Reservation not found or already processed!');
            }

            $librarian_id = $_SESSION['librarian_id'] ?? 1;

            $due_date = date('Y-m-d', strtotime('+14 days'));

            $update_sql = "UPDATE borrowing_transaction SET 
                          status = 'Borrowed', 
                          librarian_id = ?,
                          borrow_date = CURDATE(),
                          due_date = ?,
                          penalty_updated_at = NOW()
                          WHERE transaction_id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([$librarian_id, $due_date, $transaction_id]);
            
            $conn->commit();

            if (!empty($reservation['email'])) {
                try {
                    require 'vendor/autoload.php';
                    
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                    if ($smtpUser === '' || $smtpPass === '' || $smtpFrom === '') {
                        throw new Exception('SMTP credentials are not configured.');
                    }
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtpUser;
                    $mail->Password   = $smtpPass;
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom($smtpFrom, 'Library Management System');
                    $mail->addAddress($reservation['email'], $reservation['full_name']);
                    $mail->addReplyTo($smtpReplyTo, 'Library Support');

                    $mail->isHTML(true);
                    $mail->Subject = 'Reservation Confirmed - Book Ready for Pickup';
                    
                    $mail->Body = '
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                            .content { padding: 30px; background-color: #f9f9f9; border: 1px solid #ddd; border-top: none; }
                            .info-box { background-color: #e8f5e8; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px; }
                            .book-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #eee; margin-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <h1 style="margin:0;">📚 Library Management System</h1>
                            </div>
                            <div class="content">
                                <h2>Dear ' . htmlspecialchars($reservation['full_name']) . ',</h2>
                                
                                <div class="info-box">
                                    <h3 style="color: #28a745; margin-top: 0;">✓ Reservation Confirmed!</h3>
                                    <p>Your reserved book is now ready for pickup.</p>
                                </div>
                                
                                <div class="book-details">
                                    <h4>Book Details:</h4>
                                    <ul style="list-style-type: none; padding-left: 0;">
                                        <li><strong>Title:</strong> ' . htmlspecialchars($reservation['book_title']) . '</li>
                                        <li><strong>Transaction ID:</strong> ' . $transaction_id . '</li>
                                        <li><strong>Pickup Status:</strong> <span style="color: #28a745;">Ready for Collection</span></li>
                                        <li><strong>Due Date:</strong> ' . date('F d, Y', strtotime($due_date)) . '</li>
                                    </ul>
                                </div>
                                
                                <p><strong>Important Information:</strong></p>
                                <ul>
                                    <li>Please bring your Student/Employee ID: <strong>' . htmlspecialchars($reservation['institutional_id']) . '</strong></li>
                                    <li>Pickup location: Main Campus Library</li>
                                    <li>Library hours: 8:00 AM - 5:00 PM (Monday to Friday)</li>
                                    <li>Please pick up the book within 3 days</li>
                                </ul>
                                
                                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                    <strong>📅 Due Date Reminder:</strong><br>
                                    The book is due on <strong>' . date('F d, Y', strtotime($due_date)) . '</strong> (14 days from pickup).
                                    Please return it on time to avoid penalties.
                                </div>
                                
                                <p style="text-align: center; font-size: 16px; font-weight: bold;">
                                    Thank you for using our library services!
                                </p>
                            </div>
                            <div class="footer">
                                <p>This is an automated message. Please do not reply to this email.</p>
                                <p>If you have any questions, please contact the library staff.</p>
                            </div>
                        </div>
                    </body>
                    </html>';

                    $mail->AltBody = "Dear " . $reservation['full_name'] . ",\n\n"
                        . "Your reservation has been confirmed!\n\n"
                        . "Book: " . $reservation['book_title'] . "\n"
                        . "Transaction ID: " . $transaction_id . "\n"
                        . "Status: Ready for pickup\n"
                        . "Due Date: " . date('F d, Y', strtotime($due_date)) . "\n\n"
                        . "Please bring your ID: " . $reservation['institutional_id'] . "\n"
                        . "Pickup location: Main Campus Library\n"
                        . "Library hours: 8:00 AM - 5:00 PM (Monday to Friday)\n\n"
                        . "The book is due on " . date('F d, Y', strtotime($due_date)) . " (14 days from pickup).\n\n"
                        . "Thank you for using our library services!\n";

                    if ($mail->send()) {
                        
                        $log_sql = "INSERT INTO email_logs (user_id, email_type, status, sent_at) 
                                   VALUES (?, 'reservation_confirmation', 'sent', NOW())";
                        $log_stmt = $conn->prepare($log_sql);
                        $log_stmt->execute([$reservation['user_id']]);
                        
                        $email_success = true;
                    }
                    
                } catch (Exception $email_error) {
                    
                    error_log("Email sending failed: " . $email_error->getMessage());
                    $email_success = false;
                }
            }

            $alert_message = 'Reservation confirmed as borrowed!';
            if (isset($email_success) && $email_success) {
                $alert_message .= ' Email notification sent to user.';
            } elseif (isset($email_success) && !$email_success) {
                $alert_message .= ' (Email notification failed to send)';
            } elseif (empty($reservation['email'])) {
                $alert_message .= ' (User has no email address)';
            }
            
            $alert_type = 'success';
            $action = 'manage_reservations';
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }
    
    if (isset($_POST['cancel_reservation'])) {
        $transaction_id = $_POST['transaction_id'];
        
        try {
            $conn->beginTransaction();

            $sql = "SELECT bt.*, b.title as book_title, u.full_name, u.email, u.institutional_id
                    FROM borrowing_transaction bt 
                    JOIN book b ON bt.book_id = b.book_id
                    JOIN tbl_users u ON bt.user_id = u.user_id
                    WHERE bt.transaction_id = ? AND bt.status = 'Reserved'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$transaction_id]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                throw new Exception('Reservation not found!');
            }

            $update_sql = "UPDATE borrowing_transaction SET status = 'Cancelled' WHERE transaction_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([$transaction_id]);

            $book_sql = "UPDATE book SET available_copies = available_copies + 1 WHERE book_id = ?";
            $book_stmt = $conn->prepare($book_sql);
            $book_stmt->execute([$reservation['book_id']]);
            
            $conn->commit();

            if (!empty($reservation['email'])) {
                try {
                    require 'vendor/autoload.php';
                    
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                    if ($smtpUser === '' || $smtpPass === '' || $smtpFrom === '') {
                        throw new Exception('SMTP credentials are not configured.');
                    }
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtpUser;
                    $mail->Password   = $smtpPass;
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom($smtpFrom, 'Library Management System');
                    $mail->addAddress($reservation['email'], $reservation['full_name']);
                    $mail->addReplyTo($smtpReplyTo, 'Library Support');

                    $mail->isHTML(true);
                    $mail->Subject = 'Reservation Cancelled - Library Management System';
                    
                    $mail->Body = '
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                            .content { padding: 30px; background-color: #f9f9f9; border: 1px solid #ddd; border-top: none; }
                            .cancellation-box { background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; }
                            .book-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #eee; margin-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <h1 style="margin:0;">📚 Library Management System</h1>
                            </div>
                            <div class="content">
                                <h2>Dear ' . htmlspecialchars($reservation['full_name']) . ',</h2>
                                
                                <div class="cancellation-box">
                                    <h3 style="color: #dc3545; margin-top: 0;">✗ Reservation Cancelled</h3>
                                    <p>Your book reservation has been cancelled by the library staff.</p>
                                </div>
                                
                                <div class="book-details">
                                    <h4>Cancelled Reservation Details:</h4>
                                    <ul style="list-style-type: none; padding-left: 0;">
                                        <li><strong>Book Title:</strong> ' . htmlspecialchars($reservation['book_title']) . '</li>
                                        <li><strong>Transaction ID:</strong> ' . $transaction_id . '</li>
                                        <li><strong>Status:</strong> <span style="color: #dc3545;">Cancelled</span></li>
                                        <li><strong>Date:</strong> ' . date('F d, Y') . '</li>
                                    </ul>
                                </div>
                                
                                <p><strong>Possible reasons for cancellation:</strong></p>
                                <ul>
                                    <li>Reservation not picked up within the allowed timeframe</li>
                                    <li>Book no longer available</li>
                                    <li>Administrative reasons</li>
                                </ul>
                                
                                <div style="background-color: #e2e3e5; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                    <strong>ℹ️ Need Assistance?</strong><br>
                                    If you believe this cancellation was made in error, or if you have any questions,
                                    please contact the library staff during operating hours.
                                </div>
                                
                                <p style="text-align: center;">
                                    We apologize for any inconvenience this may have caused.
                                </p>
                            </div>
                            <div class="footer">
                                <p>This is an automated message. Please do not reply to this email.</p>
                                <p>If you have any questions, please contact the library staff.</p>
                            </div>
                        </div>
                    </body>
                    </html>';

                    $mail->AltBody = "Dear " . $reservation['full_name'] . ",\n\n"
                        . "Your book reservation has been cancelled.\n\n"
                        . "Book: " . $reservation['book_title'] . "\n"
                        . "Transaction ID: " . $transaction_id . "\n"
                        . "Status: Cancelled\n"
                        . "Date: " . date('F d, Y') . "\n\n"
                        . "Possible reasons for cancellation:\n"
                        . "- Reservation not picked up within timeframe\n"
                        . "- Book no longer available\n"
                        . "- Administrative reasons\n\n"
                        . "If you have questions, please contact the library staff.\n\n"
                        . "We apologize for any inconvenience.\n";

                    if ($mail->send()) {
                        
                        $log_sql = "INSERT INTO email_logs (user_id, email_type, status, sent_at) 
                                   VALUES (?, 'reservation_cancellation', 'sent', NOW())";
                        $log_stmt = $conn->prepare($log_sql);
                        $log_stmt->execute([$reservation['user_id']]);
                        
                        $email_success = true;
                    }
                    
                } catch (Exception $email_error) {
                    error_log("Cancellation email failed: " . $email_error->getMessage());
                    $email_success = false;
                }
            }
            
            $alert_message = 'Reservation cancelled successfully!';
            if (isset($email_success) && $email_success) {
                $alert_message .= ' Email notification sent to user.';
            } elseif (isset($email_success) && !$email_success) {
                $alert_message .= ' (Email notification failed to send)';
            } elseif (empty($reservation['email'])) {
                $alert_message .= ' (User has no email address)';
            }
            
            $alert_type = 'success';
            $action = 'manage_reservations';
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }
}

if (isset($_POST['save_penalty_submit'])) {
    $transaction_id = $_POST['transaction_id'] ?? null;
    $late_penalty = floatval($_POST['late_penalty'] ?? 0);
    $lost_penalty = floatval($_POST['lost_penalty'] ?? 0);
    $damage_penalty = floatval($_POST['damage_penalty'] ?? 0);
    $total_penalty = $late_penalty + $lost_penalty + $damage_penalty;
    $penalty_searched_institutional_id = $_POST['penalty_institutional_id'] ?? '';
    $is_ajax_save = !empty($_POST['ajax']);
    
    try {
        $conn->beginTransaction();
        
        $get_user_sql = "SELECT user_id FROM borrowing_transaction WHERE transaction_id = ?";
        $stmt = $conn->prepare($get_user_sql);
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaction) {
            throw new Exception('Transaction not found!');
        }
        
        $user_id = $transaction['user_id'];
        
        $update_sql = "UPDATE borrowing_transaction SET late_penalty = ?, lost_penalty = ?, damage_penalty = ?, total_penalty = ?, penalty_updated_at = NOW() WHERE transaction_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([$late_penalty, $lost_penalty, $damage_penalty, $total_penalty, $transaction_id]);
        
        $get_balance_sql = "SELECT balance FROM tbl_users WHERE user_id = ?";
        $balance_stmt = $conn->prepare($get_balance_sql);
        $balance_stmt->execute([$user_id]);
        $user_data = $balance_stmt->fetch(PDO::FETCH_ASSOC);
        $current_balance = $user_data['balance'] ?? 0;
        $new_balance = $current_balance - $total_penalty;
        
        $update_balance_sql = "UPDATE tbl_users SET balance = ? WHERE user_id = ?";
        $update_balance_stmt = $conn->prepare($update_balance_sql);
        $update_balance_stmt->execute([$new_balance, $user_id]);
        
        $conn->commit();
        
        if ($is_ajax_save) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Penalty saved.', 'institutional_id' => $penalty_searched_institutional_id]);
            exit;
        }
        
        $user_info_sql = "SELECT institutional_id, full_name FROM tbl_users WHERE user_id = ?";
        $user_info_stmt = $conn->prepare($user_info_sql);
        $user_info_stmt->execute([$user_id]);
        $user_info = $user_info_stmt->fetch(PDO::FETCH_ASSOC);
        
        $alert_message = "Penalty saved successfully!<br>";
        $alert_message .= "Late: ₱" . number_format($late_penalty, 2) . "<br>";
        $alert_message .= "Lost: ₱" . number_format($lost_penalty, 2) . "<br>";
        $alert_message .= "Damage: ₱" . number_format($damage_penalty, 2) . "<br>";
        $alert_message .= "<strong>Total Penalty: ₱" . number_format($total_penalty, 2) . "</strong><br>";
        $alert_type = 'success';
        if ($new_balance >= 0) {
            $alert_message .= "<strong>User's new balance: ₱" . number_format($new_balance, 2) . " (credit)</strong>";
        } else {
            $alert_message .= "<strong>User now owes: ₱" . number_format(abs($new_balance), 2) . "</strong>";
        }
        
        if (!empty($user_info['institutional_id'])) {
            $sql = "SELECT user_id, institutional_id, full_name, rfid_number, grade_section, user_type, status, balance FROM tbl_users WHERE institutional_id = :institutional_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':institutional_id' => $user_info['institutional_id']]);
            $penalty_search_result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        if (!empty($is_ajax_save)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $alert_message = 'Error saving penalty: ' . $e->getMessage();
        $alert_type = 'danger';
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        if (!empty($is_ajax_save)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
        $alert_message = 'Database Error: ' . $e->getMessage();
        $alert_type = 'danger';
    }
}

if ($action == 'manage_penalty' && !empty($_GET['search_id'])) {
    $institutional_id = trim($_GET['search_id']);
    $penalty_searched_institutional_id = htmlspecialchars($institutional_id);
    try {
        $sql = "SELECT user_id, institutional_id, full_name, rfid_number, grade_section, user_type, status, balance FROM tbl_users WHERE institutional_id = :institutional_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':institutional_id' => $institutional_id]);
        $penalty_search_result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$penalty_search_result) {
            $penalty_search_error = 'User not found with Student / Employee ID: ' . htmlspecialchars($institutional_id);
            $penalty_search_error_type = 'warning';
        }
    } catch (PDOException $e) {
        $penalty_search_error = 'Database error: ' . $e->getMessage();
        $penalty_search_error_type = 'danger';
    }
}
if (isset($_POST['search_penalty_user_submit'])) {
    $institutional_id = trim($_POST['penalty_institutional_id'] ?? '');
    $penalty_searched_institutional_id = htmlspecialchars($institutional_id);
    
    if (empty($institutional_id)) {
        $penalty_search_error = 'Please enter an Student / Employee ID';
        $penalty_search_error_type = 'danger';
    } else {
        try {
            $sql = "SELECT user_id, institutional_id, full_name, rfid_number, grade_section, user_type, status, balance 
                    FROM tbl_users 
                    WHERE institutional_id = :institutional_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':institutional_id' => $institutional_id]);
            $penalty_search_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$penalty_search_result) {
                $penalty_search_error = 'User not found with Student / Employee ID: ' . htmlspecialchars($institutional_id);
                $penalty_search_error_type = 'warning';
            }
        } catch (PDOException $e) {
            $penalty_search_error = 'Database error: ' . $e->getMessage();
            $penalty_search_error_type = 'danger';
        }
    }
}

$penalty_results_html = '';
if ($action == 'manage_penalty' && !empty($penalty_search_result) && empty($penalty_search_error)) {
    $user_id = $penalty_search_result['user_id'];
    $current_balance = $penalty_search_result['balance'] ?? 0;
    $sql = "SELECT t.*, b.title, b.author, u.institutional_id, u.full_name as borrower_name, u.rfid_number, u.grade_section
            FROM borrowing_transaction t
            JOIN book b ON t.book_id = b.book_id
            JOIN tbl_users u ON t.user_id = u.user_id
            WHERE t.user_id = :user_id AND t.return_date IS NULL AND t.status IN ('Borrowed', 'Overdue')
            ORDER BY t.due_date";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $active_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $penalty_results_html .= '<div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 mb-4">';
    $penalty_results_html .= '<strong>' . htmlspecialchars($penalty_search_result['full_name']) . '</strong> — ID: ' . htmlspecialchars($penalty_search_result['institutional_id']) . ' | Balance: ₱' . number_format($current_balance, 2) . '</div>';
    if (empty($active_transactions)) {
        $returned_sql = "SELECT t.*, b.title FROM borrowing_transaction t JOIN book b ON t.book_id = b.book_id WHERE t.user_id = :user_id AND t.total_penalty > 0 ORDER BY t.transaction_id DESC";
        $rstmt = $conn->prepare($returned_sql);
        $rstmt->execute([':user_id' => $user_id]);
        $returned_with_penalties = $rstmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($returned_with_penalties)) {
            $penalty_results_html .= '<div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm mb-4">Returned books with penalties (view only):</div>';
            foreach ($returned_with_penalties as $rt) {
                $rt_total = number_format((float)($rt['total_penalty'] ?? 0), 2);
                $penalty_results_html .= '<div class="border border-slate-200 rounded-lg p-4 mb-4 bg-white flex justify-between items-center">';
                $penalty_results_html .= '<div><span class="font-semibold text-slate-900">Txn #' . (int)$rt['transaction_id'] . '</span> — ' . htmlspecialchars($rt['title'] ?? '') . ' <span class="text-slate-600">(₱' . $rt_total . ')</span></div>';
                $penalty_results_html .= '<a href="generate_penalty_receipt.php?transaction_id=' . (int)$rt['transaction_id'] . '" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs">Receipt</a>';
                $penalty_results_html .= '</div>';
            }
        } else {
            $penalty_results_html .= '<div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 text-sm">No borrowed/overdue books or penalty transactions for this user.</div>';
        }
    } else {
        $penalty_searched_id = $penalty_searched_institutional_id ?? $penalty_search_result['institutional_id'] ?? '';
        foreach ($active_transactions as $transaction) {
            $tid = $transaction['transaction_id'];
            $late = isset($transaction['late_penalty']) ? number_format((float)$transaction['late_penalty'], 2) : '0.00';
            $lost = isset($transaction['lost_penalty']) ? number_format((float)$transaction['lost_penalty'], 2) : '0.00';
            $damage = isset($transaction['damage_penalty']) ? number_format((float)$transaction['damage_penalty'], 2) : '0.00';
            $total = isset($transaction['total_penalty']) ? number_format((float)$transaction['total_penalty'], 2) : '0.00';
            $penalty_results_html .= '<div class="border border-slate-200 rounded-lg p-4 mb-4 bg-white">';
            $penalty_results_html .= '<div class="font-semibold text-slate-900 mb-2">Transaction #' . $tid . ' — ' . htmlspecialchars($transaction['title']) . '</div>';
            $penalty_results_html .= '<form class="penalty-save-form" method="POST" action="transactions.php?action=manage_penalty" data-transaction-id="' . $tid . '">';
            $penalty_results_html .= '<input type="hidden" name="save_penalty_submit" value="1">';
            $penalty_results_html .= '<input type="hidden" name="transaction_id" value="' . $tid . '">';
            $penalty_results_html .= '<input type="hidden" name="penalty_institutional_id" value="' . htmlspecialchars($penalty_searched_id) . '">';
            $penalty_results_html .= '<div class="grid grid-cols-3 gap-4 mb-4">';
            $penalty_results_html .= '<div><label class="block text-xs font-medium text-slate-600 mb-1">Late (₱)</label><input type="number" name="late_penalty" value="' . $late . '" step="0.01" min="0" class="penalty-input w-full px-3 py-2 border rounded-lg" data-tid="' . $tid . '"></div>';
            $penalty_results_html .= '<div><label class="block text-xs font-medium text-slate-600 mb-1">Lost (₱)</label><input type="number" name="lost_penalty" value="' . $lost . '" step="0.01" min="0" class="penalty-input w-full px-3 py-2 border rounded-lg" data-tid="' . $tid . '"></div>';
            $penalty_results_html .= '<div><label class="block text-xs font-medium text-slate-600 mb-1">Damage (₱)</label><input type="number" name="damage_penalty" value="' . $damage . '" step="0.01" min="0" class="penalty-input w-full px-3 py-2 border rounded-lg" data-tid="' . $tid . '"></div>';
            $penalty_results_html .= '</div>';
            $penalty_results_html .= '<div class="flex justify-between items-center"><span class="text-sm font-semibold text-slate-700">Total: ₱<span class="penalty-total" data-tid="' . $tid . '">' . $total . '</span></span><button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm">Save Penalty</button></div>';
            $penalty_results_html .= '</form></div>';
        }
    }
}

if ($action == 'return' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['transaction_id'])) {
        $transaction_id = $_POST['transaction_id'];
        $return_date = date('Y-m-d');
        
        try {
            $conn->beginTransaction();

            $get_transaction = "SELECT book_id FROM borrowing_transaction 
                               WHERE transaction_id = :transaction_id 
                               AND return_date IS NULL 
                               AND status = 'Borrowed'
                               FOR UPDATE";
            
            $stmt = $conn->prepare($get_transaction);
            $stmt->execute([':transaction_id' => $transaction_id]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                throw new Exception('Transaction not found or already returned!');
            }
            
            $book_id = $transaction['book_id'];

            $sql = "UPDATE borrowing_transaction SET 
                    return_date = :return_date, 
                    status = 'Returned' 
                    WHERE transaction_id = :transaction_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':return_date' => $return_date,
                ':transaction_id' => $transaction_id
            ]);

            $update_book = "UPDATE book SET available_copies = available_copies + 1 WHERE book_id = :book_id";
            $update_stmt = $conn->prepare($update_book);
            $update_stmt->execute([':book_id' => $book_id]);
            
            $conn->commit();
            
            $alert_message = 'Book returned successfully!';
            $alert_type = 'success';
            $action = 'list';
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Database Error: ' . $e->getMessage();
            $alert_type = 'danger';
        }
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

    <div class="flex justify-end items-center mb-6">
        <div class="flex gap-3 flex-wrap">
            <a href="books.php?action=borrow" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                </svg>
                Borrow Book
            </a>
            <button onclick="openManageReservationsModal()" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Manage Reservations
            </button>
            <a href="?action=manage_penalty" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Manage Penalties
            </a>
            <button onclick="openReturnedHistoryModal()" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                Returned Books History
            </button>
        </div>
    </div>

    <?php if ($action == 'returned_history'): ?>
    <?php if (isset($_GET['ajax']) && $_GET['ajax'] == '1'): ?>
        <!-- AJAX Response for Returned History -->
        <?php
        try {
            $sql = "SELECT 
                        t.transaction_id,
                        b.title as book_title,
                        b.author as book_author,
                        u.full_name as borrower_name,
                        u.institutional_id,
                        t.borrow_date,
                        t.due_date,
                        t.return_date,
                        t.total_penalty,
                        CASE 
                            WHEN t.return_date > t.due_date THEN DATEDIFF(t.return_date, t.due_date)
                            ELSE 0 
                        END as days_late
                    FROM borrowing_transaction t
                    JOIN book b ON t.book_id = b.book_id
                    JOIN tbl_users u ON t.user_id = u.user_id
                    WHERE t.status = 'Returned'
                    ORDER BY t.return_date DESC
                    LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $returned_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total_records = count($returned_books);
            $total_penalty = array_sum(array_column($returned_books, 'total_penalty'));
            $total_late = count(array_filter($returned_books, function($b) { return $b['days_late'] > 0; }));
        ?>
        <div class="grid grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-100 rounded-lg p-4 text-center">
                <div class="text-xs text-slate-600">Total Returns</div>
                <div class="text-xl font-bold text-slate-900 mt-2"><?php echo $total_records; ?></div>
            </div>
            <div class="bg-green-100 rounded-lg p-4 text-center">
                <div class="text-xs text-slate-600">On Time</div>
                <div class="text-xl font-bold text-slate-900 mt-2"><?php echo $total_records - $total_late; ?></div>
            </div>
            <div class="bg-red-100 rounded-lg p-4 text-center">
                <div class="text-xs text-slate-600">Late Returns</div>
                <div class="text-xl font-bold text-slate-900 mt-2"><?php echo $total_late; ?></div>
            </div>
            <div class="bg-yellow-100 rounded-lg p-4 text-center">
                <div class="text-xs text-slate-600">With Penalties</div>
                <div class="text-xl font-bold text-slate-900 mt-2"><?php echo count(array_filter($returned_books, function($b) { return $b['total_penalty'] > 0; })); ?></div>
            </div>
            <div class="bg-cyan-100 rounded-lg p-4 text-center">
                <div class="text-xs text-slate-600">Total Penalties</div>
                <div class="text-xl font-bold text-slate-900 mt-2">₱<?php echo number_format($total_penalty, 2); ?></div>
            </div>
        </div>
        <?php if (empty($returned_books)): ?>
            <div class="text-center py-8 text-slate-500">No returned books found.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Book</th>
                            <th class="px-4 py-2 text-left">Borrower</th>
                            <th class="px-4 py-2 text-left">Return Date</th>
                            <th class="px-4 py-2 text-left">Penalty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($returned_books as $book): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo $book['transaction_id']; ?></td>
                            <td class="px-4 py-2">
                                <div class="font-medium"><?php echo htmlspecialchars($book['book_title']); ?></div>
                                <div class="text-xs text-slate-500"><?php echo htmlspecialchars($book['book_author']); ?></div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="font-medium"><?php echo htmlspecialchars($book['borrower_name']); ?></div>
                                <div class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($book['institutional_id']); ?></div>
                            </td>
                            <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($book['return_date'])); ?></td>
                            <td class="px-4 py-2">
                                <?php if($book['total_penalty'] > 0): ?>
                                    <span class="text-red-600 font-semibold">₱<?php echo number_format($book['total_penalty'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-green-600">₱0.00</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php
        } catch (PDOException $e) {
            echo '<div class="text-red-600">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
        ?>
    <?php else: ?>
    <!-- Returned Books History - Table with DataTables -->
    <?php
    $returned_history_error = '';
    $returned_books = [];
    try {
        $sql = "SELECT 
                    t.transaction_id,
                    b.title as book_title,
                    b.author as book_author,
                    b.isbn,
                    u.full_name as borrower_name,
                    u.institutional_id,
                    u.rfid_number,
                    u.grade_section,
                    u.user_type,
                    l.full_name as librarian_name,
                    t.borrow_date,
                    t.due_date,
                    t.return_date,
                    t.late_penalty,
                    t.lost_penalty,
                    t.damage_penalty,
                    t.total_penalty,
                    t.status,
                    t.penalty_updated_at,
                    DATEDIFF(t.return_date, t.borrow_date) as days_borrowed,
                    CASE 
                        WHEN t.return_date > t.due_date THEN DATEDIFF(t.return_date, t.due_date)
                        ELSE 0 
                    END as days_late
                FROM borrowing_transaction t
                JOIN book b ON t.book_id = b.book_id
                JOIN tbl_users u ON t.user_id = u.user_id
                LEFT JOIN librarian l ON t.librarian_id = l.librarian_id
                WHERE t.status = 'Returned'";
        $params = [];
        if (!empty($_GET['search_title'])) { $sql .= " AND b.title LIKE ?"; $params[] = '%' . $_GET['search_title'] . '%'; }
        if (!empty($_GET['search_borrower'])) { $sql .= " AND u.full_name LIKE ?"; $params[] = '%' . $_GET['search_borrower'] . '%'; }
        if (!empty($_GET['search_rfid'])) { $sql .= " AND u.rfid_number LIKE ?"; $params[] = '%' . $_GET['search_rfid'] . '%'; }
        if (!empty($_GET['search_section'])) { $sql .= " AND u.grade_section LIKE ?"; $params[] = '%' . $_GET['search_section'] . '%'; }
        if (!empty($_GET['date_from'])) { $sql .= " AND t.return_date >= ?"; $params[] = $_GET['date_from']; }
        if (!empty($_GET['date_to'])) { $sql .= " AND t.return_date <= ?"; $params[] = $_GET['date_to'] . ' 23:59:59'; }
        $sql .= " ORDER BY t.return_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $returned_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_records = count($returned_books);
        $total_late_penalty = $total_lost_penalty = $total_damage_penalty = $total_penalty = 0;
        foreach ($returned_books as $book) {
            $total_late_penalty += $book['late_penalty'];
            $total_lost_penalty += $book['lost_penalty'];
            $total_damage_penalty += $book['damage_penalty'];
            $total_penalty += $book['total_penalty'];
        }
    } catch (PDOException $e) {
        $returned_books = [];
        $total_records = $total_late_penalty = $total_lost_penalty = $total_damage_penalty = $total_penalty = 0;
        $returned_history_error = $e->getMessage();
    }
    ?>
    <div class="space-y-6">
        <!-- Toolbar: filters + export -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex flex-wrap items-end gap-4 mb-4">
                <form method="GET" action="" class="flex flex-wrap items-end gap-3 flex-1">
                    <input type="hidden" name="action" value="returned_history">
                    <div>
                        <label for="search_title" class="block text-xs font-medium text-slate-600 mb-1">Book Title</label>
                        <input type="text" id="search_title" name="search_title" placeholder="Title" value="<?php echo htmlspecialchars($_GET['search_title'] ?? ''); ?>"
                               class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-40 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="search_borrower" class="block text-xs font-medium text-slate-600 mb-1">Borrower</label>
                        <input type="text" id="search_borrower" name="search_borrower" placeholder="Borrower" value="<?php echo htmlspecialchars($_GET['search_borrower'] ?? ''); ?>"
                               class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-40 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="search_rfid" class="block text-xs font-medium text-slate-600 mb-1">RFID</label>
                        <input type="text" id="search_rfid" name="search_rfid" placeholder="RFID" value="<?php echo htmlspecialchars($_GET['search_rfid'] ?? ''); ?>"
                               class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-32 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="search_section" class="block text-xs font-medium text-slate-600 mb-1">Grade/Section</label>
                        <input type="text" id="search_section" name="search_section" placeholder="Section" value="<?php echo htmlspecialchars($_GET['search_section'] ?? ''); ?>"
                               class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-32 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="date_from" class="block text-xs font-medium text-slate-600 mb-1">From</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>"
                               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-xs font-medium text-slate-600 mb-1">To</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>"
                               class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Search
                    </button>
                    <a href="?action=returned_history" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium">Reset</a>
                </form>
                <a href="?action=returned_history&export_returned=excel" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </a>
            </div>
        </div>

        <?php if (!empty($returned_history_error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">Error loading returned books: <?php echo htmlspecialchars($returned_history_error); ?></div>
        <?php elseif (empty($returned_books)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <p class="text-slate-500 text-lg">No returned books found.</p>
                <p class="text-slate-400 text-sm mt-2">Try adjusting filters or check back later.</p>
            </div>
        <?php else: ?>
            <!-- Summary cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-xl shadow p-4 text-center">
                    <div class="text-xs font-medium text-slate-500 uppercase">Total Returns</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo $total_records; ?></div>
                </div>
                <div class="bg-emerald-50 rounded-xl shadow p-4 text-center">
                    <div class="text-xs font-medium text-emerald-700 uppercase">Late Fees</div>
                    <div class="text-lg font-bold text-emerald-800">₱<?php echo number_format($total_late_penalty, 2); ?></div>
                </div>
                <div class="bg-red-50 rounded-xl shadow p-4 text-center">
                    <div class="text-xs font-medium text-red-700 uppercase">Lost Fees</div>
                    <div class="text-lg font-bold text-red-800">₱<?php echo number_format($total_lost_penalty, 2); ?></div>
                </div>
                <div class="bg-amber-50 rounded-xl shadow p-4 text-center">
                    <div class="text-xs font-medium text-amber-700 uppercase">Damage Fees</div>
                    <div class="text-lg font-bold text-amber-800">₱<?php echo number_format($total_damage_penalty, 2); ?></div>
                </div>
                <div class="bg-blue-50 rounded-xl shadow p-4 text-center col-span-2 lg:col-span-1">
                    <div class="text-xs font-medium text-blue-700 uppercase">Total Penalties</div>
                    <div class="text-xl font-bold text-blue-800">₱<?php echo number_format($total_penalty, 2); ?></div>
                </div>
            </div>

            <!-- Data table -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="returnedBooksTable" class="data-table w-full">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Book</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Borrower</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">RFID / Section</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Borrow / Due / Return</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Penalties</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Processed By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($returned_books as $book): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo (int)$book['transaction_id']; ?></td>
                                <td class="px-6 py-4 text-sm text-slate-900">
                                    <div class="font-medium"><?php echo htmlspecialchars($book['book_title']); ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($book['book_author']); ?> · <?php echo htmlspecialchars($book['isbn']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900">
                                    <div class="font-medium"><?php echo htmlspecialchars($book['borrower_name']); ?></div>
                                    <div class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($book['institutional_id']); ?> · <?php echo htmlspecialchars($book['user_type']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php if (!empty($book['rfid_number'])): ?><div><?php echo htmlspecialchars($book['rfid_number']); ?></div><?php endif; ?>
                                    <?php if (!empty($book['grade_section'])): ?><div class="text-xs"><?php echo htmlspecialchars($book['grade_section']); ?></div><?php endif; ?>
                                    <?php if (empty($book['rfid_number']) && empty($book['grade_section'])): ?><span class="text-slate-400">—</span><?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div>Borrowed: <?php echo date('M d, Y', strtotime($book['borrow_date'])); ?></div>
                                    <div>Due: <?php echo date('M d, Y', strtotime($book['due_date'])); ?></div>
                                    <div class="font-medium">Returned: <?php echo date('M d, Y', strtotime($book['return_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-slate-100 text-slate-700"><?php echo (int)$book['days_borrowed']; ?> days</span>
                                    <?php if ((int)$book['days_late'] > 0): ?>
                                        <span class="ml-1 px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Late <?php echo (int)$book['days_late']; ?>d</span>
                                    <?php else: ?>
                                        <span class="ml-1 px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">On time</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($book['total_penalty'] > 0): ?>
                                        <div class="text-xs">
                                            <div>Late: ₱<?php echo number_format($book['late_penalty'], 2); ?></div>
                                            <div>Lost: ₱<?php echo number_format($book['lost_penalty'], 2); ?></div>
                                            <div>Damage: ₱<?php echo number_format($book['damage_penalty'], 2); ?></div>
                                            <div class="font-semibold text-slate-900">Total: ₱<?php echo number_format($book['total_penalty'], 2); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">No penalties</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php echo htmlspecialchars($book['librarian_name'] ?? 'N/A'); ?>
                                    <?php if (!empty($book['penalty_updated_at'])): ?>
                                        <div class="text-xs text-slate-400"><?php echo date('M d, Y', strtotime($book['penalty_updated_at'])); ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php elseif ($action == 'manage_reservations'): ?>
    <?php if (isset($_GET['ajax']) && $_GET['ajax'] == '1'): ?>
        <!-- AJAX Response for Manage Reservations -->
        <?php
        try {
            $sql = "SELECT bt.*, b.title, b.author, b.image_filename,
                           u.full_name as user_name, u.institutional_id, u.rfid_number, u.grade_section,
                           l.full_name as librarian_name
                    FROM borrowing_transaction bt
                    JOIN book b ON bt.book_id = b.book_id
                    JOIN tbl_users u ON bt.user_id = u.user_id
                    LEFT JOIN librarian l ON bt.librarian_id = l.librarian_id
                    WHERE bt.status = 'Reserved'
                    ORDER BY bt.borrow_date ASC
                    LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = count($reservations);
            $active = count(array_filter($reservations, function($r) {
                $deadline = strtotime($r['borrow_date'] . ' + 3 days');
                return time() <= $deadline;
            }));
            $expired = $total - $active;
            $today = count(array_filter($reservations, function($r) {
                return date('Y-m-d') == $r['borrow_date'];
            }));
        ?>
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-cyan-100 rounded-lg p-4 text-center">
                <div class="text-sm text-slate-600">Total Reservations</div>
                <div class="text-2xl font-bold text-slate-900 mt-2"><?php echo $total; ?></div>
            </div>
            <div class="bg-yellow-100 rounded-lg p-4 text-center">
                <div class="text-sm text-slate-600">Active</div>
                <div class="text-2xl font-bold text-slate-900 mt-2"><?php echo $active; ?></div>
            </div>
            <div class="bg-red-100 rounded-lg p-4 text-center">
                <div class="text-sm text-slate-600">Expired</div>
                <div class="text-2xl font-bold text-slate-900 mt-2"><?php echo $expired; ?></div>
            </div>
            <div class="bg-slate-100 rounded-lg p-4 text-center">
                <div class="text-sm text-slate-600">Today's Pickups</div>
                <div class="text-2xl font-bold text-slate-900 mt-2"><?php echo $today; ?></div>
            </div>
        </div>
        <?php if (empty($reservations)): ?>
            <div class="text-center py-8 text-slate-500">No pending reservations found.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Book</th>
                            <th class="px-4 py-2 text-left">User</th>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $res): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo $res['transaction_id']; ?></td>
                            <td class="px-4 py-2">
                                <div class="font-medium"><?php echo htmlspecialchars($res['title']); ?></div>
                                <div class="text-xs text-slate-500"><?php echo htmlspecialchars($res['author']); ?></div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="font-medium"><?php echo htmlspecialchars($res['user_name']); ?></div>
                                <div class="text-xs text-slate-500">ID: <?php echo htmlspecialchars($res['institutional_id']); ?></div>
                            </td>
                            <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($res['borrow_date'])); ?></td>
                            <td class="px-4 py-2">
                                <button onclick="openConfirmPickupModal(<?php echo $res['transaction_id']; ?>, '<?php echo htmlspecialchars(addslashes($res['title'])); ?>', '<?php echo htmlspecialchars(addslashes($res['user_name'])); ?>')" 
                                        class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs mr-1">
                                    Confirm
                                </button>
                                <button onclick="openCancelReservationModal(<?php echo (int)$res['transaction_id']; ?>, <?php echo json_encode($res['title']); ?>, <?php echo json_encode($res['user_name']); ?>)" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                                    Cancel
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php
        } catch (PDOException $e) {
            echo '<div class="text-red-600">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit();
        ?>
    <?php else: ?>
    <!-- Manage Reservations Section -->
    <div class="card">
        <div class="card-header">
            <h4><i class="bi bi-clock-history"></i> Manage Reservations</h4>
        </div>
        <div class="card-body">
            <?php
            try {
                
                $sql = "SELECT bt.*, b.title, b.author, b.image_filename,
                               u.full_name as user_name, u.institutional_id, u.rfid_number, u.grade_section,
                               l.full_name as librarian_name
                        FROM borrowing_transaction bt
                        JOIN book b ON bt.book_id = b.book_id
                        JOIN tbl_users u ON bt.user_id = u.user_id
                        LEFT JOIN librarian l ON bt.librarian_id = l.librarian_id
                        WHERE bt.status = 'Reserved'
                        ORDER BY bt.borrow_date ASC";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($reservations)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle me-2"></i>
                        No pending reservations found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th>RFID & Grade/Section</th>
                                    <th>Reservation Date</th>
                                    <th>Pickup By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservations as $res): 
                                    $pickup_deadline = date('Y-m-d', strtotime($res['borrow_date'] . ' + 3 days'));
                                    $is_expired = (strtotime(date('Y-m-d')) > strtotime($pickup_deadline));
                                ?>
                                <tr>
                                    <td><?php echo $res['transaction_id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($res['image_filename']): ?>
                                                <img src="uploads/books/<?php echo htmlspecialchars($res['image_filename']); ?>" 
                                                     class="rounded me-2" width="40" height="60" style="object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($res['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($res['author']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($res['user_name']); ?></strong><br>
                                        <small class="text-muted">ID: <?php echo htmlspecialchars($res['institutional_id']); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($res['rfid_number'])): ?>
                                            <span class="badge bg-secondary mb-1 d-block">
                                                <i class="bi bi-credit-card"></i> <?php echo htmlspecialchars($res['rfid_number']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($res['grade_section'])): ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-mortarboard"></i> <?php echo htmlspecialchars($res['grade_section']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($res['borrow_date'])); ?><br>
                                        <small class="<?php echo $is_expired ? 'text-danger' : 'text-warning'; ?>">
                                            Pickup by: <?php echo date('M d, Y', strtotime($pickup_deadline)); ?>
                                            <?php if($is_expired): ?>
                                                <br><small class="text-danger">Expired!</small>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if($res['librarian_name']): ?>
                                            <?php echo htmlspecialchars($res['librarian_name']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Reserved</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Confirm Pickup Button -->
                                            <button type="button" 
                                                    class="btn btn-success btn-sm confirm-pickup-btn"
                                                    data-transaction-id="<?php echo $res['transaction_id']; ?>"
                                                    data-book-title="<?php echo htmlspecialchars($res['title']); ?>"
                                                    data-user-name="<?php echo htmlspecialchars($res['user_name']); ?>">
                                                <i class="bi bi-check-circle"></i> Confirm Pickup
                                            </button>
                                            
                                            <!-- Cancel Reservation Button -->
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm cancel-reservation-btn"
                                                    data-transaction-id="<?php echo $res['transaction_id']; ?>"
                                                    data-book-title="<?php echo htmlspecialchars($res['title']); ?>"
                                                    data-user-name="<?php echo htmlspecialchars($res['user_name']); ?>">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Reservation Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>Total Reservations</h5>
                                    <h3><?php echo count($reservations); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>Active</h5>
                                    <h3><?php echo count(array_filter($reservations, function($r) {
                                        $deadline = strtotime($r['borrow_date'] . ' + 3 days');
                                        return time() <= $deadline;
                                    })); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>Expired</h5>
                                    <h3><?php echo count(array_filter($reservations, function($r) {
                                        $deadline = strtotime($r['borrow_date'] . ' + 3 days');
                                        return time() > $deadline;
                                    })); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h5>Today's Pickups</h5>
                                    <h3><?php echo count(array_filter($reservations, function($r) {
                                        return date('Y-m-d') == $r['borrow_date'];
                                    })); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php } catch (PDOException $e) { ?>
                <div class="alert alert-danger">Error loading reservations: <?php echo htmlspecialchars($e->getMessage()); ?></div>
            <?php } ?>
        </div>
    </div>
    <?php endif; ?>

    <?php elseif ($action == 'manage_penalty'): ?>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden p-6">
        <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Manage Penalties
        </h3>
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">Users with Penalties</h4>
            <div class="rounded-lg border border-slate-200 overflow-hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Name</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">ID</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600">Grade/Section</th>
                            <th class="px-3 py-2 text-right font-medium text-slate-600">Balance Due</th>
                            <th class="px-3 py-2 text-center font-medium text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        <?php if (empty($users_with_penalties)): ?>
                        <tr><td colspan="5" class="px-3 py-4 text-center text-slate-500">No users with penalties</td></tr>
                        <?php else: ?>
                        <?php foreach ($users_with_penalties as $up): 
                            $balance_due = abs((float)($up['balance'] ?? 0));
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 text-slate-800"><?php echo htmlspecialchars($up['full_name'] ?? ''); ?></td>
                            <td class="px-3 py-2 text-slate-600"><?php echo htmlspecialchars($up['institutional_id'] ?? ''); ?></td>
                            <td class="px-3 py-2 text-slate-600"><?php echo htmlspecialchars($up['grade_section'] ?? '-'); ?></td>
                            <td class="px-3 py-2 text-right font-medium text-red-600">₱<?php echo number_format($balance_due, 2); ?></td>
                            <td class="px-3 py-2 text-center">
                                <a href="?action=manage_penalty&search_id=<?php echo htmlspecialchars($up['institutional_id'] ?? '', ENT_QUOTES); ?>" class="text-yellow-600 hover:text-yellow-700 font-medium text-xs mr-2">Manage</a>
                                <?php if (!empty($up['receipt_txn_id'])): ?>
                                <a href="generate_penalty_receipt.php?transaction_id=<?php echo (int)$up['receipt_txn_id']; ?>" target="_blank" class="text-slate-600 hover:text-slate-700 font-medium text-xs">Receipt</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($penalty_search_error)): ?>
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm mb-4"><?php echo htmlspecialchars($penalty_search_error); ?></div>
        <?php endif; ?>
        <div id="penaltyResults">
            <?php echo $penalty_results_html; ?>
        </div>
        <div class="mt-6">
            <a href="?action=list" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors inline-block">Back to Transactions</a>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($action != 'borrow' && $action != 'return' && $action != 'returned_history' && $action != 'manage_reservations' && $action != 'manage_penalty'): ?>
        <!-- Transactions List -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="transactionsTable" class="data-table w-full">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">RFID & Grade/Section</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Borrow Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Return Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Total Penalty</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php
                        try {
                            $sql = "
                                SELECT t.*, b.title, b.available_copies, b.number_of_copies,
                                       u.institutional_id, u.full_name as borrower_name, u.rfid_number, u.grade_section,
                                       l.full_name as librarian_name
                                FROM borrowing_transaction t
                                JOIN book b ON t.book_id = b.book_id
                                JOIN tbl_users u ON t.user_id = u.user_id
                                LEFT JOIN librarian l ON t.librarian_id = l.librarian_id
                                WHERE t.status IN ('Borrowed', 'Reserved', 'Overdue')
                                ORDER BY t.borrow_date DESC
                                LIMIT 100
                            ";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($transactions)):
                        ?>
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No active transactions found</p>
                                    <p class="text-sm">All transactions are completed or no books are currently borrowed</p>
                                </div>
                            </td>
                        </tr>
                        <?php
                            else:
                                foreach ($transactions as $row): 
                                    $today = date('Y-m-d');
                                    $due_date = $row['due_date'];
                                    $status = $row['status'];

                                    $status_class = [
                                        'Reserved' => 'bg-yellow-100 text-yellow-800',
                                        'Borrowed' => 'bg-blue-100 text-blue-800',
                                        'Returned' => 'bg-green-100 text-green-800',
                                        'Overdue' => 'bg-red-100 text-red-800'
                                    ];
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                <div class="font-medium"><?php echo htmlspecialchars($row['title']); ?></div>
                                <div class="text-xs text-slate-500 mt-1">
                                    Copies: <?php echo htmlspecialchars($row['available_copies']); ?>/<?php echo htmlspecialchars($row['number_of_copies']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                <div class="font-medium"><?php echo htmlspecialchars($row['borrower_name']); ?></div>
                                <div class="text-xs text-slate-500 mt-1">
                                    ID: <?php echo htmlspecialchars($row['institutional_id']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?php if (!empty($row['rfid_number'])): ?>
                                    <div class="flex items-center gap-1 mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <?php echo htmlspecialchars($row['rfid_number']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($row['grade_section'])): ?>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                        </svg>
                                        <?php echo htmlspecialchars($row['grade_section']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div><?php echo date('M d, Y', strtotime($row['due_date'])); ?></div>
                                <?php if ($status == 'Overdue'): ?>
                                    <div class="text-xs text-red-600 mt-1 font-semibold">Overdue!</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php 
                                if ($row['return_date']) {
                                    echo date('M d, Y', strtotime($row['return_date']));
                                } else {
                                    echo '<span class="text-slate-400">Not returned</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php 
                                $total_penalty = $row['total_penalty'] ?? 0;
                                if ($total_penalty > 0):
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    ₱<?php echo number_format($total_penalty, 2); ?>
                                </span>
                                <?php 
                                else:
                                    echo '<span class="text-slate-400">₱0.00</span>';
                                endif; 
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_class[$status] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <?php if ($row['status'] == 'Reserved'): ?>
                                        <a href="?action=manage_reservations" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Manage
                                        </a>
                                    <?php elseif ($row['status'] == 'Borrowed' || $row['status'] == 'Overdue'): ?>
                                        <button type="button" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors return-book-btn"
                                                data-transaction-id="<?php echo $row['transaction_id']; ?>"
                                                data-book-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                data-borrower-name="<?php echo htmlspecialchars($row['borrower_name']); ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Return
                                        </button>
                                        <a href="?action=manage_penalty&search_id=<?php echo htmlspecialchars($row['institutional_id']); ?>" 
                                           class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Penalty
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            endif;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='10' class='px-6 py-4 text-center text-red-600'>Error loading transactions: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Confirmation Modals -->
    
    <!-- Confirm Pickup Modal -->
    <div id="confirmPickupModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[85vh] overflow-y-auto">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Confirm Book Pickup</h3>
                <button onclick="closeConfirmPickupModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <p><strong>Book:</strong> <span id="pickupBookTitle"></span></p>
                <p class="mt-2"><strong>User:</strong> <span id="pickupUserName"></span></p>
            </div>
            <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                <button onclick="closeConfirmPickupModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                <form method="POST" action="transactions.php?action=manage_reservations" style="display: inline;">
                    <input type="hidden" name="transaction_id" id="confirmPickupTransactionId">
                    <button type="submit" name="confirm_reservation" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        Confirm Pickup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Reservation Modal -->
    <div id="cancelReservationModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[85vh] overflow-y-auto">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Cancel Reservation</h3>
                <button onclick="closeCancelReservationModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <p><strong>Book:</strong> <span id="cancelBookTitle"></span></p>
                <p class="mt-2"><strong>User:</strong> <span id="cancelUserName"></span></p>
            </div>
            <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                <button onclick="closeCancelReservationModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                <form method="POST" action="transactions.php?action=manage_reservations" style="display: inline;">
                    <input type="hidden" name="transaction_id" id="cancelReservationTransactionId">
                    <button type="submit" name="cancel_reservation" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        Yes, Cancel Reservation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Return Book Modal -->
    <div id="returnBookModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[85vh] overflow-y-auto">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Return Book</h3>
                <button onclick="closeReturnBookModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <p><strong>Book:</strong> <span id="returnBookTitle"></span></p>
                <p class="mt-2"><strong>Borrower:</strong> <span id="returnBorrowerName"></span></p>
            </div>
            <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                <button onclick="closeReturnBookModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
                <form method="POST" action="transactions.php?action=return" style="display: inline;">
                    <input type="hidden" name="transaction_id" id="returnTransactionId">
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        Confirm Return
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Modal functions - must be global for onclick handlers
    function openConfirmPickupModal(transactionId, bookTitle, userName) {
        document.getElementById('pickupBookTitle').textContent = bookTitle;
        document.getElementById('pickupUserName').textContent = userName;
        document.getElementById('confirmPickupTransactionId').value = transactionId;
        document.getElementById('confirmPickupModal').classList.remove('hidden');
        document.getElementById('confirmPickupModal').classList.add('flex');
    }
    
    function closeConfirmPickupModal() {
        document.getElementById('confirmPickupModal').classList.add('hidden');
        document.getElementById('confirmPickupModal').classList.remove('flex');
    }
    
    function openCancelReservationModal(transactionId, bookTitle, userName) {
        document.getElementById('cancelBookTitle').textContent = bookTitle;
        document.getElementById('cancelUserName').textContent = userName;
        document.getElementById('cancelReservationTransactionId').value = transactionId;
        document.getElementById('cancelReservationModal').classList.remove('hidden');
        document.getElementById('cancelReservationModal').classList.add('flex');
    }
    
    function closeCancelReservationModal() {
        document.getElementById('cancelReservationModal').classList.add('hidden');
        document.getElementById('cancelReservationModal').classList.remove('flex');
    }
    
    function openReturnBookModal(transactionId, bookTitle, borrowerName) {
        document.getElementById('returnBookTitle').textContent = bookTitle;
        document.getElementById('returnBorrowerName').textContent = borrowerName;
        document.getElementById('returnTransactionId').value = transactionId;
        document.getElementById('returnBookModal').classList.remove('hidden');
        document.getElementById('returnBookModal').classList.add('flex');
    }
    
    function closeReturnBookModal() {
        document.getElementById('returnBookModal').classList.add('hidden');
        document.getElementById('returnBookModal').classList.remove('flex');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle confirm pickup button clicks
        document.querySelectorAll('.confirm-pickup-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const transactionId = this.getAttribute('data-transaction-id');
                const bookTitle = this.getAttribute('data-book-title');
                const userName = this.getAttribute('data-user-name');
                openConfirmPickupModal(transactionId, bookTitle, userName);
            });
        });
        
        // Handle cancel reservation button clicks
        document.querySelectorAll('.cancel-reservation-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const transactionId = this.getAttribute('data-transaction-id');
                const bookTitle = this.getAttribute('data-book-title');
                const userName = this.getAttribute('data-user-name');
                openCancelReservationModal(transactionId, bookTitle, userName);
            });
        });
        
        // Handle return book button clicks
        document.querySelectorAll('.return-book-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const transactionId = this.getAttribute('data-transaction-id');
                const bookTitle = this.getAttribute('data-book-title');
                const borrowerName = this.getAttribute('data-borrower-name');
                openReturnBookModal(transactionId, bookTitle, borrowerName);
            });
        });
        
        // Close modals when clicking outside
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if (this.id === 'confirmPickupModal') closeConfirmPickupModal();
                    if (this.id === 'cancelReservationModal') closeCancelReservationModal();
                    if (this.id === 'returnBookModal') closeReturnBookModal();
                }
            });
        });
    });
    </script>

    <!-- Borrow Book Modal -->
    <div id="borrowBookModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-12 pb-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 h-[85vh] flex flex-col">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-shrink-0">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                    </svg>
                    Borrow Book for User
                </h3>
                <button onclick="closeBorrowBookModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 min-h-0">
                <iframe id="borrowBookIframe" src="about:blank" class="w-full h-full border-0 rounded-b-xl" title="Borrow Book"></iframe>
            </div>
        </div>
    </div>

    <!-- Manage Reservations Modal -->
    <div id="manageReservationsModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Manage Reservations
                </h3>
                <button onclick="closeManageReservationsModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="reservationsContent" class="space-y-4">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-600 mx-auto"></div>
                        <p class="mt-4 text-slate-600">Loading reservations...</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button onclick="closeManageReservationsModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Close</button>
                    <a href="?action=manage_reservations" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors">View Full Page</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Returned Books History Modal -->
    <div id="returnedHistoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Returned Books History
                </h3>
                <button onclick="closeReturnedHistoryModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="returnedHistoryContent" class="space-y-4">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-600 mx-auto"></div>
                        <p class="mt-4 text-slate-600">Loading history...</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button onclick="closeReturnedHistoryModal()" class="px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Close</button>
                    <a href="?action=returned_history" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg transition-colors">View Full Page</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Modal functions for new modals
    function openManageReservationsModal() {
        document.getElementById('manageReservationsModal').classList.remove('hidden');
        document.getElementById('manageReservationsModal').classList.add('flex');
        loadReservations();
    }
    
    function closeManageReservationsModal() {
        document.getElementById('manageReservationsModal').classList.add('hidden');
        document.getElementById('manageReservationsModal').classList.remove('flex');
    }
    
    function openReturnedHistoryModal() {
        document.getElementById('returnedHistoryModal').classList.remove('hidden');
        document.getElementById('returnedHistoryModal').classList.add('flex');
        loadReturnedHistory();
    }
    
    function closeReturnedHistoryModal() {
        document.getElementById('returnedHistoryModal').classList.add('hidden');
        document.getElementById('returnedHistoryModal').classList.remove('flex');
    }
    
    // Load reservations data
    function loadReservations() {
        fetch('transactions.php?action=manage_reservations&ajax=1')
            .then(response => response.text())
            .then(html => {
                // Extract table content from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const table = doc.querySelector('.table-responsive table') || doc.querySelector('table');
                const stats = doc.querySelectorAll('.card');
                
                let content = '';
                
                if (stats.length > 0) {
                    content += '<div class="grid grid-cols-4 gap-4 mb-6">';
                    stats.forEach(card => {
                        const title = card.querySelector('h5')?.textContent || '';
                        const value = card.querySelector('h3')?.textContent || '';
                        const bgColor = card.classList.contains('bg-info') ? 'bg-cyan-100' : 
                                       card.classList.contains('bg-warning') ? 'bg-yellow-100' :
                                       card.classList.contains('bg-danger') ? 'bg-red-100' : 'bg-slate-100';
                        content += `<div class="${bgColor} rounded-lg p-4 text-center">
                            <div class="text-sm text-slate-600">${title}</div>
                            <div class="text-2xl font-bold text-slate-900 mt-2">${value}</div>
                        </div>`;
                    });
                    content += '</div>';
                }
                
                if (table) {
                    content += '<div class="overflow-x-auto">' + table.outerHTML + '</div>';
                } else {
                    content += '<div class="text-center py-8 text-slate-500">No pending reservations found.</div>';
                }
                
                document.getElementById('reservationsContent').innerHTML = content;
            })
            .catch(error => {
                document.getElementById('reservationsContent').innerHTML = 
                    '<div class="text-center py-8 text-red-600">Error loading reservations. <a href="?action=manage_reservations" class="text-blue-600 underline">View full page</a></div>';
            });
    }
    
    // Load returned history data
    function loadReturnedHistory() {
        fetch('transactions.php?action=returned_history&ajax=1')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const stats = doc.querySelectorAll('.card.bg-primary, .card.bg-success, .card.bg-danger, .card.bg-warning, .card.bg-info');
                const table = doc.querySelector('.table-responsive table') || doc.querySelector('table');
                
                let content = '';
                
                if (stats.length > 0) {
                    content += '<div class="grid grid-cols-5 gap-4 mb-6">';
                    stats.forEach(card => {
                        const title = card.querySelector('h6')?.textContent || '';
                        const value = card.querySelector('h3, h4, h5')?.textContent || '';
                        const bgColor = card.classList.contains('bg-primary') ? 'bg-blue-100' : 
                                       card.classList.contains('bg-success') ? 'bg-green-100' :
                                       card.classList.contains('bg-danger') ? 'bg-red-100' :
                                       card.classList.contains('bg-warning') ? 'bg-yellow-100' : 'bg-cyan-100';
                        content += `<div class="${bgColor} rounded-lg p-4 text-center">
                            <div class="text-xs text-slate-600">${title}</div>
                            <div class="text-xl font-bold text-slate-900 mt-2">${value}</div>
                        </div>`;
                    });
                    content += '</div>';
                }
                
                if (table) {
                    // Limit to first 10 rows for modal
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 10) {
                        const limitedTable = table.cloneNode(true);
                        const tbody = limitedTable.querySelector('tbody');
                        Array.from(tbody.children).slice(10).forEach(row => row.remove());
                        content += '<div class="overflow-x-auto max-h-96 overflow-y-auto">' + limitedTable.outerHTML + '</div>';
                        content += '<p class="text-sm text-slate-500 mt-4 text-center">Showing first 10 results. <a href="?action=returned_history" class="text-blue-600 underline">View all</a></p>';
                    } else {
                        content += '<div class="overflow-x-auto">' + table.outerHTML + '</div>';
                    }
                } else {
                    content += '<div class="text-center py-8 text-slate-500">No returned books found.</div>';
                }
                
                document.getElementById('returnedHistoryContent').innerHTML = content;
            })
            .catch(error => {
                document.getElementById('returnedHistoryContent').innerHTML = 
                    '<div class="text-center py-8 text-red-600">Error loading history. <a href="?action=returned_history" class="text-blue-600 underline">View full page</a></div>';
            });
    }
    
    // Penalty modal: search and save
    let currentPenaltyInstitutionalId = '';
    
    function loadPenaltyResults(institutionalId) {
        const formData = new FormData();
        formData.append('search_penalty_user_submit', '1');
        formData.append('penalty_institutional_id', institutionalId);
        formData.append('ajax', '1');
        fetch('transactions.php?action=manage_penalty', { method: 'POST', body: formData })
            .then(r => r.text())
            .then(html => {
                document.getElementById('penaltyResults').innerHTML = html;
                currentPenaltyInstitutionalId = institutionalId;
                penaltyResultsUpdateTotals();
            })
            .catch(() => {
                document.getElementById('penaltyResults').innerHTML = 
                    '<div class="text-red-600 p-4 bg-red-50 rounded-lg">Error searching user.</div>';
            });
    }
    
    function penaltyResultsUpdateTotals() {
        document.querySelectorAll('#penaltyResults .penalty-save-form').forEach(form => {
            const tid = form.getAttribute('data-transaction-id');
            const inputs = form.querySelectorAll('.penalty-input[data-tid="' + tid + '"]');
            let total = 0;
            inputs.forEach(inp => { total += parseFloat(inp.value) || 0; });
            const el = form.querySelector('.penalty-total[data-tid="' + tid + '"]');
            if (el) el.textContent = total.toFixed(2);
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const penaltyResults = document.getElementById('penaltyResults');
        if (penaltyResults) {
            penaltyResults.addEventListener('input', function(e) {
                if (e.target.classList.contains('penalty-input')) penaltyResultsUpdateTotals();
            });
            penaltyResults.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form.classList.contains('penalty-save-form')) return;
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('ajax', '1');
                fetch('transactions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.institutional_id) {
                            loadPenaltyResults(data.institutional_id);
                        }
                    })
                    .catch(() => {});
            });
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('open_borrow') === '1') {
            openBorrowBookModal();
            window.history.replaceState({}, '', window.location.pathname);
        }
        
        // Close modals when clicking outside
        ['borrowBookModal', 'manageReservationsModal', 'returnedHistoryModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        if (modalId === 'manageReservationsModal') closeManageReservationsModal();
                        if (modalId === 'returnedHistoryModal') closeReturnedHistoryModal();
                    }
                });
            }
        });
    });
    </script>
</div>

<?php require_once 'footer_unified.php'; ?>