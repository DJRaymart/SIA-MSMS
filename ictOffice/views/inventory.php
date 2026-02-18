<!-- Inventory content - design aligned with Library sub-modules -->
<div class="px-6">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      Inventory
    </h2>
    <div class="flex gap-2">
      <button type="button" onclick="generateReport()" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition-colors">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
        Print Report
      </button>
      <button type="button" id="openAddModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Add Inventory
      </button>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
      <h3 class="text-white font-bold text-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        Inventory List
      </h3>
    </div>
    <div class="p-6">
      <input type="text" id="searchInput" placeholder="Search inventory..." class="w-full max-w-md px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4" />
      <div class="overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-slate-100 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Item</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Description</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Category</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Quantity</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Model Number</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Date Added</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Remarks</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Location</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="inventoryTableBody" class="divide-y divide-slate-200">
            <!-- Rows inserted by JS -->
          </tbody>
        </table>
      </div>
      <div class="flex justify-center gap-2 mt-4" id="pagination"></div>
    </div>
  </div>
</div>

<!-- Add Inventory Modal -->
<div id="addModal" class="ict-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="ict-modal-content bg-white rounded-xl shadow-lg w-96 p-6 relative max-h-[90vh] overflow-y-auto">
    <h3 class="text-xl font-semibold mb-4">Add Inventory Item</h3>
    <form id="addInventoryForm" class="grid grid-cols-2 gap-4">

      <div class="col-span-2">
        <label for="itemName" class="block font-medium mb-1">Item</label>
        <input id="itemName" type="text" required name="item_name"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="itemModelNumber" class="block font-medium mb-1">Model Number</label>
        <input id="itemModelNumber" type="text" required name="model_no"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="itemDescription" class="block font-medium mb-1">Description</label>
        <input id="itemDescription" type="text" required name="description"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="itemCategory" class="block font-medium mb-1">Category</label>
        <select id="itemCategory" required name="category_id"
          class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="">Select Category</option>
        </select>
      </div>

      <div class="col-span-2">
        <label for="itemLocation" class="block font-medium mb-1">Location</label>
        <select id="itemLocation" required class="w-full border border-gray-300 rounded px-3 py-2" 
          name="location_id">
          <option value="">Select location</option>
        </select>
      </div>

      <div>
        <label for="itemQuantity" class="block font-medium mb-1">Quantity</label>
        <input id="itemQuantity" type="number" min="0" required name="quantity"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div>
        <label for="itemDateAdded" class="block font-medium mb-1">Date Added</label>
        <input id="itemDateAdded" type="date" required name="date_added"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="itemRemarks" class="block font-medium mb-1">Remarks</label>
        <textarea id="itemRemarks" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 resize-y"
          placeholder="Enter remarks here..." required name="remarks"></textarea>
      </div>

      <!-- Buttons -->
      <div class="col-span-2 flex justify-end gap-3 pt-2">
        <button type="button" id="cancelAddBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
          Add
        </button>
      </div>
    </form>

    <button id="closeAddModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-lg font-bold" aria-label="Close Modal">&times;</button>
  </div>
</div>

<!-- edit modal -->
 <?php include 'edit-inventory.php'; ?>
<!-- Alert container -->
<div id="alertContainer" class="fixed top-5 right-5 flex flex-col gap-2 z-50"></div>

<!-- Tailwind + custom slide animation -->
<style>
@keyframes slide-in {
  0% { transform: translateX(100%); opacity: 0; }
  100% { transform: translateX(0); opacity: 1; }
}

.animate-slide-in {
  animation: slide-in 0.5s ease forwards;
}
</style>

