<?php
require_once 'db.php';
require_once 'header_unified.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Head') {
    header("Location: index.php");
    exit();
}

$action = $_GET['action'] ?? 'list';

if ($action == 'add' || $action == 'edit') {
    
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        $action = 'list';
    }
}
$alert_message = '';
$alert_type = '';

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $institutional_id = $_POST['institutional_id'];
    $role = $_POST['role'];
    
    try {
        
        $check_sql = "SELECT COUNT(*) FROM librarian WHERE institutional_id = :institutional_id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([':institutional_id' => $institutional_id]);
        
        if ($check_stmt->fetchColumn() > 0) {
            $alert_message = 'Error: Student / Employee ID already exists!';
            $alert_type = 'danger';
        } else {
            $sql = "INSERT INTO librarian (full_name, institutional_id, role) 
                    VALUES (:full_name, :institutional_id, :role)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':full_name' => $full_name,
                ':institutional_id' => $institutional_id,
                ':role' => $role
            ]);
            
            $alert_message = 'Librarian added successfully!';
            $alert_type = 'success';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $alert_message = 'Error adding librarian: ' . $e->getMessage();
        $alert_type = 'danger';
    }
}

if ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $librarian_id = $_POST['librarian_id'];
    $full_name = $_POST['full_name'];
    $institutional_id = $_POST['institutional_id'];
    $role = $_POST['role'];
    
    try {
        
        $check_sql = "SELECT COUNT(*) FROM librarian WHERE institutional_id = :institutional_id AND librarian_id != :librarian_id";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([
            ':institutional_id' => $institutional_id,
            ':librarian_id' => $librarian_id
        ]);
        
        if ($check_stmt->fetchColumn() > 0) {
            $alert_message = 'Error: Student / Employee ID already exists!';
            $alert_type = 'danger';
        } else {
            $sql = "UPDATE librarian SET 
                    full_name = :full_name,
                    institutional_id = :institutional_id,
                    role = :role
                    WHERE librarian_id = :librarian_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':full_name' => $full_name,
                ':institutional_id' => $institutional_id,
                ':role' => $role,
                ':librarian_id' => $librarian_id
            ]);
            
            $alert_message = 'Librarian updated successfully!';
            $alert_type = 'success';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $alert_message = 'Error updating librarian: ' . $e->getMessage();
        $alert_type = 'danger';
    }
}

if ($action == 'delete' && isset($_GET['id'])) {
    $librarian_id = $_GET['id'];

    if ($librarian_id == $_SESSION['librarian_id']) {
        $alert_message = 'You cannot delete your own account!';
        $alert_type = 'danger';
        $action = 'list';
    } else {
        try {
            $sql = "DELETE FROM librarian WHERE librarian_id = :librarian_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':librarian_id' => $librarian_id]);
            
            $alert_message = 'Librarian deleted successfully!';
            $alert_type = 'success';
            $action = 'list';
        } catch (PDOException $e) {
            $alert_message = 'Error deleting librarian: ' . $e->getMessage();
            $alert_type = 'danger';
            $action = 'list';
        }
    }
}
?>

