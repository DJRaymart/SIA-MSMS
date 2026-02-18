<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/check_admin_access.php';
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
$sciLabBase = $baseS . 'sciLab';

include "../config/db.php";
include "header.php";

$today = date("Y-m-d");

$total_items = $conn->query("SELECT COUNT(*) as count FROM inventory")->fetch_assoc()['count'];

$logsTodayStmt = $conn->prepare("SELECT COUNT(*) as count FROM logs WHERE date = ?");
$logsTodayStmt->bind_param("s", $today);
$logsTodayStmt->execute();
$logs_today = $logsTodayStmt->get_result()->fetch_assoc()['count'];

$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")->fetch_assoc()['count'];

$recent_logs = $conn->query("SELECT name, time, section FROM logs ORDER BY log_id DESC LIMIT 6");

$critical_items = $conn->query("SELECT item_name, quantity FROM inventory WHERE quantity < 10 ORDER BY quantity ASC LIMIT 4");
?>

<style>
    .header-blur {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
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

    .header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }
</style>

<div class="flex h-screen bg-slate-50 overflow-hidden text-slate-800" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <?php include "sidebar.php"; ?>

    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-5">

        <div class="w-full flex-shrink-0 header py-4 px-8 flex justify-between items-center print:hidden">
            <div class="flex items-center gap-3">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-[0.2em]">Module: Dashboard</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-semibold text-slate-600 uppercase"><?php echo date("l, M d, Y"); ?></span>
            </div>
        </div>

        <div class="flex-1 flex flex-col px-8 py-6">

            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8 px-2 border-b border-slate-200 pb-8">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter">
                        Dash<span class="text-blue-600">board</span>
                    </h1>
                    <p class="text-blue-600 font-semibold uppercase tracking-wider text-xs mt-2">
                        System Status: <span class="text-emerald-600">Active</span>
                    </p>
                </div>

                <div class="flex items-center gap-3 bg-white px-5 py-2.5 rounded-xl border border-blue-200 shadow-sm">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Operations Active</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="<?php echo htmlspecialchars($sciLabBase); ?>/admin/inventory.php" class="direct-link bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Total Inventory</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $total_items ?></p>
                    <p class="text-sm opacity-80">Total items</p>
                </a>

                <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reports/log_book_report.php" class="direct-link bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Today's Logs</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $logs_today ?></p>
                    <p class="text-sm opacity-80">Entries today</p>
                </a>

                <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reservation/booking_requests.php" class="direct-link bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Pending Bookings</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $pending_bookings ?></p>
                    <p class="text-sm opacity-80">Requires action</p>
                </a>

                <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Critical Items</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $critical_items->num_rows ?></p>
                    <p class="text-sm opacity-80">Low stock items</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 flex flex-col gap-6">

                    <!-- Critical Items Section -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4">
                            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Critical Asset Pulse
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <?php if ($critical_items->num_rows > 0): ?>
                                    <?php while ($item = $critical_items->fetch_assoc()): ?>
                                        <div class="bg-slate-50 border border-red-200 p-5 rounded-xl relative overflow-hidden group hover:border-red-300 transition-all">
                                            <p class="text-xs text-slate-500 font-semibold uppercase mb-3 tracking-tighter">Asset ID: <span class="text-slate-400">#<?= rand(100, 999) ?></span></p>
                                            <p class="text-sm font-bold text-slate-900 uppercase truncate mb-4"><?= htmlspecialchars($item['item_name']) ?></p>
                                            <div class="flex items-end justify-between">
                                                <div class="flex items-end gap-1">
                                                    <span class="text-3xl font-black text-red-600 leading-none"><?= $item['quantity'] ?></span>
                                                    <span class="text-xs text-slate-500 uppercase font-semibold mb-1">Qty</span>
                                                </div>
                                                <div class="w-16 h-1.5 bg-slate-200 rounded-full mb-1.5 overflow-hidden">
                                                    <div class="h-full bg-red-500" style="width: <?= min(($item['quantity'] / 10) * 100, 100) ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="col-span-full text-center text-slate-500 font-semibold text-sm py-10">All asset levels verified within nominal range.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL -->
                <div class="space-y-6">
                    <!-- Live Access Feed -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Live Access Feed
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                <?php if ($recent_logs->num_rows > 0): ?>
                                    <?php while ($log = $recent_logs->fetch_assoc()): ?>
                                        <div class="flex gap-4 relative group border-b border-slate-200 pb-4 last:border-0">
                                            <div class="mt-1.5 w-2 h-2 rounded-full bg-blue-500 shrink-0 group-hover:scale-125 transition-transform"></div>
                                            <div class="flex-1">
                                                <div class="flex justify-between items-start mb-1">
                                                    <p class="text-sm font-bold text-slate-900 uppercase tracking-tight group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($log['name']) ?></p>
                                                    <span class="text-xs font-semibold text-slate-500"><?= $log['time'] ?></span>
                                                </div>
                                                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">
                                                    <span class="text-blue-600 mr-1 font-bold">/</span> <?= htmlspecialchars($log['section']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="h-full flex items-center justify-center py-10">
                                        <p class="text-sm font-semibold text-slate-400 italic">No recent activity...</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-4 space-y-2">
                            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/admin/inventory.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Manage Inventory
                            </a>
                            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reservation/booking_requests.php" class="block w-full bg-amber-600 hover:bg-amber-700 text-white text-center py-3 px-4 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                View Bookings
                            </a>
                            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reports/log_book_report.php" class="block w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center py-3 px-4 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.direct-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const targetUrl = link.getAttribute('href');
                const titleEl = link.querySelector('h2');
                const pageName = titleEl ? titleEl.innerText : "System Area";

                if (targetUrl && targetUrl !== '#') {
                    e.preventDefault();
                    if (typeof triggerLabLoader === "function") {
                        triggerLabLoader(targetUrl, `Initialising ${pageName} Protocol...`);
                    } else {
                        window.location.href = targetUrl;
                    }
                }
            });
        });
    });
</script>
