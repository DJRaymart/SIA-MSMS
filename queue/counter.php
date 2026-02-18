<?php include 'config.php'; ?>
<?php require_once 'header_unified.php'; ?>

<?php

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT c.*, cu.queue_number as current_queue_number, cu.name as current_customer_name 
                          FROM counters c 
                          LEFT JOIN customers cu ON c.current_customer_id = cu.id 
                          ORDER BY c.id");
    $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $counters = [];
    $error = $e->getMessage();
}
?>

<!-- Counter Management Section -->
<div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Counter Management</h2>
        <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Counter
        </button>
    </div>

    <!-- Counters Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($counters)): ?>
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-slate-500 text-lg">No counters found</p>
                <p class="text-slate-400 text-sm mt-2">Click "Add Counter" to create one</p>
            </div>
        <?php else: ?>
            <?php foreach ($counters as $counter): ?>
                <div class="bg-gradient-to-br <?php echo $counter['is_online'] ? 'from-green-50 to-emerald-50 border-green-200' : 'from-red-50 to-rose-50 border-red-200'; ?> border-2 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 mb-1"><?php echo htmlspecialchars($counter['name']); ?></h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $counter['is_online'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                                <?php echo $counter['is_online'] ? 'Online' : 'Offline'; ?>
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="toggleCounter(<?php echo $counter['id']; ?>, <?php echo $counter['is_online'] ? '0' : '1'; ?>)" 
                                    class="p-2 rounded-lg <?php echo $counter['is_online'] ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-green-100 hover:bg-green-200 text-green-700'; ?> transition-colors"
                                    title="<?php echo $counter['is_online'] ? 'Set Offline' : 'Set Online'; ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $counter['is_online'] ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M5 13l4 4L19 7'; ?>" />
                                </svg>
                            </button>
                            <button onclick="editCounter(<?php echo htmlspecialchars(json_encode($counter)); ?>)" 
                                    class="p-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 transition-colors"
                                    title="Edit Counter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="deleteCounter(<?php echo $counter['id']; ?>, '<?php echo htmlspecialchars(addslashes($counter['name'])); ?>')" 
                                    class="p-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 transition-colors"
                                    title="Delete Counter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Service Types</p>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $serviceTypes = json_decode($counter['service_types'], true);
                                if (is_array($serviceTypes) && !empty($serviceTypes)):
                                    foreach ($serviceTypes as $service):
                                ?>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs font-medium">
                                        <?php echo htmlspecialchars(ucfirst($service)); ?>
                                    </span>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <span class="text-slate-400 text-sm italic">No services assigned</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($counter['current_customer_name']): ?>
                            <div class="pt-3 border-t border-slate-200">
                                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Currently Serving</p>
                                <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($counter['current_customer_name']); ?></p>
                                <p class="text-xs text-slate-500">Queue: <?php echo htmlspecialchars($counter['current_queue_number']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="pt-3 border-t border-slate-200">
                                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Status</p>
                                <p class="text-sm text-slate-500">Available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Counter Modal -->
<div id="counterModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-200">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-slate-900" id="modalTitle">Add Counter</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <form id="counterForm" class="p-6 space-y-4">
            <input type="hidden" id="counterId" name="counter_id">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Counter Name</label>
                <input type="text" id="counterName" name="name" required
                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                       placeholder="e.g., Counter 1">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Service Types</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="services[]" value="general" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-slate-700">General</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="services[]" value="payment" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-slate-700">Payment</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="services[]" value="inquiry" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-slate-700">Inquiry/Registrar</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="services[]" value="technical" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-slate-700">Technical</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="services[]" value="support" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-slate-700">Support</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="isOnline" name="is_online" checked class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <span class="text-slate-700 font-semibold">Counter is Online</span>
                </label>
            </div>

            <div class="flex gap-3 justify-end pt-4 border-t border-slate-200">
                <button type="button" onclick="closeModal()" class="px-6 py-2 border-2 border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Save Counter
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let editingCounterId = null;

function openAddModal() {
    editingCounterId = null;
    document.getElementById('modalTitle').textContent = 'Add Counter';
    document.getElementById('counterForm').reset();
    document.getElementById('counterId').value = '';
    document.getElementById('isOnline').checked = true;
    // Uncheck all service checkboxes
    document.querySelectorAll('input[name="services[]"]').forEach(cb => cb.checked = false);
    document.getElementById('counterModal').classList.remove('hidden');
    document.getElementById('counterModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('counterModal').classList.add('hidden');
    document.getElementById('counterModal').classList.remove('flex');
    editingCounterId = null;
}

function editCounter(counter) {
    editingCounterId = counter.id;
    document.getElementById('modalTitle').textContent = 'Edit Counter';
    document.getElementById('counterId').value = counter.id;
    document.getElementById('counterName').value = counter.name;
    document.getElementById('isOnline').checked = counter.is_online == 1;
    
    // Parse and check service types
    const serviceTypes = JSON.parse(counter.service_types || '[]');
    document.querySelectorAll('input[name="services[]"]').forEach(cb => {
        cb.checked = serviceTypes.includes(cb.value);
    });
    
    document.getElementById('counterModal').classList.remove('hidden');
    document.getElementById('counterModal').classList.add('flex');
}

function deleteCounter(id, name) {
    Swal.fire({
        title: 'Delete Counter?',
        text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/manage_counter.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Counter has been deleted.', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete counter.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An error occurred while deleting the counter.', 'error');
            });
        }
    });
}

function toggleCounter(id, newStatus) {
    fetch('api/manage_counter.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', id: id, is_online: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            Swal.fire('Error!', data.message || 'Failed to update counter status.', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error!', 'An error occurred while updating the counter.', 'error');
    });
}

document.getElementById('counterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const services = Array.from(document.querySelectorAll('input[name="services[]"]:checked')).map(cb => cb.value);
    
    const data = {
        action: editingCounterId ? 'update' : 'create',
        id: editingCounterId || null,
        name: formData.get('name'),
        service_types: JSON.stringify(services),
        is_online: document.getElementById('isOnline').checked ? 1 : 0
    };
    
    fetch('api/manage_counter.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', editingCounterId ? 'Counter updated successfully.' : 'Counter created successfully.', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error!', data.message || 'Failed to save counter.', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error!', 'An error occurred while saving the counter.', 'error');
    });
});

// Close modal on outside click
document.getElementById('counterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once 'footer_unified.php'; ?>