<div class="container-fluid">
    <?php if ($alert_message): ?>
        <div class="mb-6 p-4 rounded-lg border <?php echo $alert_type == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
            <div class="flex items-center gap-2">
                <?php if ($alert_type == 'success'): ?>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                <?php else: ?>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($alert_message); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Librarian Management
            </h2>
            <p class="text-slate-500 mt-1">Manage all librarians and their access permissions</p>
        </div>
        <button onclick="openAddLibrarianModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Librarian
        </button>
    </div>

        <!-- Statistics Cards -->
        <?php
        try {
            
            $total_stmt = $conn->query("SELECT COUNT(*) as total FROM librarian");
            $total = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $head_stmt = $conn->query("SELECT COUNT(*) as head FROM librarian WHERE role = 'Head'");
            $head = $head_stmt->fetch(PDO::FETCH_ASSOC)['head'];

            $student_stmt = $conn->query("SELECT COUNT(*) as student FROM librarian WHERE role = 'Working Student'");
            $student = $student_stmt->fetch(PDO::FETCH_ASSOC)['student'];
        } catch (PDOException $e) {
            $total = 0;
            $head = 0;
            $student = 0;
        }
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold mb-1"><?php echo $total; ?></div>
                <div class="text-sm opacity-90 uppercase tracking-wide">Total Librarians</div>
            </div>
            
            <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold mb-1"><?php echo $head; ?></div>
                <div class="text-sm opacity-90 uppercase tracking-wide">Head Librarians</div>
            </div>
            
            <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold mb-1"><?php echo $student; ?></div>
                <div class="text-sm opacity-90 uppercase tracking-wide">Working Students</div>
            </div>
        </div>

        <!-- Librarians List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-slate-800 text-white px-6 py-4">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Librarian Directory
                </h3>
            </div>
            <div class="p-6">
                <?php if ($total > 0): ?>
                    <div class="overflow-x-auto">
                        <table id="librariansTable" class="data-table w-full">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Full Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Student / Employee ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT * FROM librarian ORDER BY role, full_name");
                                    $librarians = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($librarians as $row): 
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 bg-blue-600 text-white rounded font-bold text-sm">#<?php echo htmlspecialchars($row['librarian_id']); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded font-mono text-sm"><?php echo htmlspecialchars($row['institutional_id']); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($row['role'] == 'Head'): ?>
                                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                                <?php echo htmlspecialchars($row['role']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-cyan-100 text-cyan-800 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                <?php echo htmlspecialchars($row['role']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            <span class="text-green-700 font-medium text-sm">Active</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <button onclick="openEditLibrarianModal(<?php echo $row['librarian_id']; ?>, '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['institutional_id'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['role'], ENT_QUOTES); ?>')" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <?php if ($row['librarian_id'] != $_SESSION['librarian_id']): ?>
                                                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors delete-librarian-btn"
                                                        data-librarian-id="<?php echo $row['librarian_id']; ?>"
                                                        data-librarian-name="<?php echo htmlspecialchars($row['full_name']); ?>">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <button class="p-2 text-slate-400 cursor-not-allowed rounded-lg" disabled>
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; 
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='6' class='px-6 py-4 text-center text-red-600'>Error loading librarians: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <h4 class="text-lg font-semibold text-slate-700 mb-2">No Librarians Found</h4>
                        <p class="text-slate-500 mb-4">Get started by adding your first librarian to the system</p>
                        <button onclick="openAddLibrarianModal()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Librarian
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Login Instructions -->
                <div class="mt-6 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-4 text-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        <div>
                            <h5 class="font-bold mb-1">Login Instructions</h5>
                            <p class="text-sm opacity-90">Librarians use their Student / Employee ID (shown above) to login. No password required.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Add/Edit Librarian Modal -->
<div id="librarianModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center pt-20 pb-8">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="bg-slate-800 text-white px-6 py-4 flex justify-between items-center sticky top-0 z-10">
            <h3 class="text-lg font-bold flex items-center gap-2" id="modalTitle">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Librarian
            </h3>
            <button onclick="closeLibrarianModal()" class="text-white hover:text-slate-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <form method="POST" id="librarianForm" class="space-y-6">
                <input type="hidden" name="librarian_id" id="modal_librarian_id" value="">
                <input type="hidden" name="action" id="modal_action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="modal_full_name" class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                        <input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="modal_full_name" name="full_name" 
                               required
                               placeholder="Enter full name">
                    </div>
                    <div>
                        <label for="modal_institutional_id" class="block text-sm font-medium text-slate-700 mb-2">Student / Employee ID *</label>
                        <input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="modal_institutional_id" name="institutional_id" 
                               required
                               pattern="[A-Za-z0-9-]+" 
                               title="Only letters, numbers, and hyphens are allowed"
                               placeholder="e.g., LIB-001, STAFF-2024">
                        <p class="mt-1 text-xs text-slate-500">This will be used for login. Example: LIB-001, STAFF-2024</p>
                    </div>
                </div>
                
                <div>
                    <label for="modal_role" class="block text-sm font-medium text-slate-700 mb-2">Role *</label>
                    <select class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="modal_role" name="role" required>
                        <option value="">Select a role</option>
                        <option value="Head">Head Librarian</option>
                        <option value="Working Student">Working Student</option>
                    </select>
                </div>
                
                <!-- Info Alerts -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <strong class="text-blue-900">Note:</strong> 
                            <p class="text-sm text-blue-800 mt-1">Librarians will login using only their Student / Employee ID (no password required). Make sure to provide the Student / Employee ID to the librarian securely.</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <strong class="text-yellow-900">Security Notice:</strong> 
                            <p class="text-sm text-yellow-800 mt-1">Student / Employee IDs should be kept confidential and treated like passwords. Change IDs periodically for enhanced security.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="closeLibrarianModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <span id="submitButtonText">Add Librarian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 z-[200] hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-red-600 text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Confirm Deletion
            </h3>
            <button onclick="closeDeleteModal()" class="text-white hover:text-red-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h5 class="text-lg font-bold text-slate-900 mb-2">Delete Librarian?</h5>
                <p class="text-slate-600 mb-4">You are about to delete <strong id="deleteLibrarianName" class="text-slate-900"></strong>. This action cannot be undone.</p>
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mt-4">
                    <p class="text-sm text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        All associated data will be permanently removed from the system.
                    </p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 rounded-b-xl flex justify-end gap-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 text-slate-700 bg-slate-200 hover:bg-slate-300 rounded-lg transition-colors">
                Cancel
            </button>
            <a href="#" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                Yes, Delete Permanently
            </a>
        </div>
    </div>
</div>

<script>
// Librarian Modal Functions
function openAddLibrarianModal() {
    // Reset form
    document.getElementById('librarianForm').reset();
    document.getElementById('modal_librarian_id').value = '';
    document.getElementById('modal_action').value = 'add';
    document.getElementById('modalTitle').innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add New Librarian
    `;
    document.getElementById('submitButtonText').textContent = 'Add Librarian';
    
    // Show modal
    document.getElementById('librarianModal').classList.remove('hidden');
    document.getElementById('librarianModal').classList.add('flex');
    
    // Focus first input
    setTimeout(() => {
        document.getElementById('modal_full_name').focus();
    }, 100);
}

function openEditLibrarianModal(librarianId, fullName, institutionalId, role) {
    // Populate form
    document.getElementById('modal_librarian_id').value = librarianId;
    document.getElementById('modal_action').value = 'edit';
    document.getElementById('modal_full_name').value = fullName;
    document.getElementById('modal_institutional_id').value = institutionalId;
    document.getElementById('modal_role').value = role;
    
    // Update modal title and button
    document.getElementById('modalTitle').innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Edit Librarian
    `;
    document.getElementById('submitButtonText').textContent = 'Update Librarian';
    
    // Update form action
    document.getElementById('librarianForm').action = 'librarians.php?action=edit';
    
    // Show modal
    document.getElementById('librarianModal').classList.remove('hidden');
    document.getElementById('librarianModal').classList.add('flex');
    
    // Focus first input
    setTimeout(() => {
        document.getElementById('modal_full_name').focus();
    }, 100);
}

function closeLibrarianModal() {
    document.getElementById('librarianModal').classList.add('hidden');
    document.getElementById('librarianModal').classList.remove('flex');
    document.getElementById('librarianForm').reset();
    document.getElementById('librarianForm').action = 'librarians.php?action=add';
}

// Delete Modal Functions
function openDeleteModal(librarianId, librarianName) {
    document.getElementById('deleteLibrarianName').textContent = librarianName;
    document.getElementById('confirmDeleteBtn').href = 'librarians.php?action=delete&id=' + librarianId;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
    document.getElementById('deleteConfirmModal').classList.add('flex');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    document.getElementById('deleteConfirmModal').classList.remove('flex');
    document.getElementById('confirmDeleteBtn').href = '#';
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button clicks
    document.querySelectorAll('.delete-librarian-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const librarianId = this.getAttribute('data-librarian-id');
            const librarianName = this.getAttribute('data-librarian-name');
            openDeleteModal(librarianId, librarianName);
        });
    });
    
    // Close modals when clicking outside
    document.getElementById('librarianModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLibrarianModal();
        }
    });
    
    document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
    
    // Handle form submission
    document.getElementById('librarianForm').addEventListener('submit', function(e) {
        const action = document.getElementById('modal_action').value;
        this.action = 'librarians.php?action=' + action;
    });
});
</script>

<?php require_once 'footer_unified.php'; ?>