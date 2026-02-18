<!-- Category content - design aligned with Library sub-modules -->
<div id="main" class="px-6 min-h-screen">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      Categories
    </h2>
    <button type="button" id="openAddCategoryModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
      Add Category
    </button>
  </div>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
      <h3 class="text-white font-bold text-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        Category List
      </h3>
    </div>
    <div class="p-6">
      <input type="text" id="searchInput" placeholder="Search categories..." class="w-full max-w-md px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4" />
      <div class="overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-slate-100 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Category Name</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="categoryTableBody" class="divide-y divide-slate-200">
            <!-- Rows inserted by JS -->
          </tbody>
        </table>
      </div>
      <div class="flex justify-center gap-2 mt-4" id="pagination"></div>
    </div>
  </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="ict-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="ict-modal-content bg-white rounded-xl shadow-lg w-96 p-6 relative max-h-[90vh] overflow-y-auto">
    <h3 class="text-xl font-semibold mb-4">Add Category</h3>
    <form id="addCategoryForm" class="space-y-4">
      <!-- <div>
        <label for="categoryID" class="block font-medium mb-1">ID</label>
        <input id="categoryID" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="categoryID"/>
      </div> -->
      <div>
        <label for="categoryName" class="block font-medium mb-1">Category Name</label>
        <input id="categoryName" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="categoryName"/>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" id="cancelAddCategoryBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Add</button>
      </div>
    </form>
    <button id="closeAddCategoryModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-lg font-bold" aria-label="Close Modal">&times;</button>
  </div>
</div>

<?php include 'edit-category.php'; ?>
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

  // Dummy inventory data
  let categories = [];
  let editingItemId;

  function fetchCategories(){
      fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/categories')
          .then(res => res.json())
          .then(data => { 
            categories = data; 
            renderTable();
          })
          .catch(err => console.error(err));
  }
 function getDataSelectedRow(id){
    editingItemId = id;
    const item = categories.find(i => i.ID == id);
    console.log(item);
    // get values and set to edit modal
    document.getElementById('editCategoryID').value = item.ID;
    document.getElementById('editCategoryName').value = item.category_name;
    // open edit modal
    document.getElementById('editCategoryModal').classList.remove('hidden');
    
   }
  const rowsPerPage = 4;
  let currentPage = 1;
  const tableBody = document.getElementById('categoryTableBody');
  const paginationDiv = document.getElementById('pagination');
  const searchInput = document.getElementById('searchInput');

  function renderTable(page = 1, filter = '') {
    currentPage = page;
    let filteredData = categories.filter(item =>
      item.category_name.toLowerCase().includes(filter.toLowerCase())
    );

    let start = (page - 1) * rowsPerPage;
    let paginatedItems = filteredData.slice(start, start + rowsPerPage);

    tableBody.innerHTML = paginatedItems.map(item => `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-6 py-4 text-sm text-slate-900">${item.ID}</td>
        <td class="px-6 py-4 text-sm text-slate-900 font-medium">${item.category_name}</td>
        <td class="px-6 py-4 text-sm">
          <button type="button" class="text-blue-600 hover:text-blue-800 font-medium" onclick="getDataSelectedRow(${item.ID})">Edit</button>
          <span class="text-slate-300 mx-1">|</span>
          <button type="button" class="text-red-600 hover:text-red-800 font-medium" onclick="deleteCategory(${item.ID})">Delete</button>
        </td>
      </tr>
    `).join('');

    renderPagination(filteredData.length, page);
  }

  function renderPagination(totalItems, page) {
    const totalPages = Math.ceil(totalItems / rowsPerPage);
    let buttons = '';

    for(let i = 1; i <= totalPages; i++) {
      buttons += `
        <button
          class="px-3 py-1.5 rounded-lg ${i === page ? 'bg-blue-600 text-white' : 'bg-slate-200 hover:bg-slate-300 text-slate-700'} transition-colors"
          onclick="renderTable(${i}, '${searchInput.value}')"
        >
          ${i}
        </button>
      `;
    }
    paginationDiv.innerHTML = buttons;
  }

  function deleteCategory(id) {
    if(confirm('Are you sure you want to delete this category?')) {
      fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/categories?id=${id}`, {
        method: 'DELETE'
      })
      .then(res => res.json())
      .then(data => {
        if(data.success){
          categories = categories.filter(item => item.ID != id);
          // Recalculate total pages after deletion
          let filtered = categories.filter(item =>
            item.category_name.toLowerCase().includes(searchInput.value.toLowerCase())
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
  const addCategoryModal = document.getElementById('addCategoryModal');
  const openAddCategoryModalBtn = document.getElementById('openAddCategoryModalBtn');
  const closeAddCategoryModalBtn = document.getElementById('closeAddCategoryModalBtn');
  const cancelAddCategoryBtn = document.getElementById('cancelAddCategoryBtn');
  const addCategoryForm = document.getElementById('addCategoryForm');

  openAddCategoryModalBtn.addEventListener('click', () => addCategoryModal.classList.remove('hidden'));
  closeAddCategoryModalBtn.addEventListener('click', () => addCategoryModal.classList.add('hidden'));
  cancelAddCategoryBtn.addEventListener('click', () => addCategoryModal.classList.add('hidden'));

  // Edit modal
  const editCategoryModal = document.getElementById('editCategoryModal');
  const closeEditCategoryModalBtn = document.getElementById('closeEditCategoryModalBtn');
  const cancelEditCategoryBtn = document.getElementById('cancelEditCategoryBtn');
  const editCategoryForm = document.getElementById('editCategoryForm');

  closeEditCategoryModalBtn.addEventListener('click', () => editCategoryModal.classList.add('hidden'));
  cancelEditCategoryBtn.addEventListener('click', () => editCategoryModal.classList.add('hidden'));

  //handle add category form submit
  addCategoryForm.addEventListener('submit', e => {
    e.preventDefault();
     const formData = new FormData(addCategoryForm);
    const itemData = Object.fromEntries(formData);

    fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/categories', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(itemData)
      })
      .then(res => res.json())
      .then(data=>{
        if(data.success){
          fetchCategories();
          addCategoryModal.classList.add('hidden');
          addCategoryForm.reset();
          showAlert(data.message, 'success');
        } else {
          showAlert(data.message || 'Failed to add category', 'error');
        }
      })
      .catch(err=>{
        console.error(err);
        showAlert('Error adding category', 'error');
      });
  });
// handle update category form submit
 // Edit form submission
  editCategoryForm.addEventListener('submit', e=>{
    e.preventDefault();
    
    if (!confirm('Are you sure you want to update this category?')) {
      return;
    }

    const formData = new FormData(editCategoryForm);
    const itemData = Object.fromEntries(formData);

    fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/categories?id=${editingItemId}`, {
      method:'PUT',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(itemData)
    })
    .then(res => res.json())
    .then(data=>{
      if(data.success){
        fetchCategories();
        editCategoryModal.classList.add('hidden');
        editCategoryForm.reset();
        showAlert(data.message, 'success');
      } else {
        showAlert(data.error || 'Failed to update category', 'error');
      }
    })
    .catch(err=>{
      console.error(err);
      showAlert('Error updating category', 'error');
    });
  });
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

  // Initial fetch and render
  fetchCategories();
</script>

<?php include 'footer.php'; ?>
