<!-- Logbook Records content - design aligned with Library sub-modules -->
<div id="main" class="px-6 min-h-screen">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      Logbook Records
    </h2>
    <button type="button" onclick="generateLogReport()" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition-colors">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
      Print Report
    </button>
  </div>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
      <h3 class="text-white font-bold text-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        Logbook List
      </h3>
    </div>
    <div class="p-6">
      <input type="text" id="logSearchInput" placeholder="Search by name or grade & section..." class="w-full max-w-md px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4" />
      <div class="overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-slate-100 border-b border-slate-200">
            <tr>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Log ID</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Full Name</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Grade & Section</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Time-IN</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider">Time-Out</th>
              <th class="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wider text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="logbookTableBody" class="divide-y divide-slate-200">
          </tbody>
        </table>
      </div>
      <div class="flex justify-center gap-2 mt-4" id="logPagination"></div>
    </div>
  </div>
</div>

<div id="printSection" class="hidden">
  <div class="p-8 bg-white border-b-2 border-blue-600 mb-6 flex justify-between items-center">
    <div class="flex items-center gap-4">
      <img src="<?php echo htmlspecialchars(isset($ict_public) ? $ict_public : ((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/ictOffice/public')); ?>/images/school_logo.png" alt="Logo" class="h-16 w-auto object-contain" onerror="this.style.display='none'">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Logbook Activity Report</h1>
        <p class="text-gray-500 font-medium" id="printDate"></p>
      </div>
    </div>
    <div class="text-right">
      <p class="font-bold text-xl text-blue-600">ICT DEPARTMENT</p>
      <p class="text-sm text-gray-500 italic">User Access Records</p>
    </div>
  </div>

  <div class="grid grid-cols-3 gap-4 mb-8">
    <div class="p-4 bg-blue-50 border border-blue-200 rounded">
      <p class="text-xs uppercase text-blue-600 font-bold">Total Access Logs</p>
      <p class="text-2xl font-bold" id="statTotalLogs">0</p>
    </div>
    <div class="p-4 bg-green-50 border border-green-200 rounded">
      <p class="text-xs uppercase text-green-600 font-bold">Active Sessions</p>
      <p class="text-2xl font-bold" id="statActive">0</p>
    </div>
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
      <p class="text-xs uppercase text-yellow-600 font-bold">Completed Logs</p>
      <p class="text-2xl font-bold" id="statCompleted">0</p>
    </div>
  </div>

  <table class="w-full text-sm border-collapse">
    <thead>
      <tr class="border-b-2 border-gray-300 text-left bg-gray-50">
        <th class="py-2 px-2">Log ID</th>
        <th class="py-2">User Full Name</th>
        <th class="py-2">Grade & Section</th>
        <th class="py-2">Time-IN</th>
        <th class="py-2">Time-Out</th>
      </tr>
    </thead>
    <tbody id="printLogTableBody"></tbody>
  </table>

   <!-- Pagination -->
    <div class="flex justify-center gap-2 mt-4" id="pagination">
      <!-- Pagination buttons inserted by JS -->
    </div>
</div>

<!-- Alert container -->
<div id="alertContainer" class="fixed top-5 right-5 flex flex-col gap-2 z-50"></div>

