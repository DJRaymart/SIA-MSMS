<?php
require_once 'db.php';
require_once 'header_unified.php';

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$action = $_GET['action'] ?? 'list';
$alert_message = '';
$alert_type = '';

$smtpUser = trim((string) getenv('MSMS_SMTP_USER'));
$smtpPass = trim((string) getenv('MSMS_SMTP_PASSWORD'));
$smtpFrom = trim((string) getenv('MSMS_SMTP_FROM')) ?: $smtpUser;
$smtpReplyTo = trim((string) getenv('MSMS_SMTP_REPLY_TO')) ?: $smtpUser;

function sendNegativeBalanceNotification($user_email, $user_name, $balance, $user_id, $conn, $is_manual = false) {
    global $smtpUser, $smtpPass, $smtpFrom, $smtpReplyTo;

    if ($smtpUser === '' || $smtpPass === '' || $smtpFrom === '') {
        error_log('SMTP credentials missing. Set MSMS_SMTP_USER, MSMS_SMTP_PASSWORD, and MSMS_SMTP_FROM.');
        return false;
    }

    try {
        
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtpFrom, 'Library Management System');
        $mail->addAddress($user_email, $user_name);

        $mail->addReplyTo($smtpReplyTo, 'Library Support');

        $mail->isHTML(true);
        $subject = $is_manual ? 'Balance Reminder - Action Required' : 'Balance Reminder - Library Management System';
        $mail->Subject = $subject;

        $formatted_balance = '₱' . number_format(abs($balance), 2);
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4A90E2; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background-color: #f9f9f9; border: 1px solid #ddd; border-top: none; }
                .balance-alert { background-color: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #eee; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 style="margin:0;">📚 Library Management System</h1>
                </div>
                <div class="content">
                    <h2>Dear ' . htmlspecialchars($user_name) . ',</h2>
                    
                    <div class="balance-alert">
                        <h3 style="color: #d32f2f; margin-top: 0;">⚠️ Balance Reminder</h3>
                        <p>Your current account balance is: <strong style="color: #d32f2f; font-size: 18px;">-' . $formatted_balance . '</strong></p>
                    </div>
                    
                    <p>Please visit the library to settle your balance at your earliest convenience.</p>
                    
                    <p><strong>Details:</strong></p>
                    <ul>
                        <li><strong>Amount Due:</strong> ' . $formatted_balance . '</li>
                        <li><strong>Status:</strong> Outstanding Balance</li>
                        <li><strong>Action Required:</strong> Settlement needed</li>
                    </ul>
                    
                    <p>For any questions or concerns regarding your balance, please contact the library staff.</p>
                    
                    <div style="background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <strong>📍 Library Information:</strong><br>
                        • Hours: 8:00 AM - 5:00 PM<br>
                        • Location: Main Campus Library<br>
                    </div>
                    
                    <p style="text-align: center; font-size: 16px; font-weight: bold;">
                        Please visit the library to settle your balance.
                    </p>
                </div>
                <div class="footer">
                    <p>This is an automated message. Please do not reply to this email.</p>
            
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Dear $user_name,\n\n"
            . "Your current account balance is: -$formatted_balance\n\n"
            . "Please visit the library to settle your balance.\n\n"
            . "Amount Due: $formatted_balance\n"
            . "Status: Outstanding Balance\n"
            . "Action Required: Settlement needed\n\n"
           ;

        if ($mail->send()) {
            
            try {
                $email_type = $is_manual ? 'manual_balance_reminder' : 'negative_balance_reminder';
                $log_sql = "INSERT INTO email_logs (user_id, email_type, status, sent_at) 
                           VALUES (:user_id, :email_type, 'sent', NOW())";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->execute([
                    ':user_id' => $user_id,
                    ':email_type' => $email_type
                ]);
                error_log("Email sent and logged for user ID: $user_id");
                return true;
            } catch (Exception $db_error) {
                error_log("Email sent but logging failed: " . $db_error->getMessage());
                return true; 
            }
        } else {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        return false;
    }
}

