<?php
if (!defined('APP_ROOT')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$app_root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/check_admin_access.php';
require_once $app_root . '/config/db.php';

function safeCount($conn, $query) {
    if (!$conn) return 0;
    $r = @$conn->query($query);
    if (!$r) return 0;
    $row = $r->fetch_assoc();
    return ($row && isset($row['count'])) ? (int)$row['count'] : 0;
}

$stats = [
    'sciLab' => [
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM logs") + safeCount($conn, "SELECT COUNT(*) as count FROM reservations"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM logs WHERE DATE(date) = CURDATE()"),
        'attention' => safeCount($conn, "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'"),
    ],
    'library' => [
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM borrowing_transaction") + safeCount($conn, "SELECT COUNT(*) as count FROM log_book"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM log_book WHERE DATE(login_at) = CURDATE()"),
        'attention' => safeCount($conn, "SELECT COUNT(*) as count FROM borrowing_transaction WHERE status = 'Overdue'"),
    ],
    'avr' => [
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_borrowed") + safeCount($conn, "SELECT COUNT(*) as count FROM avr_reservation") + safeCount($conn, "SELECT COUNT(*) as count FROM log_attendance"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM log_attendance WHERE Date = CURDATE()"),
        'attention' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_borrowed WHERE Status = 'Overdue'"),
    ],
    'queue' => [
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM customers"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = CURDATE()"),
        'attention' => safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE status = 'waiting'"),
    ],
    'clinic' => [
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_log") + safeCount($conn, "SELECT COUNT(*) as count FROM clinic_records"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_log WHERE DATE(date) = CURDATE()") + safeCount($conn, "SELECT COUNT(*) as count FROM clinic_records WHERE DATE(date_created) = CURDATE()"),
        'attention' => 0,
    ],
    'ictOffice' => [
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_logs"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_logs WHERE DATE(time_in) = CURDATE()"),
        'attention' => 0,
    ],
];

$labelsMap = [
    'sciLab' => 'Science Lab',
    'library' => 'Library',
    'avr' => 'AVR',
    'queue' => 'Queue',
    'clinic' => 'Clinic',
    'ictOffice' => 'ICT Office',
];

$moduleLabels = [];
$moduleTotals = [];
$moduleToday = [];
$moduleAttention = [];
$systemTotal = 0;
$systemToday = 0;
$systemAttention = 0;

foreach ($labelsMap as $key => $label) {
    $moduleLabels[] = $label;
    $moduleTotals[] = (int) ($stats[$key]['total'] ?? 0);
    $moduleToday[] = (int) ($stats[$key]['today'] ?? 0);
    $moduleAttention[] = (int) ($stats[$key]['attention'] ?? 0);
    $systemTotal += (int) ($stats[$key]['total'] ?? 0);
    $systemToday += (int) ($stats[$key]['today'] ?? 0);
    $systemAttention += (int) ($stats[$key]['attention'] ?? 0);
}

$trendLabels = [];
$trendData = [];
for ($i = 6; $i >= 0; $i--) {
    $dateSql = "DATE_SUB(CURDATE(), INTERVAL $i DAY)";
    $trendLabels[] = date('M d', strtotime("-$i day"));
    $count = 0;
    $count += safeCount($conn, "SELECT COUNT(*) as count FROM logs WHERE DATE(date) = $dateSql");
    $count += safeCount($conn, "SELECT COUNT(*) as count FROM borrowing_transaction WHERE DATE(borrow_date) = $dateSql");
    $count += safeCount($conn, "SELECT COUNT(*) as count FROM log_attendance WHERE DATE(Date) = $dateSql");
    $count += safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = $dateSql");
    $count += safeCount($conn, "SELECT COUNT(*) as count FROM clinic_log WHERE DATE(date) = $dateSql");
    $count += safeCount($conn, "SELECT COUNT(*) as count FROM ict_logs WHERE DATE(time_in) = $dateSql");
    $trendData[] = (int) $count;
}

require_once 'header_unified.php';
?>

<div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide font-bold text-slate-500">Total Records</p>
            <p class="text-3xl font-black text-slate-900 mt-2"><?php echo number_format($systemTotal); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide font-bold text-slate-500">Today Activity</p>
            <p class="text-3xl font-black text-blue-700 mt-2"><?php echo number_format($systemToday); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs uppercase tracking-wide font-bold text-slate-500">Needs Attention</p>
            <p class="text-3xl font-black <?php echo $systemAttention > 0 ? 'text-amber-600' : 'text-emerald-600'; ?> mt-2"><?php echo number_format($systemAttention); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-lg font-black text-slate-900 mb-3">Module Total Records</h3>
            <div class="h-80"><canvas id="moduleTotalsChart"></canvas></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-lg font-black text-slate-900 mb-3">Today by Module</h3>
            <div class="h-80"><canvas id="moduleTodayChart"></canvas></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-lg font-black text-slate-900 mb-3">System Trend (Last 7 Days)</h3>
            <div class="h-80"><canvas id="systemTrendChart"></canvas></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-lg font-black text-slate-900 mb-3">Attention Distribution</h3>
            <div class="h-80"><canvas id="attentionChart"></canvas></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const moduleLabels = <?php echo json_encode($moduleLabels); ?>;
const moduleTotals = <?php echo json_encode($moduleTotals); ?>;
const moduleToday = <?php echo json_encode($moduleToday); ?>;
const moduleAttention = <?php echo json_encode($moduleAttention); ?>;
const trendLabels = <?php echo json_encode($trendLabels); ?>;
const trendData = <?php echo json_encode($trendData); ?>;

new Chart(document.getElementById('moduleTotalsChart'), {
    type: 'bar',
    data: {
        labels: moduleLabels,
        datasets: [{
            label: 'Total Records',
            data: moduleTotals,
            backgroundColor: ['#2563eb','#10b981','#ec4899','#8b5cf6','#14b8a6','#f59e0b'],
            borderRadius: 8
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

new Chart(document.getElementById('moduleTodayChart'), {
    type: 'bar',
    data: {
        labels: moduleLabels,
        datasets: [{
            label: 'Today',
            data: moduleToday,
            backgroundColor: '#0ea5e9',
            borderRadius: 8
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

new Chart(document.getElementById('systemTrendChart'), {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Daily Activity',
            data: trendData,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.15)',
            fill: true,
            tension: 0.35,
            pointRadius: 4
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

new Chart(document.getElementById('attentionChart'), {
    type: 'doughnut',
    data: {
        labels: moduleLabels,
        datasets: [{
            data: moduleAttention,
            backgroundColor: ['#2563eb','#10b981','#ec4899','#8b5cf6','#14b8a6','#f59e0b']
        }]
    },
    options: {
        maintainAspectRatio: false
    }
});
</script>

<?php require_once 'footer_unified.php'; ?>
