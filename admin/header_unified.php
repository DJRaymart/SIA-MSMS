<?php
if (!defined('APP_ROOT')) { require_once __DIR__ . '/../auth/path_config_loader.php'; }
$app_root = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);
require_once $app_root . '/auth/session_init.php';

if (!isset($_SESSION['admin_id'])) {
    $user_id = null;
    $user_name = 'Guest';
    $user_role = 'Guest';
} else {
    require_once $app_root . '/auth/admin_helper.php';
    $adminInfo = AdminAuth::getAdminInfo();
    $user_id = $_SESSION['admin_id'];
    $user_name = $adminInfo['name'] ?? 'Admin';
    $user_role = $adminInfo['role'] ?? 'Admin';
}

$page_title = 'Admin Dashboard';
$module_name = 'Admin';
$module_subtitle = 'Dashboard';
$module_icon = 'A';
$module_label = 'ADMIN_DASHBOARD';
$user_name = $user_name;
$user_role = $user_role;

require_once $app_root . '/auth/admin_helper.php';
$isAdmin = isAdminLoggedIn();

include $app_root . '/admin/header.php';
?>

<div class="flex">
    <main id="mainContent" class="flex-1 overflow-y-auto flex flex-col transition-all duration-300 bg-slate-50 min-h-screen">
        <div class="pt-8 pb-4 px-6">
            <!-- Page Title -->
            <div class="mb-6 max-w-7xl mx-auto text-left">
                <?php 
                $currentPageFile = basename($_SERVER['PHP_SELF']);
                $titles = [
                    'dashboard.php' => 'Admin Dashboard',
                    'analytics.php' => 'Module Analytics',
                    'accounts.php' => 'All Accounts',
                    'settings.php' => 'Account Settings',
                ];
                $subtitles = [
                    'dashboard.php' => 'Overview of all activities across MSMS modules',
                    'analytics.php' => 'Visual analytics and trends across all modules',
                    'accounts.php' => 'View all admin and student accounts • Admin only',
                    'settings.php' => 'Manage your account information and security',
                ];
                $title = $titles[$currentPageFile] ?? 'Admin Dashboard';
                $subtitle = $subtitles[$currentPageFile] ?? 'Overview of all activities across MSMS modules';
                ?>
                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    <?php echo $title; ?>
                </h2>
                <p class="text-slate-500 text-sm"><?php echo $subtitle; ?> • <?php echo date('l, F j, Y'); ?></p>
            </div>

            <!-- Admin Navbar: Settings & Accounts -->
            <?php
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            $baseS = $base === '' ? '/' : $base . '/';
            ?>
            <nav class="mb-6 max-w-7xl mx-auto">
                <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
                    <a href="<?php echo htmlspecialchars($baseS); ?>admin/dashboard.php" class="px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wide transition-colors <?php echo $currentPageFile === 'dashboard.php' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'; ?>">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            Dashboard
                        </span>
                    </a>
                    <a href="<?php echo htmlspecialchars($baseS); ?>admin/analytics.php" class="px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wide transition-colors <?php echo $currentPageFile === 'analytics.php' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'; ?>">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 14l3-3 3 2 4-5" /></svg>
                            Analytics
                        </span>
                    </a>
                    <a href="<?php echo htmlspecialchars($baseS); ?>admin/accounts.php" class="px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wide transition-colors <?php echo $currentPageFile === 'accounts.php' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'; ?>">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            List of Accounts
                        </span>
                    </a>
                    <a href="<?php echo htmlspecialchars($baseS); ?>account/settings.php" class="px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wide transition-colors <?php echo $currentPageFile === 'settings.php' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'; ?>">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><circle cx="12" cy="12" r="3" /></svg>
                            Settings
                        </span>
                    </a>
                </div>
            </nav>
