<?php
require_once 'db.php';

if (isset($_GET['action']) && $_GET['action'] == 'get_borrower' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $user_id = $_GET['id'];
    try {
        $sql = "SELECT * FROM tbl_users WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $borrower = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($borrower) {
            unset($borrower['password']);
            echo json_encode(['success' => true, 'borrower' => $borrower]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Borrower not found']);
        }
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

require_once 'header_unified.php';

$action = $_GET['action'] ?? 'list';
$alert_message = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_borrower'])) {
        $user_id = $_POST['user_id'] ?? null;
        $full_name = trim($_POST['full_name'] ?? '');
        $institutional_id = trim($_POST['institutional_id'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        $user_type = trim($_POST['user_type'] ?? '');
        $grade_section = trim($_POST['grade_section'] ?? '');
        $rfid_number = trim($_POST['rfid_number'] ?? '');
        $status = trim($_POST['status'] ?? 'Active');
        $password = trim($_POST['password'] ?? '');
        
        try {
            $conn->beginTransaction();
            
            if ($user_id) {
                
                if (!empty($password)) {
                    $sql = "UPDATE tbl_users SET full_name = :full_name, institutional_id = :institutional_id, 
                            email = :email, contact_number = :contact_number, user_type = :user_type, 
                            grade_section = :grade_section, rfid_number = :rfid_number, status = :status, 
                            password = :password WHERE user_id = :user_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':full_name' => $full_name,
                        ':institutional_id' => $institutional_id,
                        ':email' => $email,
                        ':contact_number' => $contact_number,
                        ':user_type' => $user_type,
                        ':grade_section' => $grade_section,
                        ':rfid_number' => $rfid_number,
                        ':status' => $status,
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':user_id' => $user_id
                    ]);
                } else {
                    $sql = "UPDATE tbl_users SET full_name = :full_name, institutional_id = :institutional_id, 
                            email = :email, contact_number = :contact_number, user_type = :user_type, 
                            grade_section = :grade_section, rfid_number = :rfid_number, status = :status 
                            WHERE user_id = :user_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':full_name' => $full_name,
                        ':institutional_id' => $institutional_id,
                        ':email' => $email,
                        ':contact_number' => $contact_number,
                        ':user_type' => $user_type,
                        ':grade_section' => $grade_section,
                        ':rfid_number' => $rfid_number,
                        ':status' => $status,
                        ':user_id' => $user_id
                    ]);
                }
                $alert_message = 'Borrower updated successfully!';
            } else {
                
                if (empty($password)) {
                    throw new Exception('Password is required for new borrowers');
                }
                
                $sql = "INSERT INTO tbl_users (full_name, institutional_id, email, contact_number, user_type, 
                        grade_section, rfid_number, password, status, balance, history) 
                        VALUES (:full_name, :institutional_id, :email, :contact_number, :user_type, 
                        :grade_section, :rfid_number, :password, :status, 0.00, 'New borrower')";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':full_name' => $full_name,
                    ':institutional_id' => $institutional_id,
                    ':email' => $email,
                    ':contact_number' => $contact_number,
                    ':user_type' => $user_type,
                    ':grade_section' => $grade_section,
                    ':rfid_number' => $rfid_number,
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':status' => $status
                ]);
                $alert_message = 'Borrower added successfully!';
            }
            
            $conn->commit();
            $alert_type = 'success';
            $action = 'list';
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }
}

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    try {
        $sql = "DELETE FROM tbl_users WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $alert_message = 'Borrower deleted successfully!';
        $alert_type = 'success';
    } catch (PDOException $e) {
        $alert_message = 'Error deleting borrower: ' . $e->getMessage();
        $alert_type = 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_balance'])) {
    $user_id = $_POST['user_id'];
        $balance_adjustment = floatval($_POST['balance_adjustment']);
        $adjustment_type = $_POST['adjustment_type']; 
        $reason = trim($_POST['adjustment_reason'] ?? '');
        
        try {
            $conn->beginTransaction();

            $user_sql = "SELECT balance, full_name, institutional_id FROM tbl_users WHERE user_id = :user_id";
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
                'reason' => $reason
            ];
            $alert_message = 'Balance updated successfully!<br>';
            $alert_message .= '<strong>User:</strong> ' . htmlspecialchars($user['full_name']) . '<br>';
            $alert_message .= '<strong>Previous Balance:</strong> ' . formatBalanceForDisplay($current_balance) . '<br>';
            $alert_message .= '<strong>Adjustment:</strong> ' . $operation . ': ₱   ' . number_format($balance_adjustment, 2) . '<br>';
            $alert_message .= '<strong>New Balance:</strong> ' . formatBalanceForDisplay($new_balance) . '<br>';
            if ($reason) {
                $alert_message .= '<strong>Reason:</strong> ' . htmlspecialchars($reason);
            }
            $alert_type = 'success';
            $action = 'list'; 
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $alert_message = 'Error: ' . $e->getMessage();
            $alert_type = 'danger';
        }
}

