<?php

include "../auth/session_guard.php";

include "../config/db.php";
include "../admin/header.php";

$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$whereClause = "";
$params = [];
$types = "";

if (!empty($dateFrom) && !empty($dateTo)) {
    $whereClause = " WHERE i.date_added BETWEEN ? AND ?";
    $params = [$dateFrom, $dateTo];
    $types = "ss";
}

$countSql = "SELECT COUNT(*) as total FROM inventory i" . $whereClause;
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT i.*, l.lab_name, loc.location_name 
        FROM inventory i 
        LEFT JOIN labs l ON i.lab_id = l.lab_id 
        LEFT JOIN locations loc ON i.location_id = loc.location_id"
    . $whereClause .
    " ORDER BY i.item_id ASC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$limitParams = array_merge($params, [$limit, $offset]);
$limitTypes = $types . "ii";
$stmt->bind_param($limitTypes, ...$limitParams);
$stmt->execute();
$data = $stmt->get_result();

$totalItems = $conn->query("SELECT COUNT(*) as total FROM inventory")->fetch_assoc()['total'];
$uniqueLocations = $conn->query("SELECT COUNT(DISTINCT location_id) as total FROM inventory")->fetch_assoc()['total'];

$recentEntries = $conn->query("SELECT date_added FROM inventory ORDER BY date_added DESC LIMIT 3");

$queryString = $_GET;
unset($queryString['page']);
$baseQuery = http_build_query($queryString);
$pageUrl = "?" . ($baseQuery ? $baseQuery . "&" : "") . "page=";
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
</style>

