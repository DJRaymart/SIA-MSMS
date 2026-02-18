<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
$ict_base = (rtrim(BASE_URL, '/') === '' ? '' : rtrim(BASE_URL, '/')) . '/ictOffice';
$ict_public = $ict_base . '/public';
$ict_api = $ict_base . '/api';
require_once $app_root . '/auth/session_init.php';

$page_title = 'ICT Office Management';
$module_name = 'ICT Office';
$module_subtitle = 'Inventory';
$module_icon = 'I';
$module_label = 'ICT_Office';

if (isset($_SESSION['userFullName'])) {
    $user_name = $_SESSION['userFullName'];
    $user_role = $_SESSION['userRole'] ?? 'User';
} else {
    $user_name = 'Guest';
    $user_role = 'Guest';
}

require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_access.php';
$isAdmin = isAdminLoggedIn();

$adminSidebarItems = [
    ['url' => $ict_public . '/?page=dashboard', 'file' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>'],
    ['url' => $ict_public . '/?page=inventory', 'file' => 'inventory.php', 'label' => 'Inventory', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>'],
    ['url' => $ict_public . '/?page=categories', 'file' => 'category.php', 'label' => 'Categories', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>'],
    ['url' => $ict_public . '/?page=locations', 'file' => 'location.php', 'label' => 'Locations', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>'],
    ['url' => $ict_public . '/?page=logbook-records', 'file' => 'logbook-records.php', 'label' => 'Logbook Records', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'],
];

if ($isAdmin) {
    $sidebar_items = $adminSidebarItems;
} else {
    $sidebar_items = [
        ['url' => $ict_public . '/?page=logbook', 'file' => 'logbook.php', 'label' => 'Log Book', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
    ];
}

include $app_root . '/ictOffice/admin/header.php';
?>
<div class="flex">
    <?php include $app_root . '/partials/unified_sidebar.php'; ?>
    
    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col ml-5 transition-all duration-300 bg-slate-50 min-h-screen">
        <div class="pt-8 pb-4 px-6">
            <!-- Page Title -->
            <div class="mb-6">
                <?php 
                $page = $_GET['page'] ?? 'dashboard';
                $titles = [
                    'dashboard' => 'ICT Office Dashboard',
                    'inventory' => 'Inventory Management',
                    'categories' => 'Category Management',
                    'locations' => 'Location Management',
                    'logbook' => 'Log Book',
                    'logbook-records' => 'Logbook Records',
                    'users' => 'User Management',
                    'reports' => 'Reports'
                ];
                $title = $titles[$page] ?? 'ICT Office Management';
                ?>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    <?php echo $title; ?>
                </h2>
                <p class="text-slate-500 text-sm"><?php echo date('l, F j, Y'); ?></p>
            </div>