if (isset($_GET['send_reminder']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    try {
        
        $user_sql = "SELECT balance, full_name, email, status FROM tbl_users WHERE user_id = :user_id";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->execute([':user_id' => $user_id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found!');
        }
        
        $current_balance = floatval($user['balance']);

        if ($current_balance >= 0) {
            $alert_message = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Cannot send reminder: User does not have a negative balance.</div>';
            $alert_type = 'warning';
        } elseif ($user['status'] != 'Active') {
            $alert_message = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Cannot send reminder: User account is not active.</div>';
            $alert_type = 'warning';
        } elseif (empty($user['email'])) {
            $alert_message = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Cannot send reminder: User has no email address.</div>';
            $alert_type = 'warning';
        } else {
            
            if (sendNegativeBalanceNotification($user['email'], $user['full_name'], $current_balance, $user_id, $conn, true)) {
                $alert_message = '<div class="alert alert-success">';
                $alert_message .= '<h5><i class="bi bi-envelope-check"></i> Reminder Sent Successfully!</h5>';
                $alert_message .= '<div class="mt-3">';
                $alert_message .= '<p><strong>User:</strong> ' . htmlspecialchars($user['full_name']) . '</p>';
                $alert_message .= '<p><strong>Balance:</strong> ' . formatBalanceForDisplay($current_balance) . '</p>';
                $alert_message .= '<p><strong>Email Sent To:</strong> ' . htmlspecialchars($user['email']) . '</p>';
                $alert_message .= '<p><i class="bi bi-info-circle"></i> User has been reminded to visit the library to settle their balance.</p>';
                $alert_message .= '</div></div>';
                $alert_type = 'success';
            } else {
                $alert_message = '<div class="alert alert-danger"><i class="bi bi-envelope-x"></i> Failed to send reminder email. Please try again.</div>';
                $alert_type = 'danger';
            }
        }
        
    } catch (Exception $e) {
        $alert_message = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error: ' . $e->getMessage() . '</div>';
        $alert_type = 'danger';
    }
}

if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_balance'])) {
        $user_id = $_POST['user_id'];
        $balance_adjustment = floatval($_POST['balance_adjustment']);
        $adjustment_type = $_POST['adjustment_type'];
        $reason = trim($_POST['adjustment_reason'] ?? '');
        $send_notification = isset($_POST['send_notification']);
        
        try {
            $conn->beginTransaction();

            $user_sql = "SELECT balance, full_name, email, status, institutional_id FROM tbl_users WHERE user_id = :user_id";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->execute([':user_id' => $user_id]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('User not found!');
            }
            
            $current_balance = floatval($user['balance']);

            if ($adjustment_type == 'add_debt') {
                $new_balance = $current_balance - $balance_adjustment;
                $operation = 'Added Debt';
            } else {
                $new_balance = $current_balance + $balance_adjustment;
                $operation = 'Added Credit';
            }

            $update_sql = "UPDATE tbl_users SET balance = :new_balance WHERE user_id = :user_id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([
                ':new_balance' => $new_balance,
                ':user_id' => $user_id
            ]);
            
            $conn->commit();

            $email_sent = false;
            $email_message = '';
            
            if ($new_balance < 0 && $user['status'] == 'Active' && $send_notification) {
                if (!empty($user['email'])) {
                    error_log("Attempting to send balance email to: " . $user['email']);
                    
                    if (sendNegativeBalanceNotification($user['email'], $user['full_name'], $new_balance, $user_id, $conn, false)) {
                        $email_sent = true;
                        $email_message = '<div class="alert alert-info mt-2"><i class="bi bi-envelope-check"></i> Email notification sent to user.</div>';
                    } else {
                        $email_message = '<div class="alert alert-warning mt-2"><i class="bi bi-envelope-x"></i> Email notification failed to send.</div>';
                    }
                } else {
                    $email_message = '<div class="alert alert-warning mt-2"><i class="bi bi-envelope-slash"></i> User has no email address.</div>';
                }
            }

            $balance_receipt_data = [
                'receipt_id' => 'BA' . time(),
                'transaction_date' => date('F d, Y h:i A'),
                'user_name' => $user['full_name'],
                'user_id' => $user_id,
                'institutional_id' => $user['institutional_id'] ?? '',
                'adjustment_type' => $adjustment_type,
                'amount' => $balance_adjustment,
                'previous_balance' => $current_balance,
                'new_balance' => $new_balance,
                'librarian_name' => $_SESSION['full_name'] ?? 'Library Staff',
                'reason' => $reason,
                'email_message' => $email_message ?? ''
            ];
            $alert_message = 'Balance updated successfully!';
            $alert_type = 'success';
            $action = 'list';
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error: ' . $e->getMessage() . '</div>';
            $alert_type = 'danger';
        }
    }
}