<style>
/* Same CSS Fixes from Inventory Module */
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
  .bg-blue-50 { background-color: #eff6ff !important; -webkit-print-color-adjust: exact; }
  .bg-green-50 { background-color: #f0fdf4 !important; -webkit-print-color-adjust: exact; }
  .bg-yellow-50 { background-color: #fefce8 !important; -webkit-print-color-adjust: exact; }
}
</style>

<script>
let logs = [];
const rowsPerPage = 10;
let currentLogPage = 1;
const paginationDiv = document.getElementById('logPagination');

const logSearchInput = document.getElementById('logSearchInput');
function fetchLogs() {
  fetch((window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>) + '/logbook-records')
    .then(res => res.json())
    .then(data => {
      logs = data;
      renderLogTable();
    });
}

// function renderLogTable(page = 1, filter = '') {
//   currentLogPage = page;
//   const filtered = logs.filter(l => l.fullname.toLowerCase().includes(filter.toLowerCase()));
//   const start = (page - 1) * rowsPerPage;
//   const paginated = filtered.slice(start, start + rowsPerPage);

//   document.getElementById('logbookTableBody').innerHTML = paginated.map(log => `
//     <tr class="hover:bg-gray-50 transition-colors">
//       <td class="px-6 py-3">${log.log_id}</td>
//       <td class="px-6 py-3 font-medium text-blue-700">${log.fullname}</td>
//       <td class="px-6 py-3 text-sm">${log.time_in}</td>
//       <td class="px-6 py-3 text-sm">${log.time_out || '<span class="text-orange-500 font-bold italic">Active</span>'}</td>
//       <td class="px-6 py-3 text-center">
//         <button class="text-red-600 hover:text-red-800" onclick="deleteLog(${log.log_id})">Delete</button>
//       </td>
//     </tr>
//   `).join('');
  
//   // Logic for pagination (matches inventory style)
//   renderPagination(filtered.length);
// }

  function renderLogTable(page = 1, filter = '') {
    currentLogPage = page;
    let filteredData = logs.filter(item =>
      item.fullname.toLowerCase().includes(filter.toLowerCase()) ||
      (item.grade_section && item.grade_section.toLowerCase().includes(filter.toLowerCase()))
    );

    let start = (page - 1) * rowsPerPage;
    let paginatedItems = filteredData.slice(start, start + rowsPerPage);

    document.getElementById('logbookTableBody').innerHTML = paginatedItems.map(log => `
    <tr class="hover:bg-slate-50 transition-colors">
      <td class="px-6 py-4 text-sm text-slate-900">${log.log_id}</td>
      <td class="px-6 py-4 text-sm text-slate-900 font-medium">${log.fullname}</td>
      <td class="px-6 py-4 text-sm text-slate-600">${log.grade_section || '-'}</td>
      <td class="px-6 py-4 text-sm text-slate-600">${log.time_in}</td>
      <td class="px-6 py-4 text-sm">${log.time_out || '<span class="text-orange-500 font-semibold italic">Active</span>'}</td>
      <td class="px-6 py-4 text-sm text-center">
        <button type="button" class="text-red-600 hover:text-red-800 font-medium" onclick="deleteLog(${log.log_id})">Delete</button>
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
          onclick="renderLogTable(${i}, '${logSearchInput.value.replace(/'/g, "\\'")}')"
        >
          ${i}
        </button>
      `;
    }
    paginationDiv.innerHTML = buttons;
  }

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

function generateLogReport() {
  // 1. Set Date
  document.getElementById('printDate').textContent = `Generated: ${new Date().toLocaleString()}`;

  // 2. Calculate Stats for the cards
  document.getElementById('statTotalLogs').textContent = logs.length;
  document.getElementById('statActive').textContent = logs.filter(l => !l.time_out).length;
  document.getElementById('statCompleted').textContent = logs.filter(l => l.time_out).length;

  // 3. Fill the print table
  document.getElementById('printLogTableBody').innerHTML = logs.map(l => `
    <tr class="border-b border-gray-100">
      <td class="py-2 px-2 text-xs">${l.log_id}</td>
      <td class="py-2 font-bold">${l.fullname}</td>
      <td class="py-2 text-xs">${l.grade_section || '-'}</td>
      <td class="py-2 text-xs">${l.time_in}</td>
      <td class="py-2 text-xs">${l.time_out || 'Active Session'}</td>
    </tr>
  `).join('');

  // 4. Trigger browser print
  window.print();
}

// Search interaction
document.getElementById('logSearchInput').oninput = (e) => renderLogTable(1, e.target.value);

function deleteLog(id) {
    if(confirm('Are you sure you want to delete this log?')) {
        fetch(`${window.ICT_API || <?php echo json_encode($ict_api ?? (defined('BASE_URL') ? (rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api' : '/ictOffice/api')); ?>}/logbook-records?id=${id}`, {
            method: 'DELETE'
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                // FIX: Changed item.ID to item.log_id to match your DB
                logs = logs.filter(item => item.log_id != id);
                
                let filtered = logs.filter(item =>
                    item.fullname.toLowerCase().includes(logSearchInput.value.toLowerCase())
                );
                
                const totalPages = Math.ceil(filtered.length / rowsPerPage);
                
                if (currentLogPage > totalPages && totalPages > 0) {
                    currentLogPage = totalPages;
                } else if (totalPages === 0) {
                    currentLogPage = 1;
                }
                
                renderLogTable(currentLogPage, logSearchInput.value);

                // Check if showAlert exists, otherwise use standard alert
                if (typeof showAlert === "function") {
                    showAlert(data.message || 'Log deleted successfully', 'success');
                } else {
                    alert('Log deleted successfully');
                }
            } else {
                if (typeof showAlert === "function") {
                    showAlert(data.error || 'Failed to delete log', 'error');
                } else {
                    alert(data.error || 'Failed to delete log');
                }
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            alert('An error occurred while deleting the record.');
        });
    }
}

fetchLogs();
</script>

<?php include 'footer.php'; ?>