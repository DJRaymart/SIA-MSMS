<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/check_admin_access.php';
require_once 'db.php';
require_once 'header_unified.php';

if (!isset($conn)) {
    die("Database connection failed");
}

$stats = [];
$recent_transactions = [];

try {
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(number_of_copies), 0) as total FROM book");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_books'] = $result && isset($result['total']) ? (int)$result['total'] : 0;

    $stmt = $pdo->query("SELECT COALESCE(SUM(available_copies), 0) as total FROM book");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['available_books'] = $result && isset($result['total']) ? (int)$result['total'] : 0;

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_users WHERE status = 'Active'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_users'] = $result ? $result['total'] : 0;

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM borrowing_transaction WHERE return_date IS NULL AND status IN ('Borrowed', 'Overdue')");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['borrowed_books'] = $result ? $result['total'] : 0;

    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM borrowing_transaction WHERE due_date < :today AND return_date IS NULL AND status IN ('Borrowed', 'Overdue')");
    $stmt->execute([':today' => $today]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['overdue_books'] = $result ? $result['total'] : 0;

    $stmt = $pdo->query("
        SELECT 
            t.*, 
            b.title, 
            u.full_name as user_name, 
            l.full_name as librarian_name
        FROM borrowing_transaction t
        LEFT JOIN book b ON t.book_id = b.book_id
        LEFT JOIN tbl_users u ON t.user_id = u.user_id
        LEFT JOIN librarian l ON t.librarian_id = l.librarian_id
        ORDER BY t.borrow_date DESC
        LIMIT 10
    ");
    
    if ($stmt) {
        $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $recent_transactions = [];
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error loading dashboard statistics: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    $stats = [
        'total_books' => 0,
        'available_books' => 0,
        'total_users' => 0,
        'borrowed_books' => 0,
        'overdue_books' => 0
    ];
    $recent_transactions = [];
}

if (!isset($librarian_name)) {
    $librarian_name = "Unknown";
}
if (!isset($librarian_role)) {
    $librarian_role = "Librarian";
}
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Total Books</h3>
            <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <p class="text-4xl font-black mb-2"><?php echo htmlspecialchars($stats['total_books']); ?></p>
        <p class="text-sm opacity-80"><?php echo htmlspecialchars($stats['available_books']); ?> available</p>
    </div>

    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Active Users</h3>
            <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <p class="text-4xl font-black mb-2"><?php echo htmlspecialchars($stats['total_users']); ?></p>
        <p class="text-sm opacity-80">Registered users</p>
    </div>

    <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Borrowed</h3>
            <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
            </svg>
        </div>
        <p class="text-4xl font-black mb-2"><?php echo htmlspecialchars($stats['borrowed_books']); ?></p>
        <p class="text-sm opacity-80">Currently borrowed</p>
    </div>

    <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Overdue</h3>
            <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <p class="text-4xl font-black mb-2"><?php echo htmlspecialchars($stats['overdue_books']); ?></p>
        <p class="text-sm opacity-80">Requires attention</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- RECENT TRANSACTIONS -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Recent Transactions
                </h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Book</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Borrow</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Due</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php if (!empty($recent_transactions)): ?>
                                <?php foreach ($recent_transactions as $row): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-slate-900"><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-700"><?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-600"><?php echo isset($row['borrow_date']) ? date('M d, Y', strtotime($row['borrow_date'])) : 'N/A'; ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-600"><?php echo isset($row['due_date']) ? date('M d, Y', strtotime($row['due_date'])) : 'N/A'; ?></td>
                                    <td class="px-4 py-3">
                                        <?php 
                                        $status = $row['status'] ?? 'Unknown';
                                        $status_colors = [
                                            'Borrowed' => 'bg-yellow-500',
                                            'Returned' => 'bg-green-500',
                                            'Overdue' => 'bg-red-500'
                                        ];
                                        $color = $status_colors[$status] ?? 'bg-slate-500';
                                        ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white <?php echo $color; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                        No recent transactions found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="space-y-6">
        <!-- QUICK ACTIONS -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Quick Actions
                </h3>
            </div>
            <div class="p-4 space-y-2">
                <a href="books.php?action=add" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Book
                </a>
                <a href="books.php?action=borrow" class="block w-full bg-cyan-600 hover:bg-cyan-700 text-white text-center py-3 px-4 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                    </svg>
                    Borrow Book
                </a>
                <a href="transactions.php?action=return" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                    Return Book
                </a>
            </div>
        </div>

        <!-- SYSTEM INFO -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-6 py-4">
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    System Info
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Logged in as</p>
                    <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($librarian_name); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Role</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        <?php echo htmlspecialchars($librarian_role); ?>
                    </span>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Date</p>
                    <p class="text-sm font-semibold text-slate-900"><?php echo date('F j, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer_unified.php'; ?>