<script>
  // Fetch categories and populate select
  fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/categories')
    .then(res => res.json())
    .then(categories => {
      const categorySelect = document.getElementById('itemCategory');
      const editCategorySelect = document.getElementById('editItemCategory');
      categories.forEach(cat => {
        const option = document.createElement('option');
        const editOption = document.createElement('option');
        option.value = cat.ID;
        option.textContent = cat.category_name;
        editOption.value = cat.ID;
        editOption.textContent = cat.category_name;
        categorySelect.appendChild(option);
        editCategorySelect.appendChild(editOption);
      });
    });

  // Fetch locations and populate select
  fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/locations')
    .then(res => res.json())
    .then(locations => {
      const locationSelect = document.getElementById('itemLocation');
      const editLocationSelect = document.getElementById('editItemLocation');
      locations.forEach(loc => {
        const option = document.createElement('option');
        const editOption = document.createElement('option');
        option.value = loc.location_id;
        option.textContent = loc.location_name;
        editOption.value = loc.location_id;
        editOption.textContent = loc.location_name;
        locationSelect.appendChild(option);
        editLocationSelect.appendChild(editOption);
      });
    });

  // Inventory array
  let inventory = [];
  let editingItemId;

  function fetchInventory(){
      fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/inventories')
          .then(res => res.json())
          .then(data => { 
            inventory = data; 
            renderTable();
          })
          .catch(err => console.error(err));
  }
 
 function getDataSelectedRow(id){
    editingItemId = id;
    const item = inventory.find(i => i.item_id == id);
    // get values and set to edit modal
    document.getElementById('editItemName').value = item.item_name;
    document.getElementById('editItemModelNumber').value = item.model_no;
    document.getElementById('editItemDescription').value = item.description;
    document.getElementById('editItemCategory').value = item.category_id;
    document.getElementById('editItemLocation').value = item.location_id;
    document.getElementById('editItemQuantity').value = item.quantity;
    document.getElementById('editItemDateAdded').value = item.date_added;
    document.getElementById('editItemRemarks').value = item.remarks;
    // open edit modal
    document.getElementById('editModal').classList.remove('hidden');
    
  }
  const rowsPerPage = 4;
  let currentPage = 1;
  const tableBody = document.getElementById('inventoryTableBody');
  const paginationDiv = document.getElementById('pagination');
  const searchInput = document.getElementById('searchInput');

  function renderTable(page = 1, filter = '') {
    currentPage = page;
    let filtered = inventory.filter(item =>
      item.item_name.toLowerCase().includes(filter.toLowerCase()) ||
      item.category.toLowerCase().includes(filter.toLowerCase())
    );

    let start = (page - 1) * rowsPerPage;
    let paginated = filtered.slice(start, start + rowsPerPage);

    tableBody.innerHTML = paginated.map(item => `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-6 py-4 text-sm text-slate-900">${item.item_id}</td>
        <td class="px-6 py-4 text-sm text-slate-900">${item.item_name}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.description}</td>
        <td class="px-6 py-4 text-sm text-slate-900">${item.category}</td>
        <td class="px-6 py-4 text-sm text-slate-900">${item.quantity}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.model_no}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.date_added}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.remarks}</td>
        <td class="px-6 py-4 text-sm text-slate-900">${item.location}</td>
        <td class="px-6 py-4 text-sm">
          <button type="button" class="text-blue-600 hover:text-blue-800 font-medium" onclick="getDataSelectedRow(${item.item_id})">Edit</button>
          <span class="text-slate-300 mx-1">|</span>
          <button type="button" class="text-red-600 hover:text-red-800 font-medium" onclick="deleteItem(${item.item_id})">Delete</button>
        </td>
      </tr>
    `).join('');

    renderPagination(filtered.length, page);
  }

  function renderPagination(totalItems, page) {
    const totalPages = Math.ceil(totalItems / rowsPerPage);
    paginationDiv.innerHTML = '';
    for(let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.className = `px-3 py-1.5 rounded-lg ${i===page?'bg-blue-600 text-white':'bg-slate-200 hover:bg-slate-300 text-slate-700'} transition-colors`;
      btn.onclick = () => renderTable(i, searchInput.value);
      paginationDiv.appendChild(btn);
    }
  }

  function deleteItem(id) {
    if(confirm('Are you sure?')) {
      fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/inventories?id=${id}`, {
        method: 'DELETE'
      })
      .then(res => res.json())
      .then(data => {
        if(data.success){
          inventory = inventory.filter(item => item.item_id != id);
          // Recalculate total pages after deletion
          let filtered = inventory.filter(item =>
            item.item_name.toLowerCase().includes(searchInput.value.toLowerCase()) ||
            item.category.toLowerCase().includes(searchInput.value.toLowerCase())
          );
          const totalPages = Math.ceil(filtered.length / rowsPerPage);
          // If current page is now beyond total pages, adjust to last page
          if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
          } else if (totalPages === 0) {
            currentPage = 1;
          }
          renderTable(currentPage, searchInput.value);
          showAlert(data.message || 'Item deleted successfully', 'success');
        } else {
          showAlert(data.error || 'Failed to delete item', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        showAlert('Failed to delete item', 'error');
      });
    }
  }

  searchInput.addEventListener('input', e => renderTable(1, e.target.value));

  // Modal
  const addModal = document.getElementById('addModal');
  const openAddModalBtn = document.querySelector('#openAddModalBtn');
  const closeAddModalBtn = document.querySelector('#closeAddModalBtn');
  const cancelAddBtn = document.querySelector('#cancelAddBtn');
  const addInventoryForm = document.getElementById('addInventoryForm');

  openAddModalBtn.addEventListener('click', () => addModal.classList.remove('hidden'));
  closeAddModalBtn.addEventListener('click', () => addModal.classList.add('hidden'));
  cancelAddBtn.addEventListener('click', () => addModal.classList.add('hidden'));

  // Edit Modal
  const editModal = document.getElementById('editModal');
  const closeEditModalBtn = document.querySelector('#closeEditModalBtn');
  const cancelEditBtn = document.querySelector('#cancelEditBtn');
  const editInventoryForm = document.getElementById('editInventoryForm');

  closeEditModalBtn.addEventListener('click', () => editModal.classList.add('hidden'));
  cancelEditBtn.addEventListener('click', () => editModal.classList.add('hidden'));

 function showAlert(message, type = 'success', duration = 3000) {
  const container = document.getElementById('alertContainer');
  const alert = document.createElement('div');

  const isSuccess = type === 'success';

  alert.setAttribute('role', 'alert');

  alert.innerHTML = `
    <div class="
      ${isSuccess ? 'bg-green-500' : 'bg-red-500'}
      text-white font-bold rounded-t px-4 py-2
    ">
      ${isSuccess ? 'Success' : 'Error'}
    </div>
    <div class="
      border border-t-0 rounded-b px-4 py-3
      ${isSuccess
        ? 'border-green-400 bg-green-100 text-green-700'
        : 'border-red-400 bg-red-100 text-red-700'}
    ">
      <p>${message}</p>
    </div>
  `;

  container.appendChild(alert);

  setTimeout(() => {
    alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
    setTimeout(() => alert.remove(), 500);
  }, duration);
}

  // Form submission
  addInventoryForm.addEventListener('submit', e=>{
    e.preventDefault();
    const formData = new FormData(addInventoryForm);
    const itemData = Object.fromEntries(formData);

    fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/inventories', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(itemData)
    })
    .then(res => res.json())
    .then(data=>{
      if(data.success){
        fetchInventory();
        addModal.classList.add('hidden');
        addInventoryForm.reset();
        showAlert(data.message, 'success');
      } else {
        showAlert(data.message || 'Failed to add inventory', 'error');
      }
    })
    .catch(err=>{
      console.error(err);
      showAlert('Error adding inventory item', 'error');
    });
  });

  // Edit form submission
  editInventoryForm.addEventListener('submit', e=>{
    e.preventDefault();
    const formData = new FormData(editInventoryForm);
    const itemData = Object.fromEntries(formData);

    fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/inventories?id=${editingItemId}`, {
      method:'PUT',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(itemData)
    })
    .then(res => res.json())
    .then(data=>{
      if(data.success){
        fetchInventory();
        editModal.classList.add('hidden');
        editInventoryForm.reset();
        showAlert(data.message, 'success');
      } else {
        showAlert(data.error || 'Failed to update inventory', 'error');
      }
    })
    .catch(err=>{
      console.error(err);
      showAlert('Error updating inventory item', 'error');
    });
  });
  function generateReport() {
  const printTableBody = document.getElementById('printTableBody');
  const printDate = document.getElementById('printDate');
  
  // Fix: Proper Date Formatting (e.g., October 24, 2023, 10:30 AM)
  const now = new Date();
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  };
  printDate.textContent = `Generated: ${now.toLocaleDateString('en-US', options)}`;

  // Calculate Statistics
  const totalItems = inventory.length;
  const totalQty = inventory.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);
  const uniqueCats = [...new Set(inventory.map(item => item.category))].length;

  document.getElementById('statTotalItems').textContent = totalItems;
  document.getElementById('statTotalQty').textContent = totalQty;
  document.getElementById('statTotalCats').textContent = uniqueCats;

  // Populate Table
  printTableBody.innerHTML = inventory.map(item => `
    <tr class="border-b border-gray-100 hover:bg-gray-50">
      <td class="py-3 pr-2">
        <span class="font-bold text-gray-800">${item.item_name}</span><br>
        <span class="text-xs text-gray-500 font-mono">${item.model_no}</span>
      </td>
      <td class="py-3 text-gray-600">${item.category}</td>
      <td class="py-3 text-gray-600 font-medium">${item.location}</td>
      <td class="py-3 text-right font-bold ${item.quantity < 5 ? 'text-red-600' : 'text-gray-800'}">
        ${item.quantity}
      </td>
      <td class="py-3 text-xs italic text-gray-500 pl-4">${item.remarks || '-'}</td>
    </tr>
  `).join('');

  // Trigger Print
  window.print();
}
  // Initial render
 fetchInventory();