<div class="flex h-screen bg-slate-50 overflow-hidden text-slate-800" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <?php include "../admin/sidebar.php"; ?>

    <main class="flex-1 overflow-y-auto flex flex-col ml-5" id="reportContent">

        <div class="w-full flex-shrink-0 header py-4 px-8 flex justify-between items-center print:hidden">
            <div class="flex items-center gap-3">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-[0.2em]">Module: Inventory Report</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-semibold text-slate-600 uppercase"><?php echo date("l, M d, Y"); ?></span>
            </div>
        </div>

        <div class="flex-1 flex flex-col px-8 py-6 overflow-hidden">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 px-2 border-b border-slate-200 pb-6 shrink-0">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter">
                        Inventory <span class="text-blue-600">Report</span>
                    </h1>
                    <p class="text-blue-600 font-semibold uppercase tracking-wider text-xs mt-2">
                        System Status: <span class="text-emerald-600">Active</span>
                    </p>
                </div>

                <button onclick="printInventory()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all flex items-center gap-3 shadow-lg hover:shadow-xl mt-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Report
                </button>
            </div>

            <div class="flex flex-col xl:flex-row gap-4 flex-1 overflow-hidden">
                <div class="flex-1 flex flex-col gap-4 overflow-hidden">

                    <!-- <div class="bg-slate-900/40 backdrop-blur-md rounded-xl p-3 border border-slate-800 shrink-0">
                        <form method="GET" class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="grid grid-cols-2 gap-3 w-72">
                                    <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="bg-[#0b0f1a] border border-slate-800 rounded px-3 py-1.5 text-[10px] text-white outline-none font-mono">
                                    <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="bg-[#0b0f1a] border border-slate-800 rounded px-3 py-1.5 text-[10px] text-white outline-none font-mono">
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit" class="bg-slate-800 hover:bg-blue-600 text-white px-4 py-1.5 rounded font-black text-[8px] uppercase tracking-widest transition-colors flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Filter
                                </button>

                                <button type="button" onclick="resetFilter()" class="bg-transparent hover:bg-slate-800 text-slate-400 hover:text-white px-4 py-1.5 rounded font-black text-[8px] uppercase tracking-widest border border-slate-800 transition-all flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div> -->

                    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm shrink-0">
                        <form method="GET" class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2 bg-slate-50 border border-slate-300 rounded-lg px-4 py-2">
                                <span class="text-xs font-semibold text-slate-700">Date Range:</span>
                                <input type="date" name="date_from" id="startDate" value="<?= htmlspecialchars($dateFrom) ?>" class="bg-transparent text-sm text-slate-900 outline-none">
                                <span class="text-slate-400">to</span>
                                <input type="date" name="date_to" id="endDate" value="<?= htmlspecialchars($dateTo) ?>" class="bg-transparent text-sm text-slate-900 outline-none">
                            </div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition-colors">Filter</button>
                            <button type="button" onclick="window.location.href=window.location.pathname" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-5 py-2 rounded-lg font-semibold border border-slate-300 transition-all">Reset</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-lg flex flex-col overflow-hidden">
                        <div id="inventoryTableContainer" class="flex-1 overflow-x-auto">
                            <table id="inventoryTable" class="min-w-full text-sm text-slate-700 border-collapse">
                                <thead class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <tr class="border-b border-blue-700">
                                        <th class="px-5 py-3 text-left"><input type="checkbox" id="selectAll" onclick="toggleAll(this)" class="w-4 h-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500"></th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-white text-xs tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-white text-xs tracking-wider">Asset Description</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-white text-xs tracking-wider">Hardware Specs</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-white text-xs tracking-wider">Location</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-white text-xs tracking-wider">Date Added</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <?php if ($data->num_rows > 0): ?>
                                        <?php while ($r = $data->fetch_assoc()): ?>
                                            <tr class="inventory-row hover:bg-blue-50 transition-colors group">

                                                <td class="p-5 checkbox-cell">
                                                    <input type="checkbox" class="item-checkbox w-4 h-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500" value="<?= $r['item_id'] ?>">
                                                </td>

                                                <td class="px-6 py-5 compact-td font-semibold text-slate-900 group-hover:text-blue-600">#<?= $r['item_id'] ?></td>

                                                <td class="px-6 compact-td">
                                                    <div class="font-bold text-slate-900 leading-none"><?= htmlspecialchars($r['item_name']) ?></div>
                                                    <div class="text-xs text-slate-600 mt-1 truncate max-w-[200px]"><?= htmlspecialchars($r['description']) ?></div>
                                                </td>

                                                <td class="px-6 py-5">
                                                    <div class="text-sm font-semibold text-slate-700">Model: <?= htmlspecialchars($r['model_no'] ?: '---') ?></div>
                                                    <div class="text-xs text-blue-600 mt-1">S/N: <?= htmlspecialchars($r['serial_no'] ?: '---') ?></div>
                                                </td>

                                                <td class="px-6 compact-td">
                                                    <span class="bg-blue-100 text-blue-700 border border-blue-200 px-3 py-1 rounded-lg text-xs font-semibold"><?= htmlspecialchars($r['location_name']) ?></span>
                                                </td>

                                                <td class="px-6 compact-td text-slate-600 text-sm font-semibold"><?= date("M d, Y", strtotime($r['date_added'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="px-8 py-16 text-center text-slate-500 font-semibold text-sm">No records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center shrink-0">
                            <div class="text-xs font-semibold text-slate-600">
                                Showing <?= ($totalRows > 0 ? $offset + 1 : 0) ?> to <?= min($offset + $limit, $totalRows) ?> of <?= $totalRows ?> entries
                            </div>
                            <nav class="flex items-center gap-2">
                                <?php if ($page > 1): ?>
                                    <a href="<?= $pageUrl . ($page - 1) ?>" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all">Previous</a>
                                <?php endif; ?>
                                <div class="flex items-center gap-1 mx-2">
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <a href="<?= $pageUrl . $i ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold transition-all <?= ($i == $page) ? 'bg-blue-600 text-white border border-blue-600' : 'bg-white border border-slate-300 text-slate-700 hover:bg-blue-50 hover:border-blue-300' ?>"><?= $i ?></a>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($page < $totalPages): ?>
                                    <a href="<?= $pageUrl . ($page + 1) ?>" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all">Next</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="w-full xl:w-[280px] shrink-0 space-y-4">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-lg">
                        <h3 class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Recent Entries
                        </h3>
                        <div class="space-y-3">
                            <?php while ($entry = $recentEntries->fetch_assoc()): ?>
                                <div class="flex flex-col border-l-2 border-blue-200 pl-3">
                                    <span class="text-xs text-slate-500 font-semibold">Registry Update</span>
                                    <span class="text-sm text-slate-700 font-semibold"><?= date("M d, H:i", strtotime($entry['date_added'])) ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-lg">
                        <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Summary
                        </h3>
                        <div class="space-y-3">
                            <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                                <div class="text-xs text-slate-600 font-semibold uppercase mb-1">Total Assets</div>
                                <div class="text-2xl font-black text-blue-600"><?= number_format($totalItems) ?></div>
                            </div>
                            <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg border border-emerald-200">
                                <div class="text-xs text-slate-600 font-semibold uppercase mb-1">Unique Locations</div>
                                <div class="text-2xl font-black text-emerald-600"><?= number_format($uniqueLocations) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleAll(master) {
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = master.checked);
    }

    function resetFilter() {
        window.location.href = window.location.pathname;
    }

    /**
     * Extracts data from the existing HTML table rows 
     * for the selected items only.
     */
    function generateRowsFromSelected(selectedRows) {
        return selectedRows.map((row, index) => {
            const uid = row.cells[1].innerText;
            const assetName = row.querySelector('.font-black').innerText;
            const model = row.cells[3].querySelector('.text-slate-400')?.innerText.replace('M: ', '') || '---';
            const serial = row.cells[3].querySelector('.font-mono')?.innerText.replace('S/N: ', '') || '---';
            const location = row.querySelector('span.bg-blue-500\\/5, .badge')?.innerText || 'N/A';

            return `
            <tr>
                <td>${index + 1}</td>
                <td>${uid}</td>
                <td>${assetName}</td>
                <td>${model}</td>
                <td>${serial}</td>
                <td>${location}</td>
            </tr>
        `;
        }).join('');
    }

    async function printInventory() {
        const sDate = document.getElementById('startDate').value;
        const eDate = document.getElementById('endDate').value;
        const selectedCheckboxes = Array.from(document.querySelectorAll('.item-checkbox:checked'));

        let rowsHtml = '';
        let reportMode = "";
        let reportTitle = "INVENTORY REPORT";
        let totalItems = 0;

        if (selectedCheckboxes.length > 0) {
            reportMode = "SELECTED ITEMS";
            totalItems = selectedCheckboxes.length;
            const selectedRows = selectedCheckboxes.map(cb => cb.closest('tr'));
            rowsHtml = generateRowsFromSelected(selectedRows);
            renderTemplate(rowsHtml, sDate, eDate, reportMode, totalItems, reportTitle);
        } else {
            reportMode = "FULL REGISTRY";
            try {
                const response = await fetch(`../auth/get_all_inventory.php?date_from=${sDate}&date_to=${eDate}`);
                if (!response.ok) throw new Error('File not found');
                const data = await response.json();
                totalItems = data.length;

                rowsHtml = data.map((item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>#${item.item_id}</td>
                    <td>${item.item_name}</td>
                    <td>${item.model_no || '---'}</td>
                    <td>${item.serial_no || '---'}</td>
                    <td>${item.location_name || 'N/A'}</td>
                </tr>
            `).join('');

                renderTemplate(rowsHtml, sDate, eDate, reportMode, totalItems, reportTitle);
            } catch (err) {
                console.error(err);
                alert("Error loading inventory data.");
            }
        }
    }

    function renderTemplate(rows, sDate, eDate, mode, totalCount, title) {
        const win = window.open('', '', 'width=900,height=1270');
        const currentDate = new Date().toLocaleDateString();
        const currentTime = new Date().toLocaleTimeString();

        const dateRange = (sDate && eDate) ?
            `${new Date(sDate).toLocaleDateString()} - ${new Date(eDate).toLocaleDateString()}` :
            "All Dates";

        win.document.write(`
        <html>
        <head>
            <title>Inventory Report</title>
            <style>
                @page {
                    size: A4 portrait;
                    margin: 0;
                }
                
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    width: 210mm;
                    height: 297mm;
                    position: relative;
                }
                
                /* Background with format.png */
                .background {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-image: url('../assets/format.png');
                    background-size: cover;
                    background-position: center;
                    z-index: -1;
                }

                /* Watermark image */
                .watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    opacity: 0.05;
                    z-index: -1;
                    pointer-events: none;
                }
                
                .watermark img {
                    width: 400px;
                    height: 400px;
                    object-fit: contain;
                }
                
                .content {
                    padding: 30mm 20mm 20mm 20mm;
                    height: 100%;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 15px;
                }
                
                .title {
                    font-size: 28px;
                    font-weight: bold;
                    margin: 0;
                    text-transform: uppercase;
                }
                
                .subtitle {
                    font-size: 14px;
                    color: #666;
                    margin: 5px 0;
                }
                
                .info-box {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    font-size: 12px;
                    background: #f5f5f5;
                    padding: 10px;
                    border-radius: 5px;
                }
                
                .info-item {
                    display: flex;
                    flex-direction: column;
                }
                
                .info-label {
                    font-weight: bold;
                    color: #555;
                    font-size: 10px;
                }
                
                .info-value {
                    font-size: 11px;
                    color: #333;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 10px;
                }
                
                th {
                    background: #2c3e50;
                    color: white;
                    padding: 8px;
                    text-align: left;
                    font-weight: bold;
                    border: 1px solid #ddd;
                }
                
                td {
                    padding: 6px;
                    border: 1px solid #ddd;
                }
                
                tr:nth-child(even) {
                    background: #f9f9f9;
                }
                
                .footer {
                    position: absolute;
                    bottom: 20mm;
                    left: 20mm;
                    right: 20mm;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
                
               @media print {
                     body {
                        width: 210mm;
                        height: 297mm;
                        margin: 0;
                    }
                    
                    .page-container {
                        height: 297mm;
                        position: relative;
                    }
                    
                    .content {
                        min-height: calc(297mm - 70mm); /* Account for padding */
                        position: relative;
                    }
                    
                    .watermark {
                        opacity: 0.08;
                    }
                     .footer {
                        position: fixed;
                        bottom: 20mm;
                        left: 20mm;
                        right: 20mm;
                        background: white;
                    }       
                }
            </style>
        </head>
        <body>
            <!-- Format.png as subtle background -->
            <div class="background"></div>

            <!-- Watermark image from assets -->
            <div class="watermark">
                <img src="../assets/logo.png" alt="Watermark">
            </div>

            <div class="content">
                <div class="header">
                    <h1 class="title">${title}</h1>
                    <div class="subtitle">Laboratory Equipment Inventory</div>
                    <div class="subtitle">Generated: ${currentDate} ${currentTime}</div>
                </div>
                
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">Report Period</span>
                        <span class="info-value">${dateRange}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Report Type</span>
                        <span class="info-value">${mode}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Items</span>
                        <span class="info-value">${totalCount}</span>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ID</th>
                            <th>Item Name</th>
                            <th>Model</th>
                            <th>Serial</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
                
                <div class="footer">
                    Science Laboratory Inventory System • Page 1 of 1
                </div>
            </div>
            
            <script>
                window.onload = function() {
                    setTimeout(() => {
                        window.print();
                        window.onafterprint = function() { 
                            setTimeout(() => {
                                window.close();
                            }, 500);
                        };
                    }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
        win.document.close();
    }
</script>