<?php
if (!defined('BASE_URL')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
$sciLabBase = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/') . 'sciLab';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Science Laboratory | HCMI</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($sciLabBase); ?>/assets/images/32x32.png">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($sciLabBase); ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($sciLabBase); ?>/assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        .glass-nav {
            background: rgba(29, 78, 216, 0.85);
            /* blue-700 with opacity */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Subtle animated underline for nav links */
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 50%;
            background: #cbd5e1;
            /* slate-300 */
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>

<body class="min-h-screen overflow-hidden">

    <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-indigo-500 to-teal-400"></div>

    <nav class="glass-nav sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-3 flex items-center justify-between">

            <a href="<?php echo htmlspecialchars($sciLabBase); ?>/" class="group flex items-center space-x-4 transition-transform duration-300 active:scale-95">
                <div class="relative flex-shrink-0 bg-white p-1.5 rounded-xl shadow-lg transform group-hover:rotate-3 transition-transform duration-300" style="height: 3.8rem; width: 3.8rem;">
                    <img src="<?php echo htmlspecialchars($sciLabBase); ?>/assets/images/school_logo.png"
                        alt="HCMI Logo"
                        class="h-full w-full object-contain">
                </div>

                <div class="flex flex-col">
                    <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-tight leading-none">
                        Science <span class="text-blue-200">Laboratory</span>
                    </h1>
                    <div class="flex items-center mt-1">
                        <span class="h-[1px] w-4 bg-blue-300 mr-2"></span>
                        <p class="text-[10px] md:text-xs font-bold text-blue-100 uppercase tracking-[0.15em]">
                            Holy Cross of Mintal, Inc.
                        </p>
                    </div>
                </div>
            </a>

            <div class="flex items-center space-x-4 md:space-x-8">

                <ul class="hidden md:flex space-x-8 text-white font-semibold text-sm uppercase tracking-widest">
                    <li>
                        <a href="<?php echo htmlspecialchars($sciLabBase); ?>/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="https://hcmintal.edu.ph/about-us/" class="nav-link relative py-1 hover:text-blue-100 transition-colors">
                            About Us
                        </a>
                    </li>

                </ul>

                <!-- Profile Dropdown -->
                <div class="relative" id="profileMenu">

                    <!-- Button -->
                    <button onclick="toggleProfile()"
                        class="flex items-center space-x-3 bg-white/10 hover:bg-white/20
               px-3 py-2 rounded-xl transition focus:outline-none">

                        <!-- Avatar -->
                        <div class="h-9 w-9 rounded-full bg-white text-blue-700
                    flex items-center justify-center font-extrabold">
                            G
                        </div>

                        <!-- Name -->
                        <span class="hidden md:block text-white font-semibold text-sm">
                            Guest User
                        </span>

                        <!-- Arrow -->
                        <svg class="h-4 w-4 text-white opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div id="profileDropdown"
                        class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-xl overflow-hidden">

                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-bold text-slate-800">Guest User</p>
                            <p class="text-xs text-slate-500">Science Lab User</p>
                        </div>

                        <a href="<?php echo htmlspecialchars($sciLabBase); ?>/user/profile.php"
                            class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100">
                            Profile
                        </a>

                        <a href="<?php echo htmlspecialchars($sciLabBase); ?>/logout.php"
                            class="block px-4 py-3 text-sm text-red-600 hover:bg-red-50 font-semibold">
                            Logout
                        </a>
                    </div>
                </div>

                <a href="<?php echo htmlspecialchars($sciLabBase); ?>/" class="md:hidden p-2 text-white bg-white/10 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </a>
            </div>

        </div>
    </nav>