<!-- ICT Dashboard - design aligned with Library/Queue dashboards -->
<div class="px-6">

    <!-- Stats Overview (gradient cards like Library/Queue) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Avg Users/Week</h3>
                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <p class="text-4xl font-black mb-2" id="stat-avg-weekly">...</p>
            <p class="text-sm opacity-80">Weekly average</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Total Classes</h3>
                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <p class="text-4xl font-black mb-2" id="stat-classes">...</p>
            <p class="text-sm opacity-80">Categories</p>
        </div>

        <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Avg Daily Users</h3>
                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <p class="text-4xl font-black mb-2" id="stat-avg-daily">...</p>
            <p class="text-sm opacity-80">Per day</p>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Total Stock</h3>
                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <p class="text-4xl font-black mb-2" id="stat-items">...</p>
            <p class="text-sm opacity-80">Inventory items</p>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Live Sessions</h3>
                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-4xl font-black mb-2" id="stat-active">...</p>
            <p class="text-sm opacity-80">Active now</p>
        </div>

        <div class="bg-gradient-to-br from-slate-600 to-slate-800 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">System Users</h3>
                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <p class="text-4xl font-black mb-2" id="stat-users">...</p>
            <p class="text-sm opacity-80">Registered</p>
        </div>
    </div>

    <!-- Chart + Quick Links (same layout as Library dashboard) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2.5">
                    <h3 class="text-white font-semibold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                        User Activity Trends
                    </h3>
                </div>
                <div class="p-4">
                    <canvas id="activityChart" height="140"></canvas>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-slate-600 to-slate-700 px-6 py-4">
                    <h3 class="text-white font-bold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Quick Links
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    <?php if (!isset($ict_public)) { if (!defined('BASE_URL')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; } $ict_public = (rtrim(BASE_URL, '/') === '' ? '' : rtrim(BASE_URL, '/')) . '/ictOffice/public'; } ?>
                    <a href="<?php echo htmlspecialchars($ict_public); ?>/?page=inventory" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-blue-600 hover:text-white transition-all group border border-slate-100">
                        <span class="font-medium text-slate-800 group-hover:text-white">Manage Inventory</span>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="<?php echo htmlspecialchars($ict_public); ?>/?page=logbook-records" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-blue-600 hover:text-white transition-all group border border-slate-100">
                        <span class="font-medium text-slate-800 group-hover:text-white">View Logbook</span>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var ICT_API = (typeof window.ICT_API !== 'undefined' && window.ICT_API) ? window.ICT_API : <?php echo json_encode(isset($ict_api) ? $ict_api : ((defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/ictOffice/api')); ?>;

async function loadDashboard() {
    try {
        var res = await fetch(ICT_API + '/dashboard');
        var data = await res.json();

        document.getElementById('stat-avg-weekly').innerText = data.avg_weekly != null ? data.avg_weekly : '0';
        document.getElementById('stat-classes').innerText = data.total_classes != null ? data.total_classes : '0';
        document.getElementById('stat-avg-daily').innerText = data.avg_daily != null ? data.avg_daily : '0';
        document.getElementById('stat-items').innerText = data.total_items != null ? data.total_items : '0';
        document.getElementById('stat-active').innerText = data.active_logs != null ? data.active_logs : '0';
        document.getElementById('stat-users').innerText = data.total_users != null ? data.total_users : '0';

        var chartData = data.chart_data && Array.isArray(data.chart_data) ? data.chart_data : [];
        var labels = chartData.map(function(d) { return d.label || d.day || ''; });
        var counts = chartData.map(function(d) { return d.count != null ? d.count : 0; });
        if (labels.length === 0) {
            for (var i = 6; i >= 0; i--) {
                var d = new Date(); d.setDate(d.getDate() - i);
                labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                counts.push(0);
            }
        }
        var ctx = document.getElementById('activityChart');
        if (ctx && ctx.getContext) {
            if (window._activityChartInstance) {
                window._activityChartInstance.destroy();
                window._activityChartInstance = null;
            }
            window._activityChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Log-ins', data: counts, backgroundColor: 'rgba(59, 130, 246, 0.6)' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { maxTicksLimit: 5 }, grid: { display: false } },
                        x: { grid: { display: false }, ticks: { maxRotation: 0 } }
                    }
                }
            });
        }
    } catch (e) { console.error("Error loading dashboard data", e); }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
