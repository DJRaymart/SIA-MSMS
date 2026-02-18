<?php
if (!defined('APP_ROOT')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$app_root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/security.php';
require_once $app_root . '/auth/check_admin_access.php';
require_once $app_root . '/config/db.php';
require_once $app_root . '/auth/admin_helper.php';

$adminInfo = AdminAuth::getAdminInfo();
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$baseS = $base === '' ? '/' : $base . '/';

$tab = $_GET['tab'] ?? 'students';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_student'])) {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        header('Location: ?tab=pending&csrf_error=1');
        exit;
    }

    $sid = trim($_POST['student_id'] ?? '');
    $act = $_POST['act'] ?? '';
    if ($sid && in_array($act, ['approve', 'reject'], true)) {
        $new_status = $act === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE students SET account_status = ? WHERE student_id = ? AND account_status = 'pending'");
        $stmt->bind_param("ss", $new_status, $sid);
        $stmt->execute();
        header('Location: ?tab=pending&' . ($act === 'approve' ? 'approved=1' : 'rejected=1'));
        exit;
    }
}

$adminWhere = '';
$adminParams = [];
$adminTypes = [];
if ($search !== '') {
    $adminWhere = " WHERE (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $term = '%' . $search . '%';
    $adminParams = [$term, $term, $term];
}

$adminCountQuery = "SELECT COUNT(*) as cnt FROM msms_admins" . $adminWhere;
if (!empty($adminParams)) {
    $stmt = $conn->prepare($adminCountQuery);
    $stmt->bind_param(str_repeat('s', count($adminParams)), ...$adminParams);
    $stmt->execute();
    $adminTotal = (int) $stmt->get_result()->fetch_assoc()['cnt'];
} else {
    $adminTotal = (int) ($conn->query($adminCountQuery)->fetch_assoc()['cnt'] ?? 0);
}

$admins = [];
$adminQuery = "SELECT admin_id, username, full_name, email, role, status, created_at, last_login FROM msms_admins" . $adminWhere . " ORDER BY created_at DESC";
if (!empty($adminParams)) {
    $stmt = $conn->prepare($adminQuery);
    $stmt->bind_param(str_repeat('s', count($adminParams)), ...$adminParams);
    $stmt->execute();
    $r = $stmt->get_result();
} else {
    $r = $conn->query($adminQuery);
}
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $admins[] = $row;
    }
}

$studentWhere = '';
$studentParams = [];
$approvedCond = " (account_status = 'approved' OR account_status IS NULL OR account_status = '')";
if ($search !== '') {
    $studentWhere = " WHERE (student_id LIKE ? OR name LIKE ? OR section LIKE ? OR email LIKE ?) AND " . $approvedCond;
    $term = '%' . $search . '%';
    $studentParams = [$term, $term, $term, $term];
} else {
    $studentWhere = " WHERE " . $approvedCond;
}

$studentCountQuery = "SELECT COUNT(*) as cnt FROM students" . $studentWhere;
if (!empty($studentParams)) {
    $stmt = $conn->prepare($studentCountQuery);
    $stmt->bind_param(str_repeat('s', count($studentParams)), ...$studentParams);
    $stmt->execute();
    $studentTotal = (int) $stmt->get_result()->fetch_assoc()['cnt'];
} else {
    $studentTotal = (int) ($conn->query($studentCountQuery)->fetch_assoc()['cnt'] ?? 0);
}

$pendingTotal = (int) ($conn->query("SELECT COUNT(*) as c FROM students WHERE account_status = 'pending'")->fetch_assoc()['c'] ?? 0);

$pendingStudents = [];
if ($tab === 'pending') {
    $pendingOffset = ($page - 1) * $perPage;
    $pendingStmt = $conn->prepare("SELECT id, student_id, name, email, grade, section, password, account_status FROM students WHERE account_status = 'pending' ORDER BY id DESC LIMIT ?, ?");
    $pendingStmt->bind_param("ii", $pendingOffset, $perPage);
    $pendingStmt->execute();
    $pendingResult = $pendingStmt->get_result();
    while ($row = $pendingResult->fetch_assoc()) {
        $pendingStudents[] = $row;
    }
}
$pendingPages = $pendingTotal > 0 ? max(1, ceil($pendingTotal / $perPage)) : 1;

$students = [];
$offset = ($page - 1) * $perPage;
$studentQuery = "SELECT id, student_id, name, email, grade, section, rfid_number, password, account_status FROM students" . $studentWhere . " ORDER BY name ASC LIMIT " . (int)$offset . ", " . (int)$perPage;
if (!empty($studentParams)) {
    $stmt = $conn->prepare("SELECT id, student_id, name, email, grade, section, rfid_number, password, account_status FROM students" . $studentWhere . " ORDER BY name ASC LIMIT ?, ?");
    $bindTypes = str_repeat('s', count($studentParams)) . "ii";
    $bindValues = [...$studentParams, $offset, $perPage];
    $stmt->bind_param($bindTypes, ...$bindValues);
    $stmt->execute();
    $r = $stmt->get_result();
} else {
    $r = $conn->query($studentQuery);
}
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $students[] = $row;
    }
}

$studentPages = $studentTotal > 0 ? max(1, ceil($studentTotal / $perPage)) : 1;

