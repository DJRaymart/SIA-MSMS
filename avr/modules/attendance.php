<?php
session_start();
if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/security.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid session token. Please refresh and try again.';
        header("Location: attendance.php");
        exit;
    }

    $conn = getDBConnection();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO log_attendance (Name, GradeSection, NoOfStudent, Date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $_POST['name'], $_POST['gradesection'], $_POST['noofstudent'], $_POST['date']);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Attendance record added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add attendance record.';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'update') {
            $stmt = $conn->prepare("UPDATE log_attendance SET Name=?, GradeSection=?, NoOfStudent=?, Date=? WHERE ID=?");
            $stmt->bind_param("ssisi", $_POST['name'], $_POST['gradesection'], $_POST['noofstudent'], $_POST['date'], $_POST['id']);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Attendance record updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update attendance record.';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'delete') {
            $stmt = $conn->prepare("DELETE FROM log_attendance WHERE ID=?");
            $stmt->bind_param("i", $_POST['id']);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Attendance record deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete attendance record.';
            }
            $stmt->close();
        }
    }
    $conn->close();
    header("Location: attendance.php");
    exit;
}

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM log_attendance ORDER BY Date DESC, ID DESC");
$attendance = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<?php require_once '../header_unified.php'; ?>

            <div class="flex justify-between items-center mb-6">
                <div></div>
                <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Record
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Grade & Section</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">No. of Student</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php if (empty($attendance)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">No attendance records found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendance as $record): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-slate-900"><?php echo htmlspecialchars($record['ID']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-900 font-semibold"><?php echo htmlspecialchars($record['Name']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($record['GradeSection']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($record['NoOfStudent']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($record['Date']); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <button onclick="editRecord(<?php echo htmlspecialchars(json_encode($record)); ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-colors">Edit</button>
                                                <button onclick="deleteRecord(<?php echo $record['ID']; ?>, '<?php echo htmlspecialchars(addslashes($record['Name'])); ?>')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-colors">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Add/Edit Modal -->
    <div id="attendanceModal" class="avr-modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="avr-modal-content bg-white rounded-2xl max-h-[90vh] overflow-y-auto">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center rounded-t-2xl">
                <h3 id="modalTitle" class="text-white text-xl font-bold">Add New Attendance Record</h3>
                <button onclick="closeModal()" class="text-white hover:text-blue-200 text-2xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                <form method="POST" id="attendanceForm">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="id" id="id">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Name *</label>
                        <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Grade & Section *</label>
                        <input type="text" name="gradesection" id="gradesection" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">No. of Student *</label>
                        <input type="number" name="noofstudent" id="noofstudent" min="1" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Date *</label>
                        <input type="date" name="date" id="date" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="closeModal()" class="px-6 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-semibold transition-colors">Cancel</button>
                        <button type="submit" id="submitBtn" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">Add Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var modal = document.getElementById('attendanceModal');
            if (modal && modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
        })();
        function openAddModal() {
            resetForm();
            document.getElementById('attendanceModal').classList.remove('hidden');
            document.getElementById('attendanceModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('attendanceModal').classList.add('hidden');
            document.getElementById('attendanceModal').classList.remove('flex');
            resetForm();
        }

        function editRecord(record) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('id').value = record.ID;
            document.getElementById('name').value = record.Name;
            document.getElementById('gradesection').value = record.GradeSection;
            document.getElementById('noofstudent').value = record.NoOfStudent;
            document.getElementById('date').value = record.Date;
            document.getElementById('modalTitle').textContent = 'Edit Attendance Record';
            document.getElementById('submitBtn').textContent = 'Update Record';
            document.getElementById('attendanceModal').classList.remove('hidden');
            document.getElementById('attendanceModal').classList.add('flex');
        }

        function resetForm() {
            document.getElementById('attendanceForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('id').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Attendance Record';
            document.getElementById('submitBtn').textContent = 'Add Record';
        }

        function deleteRecord(recordId, recordName) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete the attendance record for "${recordName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="${recordId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        document.getElementById('attendanceModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // SweetAlert on form submit (loading state)
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('submitBtn');
            if (btn.textContent.indexOf('Add') !== -1 || btn.textContent.indexOf('Update') !== -1) {
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
            }
        });

        // Show SweetAlert messages from PHP session
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success']; unset($_SESSION['success']); ?>',
                showConfirmButton: false,
                timer: 2000
            });
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?php echo $_SESSION['error']; unset($_SESSION['error']); ?>',
                showConfirmButton: false,
                timer: 2000
            });
        <?php endif; ?>
    </script>
<?php require_once '../footer_unified.php'; ?>

