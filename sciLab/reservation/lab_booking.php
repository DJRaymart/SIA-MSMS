<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/security.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/admin_helper.php';

include "../config/db.php";

if (isAdminLoggedIn()) {
    $adminInfo = getAdminInfo();
    $_SESSION['booking_verified_student'] = [
        'student_id' => 'ADMIN',
        'name' => $adminInfo['name'] ?? 'Staff / Admin',
        'grade_section' => '—'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $verify_error = 'Invalid session token. Please refresh and try again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($verify_error) && isset($_POST['action']) && $_POST['action'] === 'verify_student') {
    $id = trim($_POST['student_id'] ?? '');
    if (!empty($id)) {
        $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
        $stmt->bind_param("ss", $id, $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $_SESSION['booking_verified_student'] = ['student_id' => $row['student_id'], 'name' => $row['name'], 'grade_section' => $row['grade'] . ' - ' . $row['section']];
            header('Location: lab_booking.php?step=1');
            exit;
        }
    }
    $verify_error = 'Student not found or account not yet approved.';
}
if (!empty($_GET['clear_booking'])) {
    unset($_SESSION['booking_verified_student']);
    header('Location: lab_booking.php');
    exit;
}
$verified = $_SESSION['booking_verified_student'] ?? null;
if (!$verified) {
    $verify_title = 'Lab Equipment Booking';
    $verify_subtitle = 'Enter your Student ID or RFID to verify your account before booking.';
    $verify_back_url = ((defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/') . 'sciLab/portal.php';
    $verify_back_label = 'Back to Science Lab';
    $verify_accent = 'blue';
    $verify_error = $verify_error ?? null;
    $verify_standalone = true;
    require dirname(__DIR__, 2) . '/partials/verify_student_unified.php';
    exit;
}
$sciLabBase = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/sciLab' : '/sciLab';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Equipment Booking - MSMS</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($sciLabBase); ?>/assets/images/32x32.png">
    <link rel="stylesheet" href="<?= htmlspecialchars($sciLabBase); ?>/assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
<?php
date_default_timezone_set("Asia/Manila");

if (!isset($_SESSION['reservation_ref'])) {
    $_SESSION['reservation_ref'] = 'REF-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
}

$inventory_items = [];
$query = "SELECT item_id, item_name, quantity FROM inventory ORDER BY item_name ASC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inventory_items[] = $row;
    }
}

if (!isset($_GET['step']) && !isset($_GET['status'])) {
    $_SESSION['item_queue'] = [];
}

if (!isset($_SESSION['item_queue'])) {
    $_SESSION['item_queue'] = [];
}

if (isset($_GET['remove_item'])) {
    $index = $_GET['remove_item'];
    unset($_SESSION['item_queue'][$index]);
    $_SESSION['item_queue'] = array_values($_SESSION['item_queue']);

    $params = $_GET;
    unset($params['remove_item']);
    header("Location: ?" . http_build_query($params));
    exit;
}

$current_step = $_GET['step'] ?? '1';
$errors = [];

$pre_activity = $_GET['pre_act'] ?? '';
$pre_date = $_GET['pre_date'] ?? '';
$pre_grade = $_GET['pre_grade'] ?? ($verified['grade_section'] ?? '');
$pre_students = $_GET['pre_students'] ?? '';
$pre_booked = $_GET['pre_booked'] ?? ($verified['name'] ?? '');
$pre_noted = $_GET['pre_noted'] ?? ($verified['name'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($verify_error) && isset($_POST['action']) && $_POST['action'] === 'go_to_step2') {

    if (empty($_POST['activity_title'])) $errors[] = "Activity title is required.";

    $student_count = intval($_POST['student_count']);
    if (empty($_POST['student_count'])) {
        $errors[] = "Student count is required.";
    } elseif ($student_count <= 0) {
        $errors[] = "Student count must be greater than 0.";
    }

    $usage_time = strtotime($_POST['usage_datetime']);
    if ($usage_time === false || $usage_time < (time() - 60)) {

        $errors[] = "Booking date and time must be in the future.";
    }

    if (empty($errors)) {
        $params = http_build_query([
            'step' => '2',
            'pre_act' => $_POST['activity_title'],
            'pre_date' => $_POST['usage_datetime'],
            'pre_grade' => $_POST['grade_section'],
            'pre_students' => $student_count,
            'pre_booked' => $_POST['booked_by'],
            'pre_noted' => $_POST['noted_by']
        ]);
        header("Location: ?" . $params);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($verify_error) && isset($_POST['action']) && $_POST['action'] === 'add_item') {

    $selected_id = intval($_POST['item_id']);
    $req_qty = intval($_POST['quantity']);

    $item_exists = false;
    foreach ($inventory_items as $inv) {
        if ($inv['item_id'] == $selected_id) {
            $item_exists = true;
            break;
        }
    }

    if (!$item_exists) {
        $params = $_GET;
        $params['alert_type'] = 'danger';
        $params['alert_title'] = 'Invalid Item';
        $params['alert_msg'] = urlencode('Selected item does not exist.');
        header("Location: ?" . http_build_query($params));
        exit;
    }

    if ($req_qty <= 0) {
        $params = $_GET;
        $params['alert_type'] = 'danger';
        $params['alert_title'] = 'Invalid Quantity';
        $params['alert_msg'] = urlencode("Quantity must be at least 1.");
        header("Location: ?" . http_build_query($params));
        exit;
    }

    $MAX_PER_ITEM = 50;
    if ($req_qty > $MAX_PER_ITEM) {
        $params = $_GET;
        $params['alert_type'] = 'warning';
        $params['alert_title'] = 'Quantity Limit';
        $params['alert_msg'] = urlencode("Maximum allowed per item is $MAX_PER_ITEM.");
        header("Location: ?" . http_build_query($params));
        exit;
    }

    $display_name = "Unknown Item";
    $stock_available = 0;

    foreach ($inventory_items as $inv) {
        if ($inv['item_id'] == $selected_id) {
            $display_name = $inv['item_name'];
            $stock_available = intval($inv['quantity']);
            break;
        }
    }

    $qty_in_queue = 0;
    foreach ($_SESSION['item_queue'] as $queued_item) {
        if ($queued_item['id'] == $selected_id) {
            $qty_in_queue += $queued_item['qty'];
        }
    }

    if ($stock_available <= 0) {
        $params = $_GET;
        $params['alert_type'] = 'danger';
        $params['alert_title'] = 'Out of Stock';
        $params['alert_msg'] = urlencode("This item is currently out of stock.");
        header("Location: ?" . http_build_query($params));
        exit;
    }

    if (($qty_in_queue + $req_qty) > $stock_available) {
        $params = $_GET;
        $params['alert_type'] = 'warning';
        $params['alert_title'] = 'Limit Reached';
        $params['alert_msg'] = urlencode("Cannot add. Total requested exceeds available stock ($stock_available).");
        header("Location: ?" . http_build_query($params));
        exit;
    }

    $found = false;
    foreach ($_SESSION['item_queue'] as &$queued_item) {
        if ($queued_item['id'] == $selected_id) {
            $queued_item['qty'] += $req_qty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['item_queue'][] = [
            'id' => $selected_id,
            'name' => strtoupper($display_name),
            'qty' => $req_qty
        ];
    }

    header("Location: ?" . http_build_query($_GET));
    exit;
}
?>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-20px) rotate(5deg);
        }
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #3b82f6;
    }
</style>

<div class="fixed inset-0 -z-30 bg-gradient-to-br from-slate-50 via-blue-100 to-indigo-100"></div>

<div class="fixed inset-0 -z-30">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-blue-100 to-indigo-100"></div>
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(at_0%_0%,rgba(59,130,246,0.5)_0,transparent_50%),radial-gradient(at_50%_0%,rgba(139,92,246,0.5)_0,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-5"></div>
</div>

<div class="fixed inset-0 w-full h-full pointer-events-none">
    <img src="../assets/images/beaker.png" class="floating-object w-24 blur-[1px] opacity-20" style="top:15%; right:15%; animation-duration:12s;">
    <img src="../assets/images/microscope.png" class="floating-object w-32 blur-[1px] opacity-10" style="bottom:10%; left:10%; animation-duration:15s;">
    <img src="../assets/images/atom.png" class="floating-object w-16 opacity-20" style="top:20%; left:20%; animation-duration:8s;">
    <img src="../assets/images/petridish.png" class="floating-object w-16 opacity-15" style="bottom:25%; right:20%; animation-duration:10s;">
</div>

<div class="fixed inset-0 pointer-events-none z-50 overflow-hidden opacity-[0.03]">
    <div class="w-full h-1 bg-blue-500 shadow-[0_0_15px_blue] animate-scanline"></div>
</div>

<div class="relative min-h-screen w-full flex flex-col items-center justify-center py-12">
    <div class="relative z-10 w-full max-w-4xl px-6 pt-1">

        <div class="text-center mb-6">
            <span class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em]">Step <?= $current_step ?> of 2</span>
            <h1 class="text-6xl font-black tracking-tighter text-slate-900 leading-none">Lab Equipment <span class="text-blue-600 italic">Booking</span></h1>
        </div>

        <div class="relative p-[2px] bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-600 rounded-[2.5rem] shadow-2xl">
            <div class="bg-white/90 backdrop-blur-xl rounded-[2.4rem] p-9">

                <?php if ($current_step == '1'): ?>
                    <div class="mb-4 flex justify-between items-center bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                        <span class="text-xs font-bold text-slate-700"><?= htmlspecialchars($verified['name']) ?> (<?= htmlspecialchars($verified['grade_section']) ?>)</span>
                        <a href="?clear_booking=1" class="text-[10px] font-black text-blue-600 hover:underline uppercase">Use different student</a>
                    </div>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="go_to_step2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Activity</label>
                                <input type="text" name="activity_title" required value="<?= htmlspecialchars($pre_activity) ?>" class="w-full bg-slate-100/50 border-2 border-slate-100 rounded-xl px-5 py-3 text-sm transition-all uppercase outline-none focus:border-blue-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Date & Time</label>
                                <input type="datetime-local" name="usage_datetime" required value="<?= htmlspecialchars($pre_date) ?>" class="w-full bg-slate-100/50 border-2 border-slate-100 rounded-xl px-5 py-3 text-sm outline-none focus:border-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Grade & Section</label>
                                <input type="text" name="grade_section" required value="<?= htmlspecialchars($pre_grade) ?>" class="w-full bg-slate-100/50 border-2 border-slate-100 rounded-xl px-5 py-3 text-sm uppercase outline-none focus:border-blue-500">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Students</label>
                                <input type="number" name="student_count" required min="1" value="<?= htmlspecialchars($pre_students) ?>" class="w-full bg-slate-100/50 border-2 border-slate-100 rounded-xl px-5 py-3 text-sm outline-none focus:border-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-slate-400 tracking-widest ml-2">Booked By</label>
                                <input type="text" name="booked_by" required value="<?= htmlspecialchars($pre_booked) ?>" class="w-full border-b-2 border-slate-200 bg-transparent px-2 py-2 text-sm uppercase outline-none focus:border-blue-600 transition-all font-semibold">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-slate-400 tracking-widest ml-2">Noted By</label>
                                <input type="text" name="noted_by" required value="<?= htmlspecialchars($pre_noted) ?>" class="w-full border-b-2 border-slate-200 bg-transparent px-2 py-2 text-sm uppercase outline-none focus:border-blue-600 transition-all font-semibold">
                            </div>
                        </div>

                        <!-- Two buttons side by side -->
                        <div class="grid grid-cols-2 gap-4">
                            <?php $base = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/'; $clearExitUrl = $base . 'auth/clear_exit.php?redirect=' . urlencode($base . 'sciLab/portal.php'); ?>
                        <a href="<?= htmlspecialchars($clearExitUrl) ?>" class="bg-slate-200 text-slate-700 font-black py-5 rounded-2xl uppercase tracking-widest text-xs hover:bg-slate-300 transition-all shadow-lg flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Dashboard
                            </a>
                            <button type="submit" class="bg-slate-900 text-white font-black py-5 rounded-2xl uppercase tracking-widest text-xs hover:bg-blue-600 transition-all shadow-lg">
                                Next: Choose Items
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="space-y-6">
                        <div class="flex justify-between items-center bg-blue-50 p-4 rounded-2xl border border-blue-100">
                            <div>
                                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Active Session</p>
                                <p class="text-xs font-bold text-slate-700 uppercase"><?= htmlspecialchars($pre_activity) ?> | <?= htmlspecialchars($pre_grade) ?></p>
                            </div>
                            <a href="?step=1&<?= http_build_query(array_diff_key($_GET, ['step' => ''])) ?>" class="text-[10px] font-black text-blue-600 hover:text-slate-900 uppercase tracking-widest border-b-2 border-blue-600 pb-0.5 transition-colors">Edit Info</a>
                        </div>

                        <form method="POST" class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col md:flex-row gap-3">
                            <input type="hidden" name="action" value="add_item">
                            <div class="flex-1 space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-blue-600 tracking-widest ml-2">Select Equipment</label>
                                <select name="item_id" required class="w-full bg-white border-2 border-blue-100 rounded-xl px-4 py-2.5 text-sm uppercase outline-none focus:border-blue-600 font-bold">
                                    <option value="" disabled selected>Choose an item...</option>
                                    <?php if (!empty($inventory_items)): ?>
                                        <?php foreach ($inventory_items as $item): ?>
                                            <option value="<?= $item['item_id'] ?>">
                                                <?= htmlspecialchars(strtoupper($item['item_name'])) ?> (Stock: <?= $item['quantity'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="w-full md:w-32 space-y-1.5">
                                <label class="text-[9px] uppercase font-black text-blue-600 tracking-widest text-center block">Qty</label>
                                <div class="flex items-center bg-white border-2 border-blue-100 rounded-xl h-[46px]">
                                    <button type="button" onclick="adjustMainQty(-1)" class="w-10 h-full text-blue-600 font-black">-</button>
                                    <input type="number" name="quantity" id="qtyInput" required value="1" min="1" class="w-full text-center font-bold text-sm outline-none bg-transparent">
                                    <button type="button" onclick="adjustMainQty(1)" class="w-10 h-full text-blue-600 font-black">+</button>
                                </div>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="h-[46px] px-8 bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase hover:bg-slate-900 transition-all shadow-md">Add</button>
                            </div>
                        </form>

                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Selected Items List</p>
                            <?php if (empty($_SESSION['item_queue'])): ?>
                                <div class="text-center py-10 border-2 border-dashed border-slate-200 rounded-2xl">
                                    <p class="text-[10px] font-black text-slate-300 uppercase">Your list is currently empty</p>
                                </div>
                            <?php else: ?>
                                <div class="max-h-[200px] overflow-y-auto pr-2 custom-scrollbar space-y-2">
                                    <?php foreach ($_SESSION['item_queue'] as $i => $q_item): ?>
                                        <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                                            <span class="text-[11px] font-black text-slate-700 uppercase"><?= htmlspecialchars($q_item['name']) ?> &mdash; <?= $q_item['qty'] ?></span>
                                            <a href="?<?= http_build_query(array_merge($_GET, ['remove_item' => $i])) ?>" class="text-[9px] font-black text-red-500 hover:underline uppercase">Remove</a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <form id="finalBookingForm" class="pt-6 border-t border-slate-100">
                                    <input type="hidden" name="activity_title" value="<?= htmlspecialchars($pre_activity) ?>">
                                    <input type="hidden" name="usage_datetime" value="<?= htmlspecialchars($pre_date) ?>">
                                    <input type="hidden" name="grade_section" value="<?= htmlspecialchars($pre_grade) ?>">
                                    <input type="hidden" name="student_count" value="<?= htmlspecialchars($pre_students) ?>">
                                    <input type="hidden" name="booked_by" value="<?= htmlspecialchars($pre_booked) ?>">
                                    <input type="hidden" name="noted_by" value="<?= htmlspecialchars($pre_noted) ?>">
                                    <input type="hidden" name="reference_no" value="<?= $_SESSION['reservation_ref'] ?>">
                                    <input type="hidden" name="status" value="pending">

                                    <!-- Hidden fields for each item in the queue -->
                                    <?php foreach ($_SESSION['item_queue'] as $i => $q_item): ?>
                                        <input type="hidden" name="item_queue[<?= $i ?>][id]" value="<?= $q_item['id'] ?>">
                                        <input type="hidden" name="item_queue[<?= $i ?>][name]" value="<?= htmlspecialchars($q_item['name']) ?>">
                                        <input type="hidden" name="item_queue[<?= $i ?>][qty]" value="<?= $q_item['qty'] ?>">
                                    <?php endforeach; ?>

                                    <button type="submit" id="submitBtn" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-blue-600 transition-all shadow-lg">
                                        Complete & Save Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="globalAlert" class="fixed top-5 left-1/2 -translate-x-1/2 z-50"></div>
<link rel="stylesheet" href="../assets/style.css">
<script src="../assets/js/global-alert.js"></script>

<script>
    function adjustMainQty(val) {
        const input = document.getElementById('qtyInput');
        if (!input) return;
        let current = parseInt(input.value) || 1;
        // Prevent negative values by ensuring minimum is 1
        input.value = Math.max(1, current + val);
    }

    document.getElementById('finalBookingForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('submitBtn');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "SAVING RESERVATION...";

        // Create FormData from the form
        const formData = new FormData(this);

        fetch('../auth/add_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    sessionStorage.setItem('inventoryAlert', JSON.stringify({
                        type: 'success',
                        title: 'Booking Confirmed',
                        message: d.message
                    }));
                    window.location.href = 'lab_booking.php';
                } else {
                    btn.disabled = false;
                    btn.innerText = originalText;
                    showAlert('danger', 'System Error', d.message);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = originalText;
                showAlert('danger', 'Connection Error', 'Failed to communicate with the server.');
            });
    });

    window.addEventListener('DOMContentLoaded', () => {
        const alertData = sessionStorage.getItem('inventoryAlert');
        if (alertData) {
            const {
                type,
                title,
                message
            } = JSON.parse(alertData);
            if (typeof showAlert === "function") {
                showAlert(type, title, message);
            }
            sessionStorage.removeItem('inventoryAlert');
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        // 1. Handle SessionStorage alerts (Final booking success)
        const alertData = sessionStorage.getItem('inventoryAlert');
        if (alertData) {
            const {
                type,
                title,
                message
            } = JSON.parse(alertData);
            if (typeof showAlert === "function") showAlert(type, title, message);
            sessionStorage.removeItem('inventoryAlert');
        }

        // 2. Handle URL-based alerts (Stock errors)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('alert_msg')) {
            const type = urlParams.get('alert_type') || 'warning';
            const title = urlParams.get('alert_title') || 'Notice';

            // FIX: Decode the URI and replace '+' with spaces
            let message = decodeURIComponent(urlParams.get('alert_msg'));
            message = message.replace(/\+/g, ' ');

            if (typeof showAlert === "function") {
                showAlert(type, title, message);
            }

            // Clean URL without reloading page
            const newUrl = window.location.pathname + window.location.search
                .replace(/[&?]alert_type=[^&]*/, '')
                .replace(/[&?]alert_title=[^&]*/, '')
                .replace(/[&?]alert_msg=[^&]*/, '')
                .replace(/^&/, '?'); // Ensure it still starts with ? if other params exist

            window.history.replaceState({}, document.title, newUrl || window.location.pathname);
        }
    });
</script>
</body>
</html>