require_once 'header_unified.php';
?>

<div class="max-w-7xl mx-auto px-6">
    <?php
    $approvedMsg = $_GET['approved'] ?? '';
    $rejectedMsg = $_GET['rejected'] ?? '';
    $csrfError = $_GET['csrf_error'] ?? '';
    ?>
    <?php if ($csrfError): ?>
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 font-semibold">Session expired or invalid token. Please try again.</div>
    <?php endif; ?>
    <?php if ($approvedMsg): ?>
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 font-semibold">Student account approved. They can now log in.</div>
    <?php endif; ?>
    <?php if ($rejectedMsg): ?>
    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 font-semibold">Student registration rejected.</div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b border-slate-200">
        <a href="?tab=students<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
           class="px-4 py-3 font-bold text-sm uppercase tracking-wide transition-colors <?php echo $tab === 'students' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700'; ?>">
            Students (<?php echo $studentTotal; ?>)
        </a>
        <a href="?tab=pending<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
           class="px-4 py-3 font-bold text-sm uppercase tracking-wide transition-colors <?php echo $tab === 'pending' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700'; ?>">
            Pending <?php if ($pendingTotal > 0): ?><span class="inline-flex items-center justify-center ml-1 min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-black bg-amber-500 text-white"><?php echo $pendingTotal; ?></span><?php endif; ?>
        </a>
        <a href="?tab=admins<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
           class="px-4 py-3 font-bold text-sm uppercase tracking-wide transition-colors <?php echo $tab === 'admins' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700'; ?>">
            Admins (<?php echo $adminTotal; ?>)
        </a>
    </div>

    <!-- Search -->
    <form method="GET" class="mb-6">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
        <div class="flex gap-2 max-w-md">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search by name, ID, email..." 
                   class="flex-1 px-4 py-2.5 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:outline-none">
            <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors">
                Search
            </button>
        </div>
    </form>

    <?php if ($tab === 'pending'): ?>
    <!-- Pending Approvals Table -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 bg-amber-50 border-b border-amber-200">
            <p class="text-sm font-semibold text-amber-800">Verify these students are enrolled before approving. Once approved, they can log in to the portal.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Section</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($pendingStudents)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">No pending registrations.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($pendingStudents as $p): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800"><?php echo htmlspecialchars($p['student_id']); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($p['name']); ?></td>
                        <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($p['email'] ?? '—'); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($p['grade']); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($p['section']); ?></td>
                        <td class="px-6 py-4">
                            <form method="POST" class="inline-flex gap-2">
                                <input type="hidden" name="approve_student" value="1">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($p['student_id']); ?>">
                                <button type="submit" name="act" value="approve" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg transition-colors">Approve</button>
                                <button type="button" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-colors" onclick="confirmReject(this.closest('form'))">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pendingPages > 1): ?>
        <div class="px-6 py-4 border-t border-slate-200 flex justify-between items-center">
            <p class="text-sm text-slate-500">Page <?php echo $page; ?> of <?php echo $pendingPages; ?></p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                <a href="?tab=pending&page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-bold">← Prev</a>
                <?php endif; ?>
                <?php if ($page < $pendingPages): ?>
                <a href="?tab=pending&page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-bold">Next →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php elseif ($tab === 'students'): ?>
    <!-- Students Table -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Section</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">RFID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Password Set</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500">No student accounts found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($students as $s): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800"><?php echo htmlspecialchars($s['student_id']); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($s['name']); ?></td>
                        <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($s['email'] ?? '—'); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($s['grade']); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($s['section']); ?></td>
                        <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($s['rfid_number'] ?? '—'); ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-green-100 text-green-800">Approved</span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (!empty($s['password'])): ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-green-100 text-green-800">Yes</span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800">No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($studentPages > 1): ?>
        <div class="px-6 py-4 border-t border-slate-200 flex justify-between items-center">
            <p class="text-sm text-slate-500">Page <?php echo $page; ?> of <?php echo $studentPages; ?></p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                <a href="?tab=students&page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-bold">← Prev</a>
                <?php endif; ?>
                <?php if ($page < $studentPages): ?>
                <a href="?tab=students&page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm font-bold">Next →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Admins Table -->
    <div class="bg-white rounded-2xl border-2 border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Last Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($admins)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">No admin accounts found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($admins as $a): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800"><?php echo htmlspecialchars($a['username']); ?></td>
                        <td class="px-6 py-4 text-slate-700"><?php echo htmlspecialchars($a['full_name']); ?></td>
                        <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($a['email'] ?? '—'); ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $a['role'] === 'Super Admin' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'; ?>">
                                <?php echo htmlspecialchars($a['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $a['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo htmlspecialchars($a['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 text-sm"><?php echo $a['last_login'] ? date('M j, Y g:i A', strtotime($a['last_login'])) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmReject(form) {
    if (typeof Swal === 'undefined') { form.submit(); return; }
    Swal.fire({
        title: 'Reject Registration?',
        text: 'This student will not be able to log in.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Reject'
    }).then(function(r) {
        if (r.isConfirmed) {
            var inp = document.createElement('input');
            inp.name = 'act'; inp.value = 'reject'; inp.type = 'hidden';
            form.appendChild(inp);
            form.submit();
        }
    });
}
</script>
<?php
require_once 'footer_unified.php';
?>
