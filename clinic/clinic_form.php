<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/session_init.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/security.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_helper.php';
require_once dirname(__DIR__) . '/config/db.php';

if (isAdminLoggedIn()) {
    $adminInfo = getAdminInfo();
    $_SESSION['clinic_verified_student'] = [
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
            $_SESSION['clinic_verified_student'] = [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'grade_section' => ($row['grade'] ?? '') . ' - ' . ($row['section'] ?? '')
            ];
            header('Location: clinic_form.php');
            exit;
        }
    }
    $verify_error = 'Student not found or account not yet approved.';
}
if (!empty($_GET['clear_booking'])) {
    unset($_SESSION['clinic_verified_student']);
    header('Location: clinic_form.php');
    exit;
}

$verified = $_SESSION['clinic_verified_student'] ?? null;

$success = false;
$error = '';
if ($verified && $_SERVER['REQUEST_METHOD'] === 'POST' && empty($verify_error) && isset($_POST['action']) && $_POST['action'] === 'add') {
    $complaint = $conn->real_escape_string(trim($_POST['complaint'] ?? ''));
    $treatment = $conn->real_escape_string(trim($_POST['treatment'] ?? ''));
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    $time = date('H:i');
    $stmt = $conn->prepare("INSERT INTO clinic_records (student_id, name, grade_section, complaint, treatment, date, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $verified['student_id'], $verified['name'], $verified['grade_section'], $complaint, $treatment, $date, $time);
    if ($stmt->execute()) {
        $success = true;
    } else {
        $error = $conn->error ?: 'Failed to save record.';
    }
}

if (!$verified && isset($_GET['rfid'])) {
    $id_or_rfid = $conn->real_escape_string($_GET['rfid']);
    $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE (student_id = ? OR rfid_number = ?) AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') LIMIT 1");
    $stmt->bind_param("ss", $id_or_rfid, $id_or_rfid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $gs = trim(($row['grade'] ?? '') . ' - ' . ($row['section'] ?? ''), ' - ');
        echo json_encode(['student_id' => $row['student_id'], 'fullname' => $row['name'], 'grade_section' => $gs]);
    } else {
        echo json_encode([]);
    }
    exit;
}
if (!$verified && isset($_GET['search_name'])) {
    $search = $conn->real_escape_string($_GET['search_name']);
    $term = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT student_id, name, grade, section FROM students WHERE name LIKE ? AND (account_status = 'approved' OR account_status IS NULL OR account_status = '') ORDER BY name LIMIT 5");
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $gs = trim(($row['grade'] ?? '') . ' - ' . ($row['section'] ?? ''), ' - ');
        $data[] = ['student_id' => $row['student_id'], 'fullname' => $row['name'], 'grade_section' => $gs];
    }
    echo json_encode($data);
    exit;
}

if (!$verified) {
    $verify_title = 'Clinic Form';
    $verify_subtitle = 'Enter your Student ID or RFID to verify your account before recording.';
    $base = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/';
    $verify_back_url = $base . 'auth/clear_exit.php?redirect=' . urlencode($base . 'clinic/portal.php');
    $verify_back_label = 'Back to Clinic';
    $verify_accent = 'teal';
    $verify_standalone = true;
    require dirname(__DIR__) . '/partials/verify_student_unified.php';
    exit;
}

$base = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/';
$clinicBase = $base . 'clinic';
$clearExitUrl = $base . 'auth/clear_exit.php?redirect=' . urlencode($base . 'clinic/portal.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Form - MSMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="fixed inset-0 -z-30 bg-gradient-to-br from-slate-50 via-teal-50 to-cyan-100"></div>
<div class="fixed inset-0 -z-30 opacity-20 bg-[radial-gradient(at_30%_20%,rgba(20,184,166,0.4)_0,transparent_50%),radial-gradient(at_70%_0%,rgba(6,182,212,0.3)_0,transparent_50%)]"></div>

<div class="relative min-h-screen w-full flex flex-col items-center justify-center py-12">
    <div class="relative z-10 w-full max-w-xl px-6">

        <div class="text-center mb-6">
            <span class="text-[10px] font-black text-teal-600 uppercase tracking-[0.4em]">Clinic Form</span>
            <h1 class="text-5xl md:text-6xl font-black tracking-tighter text-slate-900 leading-none">Record <span class="text-teal-600 italic">Visit</span></h1>
        </div>

        <div class="relative p-[2px] bg-gradient-to-r from-teal-600 via-cyan-500 to-teal-600 rounded-[2.5rem] shadow-2xl">
            <div class="bg-white/95 backdrop-blur-xl rounded-[2.4rem] p-8 md:p-10">

                <div class="mb-6 flex justify-between items-center bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                    <span class="text-xs font-bold text-slate-700"><?= htmlspecialchars($verified['name']) ?> (<?= htmlspecialchars($verified['grade_section']) ?>)</span>
                    <a href="?clear_booking=1" class="text-[10px] font-black text-teal-600 hover:underline uppercase">Use different student</a>
                </div>

                <?php if ($success): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Record saved successfully.
                </div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="add">
                    <div class="space-y-1.5">
                        <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Complaint / Sickness *</label>
                        <textarea name="complaint" required rows="3" placeholder="Describe the complaint or sickness..." class="w-full bg-slate-100/50 border-2 border-slate-200 rounded-xl px-5 py-3 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 resize-none"></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[9px] uppercase font-black text-slate-500 tracking-widest ml-2">Treatment</label>
                        <textarea name="treatment" rows="3" placeholder="Treatment given..." class="w-full bg-slate-100/50 border-2 border-slate-200 rounded-xl px-5 py-3 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <a href="<?= htmlspecialchars($clearExitUrl) ?>" class="bg-slate-200 text-slate-700 font-black py-5 rounded-2xl uppercase tracking-widest text-xs hover:bg-slate-300 transition-all flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Back to Clinic
                        </a>
                        <button type="submit" class="bg-slate-900 text-white font-black py-5 rounded-2xl uppercase tracking-widest text-xs hover:bg-teal-600 transition-all shadow-lg">
                            Save Record
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>
