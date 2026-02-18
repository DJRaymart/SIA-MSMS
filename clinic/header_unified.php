<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) {
    require_once $app_root . '/auth/path_config_loader.php';
}
require_once $app_root . '/auth/session_init.php';

if (!defined('CLINIC_BASE')) {
    require_once __DIR__ . '/config.php';
}
$clinic_base = defined('CLINIC_BASE') ? CLINIC_BASE : ((BASE_URL === '' ? '' : BASE_URL) . '/clinic');

$page_title = 'Clinic Admin';
$module_name = 'Clinic';
$module_subtitle = 'Admin';
$module_icon = 'C';
$module_label = 'CLINIC_SYSTEM';

if (isset($_SESSION['admin_id'])) {
    require_once $app_root . '/auth/admin_helper.php';
    $adminInfo = AdminAuth::getAdminInfo();
    $user_name = $adminInfo['name'] ?? 'Admin';
    $user_role = $adminInfo['role'] ?? 'Admin';
} else {
    $user_name = 'Guest';
    $user_role = 'Guest';
}

require_once $app_root . '/auth/admin_helper.php';
$isAdmin = isAdminLoggedIn();

$sidebar_items = [
    ['url' => $clinic_base . '/admin/admin.php', 'file' => 'admin.php', 'label' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>'],
    ['url' => $clinic_base . '/admin/pages/patientrecords.php', 'file' => 'patientrecords.php', 'label' => 'Patients', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'],
    ['url' => $clinic_base . '/admin/pages/recordslogs.php', 'file' => 'recordslogs.php', 'label' => 'Records', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'],
    ['url' => $clinic_base . '/admin/pages/reportsComplaint.php', 'file' => 'reportsComplaint.php', 'label' => 'Reports Complaint', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>'],
    ['url' => $clinic_base . '/admin/pages/reportslogs.php', 'file' => 'reportslogs.php', 'label' => 'Reports Logs', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>'],
    ['url' => $clinic_base . '/admin/pages/statistics.php', 'file' => 'statistics.php', 'label' => 'Statistics', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" /></svg>'],
];

include $app_root . '/clinic/admin/header.php';
?>
<div class="flex">
    <?php include $app_root . '/partials/unified_sidebar.php'; ?>
    
    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-2 transition-all duration-300 bg-slate-50 min-h-screen">
        <div class="pt-8 pb-4 px-6">
            <!-- Page Title -->
            <div class="mb-6">
                <?php 
                $script = basename($_SERVER['PHP_SELF']);
                $titles = [
                    'admin.php' => 'Clinic Dashboard',
                    'patientrecords.php' => 'Patient Records',
                    'recordslogs.php' => 'Records & Logs',
                    'reportsComplaint.php' => 'Reports by Complaint',
                    'reportslogs.php' => 'Reports Logs',
                    'statistics.php' => 'Statistics',
                ];
                $title = $titles[$script] ?? 'Clinic Admin';
                ?>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    <?php echo $title; ?>
                </h2>
                <p class="text-slate-500 text-sm"><?php echo date('l, F j, Y'); ?></p>
            </div>
