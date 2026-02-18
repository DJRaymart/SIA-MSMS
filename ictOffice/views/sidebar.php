<?php
session_start();

$page = isset($_GET['page']) ? $_GET['page'] : '';

if (!isset($_SESSION['userFullName']) && $page !== 'logbook') {
    header("Location: /inventory_app/public/?page=login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ICT Inventory Sidebar</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        #sidebar {
            width: 16rem; /* 64 */
        }
        #sidebar.collapsed {
            width: 4rem; /* 16 */
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #e0e7ff;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .menu-item:hover {
            background: rgba(255 255 255 / 0.15);
            color: #fff;
            transform: translateX(6px);
        }

        .icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 1.75rem;
            font-size: 1.5rem;
            user-select: none;
        }

        .submenu {
            margin-left: 3rem;
            display: none;
            flex-direction: column;
            gap: 4px;
        }

        .submenu-item {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #e0e7ff;
            transition: background 0.2s ease;
        }

        .submenu-item:hover {
            background: rgba(255 255 255 / 0.15);
            color: #fff;
        }

        /* Hide text when collapsed */
        #sidebar.collapsed .menu-text,
        #sidebar.collapsed #brandText {
            display: none;
        }

        /* Center icons when collapsed */
        #sidebar.collapsed .menu-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        /* Hide submenu when collapsed */
        #sidebar.collapsed .submenu {
            display: none !important;
        }

        /* ... keep your existing sidebar width styles ... */

        /* Hide the brand container (text + hamburger) when collapsed */
        #sidebar.collapsed #brandContainer {
            opacity: 0;
            pointer-events: none;
            display: none;
        }

        /* Ensure the logo is centered when collapsed */
        #sidebar.collapsed .flex.items-center {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        /* Existing styles for menu-text */
        #sidebar.collapsed .menu-text {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100">

<aside id="sidebar"
       class="fixed top-0 left-0 h-full
              bg-gradient-to-b from-indigo-700 via-blue-600 to-blue-500
              text-white shadow-lg
              flex flex-col
              transition-all duration-300 ease-in-out">

    <!-- Brand -->
    <div class="flex items-center px-4 py-4 select-none overflow-hidden relative">
        <button onclick="toggleSidebar()" class="focus:outline-none hover:opacity-80 transition-opacity">
            <img src="<?php echo htmlspecialchars(isset($ict_public) ? $ict_public : ((defined('BASE_URL') ? rtrim(BASE_URL,'/') : '') . '/ictOffice/public')); ?>/images/school_logo.png" alt="Logo" class="w-10 h-10 min-w-[2.5rem] object-contain">
        </button>
        
        <div id="brandContainer" class="flex justify-between items-center flex-1 ml-3 transition-opacity duration-300">
            <span class="font-bold text-lg whitespace-nowrap">ICT Inventory</span>
            <button onclick="toggleSidebar()" class="text-white p-2 rounded hover:bg-indigo-900 transition-colors">
                ☰
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-col flex-grow gap-2 px-2">
       <?php 
         if(isset($_SESSION['userRole'])) 
        {   
            if($_SESSION['userRole'] == "admin"){
        ?> 

        <a href="/inventory_app/public/?page=dashboard" class="menu-item">
            <span class="icon">📊</span>
            <span class="menu-text">Dashboard</span>
        </a>
        <!-- Inventory with Submenu -->
        <div>
            <button onclick="toggleSubMenu('inventory')"
                    class="menu-item w-full text-left">
                <span class="icon">💻</span>
                <span class="menu-text flex-1">Inventory</span>
                <span class="menu-text text-sm">▾</span>
            </button>

            <div id="inventorySubMenu" class="submenu">
                <a href="/inventory_app/public/?page=inventory" class="submenu-item">
                    📦 Items
                </a>
                <a href="/inventory_app/public/?page=categories" class="submenu-item">
                    📂 Categories
                </a>
                <a href="/inventory_app/public/?page=locations" class="submenu-item">
                    📍 Locations
                </a>
            </div>
        </div>
    
        <a href="/inventory_app/public/?page=logbook-records" class="menu-item">
            <span class="icon">📄</span>
            <span class="menu-text">Logbook Records</span>
        </a>

        <!-- Inventory with Submenu --> 
        <!-- <div>
            <button onclick="toggleSubMenu('reports')"
                    class="menu-item w-full text-left">
                <span class="icon">📄</span>
                <span class="menu-text flex-1">Reports</span>
                <span class="menu-text text-sm">▾</span>
            </button>

            <div id="reportsSubMenu" class="submenu">
                <a href="/inventory_app/public/?page=reports" class="submenu-item">
                    STATISTICS REPORT
                </a>
                <a href="/inventory_app/public/?page=logbook-report" class="submenu-item">
                    LOG BOOK REPORT
                </a>
            </div>
        </div> -->
    
       <?php }} ?>
       
        <a href="/inventory_app/public/?page=logbook" class="menu-item">
            <span class="icon">📄</span>
            <span class="menu-text">Log Book</span>
        </a>

        <!-- </?php 
         if(isset($_SESSION['userRole'])) 
        {   
            if($_SESSION['userRole'] == "admin"){
        ?> 
        <a href="/inventory_app/public/?page=users" class="menu-item">
            <span class="icon">👤</span>
            <span class="menu-text">Users</span>
        </a>
        </?php }} ?> -->

          <?php 
         if(isset($_SESSION['userRole'])) 
        {   
            if($_SESSION['userRole'] != "student"){
        ?> 
        <!-- Logout -->
        <div class="mt-auto border-t border-indigo-900 pt-4">
            <a href="/inventory_app/public/?page=logout" class="menu-item text-red-300 hover:text-red-100">
                <span class="icon">🚪</span>
                <span class="menu-text">Logout</span>
            </a>
        </div>
    <?php }} ?>
    </nav>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main');

        sidebar.classList.toggle('collapsed');

        if (sidebar.classList.contains('collapsed')) {
            main.classList.replace('ml-64', 'ml-16');
        } else {
            main.classList.replace('ml-16', 'ml-64');
        }
    }

    function toggleSubMenu(menu) {
        let subMenu;
        switch(menu) {
            case 'reports':
                subMenu = document.getElementById('reportsSubMenu');
             
                break;
            case 'inventory':
                subMenu = document.getElementById('inventorySubMenu');
                break;
            default:

            subMenu.style.display =
            subMenu.style.display === 'flex' ? 'none' : 'flex';
        }
            subMenu.style.display =
            subMenu.style.display === 'flex' ? 'none' : 'flex';
    }
</script>

</body>
</html>
