<?php
$app_root = dirname(__DIR__, 2);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
$sciLabBase = $baseS . 'sciLab';
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_access.php';
$isAdmin = isAdminLoggedIn();
$studentItems = getStudentSidebarItems('sciLab');
?>
<div class="flex">
    <aside id="sidebar" class="bg-white text-slate-700 flex flex-col p-4 h-screen shadow-lg w-56 flex-shrink-0 relative border-r-2 border-blue-200 transition-all duration-300 ease-in-out">

        <!-- Sidebar Toggle -->
        <button id="sidebarToggle" class="absolute top-8 -right-3 w-6 h-12 rounded-md flex items-center justify-center bg-white border-2 border-blue-300 shadow-lg transition-all duration-300 hover:border-blue-500 hover:bg-blue-50 group z-50 overflow-hidden">
            <svg id="sidebarIcon" class="w-4 h-4 text-blue-600 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path id="iconPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Brand -->
        <div class="mb-5 mt-3 px-1 flex items-center gap-3 overflow-hidden transition-all duration-300">
            <div id="sidebarLogo" class="w-10 h-10 bg-blue-600 rounded-lg flex-shrink-0 flex items-center justify-center shadow-lg transition-all duration-300">
                <span class="text-white font-black text-xs italic transition-all duration-300" id="sidebarLogoText">S</span>
            </div>
            <span class="label font-black uppercase tracking-[0.2em] text-[10px] text-slate-800 whitespace-nowrap transition-all duration-300" id="sidebarLabel">ScienceLab_Systems</span>
        </div>

        <!-- Nav Links -->
        <nav id="navLinks" class="flex flex-col space-y-2 flex-1 overflow-y-auto custom-scrollbar sticky top-0">

            <?php if ($isAdmin): ?>
            <!-- Admin: Show all admin features -->
            <!-- Dashboard -->
            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/admin/" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50 text-slate-700 relative overflow-hidden" data-tooltip="Dashboard">
                <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10h16V10" />
                    </svg>
                </div>
                <span class="label text-xs font-bold uppercase tracking-widest transition-colors group-hover:text-blue-700">Dashboard</span>
            </a>

            <!-- Inventory -->
            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/admin/inventory.php" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50 text-slate-700 relative overflow-hidden" data-tooltip="Inventory">
                <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a1 1 0 00-1-1H5a1 1 0 00-1 1v6m16 0l-8 8-8-8" />
                    </svg>
                </div>
                <span class="label text-xs font-bold uppercase tracking-widest transition-colors group-hover:text-blue-700">Inventory</span>
            </a>

            <!-- Booking Requests -->
            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reservation/booking_requests.php" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50 text-slate-700 relative overflow-hidden" data-tooltip="Booking Requests">
                <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 6h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1z" />
                    </svg>
                </div>
                <span class="label text-xs font-bold uppercase tracking-widest transition-colors group-hover:text-blue-700">Booking Requests</span>
            </a>

            <!-- Inventory Report -->
            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reports/inventory_report.php" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50 text-slate-700 relative overflow-hidden" data-tooltip="Inventory Report">
                <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6h6v6m0 0v4H9v-4m6-6H9V5h6v6z" />
                    </svg>
                </div>
                <span class="label text-xs font-bold uppercase tracking-widest transition-colors group-hover:text-blue-700">Inventory Report</span>
            </a>

            <!-- Log Book Report -->
            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reports/log_book_report.php" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50 text-slate-700 relative overflow-hidden" data-tooltip="Log Book Report">
                <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                </div>
                <span class="label text-xs font-bold uppercase tracking-widest transition-colors group-hover:text-blue-700">Log Book Report</span>
            </a>

            <!-- Analytics Report -->
            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/reports/statistic_report.php" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 hover:bg-blue-50 text-slate-700 relative overflow-hidden" data-tooltip="Analytics Report">
                <div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12M12 6v12" />
                    </svg>
                </div>
                <span class="label text-xs font-bold uppercase tracking-widest transition-colors group-hover:text-blue-700">Analytics Report</span>
            </a>
            
            <?php elseif (!empty($studentItems)): ?>
            <!-- Student: Show only student-accessible items if logged in -->
            <?php 
            require_once $app_root . '/auth/student_helper.php';
            $isStudent = isStudentLoggedIn();
            
            if (!$isStudent): ?>
            <div class="px-4 py-3 bg-red-50 border-l-4 border-red-500 rounded-lg mb-4">
                <p class="text-xs font-bold text-red-800 uppercase">Student Login Required</p>
                <p class="text-[10px] text-red-600 mt-1">Please login to access student features</p>
                <a href="<?php echo htmlspecialchars($baseS); ?>login.php?type=student" class="mt-2 inline-block text-xs font-semibold text-blue-600 hover:text-blue-800">Student Login →</a>
            </div>
            <?php else: ?>
            <div class="px-4 py-3 bg-blue-50 border-l-4 border-blue-500 rounded-lg mb-4">
                <p class="text-xs font-bold text-blue-800 uppercase">Student Access</p>
                <p class="text-[10px] text-blue-600 mt-1">Available features</p>
            </div>
            <?php 
            foreach ($studentItems as $item) {
                $is_active = (basename($_SERVER['PHP_SELF']) == $item['file'] || strpos($_SERVER['REQUEST_URI'], $item['file']) !== false);
                echo '<a href="' . htmlspecialchars($item['url']) . '" class="nav-link group flex items-center px-4 py-3 rounded-xl transition-all duration-300 ' . ($is_active ? 'bg-blue-50 border-l-4 border-blue-600 text-blue-700' : 'hover:bg-blue-50 text-slate-700') . ' relative overflow-hidden" data-tooltip="' . htmlspecialchars($item['label']) . '">';
                echo '<div class="w-6 h-6 flex-shrink-0 flex items-center justify-center mr-4 sidebar-icon-box transition-all duration-300 group-hover:scale-110">';
                echo $item['icon'] ?? '';
                echo '</div>';
                echo '<span class="label text-xs font-bold uppercase tracking-widest transition-colors ' . ($is_active ? 'text-blue-700 font-black' : 'text-slate-600 group-hover:text-blue-700') . '">' . htmlspecialchars($item['label']) . '</span>';
                echo '</a>';
            }
            ?>
            <div class="px-4 py-3 bg-amber-50 border-l-4 border-amber-500 rounded-lg mt-4">
                <p class="text-xs font-bold text-amber-800 uppercase">Need More Access?</p>
                <a href="<?php echo htmlspecialchars($baseS); ?>login.php?type=admin" class="mt-2 inline-block text-xs font-semibold text-blue-600 hover:text-blue-800">Admin Login →</a>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <!-- No student access: Show login prompt -->
            <div class="px-4 py-3 bg-red-50 border-l-4 border-red-500 rounded-lg mb-4">
                <p class="text-xs font-bold text-red-800 uppercase">Admin Login Required</p>
                <p class="text-[10px] text-red-600 mt-1">Please login to access features</p>
                <a href="<?php echo htmlspecialchars($baseS); ?>login.php" class="mt-2 inline-block text-xs font-semibold text-blue-600 hover:text-blue-800">Login →</a>
            </div>
            <?php endif; ?>

        </nav>
    </aside>
