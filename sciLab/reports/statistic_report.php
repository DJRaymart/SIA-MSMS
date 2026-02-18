<?php

include "../auth/session_guard.php";

include "../config/db.php";
include "../admin/header.php";

$total_classes = $conn->query("SELECT COUNT(DISTINCT CONCAT(grade,'-',section)) AS total_classes FROM logs")->fetch_assoc()['total_classes'];
$avg_daily = round($conn->query("SELECT AVG(daily_count) AS avg_daily FROM (SELECT COUNT(*) AS daily_count FROM logs GROUP BY DATE(date)) AS daily_counts")->fetch_assoc()['avg_daily'], 2);

$avg_weekly = round($conn->query("SELECT AVG(weekly_count) AS avg_weekly FROM (SELECT COUNT(DISTINCT name) AS weekly_count FROM logs GROUP BY YEAR(date), WEEK(date)) AS weekly_counts")->fetch_assoc()['avg_weekly'], 2);

$chart_result = $conn->query("
    SELECT DATE(date) AS log_date, COUNT(DISTINCT name) AS user_count 
    FROM logs 
    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(date)
    ORDER BY DATE(date)
");

$chart_labels = [];
$chart_data = [];
while ($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = $row['log_date'];
    $chart_data[] = $row['user_count'];
}
?>

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
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-[0.2em]">Module: Analytics Report</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-semibold text-slate-600 uppercase"><?php echo date("l, M d, Y"); ?></span>
            </div>
        </div>

        <div class="flex-1 flex flex-col px-8 py-6 overflow-hidden">

            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8 px-2 border-b border-slate-200 pb-8 shrink-0">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter">
                        Analytics <span class="text-blue-600">Reports</span>
                    </h1>
                    <p class="text-blue-600 font-semibold uppercase tracking-wider text-xs mt-2">
                        System Status: <span class="text-emerald-600">Active</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 shrink-0">

                <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Total Classes</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $total_classes ?></p>
                    <p class="text-sm opacity-80">Active classes</p>
                </div>

                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Daily Average</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $avg_daily ?></p>
                    <p class="text-sm opacity-80">Logs per day</p>
                </div>

                <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Weekly Average</h3>
                        <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-4xl font-black mb-2"><?= $avg_weekly ?></p>
                    <p class="text-sm opacity-80">Users per week</p>
                </div>

            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-lg flex-1 flex flex-col min-h-[400px] overflow-hidden mb-4">
                <div class="flex items-center gap-6 mb-8 shrink-0">
                    <h3 class="text-lg font-bold text-blue-600 uppercase tracking-wider">Activity Graph (30 Days)</h3>
                    <div class="h-[1px] w-full bg-gradient-to-r from-slate-300 to-transparent"></div>
                </div>

                <div class="relative flex-1 w-full min-h-0">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('usersChart').getContext('2d');
    
    // Create a Gradient for the "glow" effect under the line
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)'); // Blue glow at top
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');   // Fade to transparent

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'System Traffic',
                data: <?= json_encode($chart_data) ?>,
                borderColor: '#3b82f6', // Bright Blue
                borderWidth: 4,
                fill: true,
                backgroundColor: gradient,
                tension: 0.4, // Smooth curve
                pointBackgroundColor: '#0b0f1a', // Dark center
                pointBorderColor: '#60a5fa',    // Light blue border
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#3b82f6',
                pointHoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                    bodyFont: { family: 'Inter', size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return ` Logs: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.2)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Inter', size: 12 },
                        padding: 10
                    }
                },
                x: {
                    grid: { 
                        display: true,
                        color: 'rgba(148, 163, 184, 0.1)'
                    },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Inter', size: 12 },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 10
                    }
                }
            }
        }
    });
</script>