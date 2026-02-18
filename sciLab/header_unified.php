<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
$page_title = 'Science Laboratory';
$module_name = 'Science Laboratory';
$module_subtitle = '';
$module_icon = 'SL';
$module_label = 'Science_Lab';
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Guest';

$sidebar_items = [
    ['url' => 'admin/index.php', 'file' => 'admin/index.php', 'label' => 'Dashboard', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10h16V10" /></svg>'],
    ['url' => 'admin/inventory.php', 'file' => 'admin/inventory.php', 'label' => 'Inventory', 'icon' => '<svg class="w-5 h-5 text-blue-400/70 group-hover:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.75 7.5h16.5m-16.5 0l-.75-4.5h17.25l-.75 4.5" /></svg>'],
];

include $app_root . '/partials/unified_header.php';
?>
