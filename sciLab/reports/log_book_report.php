<?php

include "../auth/session_guard.php";

include "../config/db.php";
include "../admin/header.php";

$limit = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$whereClause = "";
$params = [];
$types = "";

if (!empty($dateFrom) && !empty($dateTo)) {
    $whereClause = " WHERE date BETWEEN ? AND ?";
    $params = [$dateFrom, $dateTo];
    $types = "ss";
}

$countSql = "SELECT COUNT(*) as total FROM logs" . $whereClause;
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT * FROM logs" . $whereClause . " ORDER BY date DESC, time DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$limitParams = array_merge($params, [$limit, $offset]);
$stmt->bind_param($types . "ii", ...$limitParams);
$stmt->execute();
$logs = $stmt->get_result();

$totalLogsCount = $conn->query("SELECT COUNT(*) as total FROM logs")->fetch_assoc()['total'];
$todayLogs = $conn->query("SELECT COUNT(*) as total FROM logs WHERE DATE(date) = CURDATE()")->fetch_assoc()['total'];

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

    .header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Calendar icon styling */
    input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        opacity: 0.6;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-150%);
        }

        100% {
            transform: translateX(400%);
        }
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

<div class="flex h-screen bg-slate-50 overflow-hidden">
    <?php include "../admin/sidebar.php"; ?>

    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-5">
        <div class="w-full flex-shrink-0 header py-4 px-8 flex justify-between items-center print:hidden">
            <div class="flex items-center gap-3">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-[0.2em]">Module: Log Book Report</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-semibold text-slate-600 uppercase"><?php echo date("l, M d, Y"); ?></span>
            </div>
        </div>

        <div class="flex-1 flex flex-col px-8 py-4 overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6 px-2 border-b border-slate-200 pb-6 shrink-0">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                        LOG <span class="text-blue-600">Book</span>
                    </h1>
                    <p class="text-blue-600 font-semibold uppercase tracking-wide text-xs mt-2">
                        Activity Monitor: <span class="text-emerald-600">Session Data Captured</span>
                    </p>
                </div>

                <button onclick="generateInventoryStyleReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold text-sm uppercase tracking-wide transition-all flex items-center gap-3 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>

            <div class="flex flex-col xl:flex-row gap-4 flex-1 overflow-hidden">
                <div class="flex-1 flex flex-col gap-4 overflow-hidden">

                    <div class="bg-white backdrop-blur-md rounded-xl p-4 border border-slate-200 shadow-sm shrink-0">
                        <form method="GET" class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2 bg-slate-50 border border-slate-300 rounded-lg px-3 py-2">
                                <span class="text-xs font-bold text-slate-700 uppercase">Range:</span>
                                <input type="date" name="date_from" id="startDate" value="<?= htmlspecialchars($dateFrom) ?>" class="bg-white text-sm text-slate-900 outline-none font-semibold border-0">
                                <span class="text-slate-400">/</span>
                                <input type="date" name="date_to" id="endDate" value="<?= htmlspecialchars($dateTo) ?>" class="bg-white text-sm text-slate-900 outline-none font-semibold border-0">
                            </div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wide transition-colors">Filter Query</button>
                            <button type="button" onclick="window.location.href=window.location.pathname" class="bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wide border border-slate-300 transition-all">Reset</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
                        <div id="inventoryTableContainer" class="flex-1">
                            <table id="logsTable" class="min-w-full text-sm text-slate-700 border-collapse">
                                <thead class="bg-slate-50">
                                    <tr class="border-b border-slate-200">
                                        <th class="px-5 py-3 text-left">
                                            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 bg-white text-blue-600">
                                        </th>
                                        <th class="py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">ID</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Student Identity</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Classification</th>
                                        <th class="px-6 py-3 text-left font-bold uppercase text-slate-700 text-xs tracking-wide">Log Sequence</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if ($logs->num_rows > 0): ?>
                                        <?php while ($row = $logs->fetch_assoc()): ?>
                                            <tr class="inventory-row hover:bg-blue-50 transition-colors group">
                                                <td class="p-5">
                                                    <input type="checkbox" class="item-checkbox w-4 h-4 rounded border-slate-300 bg-white text-blue-600">
                                                </td>
                                                <td class="px-0 py-5">
                                                    <span class="text-sm font-bold text-blue-600">#<?= htmlspecialchars($row['student_id']) ?></span>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($row['name']) ?></div>
                                                </td>
                                                <td class="px-8 py-5">
                                                    <div class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 border border-blue-200">
                                                        <span class="text-xs font-semibold text-blue-700 uppercase"><?= htmlspecialchars($row['grade']) ?> • <?= htmlspecialchars($row['section']) ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-5">
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-sm font-semibold text-slate-600 uppercase"><?= date("M d", strtotime($row['date'])) ?></span>
                                                        <span class="px-2 py-1 rounded-lg bg-blue-600 text-white text-xs font-bold"><?= date("h:i A", strtotime($row['time'])) ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="px-8 py-32 text-center text-slate-500 font-bold uppercase tracking-wide text-sm">No records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center shrink-0">
                            <div class="text-xs font-semibold text-slate-600">
                                Records <?= ($totalRows > 0 ? $offset + 1 : 0) ?> to <?= min($offset + $limit, $totalRows) ?>
                            </div>
                            <nav class="flex items-center gap-2">
                                <?php if ($page > 1): ?>
                                    <a href="<?= $pageUrl . ($page - 1) ?>" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:text-blue-600 hover:border-blue-500 transition-all">&lt; Previous</a>
                                <?php endif; ?>
                                <div class="flex items-center gap-1 mx-2">
                                    <?php
                                    $start = max(1, $page - 2);
                                    $end = min($totalPages, $page + 2);
                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <a href="<?= $pageUrl . $i ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold transition-all <?= ($i == $page) ? 'bg-blue-600 text-white border border-blue-600' : 'bg-white border border-slate-300 text-slate-600 hover:text-blue-600 hover:border-blue-500' ?>"><?= sprintf("%02d", $i) ?></a>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($page < $totalPages): ?>
                                    <a href="<?= $pageUrl . ($page + 1) ?>" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:text-blue-600 hover:border-blue-500 transition-all">Next &gt;</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="w-full xl:w-[240px] shrink-0 space-y-4">
                    <div class="relative overflow-hidden bg-white backdrop-blur-md rounded-2xl p-5 border border-slate-200 shadow-sm group">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-100 rounded-full blur-3xl opacity-30 group-hover:opacity-50 transition-opacity"></div>
                        <h3 class="relative z-10 text-xs font-black text-emerald-600 uppercase tracking-wide mb-6 flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            System Status
                        </h3>
                        <div class="relative z-10 space-y-6">
                            <div>
                                <span class="text-slate-600 font-bold text-xs uppercase tracking-wide block mb-2">Cumulative Logs</span>
                                <div class="flex items-baseline gap-1">
                                    <div class="text-4xl font-black tracking-tight text-slate-900"><?= number_format($totalLogsCount) ?></div>
                                    <span class="text-xs text-slate-500 font-semibold">PTS</span>
                                </div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                                <span class="text-blue-600 font-bold text-xs uppercase tracking-wide block mb-2">Today's Traffic</span>
                                <div class="text-2xl font-black text-slate-900 tracking-tight"><?= number_format($todayLogs) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function generateInventoryStyleReport() {
        const sDate = document.getElementById('startDate').value;
        const eDate = document.getElementById('endDate').value;
        const selectedCheckboxes = Array.from(document.querySelectorAll('#logsTable .item-checkbox:checked'));

        let rowsHtml = '';
        let reportMode = "";
        let reportTitle = "ATTENDANCE REPORT";
        let totalItems = 0;

        if (selectedCheckboxes.length > 0) {
            reportMode = "SELECTED LOGS";
            totalItems = selectedCheckboxes.length;
            const selectedRows = selectedCheckboxes.map(cb => cb.closest('tr'));
            rowsHtml = generateLogRowsFromSelected(selectedRows);
        } else {
            reportMode = "ALL LOGS";
            // Get all rows from the table, excluding the "No records found" row
            const allRows = Array.from(document.querySelectorAll('#logsTable tbody tr')).filter(row => {
                // Filter out rows that don't have checkboxes (like "No records found" row)
                return row.querySelector('.item-checkbox') !== null;
            });
            totalItems = allRows.length;
            rowsHtml = generateLogRowsFromSelected(allRows);
        }

        renderLogTemplate(rowsHtml, sDate, eDate, reportMode, totalItems, reportTitle);
    }

    function generateLogRowsFromSelected(selectedRows) {
        return selectedRows.map((row, index) => {
            // Skip rows that don't have data (like "No records found" row)
            if (!row.cells || row.cells.length < 5) {
                return '';
            }
            
            // Extract data based on actual HTML structure
            const studentIdCell = row.cells[1];
            const studentId = studentIdCell ? (studentIdCell.querySelector('span')?.innerText.replace('#', '') || studentIdCell.innerText.replace('#', '').trim()) : '';
            
            const studentNameCell = row.cells[2];
            const studentName = studentNameCell ? (studentNameCell.querySelector('div')?.innerText || studentNameCell.innerText.trim()) : '';
            
            const gradeSectionCell = row.cells[3];
            const gradeSection = gradeSectionCell ? (gradeSectionCell.querySelector('span')?.innerText || gradeSectionCell.innerText.trim()) : '';
            
            const dateTimeCell = row.cells[4];
            let date = '';
            let time = '';
            if (dateTimeCell) {
                const dateSpan = dateTimeCell.querySelector('span.text-slate-600');
                const timeSpan = dateTimeCell.querySelector('span.bg-blue-600');
                date = dateSpan ? dateSpan.innerText.trim() : '';
                time = timeSpan ? timeSpan.innerText.trim() : '';
            }

            // Skip if no valid data found
            if (!studentId && !studentName) {
                return '';
            }

            return `
            <tr>
                <td>${index + 1}</td>
                <td>${studentId}</td>
                <td>${studentName}</td>
                <td>${gradeSection}</td>
                <td>${date} ${time}</td>
            </tr>
        `;
        }).filter(row => row !== '').join('');
    }

    function renderLogTemplate(rows, sDate, eDate, mode, totalCount, title) {
        const win = window.open('', '', 'width=900,height=1270');
        const currentDate = new Date().toLocaleDateString();
        const currentTime = new Date().toLocaleTimeString();

        const dateRange = (sDate && eDate) ?
            `${new Date(sDate).toLocaleDateString()} - ${new Date(eDate).toLocaleDateString()}` :
            "All Dates";

        win.document.write(`
        <html>
        <head>
            <title>Attendance Report</title>
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
                    background-image: url('<?php echo htmlspecialchars((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/sciLab/assets/format.png'); ?>');
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
                    padding: 30mm 20mm 70mm 20mm;
                    min-height: 297mm;
                    position: relative;
                    z-index: 1;
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
                <img src="<?php echo htmlspecialchars((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/sciLab/assets/logo.png'); ?>" alt="Watermark">
            </div>
            
            <div class="content">
                <div class="header">
                    <h1 class="title">${title}</h1>
                    <div class="subtitle">Student Attendance Log Book</div>
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
                        <span class="info-label">Total Records</span>
                        <span class="info-value">${totalCount}</span>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Grade & Section</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
                
                <div class="footer">
                    Science Laboratory Attendance System • Page 1 of 1
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

    // Add this to your existing script section
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.item-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });
        }
    });
</script>