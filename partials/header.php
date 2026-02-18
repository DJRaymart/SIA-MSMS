<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/auth/path_config_loader.php';
}
require_once dirname(__DIR__) . '/auth/security.php';
$base = rtrim(BASE_URL, '/');
$baseS = $base === '' ? '/' : $base . '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Mintal School Management System | HCMI</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($baseS); ?>assets/images/32x32.png">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($baseS); ?>assets/images/favicon.ico">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseS); ?>assets/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        .glass-nav {
            background: rgba(29, 78, 216, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 50%;
            background: #cbd5e1;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }
        
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">

    <nav class="glass-nav sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-3 flex items-center justify-between">

            <a href="<?php echo htmlspecialchars($base); ?>/" class="group flex items-center space-x-4 transition-transform duration-300 active:scale-95">
                <div class="relative flex-shrink-0 bg-white p-1.5 rounded-xl shadow-lg transform group-hover:rotate-3 transition-transform duration-300" style="height: 3.8rem; width: 3.8rem;">
                    <img src="<?php echo htmlspecialchars($baseS); ?>assets/images/school_logo.png"
                        alt="HCMI Logo"
                        class="h-full w-full object-contain">
                </div>

                <div class="flex flex-col">
                    <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-tight leading-none">
                        MSMS <span class="text-blue-200">Portal</span>
                    </h1>
                    <div class="flex items-center mt-1">
                        <span class="h-[1px] w-4 bg-blue-300 mr-2"></span>
                        <p class="text-[10px] md:text-xs font-bold text-blue-100 uppercase tracking-[0.15em]">
                            Holy Cross of Mintal, Inc.
                        </p>
                    </div>
                </div>
            </a>

            <div class="flex items-center space-x-2 md:space-x-8">
                <ul class="hidden md:flex space-x-8 text-white font-semibold text-sm uppercase tracking-widest">
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/sciLab/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            Science Lab
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/library/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            Library
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/avr/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            AVR
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/queue/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            Queue
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/ictOffice/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            ICT
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($base); ?>/clinic/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            Clinic
                        </a>
                    </li>
                </ul>

                <?php

                $basePath = dirname(__DIR__); 
                
                $sessionPath = file_exists($basePath . '/auth/session_init.php') 
                    ? $basePath . '/auth/session_init.php'
                    : null;
                
                $adminHelperPath = file_exists($basePath . '/auth/admin_helper.php') 
                    ? $basePath . '/auth/admin_helper.php'
                    : null;
                
                $studentHelperPath = file_exists($basePath . '/auth/student_helper.php') 
                    ? $basePath . '/auth/student_helper.php'
                    : null;
                
                $adminAuthPath = file_exists($basePath . '/auth/admin_auth.php') 
                    ? $basePath . '/auth/admin_auth.php'
                    : null;
                
                $studentAuthPath = file_exists($basePath . '/auth/student_auth.php') 
                    ? $basePath . '/auth/student_auth.php'
                    : null;
                
                if ($sessionPath) {
                    require_once $sessionPath;
                } else {
                    
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                }
                
                if ($adminHelperPath) {
                    require_once $adminHelperPath;
                }
                
                if ($adminAuthPath) {
                    require_once $adminAuthPath;
                }
                
                if ($studentHelperPath) {
                    require_once $studentHelperPath;
                }
                
                if ($studentAuthPath) {
                    require_once $studentAuthPath;
                }
                
                $isAdmin = function_exists('isAdminLoggedIn') ? isAdminLoggedIn() : false;
                $isStudent = function_exists('isStudentLoggedIn') ? isStudentLoggedIn() : false;
                $isLoggedIn = $isAdmin || $isStudent;
                
                if ($isLoggedIn):
                    if ($isAdmin && class_exists('AdminAuth')) {
                        $userInfo = AdminAuth::getAdminInfo();
                    } else if ($isStudent && function_exists('getStudentInfo')) {
                        $userInfo = getStudentInfo();
                    } else {
                        $userInfo = ['name' => 'User'];
                    }
                    $userName = $isAdmin ? ($userInfo['name'] ?? 'Admin') : ($userInfo['name'] ?? 'Student');
                    $userType = $isAdmin ? 'Admin' : 'Student';
                ?>
                <!-- User Menu Dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }" @click.away="userMenuOpen = false">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 text-white hover:text-blue-100 transition-colors focus:outline-none group">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center border border-white/30 group-hover:bg-white/30 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="hidden md:block text-sm font-semibold uppercase tracking-wide"><?php echo htmlspecialchars($userName); ?></span>
                        <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': userMenuOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-200">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide"><?php echo $userType; ?></p>
                            <p class="text-sm font-bold text-slate-900 mt-1"><?php echo htmlspecialchars($userName); ?></p>
                        </div>
                        <a href="<?php echo htmlspecialchars($base); ?>/account/settings.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Account Settings
                        </a>
                        <?php if ($isAdmin): ?>
                        <a href="<?php echo htmlspecialchars($baseS); ?>admin/logout.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-red-50 hover:text-red-700 transition-colors logout-btn">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                        <?php else: ?>
                        <a href="<?php echo htmlspecialchars($baseS); ?>student/logout.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-red-50 hover:text-red-700 transition-colors logout-btn">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?php echo htmlspecialchars($base); ?>/login.php" class="flex items-center space-x-2 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span class="hidden md:block text-sm font-semibold uppercase tracking-wide">Login</span>
                </a>
                <?php endif; ?>

                <a href="<?php echo htmlspecialchars($base ? $base . '/' : '/'); ?>" class="md:hidden p-2 text-white bg-white/10 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </a>
            </div>

        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">
    <?php msms_render_csrf_auto_form_script(); ?>