function formatBalanceForDisplay($balance) {
    $balance = floatval($balance);
    if ($balance > 0) {
        return '<span class="text-success">+₱' . number_format($balance, 2) . '</span>';
    } elseif ($balance < 0) {
        return '<span class="text-danger">-₱' . number_format(abs($balance), 2) . '</span>';
    } else {
        return '<span class="text-muted">₱' . number_format($balance, 2) . '</span>';
    }
}
?>

<div class="container-fluid">
    <?php 
    $is_balance_success = ($alert_type == 'success' && isset($balance_receipt_data));
    if ($alert_message && !$is_balance_success): ?>
        <div class="mb-6 p-4 rounded-lg border <?php 
            echo $alert_type == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 
            ($alert_type == 'danger' ? 'bg-red-50 border-red-200 text-red-800' : 
            ($alert_type == 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-800' : 
            'bg-blue-50 border-blue-200 text-blue-800')); 
        ?>">
            <?php echo $alert_message; ?>
        </div>
    <?php endif; ?>
    <?php if ($is_balance_success): ?>
    <form id="balanceReceiptForm" method="POST" action="generate_balance_receipt.php" target="_blank" style="display:none">
        <input type="hidden" name="generate_balance_receipt" value="1">
        <?php foreach ($balance_receipt_data as $k => $v): if ($k === 'email_message') continue; ?>
        <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
        <?php endforeach; ?>
        <input type="hidden" name="notes" value="Balance adjustment processed through users management">
    </form>
    <script>
    (function(){
        var d = <?php echo json_encode([
            'user' => $balance_receipt_data['user_name'],
            'previous' => ($balance_receipt_data['previous_balance'] > 0 ? '+' : ($balance_receipt_data['previous_balance'] < 0 ? '-' : '')) . '₱' . number_format(abs($balance_receipt_data['previous_balance']), 2),
            'adjustment' => ($balance_receipt_data['adjustment_type'] == 'add_debt' ? 'Added Debt' : 'Added Credit'),
            'amount' => '₱ ' . number_format($balance_receipt_data['amount'], 2),
            'new' => ($balance_receipt_data['new_balance'] > 0 ? '+' : ($balance_receipt_data['new_balance'] < 0 ? '-' : '')) . '₱' . number_format(abs($balance_receipt_data['new_balance']), 2),
            'reason' => $balance_receipt_data['reason'] ?? '',
        ]); ?>;
        var html = '<div class="text-left space-y-2 text-slate-700">' +
            '<p><strong>User:</strong> ' + d.user + '</p>' +
            '<p><strong>Previous Balance:</strong> ' + d.previous + '</p>' +
            '<p><strong>Adjustment:</strong> ' + d.adjustment + ': ' + d.amount + '</p>' +
            '<p><strong>New Balance:</strong> <span class="font-bold text-green-600">' + d.new + '</span></p>' +
            (d.reason ? '<p><strong>Reason:</strong> ' + d.reason + '</p>' : '') +
            '</div>';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Balance Updated Successfully!',
                html: html,
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '📄 Download PDF',
                cancelButtonText: 'Close',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                width: '480px'
            }).then(function(r) {
                if (r.isConfirmed && document.getElementById('balanceReceiptForm')) {
                    document.getElementById('balanceReceiptForm').submit();
                }
            });
        }
    })();
    </script>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            Users Management
        </h2>
    </div>

    <?php if ($action == 'edit' && isset($_GET['id'])): ?>
        <!-- Edit User Balance Form -->
        <?php
        $user_id = $_GET['id'];
        try {
            $sql = "SELECT * FROM tbl_users WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo '<div class="alert alert-danger">User not found!</div>';
                $action = 'list';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error fetching user: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $action = 'list';
        }
        
        if ($action == 'edit' && $user):
            $current_balance = floatval($user['balance']);
        ?>
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-person-gear"></i> Update User Balance</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="users.php?action=edit&id=<?php echo $user_id; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-person-circle"></i> User Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Full Name:</strong><br>
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Email:</strong><br>
                                                <?php 
                                                if (!empty($user['email'])) {
                                                    echo htmlspecialchars($user['email']);
                                                } else {
                                                    echo '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> No email address</span>';
                                                }
                                                ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Contact Number:</strong><br>
                                                <?php echo !empty($user['contact_number']) ? htmlspecialchars($user['contact_number']) : 'N/A'; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Student/Employee ID</strong><br>
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo htmlspecialchars($user['institutional_id']); ?>
                                                </span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Grade And Section</strong><br>
                                                    <?php echo htmlspecialchars($user['grade_section']); ?>
                                                
                                            </div>
                                            <div class="mb-2">
                                                <strong>Account Status:</strong><br>
                                                <span class="badge bg-<?php echo $user['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                    <?php echo htmlspecialchars($user['status']); ?>
                                                </span>
                                            </div>
                                             <div class="mb-2">
                                                <strong>User Type</strong><br>
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo htmlspecialchars($user['user_type']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Current Balance</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-4 fw-bold mb-3">
                                        <?php echo formatBalanceForDisplay($current_balance); ?>
                                    </div>
                                    
                                    <div class="small text-muted mb-3">
                                        <?php 
                                        if ($current_balance > 0) {
                                            echo '<span class="text-success"><i class="bi bi-arrow-up-circle"></i> User has credit (overpaid)</span>';
                                        } elseif ($current_balance < 0) {
                                            echo '<span class="text-danger"><i class="bi bi-arrow-down-circle"></i> User owes money</span>';
                                        } else {
                                            echo '<span class="text-muted"><i class="bi bi-check-circle"></i> Balance settled</span>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="alert alert-light">
                                        <h6><i class="bi bi-info-circle"></i> Balance Explanation</h6>
                                        <ul class="mb-0 small">
                                            <li><span class="text-success">+ ₱XX.XX</span>: User has credit (overpaid)</li>
                                            <li><span class="text-muted">₱0.00</span>: Balance settled</li>
                                            <li><span class="text-danger">- ₱XX.XX</span>: User owes money</li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Send Manual Reminder Button (only show if user has negative balance) -->
                                    <?php if ($current_balance < 0 && $user['status'] == 'Active' && !empty($user['email'])): ?>
                                    <div class="mt-4">
                                        <a href="users.php?send_reminder=1&id=<?php echo $user_id; ?>" 
                                           class="btn btn-warning btn-lg w-100"
                                           onclick="return confirm('Send balance reminder to <?php echo htmlspecialchars($user['full_name']); ?>?')">
                                            <i class="bi bi-envelope-exclamation"></i> Send Reminder Email
                                        </a>
                                        <small class="text-muted d-block mt-2">
                                            Send a reminder email to the user about their negative balance.
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Balance Adjustment</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="adjustment_type" class="form-label">Adjustment Type *</label>
                                        <select class="form-control" id="adjustment_type" name="adjustment_type" required>
                                            <option value="add_debt">Add Debt (User Owes More)</option>
                                            <option value="add_credit">Add Credit (User Pays)</option>
                                        </select>
                                        <div class="form-text">
                                            <strong>Add Debt:</strong> User owes more money (balance becomes more negative)<br>
                                            <strong>Add Credit:</strong> User makes payment (balance becomes more positive)
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="balance_adjustment" class="form-label">Amount (₱) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="balance_adjustment" 
                                                   name="balance_adjustment" step="0.01" min="0.01" 
                                                   value="0.00" required>
                                        </div>
                                        <div class="form-text">Enter the amount to adjust the balance</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="adjustment_reason" class="form-label">Reason for Adjustment</label>
                                        <textarea class="form-control" id="adjustment_reason" name="adjustment_reason" 
                                                  rows="3" placeholder="Enter reason for this balance adjustment..."></textarea>
                                        <div class="form-text">Optional: Late fees, lost book penalty, payment received, etc.</div>
                                    </div>
                                    
                                    <!-- Email Notification Option -->
                                    <div class="mb-3" id="notificationSection" style="display: none;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="send_notification" name="send_notification" value="1" checked>
                                            <label class="form-check-label" for="send_notification">
                                                <i class="bi bi-envelope-exclamation text-danger"></i> 
                                                Send email notification to user about negative balance
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <?php if (!empty($user['email'])): ?>
                                                Email will be sent to: <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                                            <?php else: ?>
                                                <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> User has no email address - cannot send notification</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-light">
                                        <h6><i class="bi bi-calculator"></i> New Balance Preview</h6>
                                        <div id="balancePreview">
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Current:</strong><br>
                                                    <span id="currentBalancePreview">
                                                        <?php echo formatBalanceForDisplay($current_balance); ?>
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Adjustment:</strong><br>
                                                    <span id="adjustmentPreview">₱0.00</span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-12">
                                                    <strong>New Balance:</strong><br>
                                                    <span id="newBalancePreview" class="fw-bold">
                                                        <?php echo formatBalanceForDisplay($current_balance); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="users.php" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" name="update_balance" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const adjustmentType = document.getElementById('adjustment_type');
            const balanceAdjustment = document.getElementById('balance_adjustment');
            const notificationSection = document.getElementById('notificationSection');
            const currentBalance = <?php echo $current_balance; ?>;
            const userHasEmail = <?php echo !empty($user['email']) ? 'true' : 'false'; ?>;
            
            function updateBalancePreview() {
                const adjustment = parseFloat(balanceAdjustment.value) || 0;
                const type = adjustmentType.value;
                
                // Update adjustment preview
                const adjustmentPreview = document.getElementById('adjustmentPreview');
                if (type === 'add_debt') {
                    adjustmentPreview.textContent = '-₱' + adjustment.toFixed(2);
                    adjustmentPreview.className = 'text-danger';
                } else {
                    adjustmentPreview.textContent = '+₱' + adjustment.toFixed(2);
                    adjustmentPreview.className = 'text-success';
                }
                
                // Calculate new balance
                let newBalance = currentBalance;
                if (type === 'add_debt') {
                    newBalance = currentBalance - adjustment;
                } else {
                    newBalance = currentBalance + adjustment;
                }
                
                // Update new balance preview
                const newBalancePreview = document.getElementById('newBalancePreview');
                
                if (newBalance > 0) {
                    newBalancePreview.innerHTML = '<span class="text-success">+₱' + Math.abs(newBalance).toFixed(2) + '</span>';
                } else if (newBalance < 0) {
                    newBalancePreview.innerHTML = '<span class="text-danger">-₱' + Math.abs(newBalance).toFixed(2) + '</span>';
                } else {
                    newBalancePreview.innerHTML = '<span class="text-muted">₱' + newBalance.toFixed(2) + '</span>';
                }
                
                // Show/hide notification section
                if (newBalance < 0 && userHasEmail) {
                    notificationSection.style.display = 'block';
                } else {
                    notificationSection.style.display = 'none';
                    document.getElementById('send_notification').checked = false;
                }
            }
            
            // Event listeners
            adjustmentType.addEventListener('change', updateBalancePreview);
            balanceAdjustment.addEventListener('input', updateBalancePreview);
            
            // Initial preview
            updateBalancePreview();
        });
        </script>
        
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Users List -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="usersTable" class="data-table w-full">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Student/Employee ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">User Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Grade And Section</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php
                        try {
                            $stmt = $conn->query("SELECT * FROM tbl_users ORDER BY full_name");
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($users)):
                        ?>
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No users found</p>
                                    <p class="text-sm">Users will appear here once added</p>
                                </div>
                            </td>
                        </tr>
                        <?php
                            else:
                                foreach ($users as $row): 
                                    $status_class = [
                                        'Active' => 'bg-green-100 text-green-800',
                                        'Inactive' => 'bg-red-100 text-red-800'
                                    ];
                                    
                                    $balance = floatval($row['balance']);
                                    $balance_display = formatBalanceForDisplay($balance);

                                    $can_send_reminder = ($balance < 0 && $row['status'] == 'Active' && !empty($row['email']));
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                <div class="font-medium"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <?php if (!empty($row['address'])): ?>
                                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Has address
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php echo !empty($row['contact_number']) ? htmlspecialchars($row['contact_number']) : '<span class="text-slate-400">N/A</span>'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-slate-100 text-slate-700 border border-slate-300">
                                    <?php echo htmlspecialchars($row['institutional_id']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-slate-800 text-white">
                                    <?php echo htmlspecialchars($row['user_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php echo htmlspecialchars($row['grade_section']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                <?php echo $balance_display; ?>
                                <?php if ($balance < 0): ?>
                                    <div class="text-xs text-red-600 mt-1 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Needs settlement
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_class[$row['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="users.php?action=edit&id=<?php echo $row['user_id']; ?>" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Update Balance
                                    </a>
                                    
                                    <?php if ($can_send_reminder): ?>
                                    <a href="users.php?send_reminder=1&id=<?php echo $row['user_id']; ?>" 
                                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors"
                                       title="Send balance reminder email"
                                       onclick="return confirm('Send balance reminder to <?php echo htmlspecialchars(addslashes($row['full_name'])); ?>?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Remind
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            endif;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='10' class='px-6 py-4 text-center text-red-600'>Error loading users: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer_unified.php'; ?>