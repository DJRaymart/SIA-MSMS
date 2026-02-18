    <?php
    
    include "../auth/session_guard.php";

    include "../config/db.php";
    include "header.php";

    $limit = 9; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    $total_result = $conn->query("SELECT COUNT(*) as total FROM inventory");
    $total_items = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $limit);

    $result = $conn->query("
        SELECT i.*, l.lab_name, loc.location_name
        FROM inventory i
        LEFT JOIN labs l ON i.lab_id = l.lab_id
        LEFT JOIN locations loc ON i.location_id = loc.location_id
        ORDER BY i.date_added DESC
        LIMIT $offset, $limit
    ");

    $alert = null;
    
    ?>
    <style>
        body {
            overflow: hidden;
        }

        #inventoryTableContainer {
            overflow: hidden;
        }

        .compact-td {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        .header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Floating modal - AVR style */
        .sciLab-modal-overlay {
            backdrop-filter: blur(6px);
            animation: sciLab-modal-fadeIn 0.25s ease-out;
            z-index: 9999;
            padding: 1rem;
            box-sizing: border-box;
        }
        .sciLab-modal-content {
            animation: sciLab-modal-floatIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: min(48rem, calc(100vw - 2rem));
            flex-shrink: 0;
        }
        @keyframes sciLab-modal-fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes sciLab-modal-floatIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
    <script src="<?php echo htmlspecialchars((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/sciLab/assets/js/global-alert.js'); ?>"></script>

    <div class="flex h-screen bg-slate-50 overflow-hidden">
        <?php include "../admin/sidebar.php"; ?>

        <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-5">

            <div class="w-full flex-shrink-0 header py-4 px-8 flex justify-between items-center print:hidden">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                    <h2 class="text-sm font-black text-slate-700 uppercase tracking-[0.2em]">Module: Inventory</h2>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs font-semibold text-slate-600 uppercase"><?php echo date("l, M d, Y"); ?></span>
                </div>
            </div>

            <div class="flex-1 flex flex-col px-8 py-4 overflow-hidden">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 px-2 border-b border-slate-200 pb-6 shrink-0">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 tracking-tighter">
                            Inventory <span class="text-blue-600">Registry</span>
                        </h2>
                        <p class="text-sm font-semibold text-slate-600 mt-2">Total Assets Tracked: <span class="text-blue-600 font-bold"><?php echo $total_items; ?></span></p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div class="relative">
                            <input type="text" id="inventorySearch" placeholder="Search database..."
                                class="bg-white border border-slate-300 rounded-xl px-4 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none w-64 transition-all">
                        </div>
                        <button id="openAddModal"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wide shadow-lg transition-all transform hover:scale-105 active:scale-95">
                            + Register New Item
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
                    <table id="inventoryTable" class="min-w-full text-sm text-slate-700 border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-5 py-3 text-left font-bold text-slate-600 text-xs tracking-wide w-12">
                                    <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">ID</th>
                                <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Item Name</th>
                                <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Quantity</th>
                                <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Model No</th>
                                <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Serial No</th>
                                <th class="px-6 py-3 text-center font-bold uppercase text-slate-700 text-xs tracking-wide">Location</th>
                                <th class="px-6 py-3 text-center font-bold uppercase text-slate-700 text-xs tracking-wide">Lab</th>
                                <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="inventory-row hover:bg-blue-50 transition-colors cursor-pointer group" data-item='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>
                                        <td class="p-5 checkbox-cell">
                                            <input type="checkbox" class="item-checkbox w-4 h-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500" value="<?= $row['item_id'] ?>">
                                        </td>
                                        <td class="p-4 font-mono text-blue-600 font-semibold">#<?= $row['item_id'] ?></td>
                                        <td class="p-3 font-bold text-slate-900"><?= htmlspecialchars($row['item_name']) ?></td>
                                        <td class="p-3 font-semibold">
                                            <span class="text-blue-600 font-bold"><?= htmlspecialchars($row['quantity']) ?></span>
                                            <span class="text-xs text-slate-500 ml-1">units</span>
                                        </td>
                                        <td class="p-3 font-mono text-slate-700"><?= htmlspecialchars($row['model_no']) ?></td>
                                        <td class="p-3 font-mono text-slate-700"><?= htmlspecialchars($row['serial_no']) ?></td>
                                        <td class="p-3 text-center">
                                            <span class="px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 text-xs font-semibold text-blue-700">
                                                <?= htmlspecialchars($row['location_name']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="px-3 py-1 rounded-lg bg-purple-50 border border-purple-200 text-xs font-semibold text-purple-700">
                                                <?= htmlspecialchars($row['lab_name']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 font-mono text-xs text-slate-500"><?= date("Y.m.d", strtotime($row['date_added'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center shrink-0">
                        <p class="text-xs font-semibold text-slate-600">
                            Showing <?php echo ($total_items > 0 ? $offset + 1 : 0); ?> to <?php echo min($offset + $limit, $total_items); ?> of <?php echo $total_items; ?> entries
                        </p>

                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <button type="button" onclick="changeSector(<?php echo $page - 1; ?>)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:text-blue-600 hover:border-blue-500 transition-all cursor-pointer">
                                    &lt; Previous
                                </button>
                            <?php endif; ?>

                            <?php
                            $start_loop = max(1, $page - 2);
                            $end_loop = min($total_pages, $page + 2);
                            for ($i = $start_loop; $i <= $end_loop; $i++): ?>
                                <button type="button"
                                    onclick="changeSector(<?php echo $i; ?>)"
                                    class="px-4 py-2 rounded-lg text-xs font-semibold transition-all cursor-pointer <?php echo ($i == $page) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border border-slate-300 text-slate-600 hover:text-blue-600 hover:border-blue-500'; ?>">
                                    <?php echo sprintf("%02d", $i); ?>
                                </button>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <button type="button" onclick="changeSector(<?php echo $page + 1; ?>)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:text-blue-600 hover:border-blue-500 transition-all cursor-pointer">
                                    Next &gt;
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form id="paginationNavigator" method="GET" style="display:none;">
                        <input type="hidden" name="page" id="navPageInput">
                    </form>
                </div>

                <div id="bulkActionBar" class="fixed bottom-10 left-1/2 -translate-x-1/2 bg-white border border-red-300 px-6 py-4 rounded-xl shadow-lg hidden items-center gap-6 z-40">
                    <div class="flex flex-col">
                        <span id="selectedCount" class="text-slate-900 font-bold text-sm">0 Items Selected</span>
                        <span class="text-xs font-semibold text-slate-500">Bulk Action Mode</span>
                    </div>
                    <button id="deleteSelected" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-all transform hover:scale-105">
                        Delete Selected
                    </button>
                    <button id="cancelSelection" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Cancel
                    </button>
                </div>
        </main>
    </div>

    <div id="viewModal" class="sciLab-modal-overlay fixed inset-0 bg-black/50 hidden items-center justify-center flex">
        <div class="sciLab-modal-content bg-white border border-slate-200 rounded-2xl relative overflow-hidden flex flex-col max-h-[90vh] w-full max-w-3xl">
        <form id="modalForm" class="flex flex-col flex-1 min-h-0">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent"></div>

            <button type="button" id="closeModal" class="absolute top-4 right-6 text-slate-500 hover:text-slate-900 text-3xl font-light transition-colors z-10">&times;</button>

            <div class="p-8 pb-4">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    System <span class="text-blue-600">Registry Entry</span>
                </h2>
                <div class="flex items-center gap-4 mt-2">
                    <p class="text-xs font-semibold text-slate-600">Query ID: <span id="displayItemId" class="text-blue-600 font-bold">#000</span></p>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>
            </div>

            <div class="p-8 pt-0 overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 text-slate-700">
                    <input type="hidden" name="item_id" id="modalItemId">

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Item Name</label>
                        <input type="text" name="item_name" id="modalItemName" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all" required>
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Quantity</label>
                        <input type="number" name="quantity" id="modalQuantity" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none font-semibold text-blue-600" required min="1">
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Model Number</label>
                        <input type="text" name="model_no" id="modalModelNo" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Serial Number</label>
                        <input type="text" name="serial_no" id="modalSerialNo" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Assigned Location</label>
                        <select name="location_id" id="modalLocation" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                            <?php
                            $locations = $conn->query("SELECT location_id, location_name FROM locations");
                            while ($loc = $locations->fetch_assoc()):
                            ?>
                                <option value="<?= $loc['location_id'] ?>"><?= htmlspecialchars($loc['location_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Laboratory Node</label>
                        <select name="lab_id" id="modalLab" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none appearance-none">
                            <?php
                            $labs = $conn->query("SELECT lab_id, lab_name FROM labs");
                            while ($lab = $labs->fetch_assoc()):
                            ?>
                                <option value="<?= $lab['lab_id'] ?>"><?= htmlspecialchars($lab['lab_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Technical Description</label>
                        <textarea name="description" id="modalDescription" rows="2" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Security Remarks</label>
                        <textarea name="remarks" id="modalRemarks" rows="2" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Registration Date</label>
                        <input type="text" id="modalDateAdded" class="w-full bg-slate-50 border border-slate-300 p-3 rounded-lg text-slate-500 text-sm font-semibold cursor-not-allowed" readonly>
                    </div>
                </div>
            </div>

            <div class="p-8 border-t border-slate-200 flex justify-end items-center gap-6 bg-slate-50">
                <button type="button" id="closeButton" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg text-sm font-bold shadow-lg transition-all active:scale-95">
                    Update Database
                </button>
            </div>
        </form>
        </div>
    </div>

    <div id="addModal" class="sciLab-modal-overlay fixed inset-0 bg-black/50 hidden items-center justify-center flex">
        <div class="sciLab-modal-content bg-white border border-slate-200 rounded-2xl relative overflow-hidden flex flex-col max-h-[90vh] w-full max-w-3xl">
        <form id="addForm" class="flex flex-col flex-1 min-h-0">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-emerald-500 to-transparent"></div>
            <button type="button" id="closeAddModal" class="absolute top-4 right-6 text-slate-500 hover:text-slate-900 text-3xl font-light transition-colors focus:outline-none">&times;</button>

            <div class="p-8 pb-4">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Initialize <span class="text-emerald-600">New Asset</span></h2>
            </div>

            <div class="p-8 pt-0 overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Item Name</label>
                        <input type="text" name="item_name" required class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Quantity</label>
                        <input type="number" name="quantity" required min="1" value="1" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all font-semibold text-emerald-600">
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Model Number</label>
                        <input type="text" name="model_no" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Serial Number</label>
                        <input type="text" name="serial_no" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Location</label>
                        <select name="location_id" required class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none appearance-none">
                            <option value="" disabled selected>Select location...</option>
                            <?php
                            $locations_q = $conn->query("SELECT * FROM locations");
                            while ($l = $locations_q->fetch_assoc()) echo "<option value='{$l['location_id']}'>{$l['location_name']}</option>";
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Lab</label>
                        <select name="lab_id" required class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none appearance-none">
                            <option value="" disabled selected>Select lab...</option>
                            <?php
                            $labs_q = $conn->query("SELECT * FROM labs");
                            while ($lb = $labs_q->fetch_assoc()) echo "<option value='{$lb['lab_id']}'>{$lb['lab_name']}</option>";
                            ?>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Technical Description</label>
                        <textarea name="description" rows="2" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none"></textarea>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">Security Remarks</label>
                        <textarea name="remarks" rows="2" class="w-full bg-white border border-slate-300 p-3 rounded-lg text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none" placeholder="Enter security protocols or status..."></textarea>
                    </div>
                </div>
            </div>

            <div class="p-8 border-t border-slate-200 flex justify-end items-center gap-6 bg-slate-50">
                <button type="button" id="cancelAddModal" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Cancel</button>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-2.5 rounded-lg text-sm font-bold shadow-lg active:scale-95 transition-all">Register Asset</button>
            </div>
        </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Elements
            const searchInput = document.getElementById('inventorySearch');
            const tableRows = document.querySelectorAll('.inventory-row');
            const viewModal = document.getElementById('viewModal');
            const modalForm = document.getElementById('modalForm');

            // Modal Input Selectors
            const modalInputs = {
                id: document.getElementById('modalItemId'),
                displayId: document.getElementById('displayItemId'),
                name: document.getElementById('modalItemName'),
                qty: document.getElementById('modalQuantity'), // Linked to fixed ID
                model: document.getElementById('modalModelNo'),
                serial: document.getElementById('modalSerialNo'),
                date: document.getElementById('modalDateAdded'),
                desc: document.getElementById('modalDescription'),
                remarks: document.getElementById('modalRemarks'),
                loc: document.getElementById('modalLocation'),
                lab: document.getElementById('modalLab')
            };

            // ================= SEARCH =================
            searchInput.addEventListener('input', () => {
                const filter = searchInput.value.toLowerCase();
                tableRows.forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
                });
            });

            // ================= VIEW / UPDATE MODAL =================
            tableRows.forEach(row => {
                row.addEventListener('click', (e) => {
                    // Don't open modal if checkbox was clicked
                    if (e.target.closest('.checkbox-cell')) return;

                    const data = JSON.parse(row.dataset.item);

                    modalInputs.id.value = data.item_id;
                    modalInputs.displayId.innerText = `#${data.item_id}`;
                    modalInputs.name.value = data.item_name;
                    modalInputs.qty.value = data.quantity; // Population of quantity field
                    modalInputs.model.value = data.model_no;
                    modalInputs.serial.value = data.serial_no;
                    modalInputs.date.value = data.date_added;
                    modalInputs.desc.value = data.description;
                    modalInputs.remarks.value = data.remarks || '';
                    modalInputs.loc.value = data.location_id;
                    modalInputs.lab.value = data.lab_id;

                    viewModal.classList.remove('hidden');
                    viewModal.classList.add('flex');
                });
            });

            const closeViewModal = () => {
                viewModal.classList.add('hidden');
                viewModal.classList.remove('flex');
            };

            document.getElementById('closeModal').onclick = closeViewModal;
            document.getElementById('closeButton').onclick = closeViewModal;

            modalForm.addEventListener('submit', e => {
                e.preventDefault();
                // FormData automatically picks up the name="quantity" attribute from the HTML
                fetch('../auth/updateinventory.php', {
                        method: 'POST',
                        body: new FormData(modalForm)
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.status === 'success') {
                            sessionStorage.setItem('inventoryAlert', JSON.stringify({
                                type: 'success',
                                title: 'Registry Updated',
                                message: d.message
                            }));
                            location.reload();
                        } else {
                            showAlert('danger', 'System Error', d.message);
                        }
                    });
            });

            // ================= BULK ACTIONS =================
            const selectAll = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const bulkActionBar = document.getElementById('bulkActionBar');
            const selectedCountText = document.getElementById('selectedCount');
            const deleteSelectedBtn = document.getElementById('deleteSelected');

            function updateBulkBar() {
                const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
                if (checkedCount > 0) {
                    bulkActionBar.classList.remove('hidden');
                    bulkActionBar.classList.add('flex');
                    selectedCountText.innerText = `${checkedCount} Items Selected`;
                } else {
                    bulkActionBar.classList.add('hidden');
                    bulkActionBar.classList.remove('flex');
                }
            }

            selectAll.addEventListener('change', () => {
                itemCheckboxes.forEach(cb => {
                    if (cb.closest('tr').style.display !== 'none') {
                        cb.checked = selectAll.checked;
                    }
                });
                updateBulkBar();
            });

            itemCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkBar);
            });

            document.getElementById('cancelSelection').onclick = () => {
                itemCheckboxes.forEach(cb => cb.checked = false);
                selectAll.checked = false;
                updateBulkBar();
            };

            deleteSelectedBtn.onclick = async () => {
                const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);

                if (ids.length === 0) return;

                const confirmed = await showSystemPrompt(
                    "Purge_Registry",
                    `Permanently erase ${ids.length} item(s) from the secure database?`
                );

                if (confirmed) {
                    const formData = new FormData();
                    formData.append('item_ids', JSON.stringify(ids));

                    fetch('../auth/delete_bulk_inventory.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(async response => {
                            const text = await response.text();
                            try {
                                return JSON.parse(text);
                            } catch (err) {
                                console.error("Server sent invalid JSON:", text);
                                throw new Error("Invalid Server Response");
                            }
                        })
                        .then(d => {
                            if (d.status === 'success') {
                                sessionStorage.setItem('inventoryAlert', JSON.stringify({
                                    type: 'success',
                                    title: 'Purge Complete',
                                    message: d.message
                                }));
                                location.reload();
                            } else {
                                showAlert('danger', 'Error', d.message);
                            }
                        })
                        .catch(err => {
                            console.error("Fetch Error:", err);
                            showAlert('danger', 'System Error', 'Could not execute purge. Check network logs.');
                        });
                }
            };

            // ================= ADD ITEM =================
            const addModal = document.getElementById('addModal');
            const addForm = document.getElementById('addForm');

            document.getElementById('openAddModal').onclick = () => {
                addForm.reset();
                addModal.classList.remove('hidden');
                addModal.classList.add('flex');
            };

            [document.getElementById('closeAddModal'), document.getElementById('cancelAddModal')].forEach(btn => {
                btn.onclick = () => {
                    addModal.classList.add('hidden');
                    addModal.classList.remove('flex');
                };
            });

            addForm.addEventListener('submit', e => {
                e.preventDefault();
                // FormData automatically includes name="quantity" from the Add Modal
                fetch('../auth/addinventory.php', {
                        method: 'POST',
                        body: new FormData(addForm)
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.status === 'success') {
                            sessionStorage.setItem('inventoryAlert', JSON.stringify({
                                type: 'success',
                                title: 'Asset Registered',
                                message: d.message
                            }));
                            location.reload();
                        } else {
                            showAlert('danger', 'Registration Failed', d.message);
                        }
                    });
            });

            // Initial Alert Check
            const alertData = sessionStorage.getItem('inventoryAlert');
            if (alertData) {
                const {
                    type,
                    title,
                    message
                } = JSON.parse(alertData);
                showAlert(type, title, message, 7000);
                sessionStorage.removeItem('inventoryAlert');
            }
        });

        function changeSector(pageNum) {
            const navForm = document.getElementById('paginationNavigator');
            const pageInput = document.getElementById('navPageInput');
            pageInput.value = pageNum;
            navForm.submit();
        }
    </script>