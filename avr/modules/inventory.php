<?php
session_start();
if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/security.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Invalid session token. Please refresh and try again.';
        header("Location: inventory.php");
        exit;
    }

    $conn = getDBConnection();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO avr_inventory (Item, Description, Model, SerialNo, DateReceived, Remark) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $_POST['item'], $_POST['description'], $_POST['model'], $_POST['serialno'], $_POST['datereceived'], $_POST['remark']);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Item added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add item.';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'update') {
            $stmt = $conn->prepare("UPDATE avr_inventory SET Item=?, Description=?, Model=?, SerialNo=?, DateReceived=?, Remark=? WHERE ItemID=?");
            $stmt->bind_param("ssssssi", $_POST['item'], $_POST['description'], $_POST['model'], $_POST['serialno'], $_POST['datereceived'], $_POST['remark'], $_POST['itemid']);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Item updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update item.';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'delete') {
            $stmt = $conn->prepare("DELETE FROM avr_inventory WHERE ItemID=?");
            $stmt->bind_param("i", $_POST['itemid']);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Item deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete item.';
            }
            $stmt->close();
        }
    }
    $conn->close();
    header("Location: inventory.php");
    exit;
}

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM avr_inventory ORDER BY ItemID DESC");
$items = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<?php require_once '../header_unified.php'; ?>

            <div class="flex justify-between items-center mb-6">
                <div></div>
                <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Item
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">ItemID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Model</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Serial No.</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Date Received</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Remark</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-slate-500">No items found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-slate-900"><?php echo htmlspecialchars($item['ItemID']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-900 font-semibold"><?php echo htmlspecialchars($item['Item']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['Description']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['Model']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['SerialNo']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['DateReceived']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['Remark']); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-colors">Edit</button>
                                                <button onclick="deleteItem(<?php echo $item['ItemID']; ?>, '<?php echo htmlspecialchars(addslashes($item['Item'])); ?>')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-colors">Delete</button>
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
    <div id="inventoryModal" class="avr-modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="avr-modal-content bg-white rounded-2xl max-h-[90vh] overflow-y-auto">
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center rounded-t-2xl">
                <h3 id="modalTitle" class="text-white text-xl font-bold">Add New Item</h3>
                <button onclick="closeModal()" class="text-white hover:text-blue-200 text-2xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                <form method="POST" id="inventoryForm">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="itemid" id="itemid">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Item *</label>
                        <input type="text" name="item" id="item" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                        <textarea name="description" id="description" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Model</label>
                        <input type="text" name="model" id="model" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Serial No.</label>
                        <input type="text" name="serialno" id="serialno" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Date Received</label>
                        <input type="date" name="datereceived" id="datereceived" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Remark</label>
                        <textarea name="remark" id="remark" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="closeModal()" class="px-6 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-semibold transition-colors">Cancel</button>
                        <button type="submit" id="submitBtn" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var modal = document.getElementById('inventoryModal');
            if (modal && modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
        })();
        function openAddModal() {
            resetForm();
            document.getElementById('inventoryModal').classList.remove('hidden');
            document.getElementById('inventoryModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('inventoryModal').classList.add('hidden');
            document.getElementById('inventoryModal').classList.remove('flex');
            resetForm();
        }

        function editItem(item) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('itemid').value = item.ItemID;
            document.getElementById('item').value = item.Item;
            document.getElementById('description').value = item.Description || '';
            document.getElementById('model').value = item.Model || '';
            document.getElementById('serialno').value = item.SerialNo || '';
            document.getElementById('datereceived').value = item.DateReceived || '';
            document.getElementById('remark').value = item.Remark || '';
            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('submitBtn').textContent = 'Update Item';
            document.getElementById('inventoryModal').classList.remove('hidden');
            document.getElementById('inventoryModal').classList.add('flex');
        }

        function resetForm() {
            document.getElementById('inventoryForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('itemid').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Item';
            document.getElementById('submitBtn').textContent = 'Add Item';
        }

        function deleteItem(itemId, itemName) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete "${itemName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="itemid" value="${itemId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        document.getElementById('inventoryModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // SweetAlert on form submit (loading state)
        document.getElementById('inventoryForm').addEventListener('submit', function(e) {
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

