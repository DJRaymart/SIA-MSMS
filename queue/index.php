<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/check_admin_access.php';
include 'config.php'; 
require_once 'header_unified.php';

$queue_stats = ['waiting' => 0, 'serving' => 0, 'completed' => 0, 'today_total' => 0];
$queue_customers = [];
$queue_counters = [];
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT COUNT(*) as c FROM customers WHERE status = 'waiting' AND DATE(created_at) = CURDATE()");
    if ($stmt) $queue_stats['waiting'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['c'];
    $stmt = $conn->query("SELECT COUNT(*) as c FROM customers WHERE status = 'serving' AND DATE(created_at) = CURDATE()");
    if ($stmt) $queue_stats['serving'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['c'];
    $stmt = $conn->query("SELECT COUNT(*) as c FROM customers WHERE status = 'completed' AND DATE(created_at) = CURDATE()");
    if ($stmt) $queue_stats['completed'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['c'];
    $stmt = $conn->query("SELECT COUNT(*) as c FROM customers WHERE DATE(created_at) = CURDATE()");
    if ($stmt) $queue_stats['today_total'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['c'];
    $queue_page = max(1, (int)($_GET['page'] ?? 1));
    $queue_per_page = 10;
    $queue_offset = ($queue_page - 1) * $queue_per_page;
    $stmt = $conn->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT " . (int)$queue_per_page . " OFFSET " . (int)$queue_offset);
    $queue_customers = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    $stmt = $conn->query("SELECT COUNT(*) as c FROM customers");
    $queue_total = $stmt ? (int)$stmt->fetch(PDO::FETCH_ASSOC)['c'] : 0;
    $queue_total_pages = $queue_total > 0 ? (int)ceil($queue_total / $queue_per_page) : 1;
    $stmt = $conn->query("SELECT c.*, cust.name as current_customer_name FROM counters c LEFT JOIN customers cust ON c.current_customer_id = cust.id");
    if ($stmt) $queue_counters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    
}
?>

<div class="container-fluid">
    <!-- Stats Overview (values from server so they show on first load) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Waiting</h3>
                    <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-4xl font-black mb-2" id="waiting-count"><?php echo (int)$queue_stats['waiting']; ?></p>
                <p class="text-sm opacity-80">In queue</p>
            </div>
            
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Serving</h3>
                    <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-4xl font-black mb-2" id="serving-count"><?php echo (int)$queue_stats['serving']; ?></p>
                <p class="text-sm opacity-80">Currently being served</p>
            </div>
            
            <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Completed</h3>
                    <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-4xl font-black mb-2" id="completed-count"><?php echo (int)$queue_stats['completed']; ?></p>
                <p class="text-sm opacity-80">Finished today</p>
            </div>
            
            <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Today's Total</h3>
                    <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <p class="text-4xl font-black mb-2" id="today-count"><?php echo (int)$queue_stats['today_total']; ?></p>
                <p class="text-sm opacity-80">All customers today</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Customer Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fas fa-plus-circle mr-2"></i>Add New Customer</h2>
                    
                    <form id="customerForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                            <input type="text" id="customerName" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Enter customer name">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
                            <select id="serviceType" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select service type</option>
                                <option value="payment">Payment</option>
                                <option value="inquiry">Registrar</option>
                            </select>
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">
                            <i class="fas fa-ticket-alt mr-2"></i>Generate Queue Number
                        </button>
                    </form>
                    
                    <!-- Generated Queue Display -->
                    <div id="queueResult" class="mt-6 hidden">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                            <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Queue Number Generated</h3>
                            <div class="text-3xl font-bold text-green-600 queue-number mb-2" id="generatedQueue"></div>
                            <p class="text-gray-600">Please wait for your number to be called</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queue Management -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6 card-hover">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-list mr-2"></i>Queue Management</h2>
                        <div class="flex flex-wrap items-center gap-4">
                            <?php if (!empty($queue_counters)): ?>
                            <div class="flex items-center gap-2">
                                <label for="callToCounter" class="text-sm font-medium text-gray-700">Call to (shows on display):</label>
                                <select id="callToCounter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <?php 
                                    $selectedId = null;
                                    foreach ($queue_counters as $cnt): 
                                        $isOnline = $cnt['is_online'] ?? 1;
                                        if ($selectedId === null && $isOnline) $selectedId = (int)$cnt['id'];
                                        $sel = ($selectedId !== null && (int)$cnt['id'] === $selectedId) ? ' selected' : '';
                                    ?>
                                    <option value="<?php echo (int)$cnt['id']; ?>"<?php echo $isOnline ? '' : ' disabled'; ?><?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($cnt['name'] ?? 'Counter ' . $cnt['id']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <button onclick="refreshQueue()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                                <i class="fas fa-sync-alt mr-2"></i>Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Counter Status (server-rendered so it shows on first load) -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Counter Status</h3>
                        <div id="countersStatus" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php
                            if (empty($queue_counters)) {
                                echo '<div class="text-center text-gray-500">No counters configured</div>';
                            } else {
                                foreach ($queue_counters as $cnt) {
                                    $st = $cnt['service_types'];
                                    if (is_string($st)) { $st = @json_decode($st, true) ?: []; }
                                    $stText = is_array($st) ? implode(', ', $st) : 'General';
                                    $cls = !empty($cnt['is_online']) ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                                    $badge = !empty($cnt['is_online']) ? 'bg-green-200 text-green-800">Online' : 'bg-red-200 text-red-800">Offline';
                                    $serving = !empty($cnt['current_customer_name']) ? 'Serving: <span class="font-bold">' . htmlspecialchars($cnt['current_customer_name']) . '</span>' : 'Available';
                                    echo '<div class="border rounded-lg p-4 ' . $cls . '"><div class="flex justify-between items-center mb-2"><h4 class="font-semibold">' . htmlspecialchars($cnt['name'] ?? 'Counter') . '</h4><span class="px-2 py-1 rounded text-xs ' . $badge . '</span></div><div class="text-sm text-gray-600 mb-2">Services: ' . htmlspecialchars($stText) . '</div><div class="text-sm">' . $serving . '</div></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Queue List (server-rendered so it shows on first load) -->
                    <div class="overflow-x-auto">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-sm text-gray-600">Showing <span id="queueRange"><?php echo $queue_total > 0 ? min($queue_offset + 1, $queue_total) . '-' . min($queue_offset + $queue_per_page, $queue_total) : 0; ?></span> of <span id="queueTotal"><?php echo $queue_total; ?></span> customers</p>
                            <nav id="queuePagination" class="flex gap-2 items-center">
                                <button type="button" onclick="goToQueuePage(1)" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50" title="First">«</button>
                                <button type="button" onclick="goToQueuePage(Math.max(1, (window.QUEUE_CURRENT_PAGE||1)-1))" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50">Prev</button>
                                <span class="text-sm text-gray-600">Page <span id="queuePageNum"><?php echo $queue_page; ?></span> of <span id="queueTotalPages"><?php echo $queue_total_pages; ?></span></span>
                                <button type="button" onclick="goToQueuePage(Math.min((window.QUEUE_TOTAL_PAGES||1), (window.QUEUE_CURRENT_PAGE||1)+1))" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50">Next</button>
                                <button type="button" onclick="goToQueuePage(window.QUEUE_TOTAL_PAGES||1)" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50" title="Last">»</button>
                            </nav>
                        </div>
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Queue No.</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Service</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Time</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="queueTable" class="divide-y divide-gray-200">
                                <?php
                                if (empty($queue_customers)) {
                                    echo '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500"><i class="fas fa-inbox text-4xl mb-2 block"></i>No customers in queue</td></tr>';
                                } else {
                                    $statusMap = ['waiting' => ['bg-yellow-100 text-yellow-800', 'Waiting'], 'serving' => ['bg-blue-100 text-blue-800', 'Serving'], 'completed' => ['bg-green-100 text-green-800', 'Completed'], 'cancelled' => ['bg-red-100 text-red-800', 'Cancelled']];
                                    foreach ($queue_customers as $c) {
                                        $sc = $statusMap[$c['status']] ?? ['bg-gray-100 text-gray-800', $c['status']];
                                        $created = strtotime($c['created_at']);
                                        $dateStr = date('M d, Y', $created);
                                        $timeStr = date('g:i A', $created);
                                        $actions = '';
                                        if ($c['status'] === 'waiting') $actions .= '<button onclick="callCustomer(' . (int)$c['id'] . ')" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition duration-200"><i class="fas fa-bullhorn mr-1"></i>Call</button> ';
                                        if ($c['status'] === 'serving') $actions .= '<button onclick="completeCustomer(' . (int)$c['id'] . ')" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition duration-200"><i class="fas fa-check mr-1"></i>Complete</button> ';
                                        if ($c['status'] !== 'completed' && $c['status'] !== 'cancelled') $actions .= '<button onclick="cancelCustomer(' . (int)$c['id'] . ')" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition duration-200"><i class="fas fa-times mr-1"></i>Cancel</button>';
                                        echo '<tr class="hover:bg-gray-50"><td class="px-4 py-3"><span class="queue-number text-lg font-bold">' . htmlspecialchars($c['queue_number']) . '</span></td><td class="px-4 py-3">' . htmlspecialchars($c['name']) . '</td><td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">' . htmlspecialchars($c['service_type']) . '</span></td><td class="px-4 py-3"><span class="px-3 py-1 rounded-full text-sm font-medium ' . $sc[0] . '">' . $sc[1] . '</span></td><td class="px-4 py-3 text-sm text-gray-500">' . $dateStr . '</td><td class="px-4 py-3 text-sm text-gray-500">' . $timeStr . '</td><td class="px-4 py-3"><div class="flex space-x-2">' . $actions . '</div></td></tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>

<?php
$queue_base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$queue_api_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $queue_base . '/api';
?>
<script>
    window.QUEUE_BASE = <?php echo json_encode($queue_base); ?>;
    window.QUEUE_API_URL = <?php echo json_encode($queue_api_url); ?>;
    window.QUEUE_CURRENT_PAGE = <?php echo (int)$queue_page; ?>;
    window.QUEUE_TOTAL_PAGES = <?php echo (int)$queue_total_pages; ?>;
</script>
<script src="js/main.js"></script>
<?php require_once 'footer_unified.php'; ?>