</script>

<div id="printSection" class="hidden">
  <div class="p-8 bg-white border-b-2 border-blue-600 mb-6 flex justify-between items-center">
    <div class="flex items-center gap-4">
      <img src="<?php echo htmlspecialchars(isset($ict_public) ? $ict_public : ((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/ictOffice/public')); ?>/images/school_logo.png" alt="Company Logo" class="h-16 w-auto object-contain fallback-logo" onerror="this.style.display='none'">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Inventory Status Report</h1>
        <p class="text-gray-500 font-medium" id="printDate"></p>
      </div>
    </div>
    <div class="text-right">
      <p class="font-bold text-xl text-blue-600 uppercase tracking-wider">Holy Cross Mintal</p>
      <p class="text-sm text-gray-500 italic">ICT Office</p>
    </div>
  </div>

  <div class="grid grid-cols-3 gap-4 mb-8">
    <div class="p-4 bg-blue-50 border border-blue-200 rounded">
      <p class="text-xs uppercase text-blue-600 font-bold">Total Unique Items</p>
      <p class="text-2xl font-bold" id="statTotalItems">0</p>
    </div>
    <div class="p-4 bg-green-50 border border-green-200 rounded">
      <p class="text-xs uppercase text-green-600 font-bold">Total Stock Quantity</p>
      <p class="text-2xl font-bold" id="statTotalQty">0</p>
    </div>
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
      <p class="text-xs uppercase text-yellow-600 font-bold">Categories Tracked</p>
      <p class="text-2xl font-bold" id="statTotalCats">0</p>
    </div>
  </div>

  <table class="w-full text-sm border-collapse">
    <thead>
      <tr class="border-b-2 border-gray-300 text-left">
        <th class="py-2">Item/Model</th>
        <th class="py-2">Category</th>
        <th class="py-2">Location</th>
        <th class="py-2 text-right">Qty</th>
        <th class="py-2 pl-4">Remarks</th>
      </tr>
    </thead>
    <tbody id="printTableBody">
      </tbody>
  </table>
</div>

<style>
@media print {
  body * { visibility: hidden; }
  #printSection, #printSection * { visibility: visible; }
  #printSection {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    display: block !important;
  }
  /* Remove shadows and colors may need adjustment for printers */
  .bg-blue-50 { background-color: #eff6ff !important; -webkit-print-color-adjust: exact; }

}
@media print {
  /* Force colors and images to print */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  /* Ensure the logo has a minimum width for clarity */
  .fallback-logo {
    max-height: 80px;
    width: auto;
  }

  /* Hide the print button itself if it's somehow caught in the flow */
  button { display: none !important; }
}

</style>
<?php include 'footer.php'; ?>