function formatBalanceForDisplay($balance) {
    $balance = floatval($balance);
    if ($balance > 0) {
        return '<span class="text-green-600 font-semibold">+₱' . number_format($balance, 2) . '</span>';
    } elseif ($balance < 0) {
        return '<span class="text-red-600 font-semibold">-₱' . number_format(abs($balance), 2) . '</span>';
    } else {
        return '<span class="text-slate-600 font-semibold">₱' . number_format($balance, 2) . '</span>';
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
            <?php if ($alert_type == 'success' && strpos($alert_message, '<br>') !== false): ?>
                <?php echo $alert_message; ?>
            <?php else: ?>
                <?php echo htmlspecialchars($alert_message); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($is_balance_success): ?>
    <form id="balanceReceiptForm" method="POST" action="generate_balance_receipt.php" target="_blank" style="display:none">
        <input type="hidden" name="generate_balance_receipt" value="1">
        <?php foreach ($balance_receipt_data as $k => $v): ?>
        <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
        <?php endforeach; ?>
        <input type="hidden" name="notes" value="Balance adjustment processed through borrowers management">
    </form>
    <script>
    (function(){
        var d = <?php echo json_encode([
            'user' => $balance_receipt_data['user_name'],
            'previous' => ($balance_receipt_data['previous_balance'] > 0 ? '+' : ($balance_receipt_data['previous_balance'] < 0 ? '-' : '')) . '₱' . number_format(abs($balance_receipt_data['previous_balance']), 2),
            'adjustment' => ($balance_receipt_data['adjustment_type'] == 'add_debt' ? 'Added Debt' : 'Added Credit'),
            'amount' => '₱ ' . number_format($balance_receipt_data['amount'], 2),
            'new' => ($balance_receipt_data['new_balance'] > 0 ? '+' : ($balance_receipt_data['new_balance'] < 0 ? '-' : '')) . '₱' . number_format(abs($balance_receipt_data['new_balance']), 2),
            'reason' => $balance_receipt_data['reason'] ?? ''
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
            Borrowers Management
        </h2>
        <button onclick="openBorrowerModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add Borrower</span>
        </button>
    </div>

    <?php
    
    $balance_modal_id = ($action == 'edit_balance' && isset($_GET['id'])) ? intval($_GET['id']) : 0;
    ?>
    <?php if (true): ?>
        <!-- Borrowers List -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="borrowersTable" class="data-table w-full">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Student/Employee ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Grade/Section</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php
                        try {
                            $stmt = $conn->query("SELECT * FROM tbl_users ORDER BY full_name");
                            $borrowers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($borrowers)):
                        ?>
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No borrowers found</p>
                                    <p class="text-sm">Click "Add Borrower" to add a new borrower</p>
                                </div>
                            </td>
                        </tr>
                        <?php
                            else:
                                foreach ($borrowers as $row): 
                                    $status_class = [
                                        'Active' => 'bg-green-100 text-green-800',
                                        'Inactive' => 'bg-red-100 text-red-800'
                                    ];
                                    
                                    $balance = floatval($row['balance']);
                                    $balance_display = formatBalanceForDisplay($balance);
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-900">
                                <div class="font-medium"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php echo !empty($row['email']) ? htmlspecialchars($row['email']) : '<span class="text-slate-400">N/A</span>'; ?>
                            </td>
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
                                    <?php echo htmlspecialchars($row['user_type'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php echo htmlspecialchars($row['grade_section'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                <?php echo $balance_display; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_class[$row['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <button onclick="openBorrowerModal(<?php echo $row['user_id']; ?>)" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                    <button type="button" onclick="openBalanceModal(<?php echo $row['user_id']; ?>)" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Balance
                                    </button>
                                    <a href="borrowers.php?delete=1&id=<?php echo $row['user_id']; ?>" 
                                       class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition-colors"
                                       onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars(addslashes($row['full_name'])); ?>?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            endif;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='10' class='px-6 py-4 text-center text-red-600'>Error loading borrowers: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Balance Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-slate-50 border-t border-slate-200">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-center">
                        <h6 class="text-sm font-semibold text-slate-600 mb-2">Total Borrowers</h6>
                        <h3 class="text-2xl font-bold text-slate-900">
                            <?php
                            try {
                                $count_stmt = $conn->query("SELECT COUNT(*) as total FROM tbl_users");
                                $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
                                echo $count['total'];
                            } catch (PDOException $e) {
                                echo '0';
                            }
                            ?>
                        </h3>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-center">
                        <h6 class="text-sm font-semibold text-slate-600 mb-2">Total Debt Owed</h6>
                        <h4 class="text-xl font-bold text-red-600">
                            <?php
                            try {
                                $debt_stmt = $conn->query("SELECT SUM(balance) as total_debt FROM tbl_users WHERE balance < 0");
                                $debt = $debt_stmt->fetch(PDO::FETCH_ASSOC);
                                echo formatBalanceForDisplay($debt['total_debt'] ?? 0);
                            } catch (PDOException $e) {
                                echo '₱0.00';
                            }
                            ?>
                        </h4>
                        <small class="text-slate-500 text-xs">Negative balances</small>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-center">
                        <h6 class="text-sm font-semibold text-slate-600 mb-2">Total Credit</h6>
                        <h4 class="text-xl font-bold text-green-600">
                            <?php
                            try {
                                $credit_stmt = $conn->query("SELECT SUM(balance) as total_credit FROM tbl_users WHERE balance > 0");
                                $credit = $credit_stmt->fetch(PDO::FETCH_ASSOC);
                                echo formatBalanceForDisplay($credit['total_credit'] ?? 0);
                            } catch (PDOException $e) {
                                echo '₱0.00';
                            }
                            ?>
                        </h4>
                        <small class="text-slate-500 text-xs">Positive balances</small>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-center">
                        <h6 class="text-sm font-semibold text-slate-600 mb-2">Settled Accounts</h6>
                        <h4 class="text-2xl font-bold text-slate-900">
                            <?php
                            try {
                                $settled_stmt = $conn->query("SELECT COUNT(*) as settled FROM tbl_users WHERE balance = 0");
                                $settled = $settled_stmt->fetch(PDO::FETCH_ASSOC);
                                echo $settled['settled'];
                            } catch (PDOException $e) {
                                echo '0';
                            }
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Balance Modal -->
<div id="balanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-900">Update User Balance</h3>
            <button type="button" onclick="closeBalanceModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form method="POST" action="borrowers.php" id="balanceForm" class="p-6">
            <input type="hidden" name="update_balance" value="1">
            <input type="hidden" name="user_id" id="balance_modal_user_id">
            
            <div class="mb-4 p-4 bg-slate-50 rounded-lg">
                <p class="text-sm text-slate-600 mb-1">Borrower</p>
                <p class="font-semibold text-slate-900" id="balance_modal_user_name">—</p>
                <p class="text-sm mt-2">Current balance: <span id="balance_modal_current_balance" class="font-semibold">₱0.00</span></p>
            </div>
            
            <div class="mb-4">
                <label for="balance_adjustment_type" class="block text-sm font-medium text-slate-700 mb-1">Adjustment Type *</label>
                <select id="balance_adjustment_type" name="adjustment_type" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="add_debt">Add Debt (User Owes More)</option>
                    <option value="add_credit">Add Credit (User Pays)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">Add Debt: balance becomes more negative. Add Credit: user pays, balance becomes more positive.</p>
            </div>
            
            <div class="mb-4">
                <label for="balance_adjustment_amount" class="block text-sm font-medium text-slate-700 mb-1">Amount (₱) *</label>
                <input type="number" id="balance_adjustment_amount" name="balance_adjustment" step="0.01" min="0.01" value="0.00" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="mb-4">
                <label for="balance_adjustment_reason" class="block text-sm font-medium text-slate-700 mb-1">Reason for Adjustment</label>
                <textarea id="balance_adjustment_reason" name="adjustment_reason" rows="2" placeholder="Optional: Late fees, lost book penalty, payment received..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            
            <div class="mb-6 p-4 bg-slate-50 rounded-lg">
                <p class="text-sm font-medium text-slate-700 mb-2">New Balance Preview</p>
                <p class="text-sm">Adjustment: <span id="balance_adjustment_preview" class="font-semibold">₱0.00</span></p>
                <p class="text-sm mt-1">New balance: <span id="balance_new_preview" class="font-bold text-slate-900">₱0.00</span></p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeBalanceModal()"
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Update Balance
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Borrower Modal -->
<div id="borrowerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-900" id="modalTitle">Add Borrower</h3>
            <button onclick="closeBorrowerModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form method="POST" action="borrowers.php" id="borrowerForm" class="p-6">
            <input type="hidden" name="user_id" id="modal_user_id">
            <input type="hidden" name="save_borrower" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="full_name" class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="institutional_id" class="block text-sm font-medium text-slate-700 mb-1">Student/Employee ID *</label>
                    <input type="text" id="institutional_id" name="institutional_id" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="contact_number" class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="user_type" class="block text-sm font-medium text-slate-700 mb-1">User Type</label>
                    <select id="user_type" name="user_type"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                
                <div>
                    <label for="grade_section" class="block text-sm font-medium text-slate-700 mb-1">Grade/Section</label>
                    <input type="text" id="grade_section" name="grade_section"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="rfid_number" class="block text-sm font-medium text-slate-700 mb-1">RFID Number</label>
                    <input type="text" id="rfid_number" name="rfid_number"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select id="status" name="status"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                        Password <span id="passwordRequired" class="text-red-500">*</span>
                        <span id="passwordOptional" class="text-slate-500 text-xs">(Leave blank to keep current password)</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeBorrowerModal()" 
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Save Borrower
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer_unified.php'; ?>

<script>
// Borrower Modal Functions
let borrowerData = {};

function openBorrowerModal(userId = null) {
    const modal = document.getElementById('borrowerModal');
    const form = document.getElementById('borrowerForm');
    const title = document.getElementById('modalTitle');
    const passwordRequired = document.getElementById('passwordRequired');
    const passwordOptional = document.getElementById('passwordOptional');
    
    if (userId) {
        // Edit mode
        title.textContent = 'Edit Borrower';
        document.getElementById('modal_user_id').value = userId;
        passwordRequired.style.display = 'none';
        passwordOptional.style.display = 'inline';
        document.getElementById('password').removeAttribute('required');
        
        // Fetch borrower data
        fetch(`borrowers.php?action=get_borrower&id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('full_name').value = data.borrower.full_name || '';
                    document.getElementById('institutional_id').value = data.borrower.institutional_id || '';
                    document.getElementById('email').value = data.borrower.email || '';
                    document.getElementById('contact_number').value = data.borrower.contact_number || '';
                    document.getElementById('user_type').value = data.borrower.user_type || 'Student';
                    document.getElementById('grade_section').value = data.borrower.grade_section || '';
                    document.getElementById('rfid_number').value = data.borrower.rfid_number || '';
                    document.getElementById('status').value = data.borrower.status || 'Active';
                }
            })
            .catch(error => {
                console.error('Error fetching borrower:', error);
            });
    } else {
        // Add mode
        title.textContent = 'Add Borrower';
        document.getElementById('modal_user_id').value = '';
        form.reset();
        passwordRequired.style.display = 'inline';
        passwordOptional.style.display = 'none';
        document.getElementById('password').setAttribute('required', 'required');
    }
    
    modal.classList.remove('hidden');
}

function closeBorrowerModal() {
    const modal = document.getElementById('borrowerModal');
    modal.classList.add('hidden');
}

// Balance Modal
let balanceModalCurrentBalance = 0;

function openBalanceModal(userId) {
    const modal = document.getElementById('balanceModal');
    document.getElementById('balance_modal_user_id').value = userId;
    document.getElementById('balance_adjustment_type').value = 'add_debt';
    document.getElementById('balance_adjustment_amount').value = '0.00';
    document.getElementById('balance_adjustment_reason').value = '';
    
    fetch(`borrowers.php?action=get_borrower&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const b = data.borrower;
                document.getElementById('balance_modal_user_name').textContent = b.full_name || '—';
                balanceModalCurrentBalance = parseFloat(b.balance) || 0;
                const cur = balanceModalCurrentBalance;
                const curText = cur > 0 ? '+₱' + cur.toFixed(2) : (cur < 0 ? '-₱' + Math.abs(cur).toFixed(2) : '₱0.00');
                document.getElementById('balance_modal_current_balance').textContent = curText;
                document.getElementById('balance_modal_current_balance').className = 'font-semibold ' + (cur > 0 ? 'text-green-600' : (cur < 0 ? 'text-red-600' : 'text-slate-600'));
                updateBalanceModalPreview();
            }
        })
        .catch(err => console.error('Error fetching borrower:', err));
    
    modal.classList.remove('hidden');
}

function closeBalanceModal() {
    document.getElementById('balanceModal').classList.add('hidden');
    const url = new URL(window.location.href);
    if (url.searchParams.get('action') === 'edit_balance') {
        url.searchParams.delete('action');
        url.searchParams.delete('id');
        window.history.replaceState({}, '', url.pathname + (url.search || ''));
    }
}

function updateBalanceModalPreview() {
    const type = document.getElementById('balance_adjustment_type').value;
    const amount = parseFloat(document.getElementById('balance_adjustment_amount').value) || 0;
    const cur = balanceModalCurrentBalance;
    const adjText = type === 'add_debt' ? '-₱' + amount.toFixed(2) : '+₱' + amount.toFixed(2);
    const newBal = type === 'add_debt' ? cur - amount : cur + amount;
    const newText = newBal > 0 ? '+₱' + newBal.toFixed(2) : (newBal < 0 ? '-₱' + Math.abs(newBal).toFixed(2) : '₱0.00');
    
    document.getElementById('balance_adjustment_preview').textContent = adjText;
    document.getElementById('balance_adjustment_preview').className = 'font-semibold ' + (type === 'add_debt' ? 'text-red-600' : 'text-green-600');
    document.getElementById('balance_new_preview').textContent = newText;
    document.getElementById('balance_new_preview').className = 'font-bold ' + (newBal > 0 ? 'text-green-600' : (newBal < 0 ? 'text-red-600' : 'text-slate-900'));
}

document.getElementById('balance_adjustment_type').addEventListener('change', updateBalanceModalPreview);
document.getElementById('balance_adjustment_amount').addEventListener('input', updateBalanceModalPreview);

document.getElementById('balanceModal').addEventListener('click', function(e) {
    if (e.target === this) closeBalanceModal();
});

// Open balance modal on load if URL has action=edit_balance&id=
<?php if ($balance_modal_id > 0): ?>
document.addEventListener('DOMContentLoaded', function() {
    openBalanceModal(<?php echo $balance_modal_id; ?>);
});
<?php endif; ?>

// Close borrower modal when clicking outside
document.getElementById('borrowerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBorrowerModal();
    }
});

// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>