</div>

<style>
    /* Collapsed mode adjustments */
    .w-16 .sidebar-icon-box {
        margin-right: 0 !important;
        width: 100% !important;
    }

    .w-16 .nav-link {
        padding-left: 0 !important;
        padding-right: 0 !important;
        justify-content: center !important;
    }

    /* Tooltip */
    .nav-link[data-tooltip]:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #1e40af;
        color: #fff;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        white-space: nowrap;
        z-index: 50;
        opacity: 1;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
const iconPath = document.getElementById('iconPath');
const mainContent = document.getElementById('mainContent');

// Logo elements
const logoContainer = document.getElementById('sidebarLogo'); // the rounded div
const logoText = document.getElementById('sidebarLogoText');  // the "S"
const label = document.getElementById('sidebarLabel');       // Core_Systems

function applySidebarState(collapsed) {
    // Sidebar width
    sidebar.classList.toggle('w-16', collapsed);
    sidebar.classList.toggle('w-56', !collapsed);
    if (mainContent) {
        mainContent.classList.toggle('ml-5', collapsed);
        mainContent.classList.toggle('ml-5', !collapsed);
    }

    // Hide normal nav labels
    document.querySelectorAll('.label').forEach(l => l.classList.toggle('hidden', collapsed));

    // Sidebar toggle icon direction
    iconPath.setAttribute('d', collapsed ? 'M9 5l7 7-7 7' : 'M15 5l-7 7 7 7');

    // Shrink / grow the logo container
    if(collapsed) {
        logoContainer.style.width = '2rem';
        logoContainer.style.height = '2rem';
        logoContainer.style.boxShadow = '0 0 10px rgba(37,99,235,0.4)';
        logoText.style.transform = 'scale(0.6)';
        logoText.style.opacity = '0.7';
        label.style.transform = 'scale(0)';
        label.style.opacity = '0';
    } else {
        logoContainer.style.width = '2.5rem';
        logoContainer.style.height = '2.5rem';
        logoContainer.style.boxShadow = '0 0 20px rgba(37,99,235,0.5)';
        logoText.style.transform = 'scale(1)';
        logoText.style.opacity = '1';
        label.style.transform = 'scale(1)';
        label.style.opacity = '1';
    }

    window.dispatchEvent(new Event('resize'));
}

// Apply previous state
let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
applySidebarState(isCollapsed);

// Toggle sidebar
toggleBtn.addEventListener('click', () => {
    isCollapsed = !isCollapsed;
    localStorage.setItem('sidebarCollapsed', isCollapsed);
    applySidebarState(isCollapsed);
});
</script>

<link rel="stylesheet" href="<?php echo htmlspecialchars($sciLabBase); ?>/assets/style.css">