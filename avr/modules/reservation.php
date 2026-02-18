<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/security.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/auth/admin_helper.php';

require_once '../config/database.php';

if (isAdminLoggedIn()) {
    $adminInfo = getAdminInfo();
    $_SESSION['avr_verified_student'] = [
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
        require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/db.php';
        $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
        $stmt->bind_param("ss", $id, $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $_SESSION['avr_verified_student'] = ['name' => $row['name'], 'grade_section' => $row['grade'] . ' - ' . $row['section']];
            header('Location: reservation.php');
            exit;
        }
    }
    $verify_error = 'Student not found or account not yet approved.';
}
if (!empty($_GET['clear_booking'])) {
    unset($_SESSION['avr_verified_student']);
    header('Location: reservation.php');
    exit;
}

$verified = $_SESSION['avr_verified_student'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($verify_error) && isset($_POST['action']) && $_POST['action'] == 'add') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO avr_reservation (Name, Department, Date, Time, Purpose) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['name'], $_POST['department'], $_POST['date'], $_POST['time'], $_POST['purpose']);
    if ($stmt->execute()) {
        $_SESSION['avr_success'] = 'Reservation added successfully!';
    } else {
        $_SESSION['avr_error'] = 'Failed to add reservation.';
    }
    $stmt->close();
    $conn->close();
    header('Location: reservation.php');
    exit;
}

$success = $_SESSION['avr_success'] ?? null;
$error = $_SESSION['avr_error'] ?? null;
if (isset($_SESSION['avr_success'])) unset($_SESSION['avr_success']);
if (isset($_SESSION['avr_error'])) unset($_SESSION['avr_error']);
?>
<?php if (!$verified): ?>
<?php
$verify_title = 'AVR Reservation';
$verify_subtitle = 'Enter your Student ID or RFID to verify your account before making a reservation.';
$verify_back_url = ((defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/') . 'avr/portal.php';
$verify_back_label = 'Back to AVR';
$verify_accent = 'pink';
$verify_error = $verify_error ?? null;
$verify_standalone = true;
require dirname(__DIR__, 2) . '/partials/verify_student_unified.php';
?>
<?php else: ?>
<?php
$avrBase = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/avr' : '/avr';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVR Reservation - MSMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="fixed inset-0 -z-30 bg-gradient-to-br from-slate-50 via-pink-50 to-rose-100"></div>
<div class="fixed inset-0 -z-30 opacity-20 bg-[radial-gradient(at_30%_20%,rgba(236,72,153,0.4)_0,transparent_50%),radial-gradient(at_70%_0%,rgba(244,63,94,0.3)_0,transparent_50%)]"></div>

<div class="relative min-h-screen w-full flex flex-col items-center justify-center py-12">
    <div class="relative z-10 w-full max-w-xl px-6">

        <div class="text-center mb-6">
            <span class="text-[10px] font-black text-pink-600 uppercase tracking-[0.4em]">AVR Reservation</span>
            <h1 class="text-5xl md:text-6xl font-black tracking-tighter text-slate-900 leading-none">Make a <span class="text-pink-600 italic">Reservation</span></h1>
        </div>

        <div class="relative p-[2px] bg-gradient-to-r from-pink-600 via-rose-500 to-pink-600 rounded-[2.5rem] shadow-2xl">
            <div class="bg-white/95 backdrop-blur-xl rounded-[2.4rem] p-8 md:p-10">

                <div class="mb-6 flex justify-between items-center bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                    <span class="text-xs font-bold text-slate-700"><?= htmlspecialchars($verified['name']) ?> (<?= htmlspecialchars($verified['grade_section']) ?>)</span>
                    <a href="?clear_booking=1" class="text-[10px] font-black text-pink-600 hover:underline uppercase">Use different student</a>
                </div>

                <?php if ($success): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($verified['name']) ?>">
                    <input type="hidden" name="department" value="<?= htmlspecialchars($verified['grade_section']) ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Date *</label>
                            <input type="date" name="date" required class="w-full bg-slate-100/50 border-2 border-slate-200 rounded-xl px-5 py-3 text-sm outline-none focus:border-pink-500">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Time *</label>
                            <input type="time" name="time" required class="w-full bg-slate-100/50 border-2 border-slate-200 rounded-xl px-5 py-3 text-sm outline-none focus:border-pink-500">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Purpose</label>
                        <textarea name="purpose" rows="2" placeholder="e.g. Class presentation, group study..." class="w-full bg-slate-100/50 border-2 border-slate-200 rounded-xl px-5 py-3 text-sm outline-none focus:border-pink-500 resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <?php $base = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/'; $clearExitUrl = $base . 'auth/clear_exit.php?redirect=' . urlencode($avrBase . '/portal.php'); ?>
                        <a href="<?= htmlspecialchars($clearExitUrl) ?>" class="bg-slate-200 text-slate-700 font-black py-5 rounded-2xl uppercase tracking-widest text-xs hover:bg-slate-300 transition-all flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Back to AVR
                        </a>
                        <button type="submit" class="bg-slate-900 text-white font-black py-5 rounded-2xl uppercase tracking-widest text-xs hover:bg-pink-600 transition-all shadow-lg">
                            Submit Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>
<?php endif; ?>
