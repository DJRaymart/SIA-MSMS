<?php

include "../auth/session_guard.php";

include "../config/db.php";
include "../admin/header.php";

$limit = 7;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$status = $_GET['status'] ?? '';
$where = "WHERE 1";
$params = [];
$types = "";

if ($status !== '') {
    $where .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$countSql = "SELECT COUNT(*) total FROM reservations $where";
$stmt = $conn->prepare($countSql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT r.*, 
               GROUP_CONCAT(CONCAT(ri.qty, ' × ', i.item_name) SEPARATOR ', ') AS items
        FROM reservations r
        LEFT JOIN reservation_items ri ON ri.reservation_id = r.id
        LEFT JOIN inventory i ON i.item_id = ri.item_id
        $where
        GROUP BY r.id
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result();

$pending = $conn->query("SELECT COUNT(*) c FROM reservations WHERE status='pending'")->fetch_assoc()['c'];
$approved = $conn->query("SELECT COUNT(*) c FROM reservations WHERE status='approved'")->fetch_assoc()['c'];
$declined = $conn->query("SELECT COUNT(*) c FROM reservations WHERE status='declined'")->fetch_assoc()['c'];
?>

<script src="<?php echo htmlspecialchars((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/sciLab/assets/js/global-alert.js'); ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pendingAlert = sessionStorage.getItem('inventoryAlert');
        if (pendingAlert) {
            const data = JSON.parse(pendingAlert);
            showAlert(data.type, data.title, data.message);
            sessionStorage.removeItem('inventoryAlert');
        }
    });

    function updateStatus(id, action) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);

        fetch('../auth/update_reservation_status.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    showAlert('success', 'Registry Sync', d.message);

                    // Find the row for this reservation
                    const row = document.querySelector(`button[onclick="updateStatus(${id}, 'approve')"]`)?.closest('tr');
                    if (!row) return;

                    // Update status cell
                    const statusCell = row.querySelector('td:nth-child(6) span');
                    if (action === 'approve') {
                        statusCell.textContent = 'APPROVED';
                        statusCell.className = 'px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-500/10 text-emerald-400';

                        // Remove all existing buttons
                        row.querySelectorAll('button').forEach(btn => btn.remove());

                        // Create Print button dynamically
                        const printBtn = document.createElement('button');
                        printBtn.textContent = 'Print';
                        printBtn.className = 'print-btn bg-blue-600/20 text-blue-400 px-3 py-1 rounded text-[9px] font-black uppercase hover:bg-blue-600 hover:text-white';
                        printBtn.onclick = () => printReservation(d.reservation);

                        // Append to action cell
                        row.querySelector('td:last-child .flex').appendChild(printBtn);

                    } else {
                        statusCell.textContent = 'DECLINED';
                        statusCell.className = 'px-2 py-0.5 rounded text-[9px] font-black uppercase bg-red-500/10 text-red-400';

                        // Remove action buttons after decline
                        row.querySelectorAll('button').forEach(btn => btn.remove());
                    }

                } else {
                    showAlert('danger', 'System Error', d.message);
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('danger', 'System Error', 'Failed to update status.');
            });
    }

    // --- IMPROVED PRINT CONTENT ---
    window.printReservation = function(reservation) {
        const win = window.open('', '', 'width=900,height=1270');
        const currentDate = new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const currentTime = new Date().toLocaleTimeString();

        const html = `
        <html>
        <head>
            <title>Reservation Slip - ${reservation.reference_no}</title>
            <style>
                @page { size: A4 portrait; margin: 0; }
                body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; }
                
                .page-container { position: relative; width: 210mm; height: 297mm; padding: 40mm 25mm; box-sizing: border-box; overflow: hidden; }
                
                .background { position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                              background-image: url('../assets/format.png'); background-size: cover; 
                              background-position: center; z-index: -2; }
                              
                .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); 
                             opacity: 0.03; z-index: -1; pointer-events: none; }
                .watermark img { width: 500px; }

                .header { border-bottom: 3px solid #0f172a; padding-bottom: 20px; margin-bottom: 40px; }
                .title { font-size: 42px; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: -1px; color: #0f172a; }
                .ref-badge { display: inline-block; background: #0f172a; color: white; padding: 5px 15px; font-size: 18px; font-weight: bold; margin-top: 10px; }

                .content { display: grid; grid-template-columns: 1fr; gap: 30px; }
                .info-group { border-left: 4px solid #3b82f6; padding-left: 20px; }
                .info-label { font-size: 12px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 2px; }
                .info-value { font-size: 24px; font-weight: 700; color: #0f172a; display: block; margin-top: 4px; }
                
                .footer { position: absolute; bottom: 20mm; left: 25mm; right: 25mm; 
                          text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
                
                @media print { 
                    body { -webkit-print-color-adjust: exact; }
                    .background { display: block; }
                }
            </style>
        </head>
        <body>
            <div class="page-container">
                <div class="background"></div>
                <div class="watermark"><img src="../assets/logo.png"></div>

                <div class="header">
                    <h1 class="title">Reservation Slip</h1>
                    <div class="ref-badge">REF: ${reservation.reference_no}</div>
                    <div style="margin-top: 15px; font-size: 14px; font-weight: 600;">Issued on ${currentDate} at ${currentTime}</div>
                </div>

                <div class="content">
                    <div class="info-group">
                        <span class="info-label">Instructor / Activity</span>
                        <span class="info-value">${reservation.activity}</span>
                        <span style="font-size: 16px; color: #3b82f6;">Section: ${reservation.grade_section}</span>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Schedule of Usage</span>
                        <span class="info-value">${new Date(reservation.usage_date).toLocaleString()}</span>
                    </div>
                 <div class="info-group">
                    <span class="info-label">Equipment Details</span>
                    <span class="info-value">
                        ${reservation.items} 
                    </span>
                </div>

                    <div class="info-group">
                        <span class="info-label">Student Engagement</span>
                        <span class="info-value">${reservation.student_count} Total Students</span>
                    </div>
                </div>

                <div class="footer">
                    <strong>Science Laboratory Information System</strong><br>
                    This is a computer-generated document. No signature required.
                </div>
            </div>
            <script>
                window.onload = () => {
                    setTimeout(() => {
                        window.print();
                        window.onafterprint = () => window.close();
                    }, 500);
                };
            <\/script>
        </body>
        </html>`;

        win.document.write(html);
        win.document.close();
    };
