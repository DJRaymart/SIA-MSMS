<!-- Users content - design aligned with Library sub-modules -->
<div id="main" class="px-6 min-h-screen">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
      Users
    </h2>
  </div>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
      <h3 class="text-white font-bold text-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        User List
      </h3>
    </div>
    <div class="p-6">
      <input type="text" id="searchInput" placeholder="Search users..." class="w-full max-w-md px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4" />
      <div class="overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-slate-100 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Student ID Number</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Fullname</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Grade & Section</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">RFID Number</th>
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

<script>
  // Dummy inventory data
  let inventory = [
    { id: 1, name: 'Dell Laptop', category: 'Hardware', quantity: 15, status: 'Available' },
    { id: 2, name: 'HP Printer', category: 'Hardware', quantity: 5, status: 'In Use' },
    { id: 3, name: 'Adobe Photoshop', category: 'Software', quantity: 12, status: 'Available' },
    { id: 4, name: 'Network Cable', category: 'Accessories', quantity: 50, status: 'Available' },
    { id: 5, name: 'Projector', category: 'Hardware', quantity: 3, status: 'Maintenance' },
    { id: 6, name: 'MS Office License', category: 'Software', quantity: 20, status: 'Available' },
    { id: 7, name: 'Wireless Mouse', category: 'Accessories', quantity: 30, status: 'In Use' },
  ];

  const rowsPerPage = 4;
  let currentPage = 1;
  const tableBody = document.getElementById('inventoryTableBody');
  const paginationDiv = document.getElementById('pagination');                              
  const searchInput = document.getElementById('searchInput');

  function renderTable(page = 1, filter = '') {
    currentPage = page;
    let filteredData = inventory.filter(item =>
      item.name.toLowerCase().includes(filter.toLowerCase()) ||
      item.category.toLowerCase().includes(filter.toLowerCase()) ||
      item.status.toLowerCase().includes(filter.toLowerCase())
    );

    let start = (page - 1) * rowsPerPage;
    let paginatedItems = filteredData.slice(start, start + rowsPerPage);

    tableBody.innerHTML = paginatedItems.map(item => `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-6 py-4 text-sm text-slate-900">${item.id}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.name}</td>
        <td class="px-6 py-4 text-sm text-slate-900 font-medium">${item.category}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.quantity}</td>
        <td class="px-6 py-4 text-sm text-slate-600">${item.status}</td>
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

  function deleteItem(id) {
    if(confirm('Are you sure?')) {
      inventory = inventory.filter(item => item.id !== id);
      renderTable(currentPage, searchInput.value);
    }
  }

  searchInput.addEventListener('input', e => renderTable(1, e.target.value));

  // Initial render
  renderTable();
</script>

<?php include 'footer.php'; ?>
