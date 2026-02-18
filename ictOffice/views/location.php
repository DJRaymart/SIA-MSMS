<!-- Location content - design aligned with Library sub-modules -->
<div id="main" class="px-6 min-h-screen">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      Locations
    </h2>
    <button type="button" id="openAddLocationModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
      Add Location
    </button>
  </div>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
      <h3 class="text-white font-bold text-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        Location List
      </h3>
    </div>
    <div class="p-6">
      <input type="text" id="searchInput" placeholder="Search locations..." class="w-full max-w-md px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4" />
      <div class="overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-slate-100 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Location</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="locationTableBody" class="divide-y divide-slate-200">
            <!-- Rows inserted by JS -->
          </tbody>
        </table>
      </div>
      <div class="flex justify-center gap-2 mt-4" id="pagination"></div>
    </div>
  </div>
</div>

<!-- Add Location Modal -->
<div id="addLocationModal" class="ict-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="ict-modal-content bg-white rounded-xl shadow-lg w-96 p-6 relative max-h-[90vh] overflow-y-auto">
    <h3 class="text-xl font-semibold mb-4">Add Location</h3>
    <form id="addLocationForm" class="space-y-4">
      <!-- <div>
        <label for="categoryID" class="block font-medium mb-1">ID</label>
        <input id="categoryID" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="categoryID"/>
      </div> -->
      <div>
        <label for="locationName" class="block font-medium mb-1">Location Name</label>
        <input id="locationName" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="locationName"/>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" id="cancelAddLocationBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Add</button>
      </div>
    </form>
    <button id="closeAddLocationModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-lg font-bold" aria-label="Close Modal">&times;</button>
  </div>
</div>

<?php include 'edit-location.php'; ?>
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
  let locations = [];
  let editingItemId;

  function fetchLocations(){
      fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/locations')
          .then(res => res.json())
          .then(data => { 
            locations = data; 
            renderTable();
          })
          .catch(err => console.error(err));
  }
 function getDataSelectedRow(id){
    editingItemId = id;
    const item = locations.find(i => i.location_id == id);
    console.log(item);

    // get values and set to edit modal
    document.getElementById('editLocationID').value = item.location_id;
    document.getElementById('editLocationName').value = item.location_name;
    // open edit modal
    document.getElementById('editLocationModal').classList.remove('hidden');
    
   }
  const rowsPerPage = 4;
  let currentPage = 1;
  const tableBody = document.getElementById('locationTableBody');
  const paginationDiv = document.getElementById('pagination');
  const searchInput = document.getElementById('searchInput');

  function renderTable(page = 1, filter = '') {
    currentPage = page;
    let filteredData = locations.filter(item =>
      item.location_name.toLowerCase().includes(filter.toLowerCase())
    );

    let start = (page - 1) * rowsPerPage;
    let paginatedItems = filteredData.slice(start, start + rowsPerPage);

    tableBody.innerHTML = paginatedItems.map(item => `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-6 py-4 text-sm text-slate-900">${item.location_id}</td>
        <td class="px-6 py-4 text-sm text-slate-900 font-medium">${item.location_name}</td>
        <td class="px-6 py-4 text-sm">
          <button type="button" class="text-blue-600 hover:text-blue-800 font-medium" onclick="getDataSelectedRow(${item.location_id})">Edit</button>
          <span class="text-slate-300 mx-1">|</span>
          <button type="button" class="text-red-600 hover:text-red-800 font-medium" onclick="deleteLocation(${item.location_id})">Delete</button>
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

  function deleteLocation(id) {
    if(confirm('Are you sure you want to delete this location?')) {
      fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/locations?id=${id}`, {
        method: 'DELETE'
      })
      .then(res => res.json())
      .then(data => {
        if(data.success){
          locations = locations.filter(item => item.location_id != id);
          // Recalculate total pages after deletion
          let filtered = locations.filter(item =>
            item.location_name.toLowerCase().includes(searchInput.value.toLowerCase())
          );
          const totalPages = Math.ceil(filtered.length / rowsPerPage);
          // If current page is now beyond total pages, adjust to last page
          if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
          } else if (totalPages === 0) {
            currentPage = 1;
          }
          renderTable(currentPage, searchInput.value);
          showAlert(data.message || 'item deleted successfully', 'success');
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
  const addLocationModal = document.getElementById('addLocationModal');
  const openAddLocationModalBtn = document.getElementById('openAddLocationModalBtn');
  const closeAddLocationModalBtn = document.getElementById('closeAddLocationModalBtn');
  const cancelAddLocationBtn = document.getElementById('cancelAddLocationBtn');
  const addLocationForm = document.getElementById('addLocationForm');
  openAddLocationModalBtn.addEventListener('click', () => addLocationModal.classList.remove('hidden'));
  closeAddLocationModalBtn.addEventListener('click', () => addLocationModal.classList.add('hidden'));
  cancelAddLocationBtn.addEventListener('click', () => addLocationModal.classList.add('hidden'));

  // Edit modal
  const editLocationModal = document.getElementById('editLocationModal');
  const closeEditLocationModalBtn = document.getElementById('closeEditLocationModalBtn');
  const cancelEditLocationBtn = document.getElementById('cancelEditLocationBtn');
  const editLocationForm = document.getElementById('editLocationForm');

  closeEditLocationModalBtn.addEventListener('click', () => editLocationModal.classList.add('hidden'));
  cancelEditLocationBtn.addEventListener('click', () => editLocationModal.classList.add('hidden'));

  //handle add location form submit
  addLocationForm.addEventListener('submit', e => {
    e.preventDefault();
     const formData = new FormData(addLocationForm);
    const itemData = Object.fromEntries(formData);

    fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/locations', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(itemData)
      })
      .then(res => res.json())
      .then(data=>{
        if(data.success){
          fetchLocations();
          addLocationModal.classList.add('hidden');
          addLocationForm.reset();
          showAlert(data.message, 'success');
        } else {
          showAlert(data.message || 'Failed to add location', 'error');
        }
      })
      .catch(err=>{
        console.error(err);
        showAlert('Error adding location', 'error');
      });
  });
// handle update location form submit
 // Edit form submission
  editLocationForm.addEventListener('submit', e=>{
    e.preventDefault();

    if (!confirm('Are you sure you want to update this location?')) {
      return;
    }

    const formData = new FormData(editLocationForm);
    const itemData = Object.fromEntries(formData);

    fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/locations?id=${editingItemId}`, {
      method:'PUT',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(itemData)
    })
    .then(res => res.json())
    .then(data=>{
      if(data.success){
        fetchLocations();
        editLocationModal.classList.add('hidden');
        editLocationForm.reset();
        showAlert(data.message, 'success');
      } else {
        showAlert(data.error || 'Failed to update location', 'error');
      }
    })
    .catch(err=>{
      console.error(err);
      showAlert('Error updating location', 'error');
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
  fetchLocations();
</script>

<?php include 'footer.php'; ?>
