<?php

$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
require_once $app_root . '/auth/session_init.php';

if (!isset($_SESSION['user_id'])) {
    $user_id = null;
    $user_name = 'Guest';
    $user_role = 'Guest';
} else {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['full_name'] ?? 'User';
    $user_role = $_SESSION['user_type'] ?? 'User';
}

$page_title = 'AVR Management System';
$module_name = 'AVR';
$module_subtitle = 'Management';
$module_icon = 'A';
$module_label = 'AVR_System';
$user_name = $user_name;
$user_role = $user_role;

$is_in_modules = strpos($_SERVER['SCRIPT_NAME'], '/modules/') !== false;
$base_path = $is_in_modules ? '../' : '';

require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_access.php';
$isAdmin = isAdminLoggedIn();

$adminSidebarItems = [
    ['url' => $base_path . 'index.php', 'file' => 'index.php', 'label' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10h16V10" /></svg>'],
    ['url' => $base_path . 'modules/inventory.php', 'file' => 'inventory.php', 'label' => 'Inventory', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>'],
    ['url' => $base_path . 'modules/borrowed.php', 'file' => 'borrowed.php', 'label' => 'Borrowed', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12A1.5 1.5 0 0118 21h-4.5V19.5a.75.75 0 00-.75-.75h-2.5a.75.75 0 00-.75.75V21H6a1.5 1.5 0 01-1.481-1.243l1.263-12A1.5 1.5 0 016.75 7.5h10.5a1.5 1.5 0 011.481 1.007zM18.75 7.5h-1.5v-3a2.25 2.25 0 00-4.5 0v3h-1.5" /></svg>'],
    ['url' => $base_path . 'modules/attendance.php', 'file' => 'attendance.php', 'label' => 'Attendance', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'],
    ['url' => $base_path . 'modules/analytics.php', 'file' => 'analytics.php', 'label' => 'Analytics', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>'],
];

if ($isAdmin) {
    $sidebar_items = $adminSidebarItems;
} else {
    
    $sidebar_items = [
        ['url' => $base_path . 'modules/reservation.php', 'file' => 'reservation.php', 'label' => 'Reservation', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>'],
    ];
}

include $app_root . '/avr/admin/header.php';
?>

<div class="flex">
    <?php include $app_root . '/partials/unified_sidebar.php'; ?>
    
    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-5 transition-all duration-300 bg-slate-50 min-h-screen">
        <div class="pt-8 pb-4 px-6">
            <!-- Page Title -->
            <div class="mb-6">
                <?php 
                $page = basename($_SERVER['PHP_SELF']);
                $path = str_replace((defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/avr/', '', $_SERVER['SCRIPT_FILENAME']);
                $titles = [
                    'index.php' => 'AVR Dashboard',
                    'inventory.php' => 'Inventory Management',
                    'borrowed.php' => 'Borrowed Items',
                    'reservation.php' => 'Reservation Management',
                    'attendance.php' => 'Attendance Log',
                    'analytics.php' => 'Analytics & Reports'
                ];
                $title = $titles[$page] ?? 'AVR Management';
                ?>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    <?php echo $title; ?>
                </h2>
                <p class="text-slate-500 text-sm"><?php echo date('l, F j, Y'); ?></p>
            </div>
