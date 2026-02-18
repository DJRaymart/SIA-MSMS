<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
require_once $app_root . '/auth/session_init.php';

if (!isset($_SESSION['librarian_id'])) {
    $librarian_id = null;
    $librarian_name = 'Guest';
    $librarian_role = 'Guest';
} else {
    $librarian_id = $_SESSION['librarian_id'];
    $librarian_name = $_SESSION['full_name'];
    $librarian_role = $_SESSION['role'];
}

$page_title = 'Library Management System';
$module_name = 'Library';
$module_subtitle = 'Management';
$module_icon = 'L';
$module_label = 'Library_System';
$user_name = $librarian_name;
$user_role = $librarian_role;

require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_access.php';
$isAdmin = isAdminLoggedIn();

$adminSidebarItems = [
    ['url' => 'index.php', 'file' => 'index.php', 'label' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10h16V10" /></svg>'],
    ['url' => 'books.php', 'file' => 'books.php', 'label' => 'Books', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>'],
    ['url' => 'borrowers.php', 'file' => 'borrowers.php', 'label' => 'Users', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'],
    ['url' => 'transactions.php', 'file' => 'transactions.php', 'label' => 'Transactions', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>'],
    ['url' => 'log_book.php', 'file' => 'log_book.php', 'label' => 'Log Book', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
];

if ($isAdmin) {
    $sidebar_items = $adminSidebarItems;
} else {
    
    $sidebar_items = [
        ['url' => 'log_book.php', 'file' => 'log_book.php', 'label' => 'Log Book', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
    ];
}

if ($librarian_role == 'Head') {
    $sidebar_items[] = ['url' => 'librarians.php', 'file' => 'librarians.php', 'label' => 'Librarians', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'];
}

include $app_root . '/library/admin/header.php';
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
                    'index.php' => 'Dashboard',
                    'books.php' => 'Book Management',
                    'users.php' => 'Borrowers Management',
                    'borrowers.php' => 'Borrowers Management',
                    'log_book.php' => 'Log Book',
                    'transactions.php' => 'Transaction Management',
                    'librarians.php' => 'Librarian Management'
                ];
                ?>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    <?php echo $titles[$page] ?? 'Library Management'; ?>
                </h2>
                <p class="text-slate-500 text-sm"><?php echo date('l, F j, Y'); ?></p>
            </div>
