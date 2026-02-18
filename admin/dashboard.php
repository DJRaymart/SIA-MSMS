<?php
if (!defined('APP_ROOT')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$app_root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/check_admin_access.php';
require_once $app_root . '/config/db.php';
require_once $app_root . '/auth/admin_helper.php';

$adminInfo = AdminAuth::getAdminInfo();
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$baseS = $base === '' ? '/' : $base . '/';

$dateFilter = $_GET['date'] ?? 'all'; 
$moduleFilter = $_GET['module'] ?? 'all'; 

function safeCount($conn, $query) {
    if (!$conn) return 0;
    $r = @$conn->query($query);
    if (!$r) return 0;
    $row = $r->fetch_assoc();
    return ($row && isset($row['count'])) ? (int)$row['count'] : 0;
}

function getDateCondition($columnName, $filter) {
    switch ($filter) {
        case 'today':
            return "DATE($columnName) = CURDATE()";
        case 'week':
            return "$columnName >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        case 'month':
            return "$columnName >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        case 'all':
            return "1=1";
        default:
            return "DATE($columnName) = CURDATE()";
    }
}

$activities = [];

if ($moduleFilter === 'all' || $moduleFilter === 'sciLab') {
    
    $dateCond = getDateCondition('date', $dateFilter);
    $logsQuery = "SELECT 
        log_id as id,
        CONCAT(name, ' (', grade, '-', section, ')') as title,
        CONCAT('Logged attendance at ', TIME_FORMAT(time, '%h:%i %p')) as description,
        CONCAT(date, ' ', time) as activity_date,
        'sciLab' as module,
        'attendance' as type,
        'blue' as color
    FROM logs 
    WHERE $dateCond
    ORDER BY date DESC, time DESC 
    LIMIT 20";
    
    $result = @$conn->query($logsQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    $dateCond = getDateCondition('created_at', $dateFilter);
    $reservationsQuery = "SELECT 
        id,
        CONCAT(activity, ' - ', grade_section) as title,
        CONCAT('Reservation for ', student_count, ' students on ', DATE_FORMAT(usage_date, '%M %d, %Y at %h:%i %p'), ' - Status: ', UPPER(status)) as description,
        created_at as activity_date,
        'sciLab' as module,
        'reservation' as type,
        CASE 
            WHEN status = 'approved' THEN 'green'
            WHEN status = 'pending' THEN 'yellow'
            ELSE 'red'
        END as color
    FROM reservations 
    WHERE $dateCond
    ORDER BY created_at DESC 
    LIMIT 20";
    
    $result = @$conn->query($reservationsQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
}

if ($moduleFilter === 'all' || $moduleFilter === 'library') {
    
    $dateCond = getDateCondition('bt.borrow_date', $dateFilter);
    $borrowingQuery = "SELECT 
        bt.transaction_id as id,
        CONCAT(b.title, ' - ', u.full_name) as title,
        CONCAT('Book ', UPPER(bt.status), ' - Due: ', DATE_FORMAT(bt.due_date, '%M %d, %Y')) as description,
        bt.borrow_date as activity_date,
        'library' as module,
        'transaction' as type,
        CASE 
            WHEN bt.status = 'Returned' THEN 'green'
            WHEN bt.status = 'Overdue' THEN 'red'
            WHEN bt.status = 'Borrowed' THEN 'blue'
            ELSE 'yellow'
        END as color
    FROM borrowing_transaction bt
    JOIN book b ON bt.book_id = b.book_id
    JOIN tbl_users u ON bt.user_id = u.user_id
    WHERE $dateCond
    ORDER BY bt.borrow_date DESC 
    LIMIT 20";
    
    $result = @$conn->query($borrowingQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    $dateCond = getDateCondition('login_at', $dateFilter);
    $logBookQuery = "SELECT 
        ID as id,
        CONCAT(full_name, ' (', grade_section, ')') as title,
        CONCAT('Library login at ', TIME_FORMAT(login_at, '%h:%i %p')) as description,
        login_at as activity_date,
        'library' as module,
        'login' as type,
        'cyan' as color
    FROM log_book 
    WHERE $dateCond
    ORDER BY login_at DESC 
    LIMIT 20";
    
    $result = @$conn->query($logBookQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
}

if ($moduleFilter === 'all' || $moduleFilter === 'avr') {
    
    $dateCond = getDateCondition('DateBorrowed', $dateFilter);
    $avrBorrowedQuery = "SELECT 
        ID as id,
        CONCAT(Item, ' - ', Name) as title,
        CONCAT('Borrowed ', Quantity, ' item(s) - Due: ', DATE_FORMAT(DueDate, '%M %d, %Y'), ' - Status: ', Status) as description,
        DateBorrowed as activity_date,
        'avr' as module,
        'borrowed' as type,
        CASE 
            WHEN Status = 'Returned' THEN 'green'
            WHEN Status = 'Overdue' THEN 'red'
            ELSE 'pink'
        END as color
    FROM avr_borrowed 
    WHERE $dateCond
    ORDER BY DateBorrowed DESC 
    LIMIT 20";
    
    $result = @$conn->query($avrBorrowedQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    $dateCond = getDateCondition('Date', $dateFilter);
    $avrReservationQuery = "SELECT 
        ID as id,
        CONCAT(Name, ' - ', Department) as title,
        CONCAT('Reservation on ', DATE_FORMAT(Date, '%M %d, %Y'), ' at ', TIME_FORMAT(Time, '%h:%i %p'), ' - Purpose: ', COALESCE(Purpose, 'N/A')) as description,
        CONCAT(Date, ' ', Time) as activity_date,
        'avr' as module,
        'reservation' as type,
        'rose' as color
    FROM avr_reservation 
    WHERE $dateCond
    ORDER BY Date DESC, Time DESC 
    LIMIT 20";
    
    $result = @$conn->query($avrReservationQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    $dateCond = getDateCondition('Date', $dateFilter);
    $avrAttendanceQuery = "SELECT 
        ID as id,
        CONCAT(Name, ' - ', GradeSection) as title,
        CONCAT('Attendance logged for ', NoOfStudent, ' student(s)') as description,
        Date as activity_date,
        'avr' as module,
        'attendance' as type,
        'orange' as color
    FROM log_attendance 
    WHERE $dateCond
    ORDER BY Date DESC 
    LIMIT 20";
    
    $result = @$conn->query($avrAttendanceQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
}

if ($moduleFilter === 'all' || $moduleFilter === 'queue') {
    
    $dateCond = getDateCondition('created_at', $dateFilter);
    $queueQuery = "SELECT 
        id,
        CONCAT('Queue #', queue_number, ' - ', name) as title,
        CONCAT('Service: ', service_type, ' - Status: ', UPPER(status)) as description,
        created_at as activity_date,
        'queue' as module,
        'queue' as type,
        CASE 
            WHEN status = 'completed' THEN 'green'
            WHEN status = 'serving' THEN 'blue'
            WHEN status = 'waiting' THEN 'yellow'
            ELSE 'red'
        END as color
    FROM customers 
    WHERE $dateCond
    ORDER BY created_at DESC 
    LIMIT 20";
    
    $result = @$conn->query($queueQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
}

if ($moduleFilter === 'all' || $moduleFilter === 'clinic') {
    
    $dateCond = getDateCondition('date', $dateFilter);
    $clinicLogQuery = "SELECT 
        id,
        CONCAT(name, ' (', grade_section, ')') as title,
        CONCAT('Clinic visit at ', TIME_FORMAT(time, '%h:%i %p')) as description,
        CONCAT(date, ' ', time) as activity_date,
        'clinic' as module,
        'visit' as type,
        'teal' as color
    FROM clinic_log 
    WHERE $dateCond
    ORDER BY date DESC, time DESC 
    LIMIT 20";
    
    $result = @$conn->query($clinicLogQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    $dateCondCr = getDateCondition('date_created', $dateFilter);
    $clinicRecordsQuery = "SELECT 
        id,
        CONCAT(name, ' - ', grade_section) as title,
        CONCAT(LEFT(complaint, 60), IF(LENGTH(complaint) > 60, '...', '')) as description,
        COALESCE(CONCAT(COALESCE(date, CURDATE()), ' ', COALESCE(TIME(date_created), '00:00:00')), date_created) as activity_date,
        'clinic' as module,
        'record' as type,
        'cyan' as color
    FROM clinic_records 
    WHERE $dateCondCr
    ORDER BY date_created DESC 
    LIMIT 20";
    
    $result = @$conn->query($clinicRecordsQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
}

if ($moduleFilter === 'all' || $moduleFilter === 'ictOffice') {
    $dateCond = getDateCondition('l.time_in', $dateFilter);
    $ictLogsQuery = "SELECT 
        l.log_id as id,
        CONCAT(u.fullname, ' (', COALESCE(u.grade_section, 'Staff'), ')') as title,
        CONCAT('ICT login at ', TIME_FORMAT(l.time_in, '%h:%i %p')) as description,
        l.time_in as activity_date,
        'ictOffice' as module,
        'login' as type,
        'amber' as color
    FROM ict_logs l
    JOIN ict_users u ON l.user_id = u.id
    WHERE $dateCond
    ORDER BY l.time_in DESC 
    LIMIT 20";
    
    $result = @$conn->query($ictLogsQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
    }
}

usort($activities, function($a, $b) {
    return strtotime($b['activity_date']) - strtotime($a['activity_date']);
});

$activitiesFull = $activities;
$totalActivities = count($activitiesFull);

$stats = [
    'sciLab' => [
        'logs_total' => safeCount($conn, "SELECT COUNT(*) as count FROM logs"),
        'reservations_total' => safeCount($conn, "SELECT COUNT(*) as count FROM reservations"),
        'logs_today' => safeCount($conn, "SELECT COUNT(*) as count FROM logs WHERE DATE(date) = CURDATE()"),
        'logs_week' => safeCount($conn, "SELECT COUNT(*) as count FROM logs WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"),
        'pending_reservations' => safeCount($conn, "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'"),
        'approved_reservations' => safeCount($conn, "SELECT COUNT(*) as count FROM reservations WHERE status = 'approved'"),
        'total_inventory' => safeCount($conn, "SELECT COUNT(*) as count FROM inventory"),
        'total_reservations' => safeCount($conn, "SELECT COUNT(*) as count FROM reservations"),
    ],
    'library' => [
        'borrowed_books' => safeCount($conn, "SELECT COUNT(*) as count FROM borrowing_transaction WHERE status = 'Borrowed'"),
        'overdue_books' => safeCount($conn, "SELECT COUNT(*) as count FROM borrowing_transaction WHERE status = 'Overdue'"),
        'total_books' => safeCount($conn, "SELECT COUNT(*) as count FROM book"),
        'available_books' => safeCount($conn, "SELECT COUNT(*) as count FROM book WHERE status = 'Available'"),
        'transactions_total' => safeCount($conn, "SELECT COUNT(*) as count FROM borrowing_transaction"),
        'logins_total' => safeCount($conn, "SELECT COUNT(*) as count FROM log_book"),
        'logins_today' => safeCount($conn, "SELECT COUNT(*) as count FROM log_book WHERE DATE(login_at) = CURDATE()"),
        'total_users' => safeCount($conn, "SELECT COUNT(*) as count FROM tbl_users WHERE status = 'Active'"),
    ],
    'avr' => [
        'active_borrowed' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_borrowed WHERE Status = 'Active'"),
        'overdue_borrowed' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_borrowed WHERE Status = 'Overdue'"),
        'upcoming_reservations' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_reservation WHERE Date >= CURDATE()"),
        'total_equipment' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_inventory"),
        'borrowed_total' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_borrowed"),
        'reservations_total' => safeCount($conn, "SELECT COUNT(*) as count FROM avr_reservation"),
        'attendance_total' => safeCount($conn, "SELECT COUNT(*) as count FROM log_attendance"),
        'attendance_today' => safeCount($conn, "SELECT COUNT(*) as count FROM log_attendance WHERE Date = CURDATE()"),
    ],
    'queue' => [
        'waiting' => safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE status = 'waiting'"),
        'serving' => safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE status = 'serving'"),
        'total' => safeCount($conn, "SELECT COUNT(*) as count FROM customers"),
        'today' => safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE DATE(created_at) = CURDATE()"),
        'completed_today' => safeCount($conn, "SELECT COUNT(*) as count FROM customers WHERE status = 'completed' AND DATE(created_at) = CURDATE()"),
        'total_counters' => safeCount($conn, "SELECT COUNT(*) as count FROM counters"),
        'online_counters' => safeCount($conn, "SELECT COUNT(*) as count FROM counters WHERE is_online = 1"),
    ],
    'clinic' => [
        'visits_total' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_log"),
        'visits_today' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_log WHERE DATE(date) = CURDATE()"),
        'visits_week' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_log WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"),
        'total_records' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_records"),
        'records_today' => safeCount($conn, "SELECT COUNT(*) as count FROM clinic_records WHERE DATE(date_created) = CURDATE()"),
        'total_students' => safeCount($conn, "SELECT COUNT(DISTINCT student_id) as count FROM clinic_records"),
    ],
    'ictOffice' => [
        'total_inventory' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_inventory"),
        'total_categories' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_categories"),
        'logins_total' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_logs"),
        'logins_today' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_logs WHERE DATE(time_in) = CURDATE()"),
        'total_users' => safeCount($conn, "SELECT COUNT(*) as count FROM ict_users"),
        'connected' => true,
    ],
];

require_once 'header_unified.php';
?>

<div class="max-w-7xl mx-auto px-6">

    <!-- System Overview -->
    <?php
    $systemTotal = ($stats['sciLab']['logs_total'] ?? 0) + ($stats['sciLab']['reservations_total'] ?? 0)
        + ($stats['library']['transactions_total'] ?? 0) + ($stats['library']['logins_total'] ?? 0)
        + ($stats['avr']['borrowed_total'] ?? 0) + ($stats['avr']['reservations_total'] ?? 0) + ($stats['avr']['attendance_total'] ?? 0)
        + ($stats['queue']['total'] ?? 0)
        + ($stats['clinic']['visits_total'] ?? 0) + ($stats['clinic']['total_records'] ?? 0)
        + ($stats['ictOffice']['logins_total'] ?? 0);
    $systemAlerts = $stats['sciLab']['pending_reservations'] + $stats['library']['overdue_books'] + $stats['avr']['overdue_borrowed'];
    ?>
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-6 mb-8 border border-slate-700 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
                <h2 class="text-xl font-black text-white uppercase tracking-wide mb-1">System Overview</h2>
                <p class="text-slate-400 text-sm">MSMS modules status • <?php echo date('l, F j, Y \a\t g:i A'); ?></p>
            </div>
            <div class="flex flex-wrap gap-6">
                <div class="text-center">
                    <p class="text-3xl font-black text-blue-400"><?php echo $systemTotal; ?></p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Total Activities (All Time)</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-black <?php echo $systemAlerts > 0 ? 'text-amber-400' : 'text-green-400'; ?>"><?php echo $systemAlerts; ?></p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Pending / Overdue</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-black text-emerald-400">6</p>
                    <p class="text-xs text-slate-500 uppercase tracking-wider">Active Modules</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <!-- Science Lab Stats -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-blue-800 uppercase tracking-wide">Science Lab</h3>
                <a href="<?php echo htmlspecialchars($baseS); ?>sciLab/admin/" class="text-blue-600 hover:text-blue-800" title="Go to Science Lab">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-blue-700">Today's Logs:</span>
                    <span class="font-black text-blue-900"><?php echo $stats['sciLab']['logs_today']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-blue-700">Logs (7 days):</span>
                    <span class="font-black text-blue-900"><?php echo $stats['sciLab']['logs_week']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-blue-700">Pending Reservations:</span>
                    <span class="font-black text-blue-900"><?php echo $stats['sciLab']['pending_reservations']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-blue-700">Approved:</span>
                    <span class="font-black text-blue-900"><?php echo $stats['sciLab']['approved_reservations']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-blue-700">Total Inventory:</span>
                    <span class="font-black text-blue-900"><?php echo $stats['sciLab']['total_inventory']; ?></span>
                </div>
            </div>
        </div>

        <!-- Library Stats -->
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border-2 border-emerald-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-wide">Library</h3>
                <a href="<?php echo htmlspecialchars($baseS); ?>library/" class="text-emerald-600 hover:text-emerald-800" title="Go to Library">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-emerald-700">Borrowed:</span>
                    <span class="font-black text-emerald-900"><?php echo $stats['library']['borrowed_books']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-emerald-700">Overdue:</span>
                    <span class="font-black <?php echo $stats['library']['overdue_books'] > 0 ? 'text-red-600' : 'text-emerald-900'; ?>"><?php echo $stats['library']['overdue_books']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-emerald-700">Total Books:</span>
                    <span class="font-black text-emerald-900"><?php echo $stats['library']['total_books']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-emerald-700">Available:</span>
                    <span class="font-black text-emerald-900"><?php echo $stats['library']['available_books']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-emerald-700">Logins Today:</span>
                    <span class="font-black text-emerald-900"><?php echo $stats['library']['logins_today']; ?></span>
                </div>
            </div>
        </div>

        <!-- AVR Stats -->
        <div class="bg-gradient-to-br from-pink-50 to-pink-100 border-2 border-pink-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-pink-800 uppercase tracking-wide">AVR</h3>
                <a href="<?php echo htmlspecialchars($baseS); ?>avr/" class="text-pink-600 hover:text-pink-800" title="Go to AVR">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-pink-700">Active Borrowed:</span>
                    <span class="font-black text-pink-900"><?php echo $stats['avr']['active_borrowed']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-pink-700">Overdue:</span>
                    <span class="font-black <?php echo $stats['avr']['overdue_borrowed'] > 0 ? 'text-red-600' : 'text-pink-900'; ?>"><?php echo $stats['avr']['overdue_borrowed']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-pink-700">Upcoming Reservations:</span>
                    <span class="font-black text-pink-900"><?php echo $stats['avr']['upcoming_reservations']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-pink-700">Total Equipment:</span>
                    <span class="font-black text-pink-900"><?php echo $stats['avr']['total_equipment']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-pink-700">Attendance Today:</span>
                    <span class="font-black text-pink-900"><?php echo $stats['avr']['attendance_today']; ?></span>
                </div>
            </div>
        </div>

        <!-- Queue Stats -->
        <div class="bg-gradient-to-br from-violet-50 to-violet-100 border-2 border-violet-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-violet-800 uppercase tracking-wide">Queue</h3>
                <a href="<?php echo htmlspecialchars($baseS); ?>queue/" class="text-violet-600 hover:text-violet-800" title="Go to Queue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-violet-700">Waiting:</span>
                    <span class="font-black text-violet-900"><?php echo $stats['queue']['waiting']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-violet-700">Serving:</span>
                    <span class="font-black text-violet-900"><?php echo $stats['queue']['serving']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-violet-700">Completed Today:</span>
                    <span class="font-black text-violet-900"><?php echo $stats['queue']['completed_today']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-violet-700">Total Today:</span>
                    <span class="font-black text-violet-900"><?php echo $stats['queue']['today']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-violet-700">Online Counters:</span>
                    <span class="font-black text-violet-900"><?php echo $stats['queue']['online_counters']; ?>/<?php echo $stats['queue']['total_counters']; ?></span>
                </div>
            </div>
        </div>

        <!-- Clinic Stats -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 border-2 border-teal-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-teal-800 uppercase tracking-wide">Clinic</h3>
                <a href="<?php echo htmlspecialchars($baseS); ?>clinic/" class="text-teal-600 hover:text-teal-800" title="Go to Clinic">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-teal-700">Visits Today:</span>
                    <span class="font-black text-teal-900"><?php echo $stats['clinic']['visits_today']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-teal-700">Visits (7 days):</span>
                    <span class="font-black text-teal-900"><?php echo $stats['clinic']['visits_week']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-teal-700">Records Today:</span>
                    <span class="font-black text-teal-900"><?php echo $stats['clinic']['records_today']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-teal-700">Total Records:</span>
                    <span class="font-black text-teal-900"><?php echo $stats['clinic']['total_records']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-teal-700">Unique Students:</span>
                    <span class="font-black text-teal-900"><?php echo $stats['clinic']['total_students']; ?></span>
                </div>
            </div>
        </div>

        <!-- ICT Office Stats -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 border-2 border-amber-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wide">ICT Office</h3>
                <a href="<?php echo htmlspecialchars($baseS); ?>ictOffice/" class="text-amber-600 hover:text-amber-800" title="Go to ICT Office">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
            <?php if ($stats['ictOffice']['connected']): ?>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-amber-700">Total Inventory:</span>
                    <span class="font-black text-amber-900"><?php echo $stats['ictOffice']['total_inventory']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-amber-700">Categories:</span>
                    <span class="font-black text-amber-900"><?php echo $stats['ictOffice']['total_categories']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-amber-700">Logins Today:</span>
                    <span class="font-black text-amber-900"><?php echo $stats['ictOffice']['logins_today']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-amber-700">Total Users:</span>
                    <span class="font-black text-amber-900"><?php echo $stats['ictOffice']['total_users']; ?></span>
                </div>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <p class="text-xs text-amber-700">Inventory & log management for ICT equipment.</p>
                <a href="<?php echo htmlspecialchars($baseS); ?>ictOffice/portal.php" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-200 hover:bg-amber-300 text-amber-900 rounded-lg text-sm font-bold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    Open ICT Office
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Module Quick Links -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="<?php echo htmlspecialchars($baseS); ?>account/settings.php" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><circle cx="12" cy="12" r="3" /></svg> Account Settings
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>admin/accounts.php" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> All Accounts
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>sciLab/admin/" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5" /></svg> Science Lab
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>library/" class="px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25v14.25" /></svg> Library
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>avr/" class="px-4 py-2 bg-pink-100 hover:bg-pink-200 text-pink-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72" /></svg> AVR
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>queue/" class="px-4 py-2 bg-violet-100 hover:bg-violet-200 text-violet-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 5.25h16.5m-16.5 4.5h16.5" /></svg> Queue
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>clinic/" class="px-4 py-2 bg-teal-100 hover:bg-teal-200 text-teal-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347" /></svg> Clinic
        </a>
        <a href="<?php echo htmlspecialchars($baseS); ?>ictOffice/portal.php" class="px-4 py-2 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192" /></svg> ICT Office
        </a>
    </div>

    <!-- Filters & Search -->
    <form method="get" action="" class="bg-white rounded-xl shadow-lg border border-slate-200 p-6 mb-8" id="dashboardFiltersForm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">Search (realtime)</label>
                <input type="text" id="activitySearch" autocomplete="off"
                    placeholder="Search activities by title, description, or module..." 
                    class="w-full px-4 py-2 border-2 border-slate-300 rounded-lg focus:border-blue-600 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">Date Range</label>
                <select name="date" class="px-4 py-2 border-2 border-slate-300 rounded-lg focus:border-blue-600 outline-none" onchange="this.form.submit()">
                    <option value="all" <?php echo $dateFilter === 'all' ? 'selected' : ''; ?>>All Time</option>
                    <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo $dateFilter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" <?php echo $dateFilter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">Module</label>
                <select name="module" class="px-4 py-2 border-2 border-slate-300 rounded-lg focus:border-blue-600 outline-none" onchange="this.form.submit()">
                    <option value="all" <?php echo $moduleFilter === 'all' ? 'selected' : ''; ?>>All Modules</option>
                    <option value="sciLab" <?php echo $moduleFilter === 'sciLab' ? 'selected' : ''; ?>>Science Lab</option>
                    <option value="library" <?php echo $moduleFilter === 'library' ? 'selected' : ''; ?>>Library</option>
                    <option value="avr" <?php echo $moduleFilter === 'avr' ? 'selected' : ''; ?>>AVR</option>
                    <option value="queue" <?php echo $moduleFilter === 'queue' ? 'selected' : ''; ?>>Queue</option>
                    <option value="clinic" <?php echo $moduleFilter === 'clinic' ? 'selected' : ''; ?>>Clinic</option>
                    <option value="ictOffice" <?php echo $moduleFilter === 'ictOffice' ? 'selected' : ''; ?>>ICT Office</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- Activities Timeline -->
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-6">
        <h2 class="text-2xl font-black text-slate-900 mb-6">Recent Activities</h2>
        
        <?php if (empty($activitiesFull) && $totalActivities == 0): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-slate-600 font-semibold">No activities found for the selected filters</p>
            </div>
        <?php else: ?>
            <?php
            $colorMap = [
                'blue' => 'border-l-blue-500',
                'green' => 'border-l-green-500',
                'yellow' => 'border-l-yellow-500',
                'red' => 'border-l-red-500',
                'cyan' => 'border-l-cyan-500',
                'pink' => 'border-l-pink-500',
                'rose' => 'border-l-rose-500',
                'orange' => 'border-l-orange-500',
                'violet' => 'border-l-violet-500',
                'teal' => 'border-l-teal-500',
                'amber' => 'border-l-amber-500',
            ];
            ?>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Time</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Module</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Description</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200" id="activitiesTableBody">
                        <?php foreach ($activitiesFull as $activity): 
                            $activityDate = date('M d, Y', strtotime($activity['activity_date']));
                            $activityTime = date('h:i A', strtotime($activity['activity_date']));
                            $rowBorder = $colorMap[$activity['color']] ?? 'border-l-slate-500';
                            $searchableText = strtolower(($activity['title'] ?? '') . ' ' . ($activity['description'] ?? '') . ' ' . ($activity['module'] ?? ''));
                        ?>
                        <tr class="activity-row hover:bg-slate-50 transition-colors border-l-4 <?php echo $rowBorder; ?>" data-search="<?php echo htmlspecialchars($searchableText); ?>">
                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap"><?php echo $activityDate; ?></td>
                            <td class="px-4 py-3 text-sm text-slate-700 whitespace-nowrap"><?php echo $activityTime; ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wide px-2 py-1 rounded bg-slate-100"><?php echo ucfirst($activity['module']); ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-slate-900"><?php echo htmlspecialchars($activity['title']); ?></td>
                            <td class="px-4 py-3 text-sm text-slate-600"><?php echo htmlspecialchars($activity['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination & Count -->
            <div id="activityPagination" class="mt-6 pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div id="activityCount" class="text-sm text-slate-600">Showing <strong>1-<?php echo min(10, count($activitiesFull)); ?></strong> of <strong><?php echo count($activitiesFull); ?></strong></div>
                <div id="activityPaginationControls" class="flex items-center gap-2 flex-shrink-0">
                    <button type="button" id="activityPagePrev" class="px-3 py-1.5 bg-slate-300 text-slate-500 rounded font-semibold text-sm cursor-not-allowed" disabled>← Back</button>
                    <span id="activityPageInfo" class="px-3 py-1.5 text-slate-700 font-semibold text-sm">1 / <?php echo max(1, ceil(count($activitiesFull) / 10)); ?></span>
                    <button type="button" id="activityPageNext" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold text-sm">Next →</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
(function() {
    var PER_PAGE = 10;
    var searchInput = document.getElementById('activitySearch');
    var rows = document.querySelectorAll('.activity-row');
    var countEl = document.getElementById('activityCount');
    var pageInfoEl = document.getElementById('activityPageInfo');
    var prevBtn = document.getElementById('activityPagePrev');
    var nextBtn = document.getElementById('activityPageNext');
    var currentPage = 1;

    function getFilteredRows() {
        var q = (searchInput && searchInput.value || '').trim().toLowerCase();
        var filtered = [];
        for (var i = 0; i < rows.length; i++) {
            var text = rows[i].getAttribute('data-search') || '';
            if (!q || text.indexOf(q) !== -1) {
                filtered.push(rows[i]);
            }
        }
        return filtered;
    }

    function showPage(filtered, page) {
        var total = filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
        page = Math.max(1, Math.min(page, totalPages));
        currentPage = page;

        var start = (page - 1) * PER_PAGE;
        var end = Math.min(start + PER_PAGE, total);

        for (var i = 0; i < rows.length; i++) {
            rows[i].style.display = 'none';
        }
        for (var j = start; j < end; j++) {
            filtered[j].style.display = '';
        }

        if (countEl) {
            var txt = total === 0 ? 'No activities' : 'Showing <strong>' + (start + 1) + '-' + end + '</strong> of <strong>' + total + '</strong>';
            countEl.innerHTML = txt;
        }
        if (pageInfoEl) {
            pageInfoEl.textContent = page + ' / ' + totalPages;
        }
        if (prevBtn) {
            prevBtn.disabled = page <= 1;
            prevBtn.className = page <= 1 ? 'px-3 py-1.5 bg-slate-300 text-slate-500 rounded font-semibold text-sm cursor-not-allowed' : 'px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold text-sm cursor-pointer';
        }
        if (nextBtn) {
            nextBtn.disabled = page >= totalPages;
            nextBtn.className = page >= totalPages ? 'px-3 py-1.5 bg-slate-300 text-slate-500 rounded font-semibold text-sm cursor-not-allowed' : 'px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold text-sm cursor-pointer';
        }
    }

    function update() {
        var filtered = getFilteredRows();
        currentPage = 1;
        showPage(filtered, 1);
    }

    if (searchInput) {
        searchInput.addEventListener('input', update);
        searchInput.addEventListener('keyup', update);
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            var filtered = getFilteredRows();
            showPage(filtered, currentPage - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            var filtered = getFilteredRows();
            showPage(filtered, currentPage + 1);
        });
    }

    update();
})();
</script>

<?php require_once 'footer_unified.php'; ?>
