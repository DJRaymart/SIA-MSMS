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

$page_title = 'Queue Management System';
$module_name = 'Queue';
$module_subtitle = 'Management';
$module_icon = 'Q';
$module_label = 'Queue_System';
$user_name = $user_name;
$user_role = $user_role;

require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_access.php';
$isAdmin = isAdminLoggedIn();

$adminSidebarItems = [
    ['url' => 'index.php', 'file' => 'index.php', 'label' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10h16V10" /></svg>'],
    ['url' => 'counter.php', 'file' => 'counter.php', 'label' => 'Counters', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" /></svg>'],
    ['url' => 'display.php', 'file' => 'display.php', 'label' => 'Display', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'],
    ['url' => 'settings.php', 'file' => 'settings.php', 'label' => 'Settings', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'],
];

if ($isAdmin) {
    $sidebar_items = $adminSidebarItems;
} else {
    
    $sidebar_items = [
        ['url' => 'portal.php', 'file' => 'portal.php', 'label' => 'Get Queue', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
        ['url' => 'display.php', 'file' => 'display.php', 'label' => 'Queue Display', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'],
    ];
}

include $app_root . '/queue/admin/header.php';
?>

<div class="flex">
    <?php include $app_root . '/partials/unified_sidebar.php'; ?>
    
    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-5 transition-all duration-300 bg-slate-50 min-h-screen">
        <div class="pt-8 pb-4 px-6">
            <!-- Page Title -->
            <div class="mb-6">
                <?php 
                $page = basename($_SERVER['PHP_SELF']);
                $titles = [
                    'index.php' => 'Queue Dashboard',
                    'counter.php' => 'Counter Management',
                    'display.php' => 'Queue Display',
                    'settings.php' => 'Display Settings'
                ];
                ?>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    <?php echo $titles[$page] ?? 'Queue Management'; ?>
                </h2>
                <p class="text-slate-500 text-sm"><?php echo date('l, F j, Y'); ?></p>
            </div>