</script>

<style>
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
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-[0.2em]">Module: Booking Requests</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-semibold text-slate-600 uppercase"><?php echo date("l, M d, Y"); ?></span>
            </div>
        </div>

        <div class="flex-1 flex flex-col px-8 py-6 overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 px-2 border-b border-slate-200 pb-6 shrink-0">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter">
                        Booking <span class="text-blue-600">Requests</span>
                    </h1>
                    <p class="text-blue-600 font-semibold uppercase tracking-wider text-xs mt-2">
                        System Status: <span class="text-emerald-600">Active</span>
                    </p>
                </div>
                <form method="GET">
                    <select name="status" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-900 text-sm px-4 py-2 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="declined" <?= $status == 'declined' ? 'selected' : '' ?>>Declined</option>
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <?php
                $cards = [
                    ['Pending', $pending, 'yellow', '⏳', 'from-amber-500 to-orange-600'],
                    ['Approved', $approved, 'emerald', '✔', 'from-emerald-500 to-teal-600'],
                    ['Declined', $declined, 'red', '✖', 'from-red-500 to-rose-600']
                ];
                foreach ($cards as $c): ?>
                    <div class="bg-gradient-to-br <?= $c[4] ?> rounded-2xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90"><?= $c[0] ?></h3>
                            <span class="text-3xl opacity-50"><?= $c[3] ?></span>
                        </div>
                        <p class="text-4xl font-black mb-2"><?= $c[1] ?></p>
                        <p class="text-sm opacity-80">Total requests</p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-lg mt-6 overflow-hidden">
                <table class="w-full text-sm text-slate-700">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600">
                        <tr>
                            <th class="px-6 py-3 text-left uppercase font-bold tracking-wider text-xs text-white">Ref</th>
                            <th class="px-6 py-3 text-left uppercase font-bold tracking-wider text-xs text-white">Activity</th>
                            <th class="px-6 py-3 text-left uppercase font-bold tracking-wider text-xs text-white">Schedule</th>
                            <th class="px-6 py-3 text-left uppercase font-bold tracking-wider text-xs text-white">Items / Qty</th>
                            <th class="px-6 py-3 text-left uppercase font-bold tracking-wider text-xs text-white">Students</th>
                            <th class="px-6 py-3 text-left uppercase font-bold tracking-wider text-xs text-white">Status</th>
                            <th class="px-6 py-3 text-center uppercase font-bold tracking-wider text-xs text-white">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if ($data->num_rows): while ($r = $data->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50 transition-all">
                                    <!-- Reference -->
                                    <td class="px-6 py-4 font-semibold text-slate-900"><?= $r['reference_no'] ?></td>

                                    <!-- Activity / Section -->
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($r['activity']) ?></div>
                                        <div class="text-xs text-slate-600 mt-1">Section: <?= htmlspecialchars($r['grade_section']) ?></div>
                                    </td>

                                    <!-- Schedule -->
                                    <td class="px-6 py-4 text-sm text-slate-700"><?= date("M d, Y h:i A", strtotime($r['usage_date'])) ?></td>

                                    <!-- Item / Qty -->
                                    <td class="px-6 py-4">
                                        <span class="text-blue-600 font-semibold">Items:</span>
                                        <span class="ml-2 text-slate-700"><?= htmlspecialchars($r['items']) ?></span>
                                    </td>

                                    <!-- Student Count -->
                                    <td class="px-6 py-4 font-bold text-slate-900"><?= $r['student_count'] ?></td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-amber-100 text-amber-700 border-amber-300',
                                            'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
                                            'declined' => 'bg-red-100 text-red-700 border-red-300'
                                        ];
                                        ?>
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold uppercase border <?= $statusColors[$r['status']] ?>">
                                            <?= strtoupper($r['status']) ?>
                                        </span>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col md:flex-row gap-2 justify-center items-center">
                                            <?php if ($r['status'] == 'pending'): ?>
                                                <button type="button" onclick="updateStatus(<?= $r['id'] ?>, 'approve')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all shadow-sm hover:shadow-md">
                                                    Approve
                                                </button>
                                                <button type="button" onclick="updateStatus(<?= $r['id'] ?>, 'decline')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all shadow-sm hover:shadow-md">
                                                    Decline
                                                </button>
                                            <?php elseif ($r['status'] == 'approved'): ?>
                                                <button type="button" onclick='printReservation(<?= json_encode($r) ?>)' class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all shadow-sm hover:shadow-md">
                                                    Print
                                                </button>
                                            <?php else: ?>
                                                <span class="text-red-600 font-semibold text-xs">Declined</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="py-16 text-center font-semibold text-slate-500">No reservations found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </main>
</div>