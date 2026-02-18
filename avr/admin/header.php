<?php
$app_root = dirname(__DIR__, 2);
if (!defined('BASE_URL')) {
    require_once $app_root . '/auth/path_config_loader.php';
}
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/security.php';
require_once $app_root . '/auth/admin_auth.php';
$base = rtrim(BASE_URL, '/');
$baseS = $base === '' ? '/' : $base . '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Terminal | AVR</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($baseS); ?>assets/images/32x32.png">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($baseS); ?>assets/images/favicon.ico">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseS); ?>assets/unified-theme.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        /* Enable vertical scrolling for dashboard layout */
        html,
        body {
            height: auto !important;
            overflow-y: auto !important;
        }

        main {
            overflow-y: auto !important;
        }

        .flex.h-screen {
            height: auto !important;
            min-height: 100vh;
        }

        /* Glass Effect with Blue Border Glow */
        .glass-header {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.3);
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(0.8);
            }
        }

        .animate-status {
            animation: pulse-dot 2s infinite;
        }

        [x-cloak] {
            display: none !important;
        }

        /* AVR Floating Modal - z-index above nav (z-100), moved to body to avoid overflow clipping */
        .avr-modal-overlay {
            backdrop-filter: blur(6px);
            animation: avr-modal-fadeIn 0.25s ease-out;
            padding: 1rem;
            box-sizing: border-box;
            z-index: 9999;
        }
        .avr-modal-content {
            animation: avr-modal-floatIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: min(28rem, calc(100vw - 2rem));
            flex-shrink: 0;
        }
        @keyframes avr-modal-fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes avr-modal-floatIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>

<body class="bg-slate-50">

    <nav class="bg-blue-600 sticky top-0 z-[100] shadow-lg">
        <div class="w-full px-6 md:px-8 py-4 flex flex-col md:flex-row items-center justify-between">

            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-blue-600 p-2 rounded-full shadow-lg border-2 border-blue-400" style="height: 3.5rem; width: 3.5rem;">
                    <div class="h-full w-full bg-white rounded-full flex items-center justify-center">
                        <img src="<?php echo htmlspecialchars($baseS); ?>assets/images/school_logo.png" alt="Logo" class="h-full w-full object-contain p-1">
                    </div>
                </div>

                <div class="flex flex-col justify-center">
                    <h1 class="text-xl md:text-2xl font-black text-white leading-tight tracking-tighter uppercase">
                        AVR ADMIN
                    </h1>
                    <p class="text-[10px] md:text-xs text-white font-semibold tracking-widest uppercase">
                        HOLY CROSS OF MINTAL, INC.
                    </p>
                </div>
            </div>

            <ul class="flex flex-col md:flex-row md:space-x-1 items-center mt-4 md:mt-0">
                <!-- Home Button -->
                <li class="mr-2 md:mr-4">
                    <a href="<?php echo htmlspecialchars($base ? $base . '/' : '/'); ?>" class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-all duration-300 group border border-white/20 hover:border-white/40" title="Home">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </a>
                </li>
                
                <li class="relative ml-4 pl-4 border-l-2 border-blue-300" x-data="{ profileOpen: false }" @click.away="profileOpen = false">

                    <button @click="profileOpen = !profileOpen" class="flex items-center space-x-3 focus:outline-none group">
                        <div class="flex flex-col items-end hidden md:flex">
                            <?php 
                            $adminInfo = AdminAuth::getAdminInfo();
                            if ($adminInfo):
                            ?>
                                <span class="text-sm font-black uppercase tracking-wide text-white leading-none group-hover:text-blue-200 transition-colors"><?php echo strtoupper($adminInfo['username']); ?></span>
                                <div class="flex items-center mt-1">
                                    <span class="text-xs font-semibold text-white uppercase tracking-wide"><?php echo htmlspecialchars($adminInfo['name']); ?></span>
                                    <span class="ml-2 w-2 h-2 bg-green-500 rounded-full animate-status shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                                </div>
                            <?php else: ?>
                                <span class="text-sm font-black uppercase tracking-wide text-white leading-none group-hover:text-blue-200 transition-colors">GUEST</span>
                                <div class="flex items-center mt-1">
                                    <span class="text-xs font-semibold text-white uppercase tracking-wide">Not Logged In</span>
                                    <span class="ml-2 w-2 h-2 bg-red-500 rounded-full animate-status shadow-[0_0_8px_rgba(239,68,68,0.8)]"></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="relative p-0.5 rounded-full bg-blue-500 group-hover:bg-blue-500 transition-all">
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center overflow-hidden border-2 border-blue-400 group-hover:border-white shadow-sm">
                                <svg class="w-6 h-6 text-white group-hover:text-blue-100 transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                        </div>
                    </button>

                    <div x-cloak x-show="profileOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                        class="absolute right-0 mt-4 w-56 bg-white border-2 border-blue-200 shadow-xl rounded-2xl z-[110] overflow-hidden">

                        <?php 
                        $adminInfo = AdminAuth::getAdminInfo();
                        if ($adminInfo):
                        ?>
                            <div class="px-5 py-4 bg-blue-50 border-b border-blue-200">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[9px] font-mono text-blue-600 uppercase tracking-widest">Active_Session</span>
                                    <span class="text-[9px] font-mono text-slate-500">v2.0.4</span>
                                </div>
                                <p class="text-xs font-black text-slate-800 uppercase tracking-wider"><?php echo htmlspecialchars($adminInfo['role']); ?></p>
                                <p class="text-[10px] text-slate-600 font-medium truncate mt-0.5"><?php echo htmlspecialchars($adminInfo['email'] ?? 'admin@hcmi.com'); ?></p>
                            </div>

                            <div class="p-2 bg-white">
                                <ul class="space-y-1">
                                    <li>
                                        <a href="<?php echo htmlspecialchars($baseS); ?>account/settings.php" class="nav-loader-link flex items-center px-4 py-2.5 rounded-xl hover:bg-blue-50 text-slate-600 hover:text-blue-700 transition-all group/item">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-3 group-hover/item:bg-blue-200 transition-colors">
                                                <svg class="w-4 h-4 text-blue-600 group-hover/item:text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </div>
                                            <span class="text-[10px] font-bold uppercase tracking-widest">Account Settings</span>
                                        </a>
                                    </li>

                                    <li class="flex items-center my-2">
                                        <div class="flex-grow border-t border-blue-200"></div>
                                        <span class="px-3 text-[8px] font-mono text-slate-400 uppercase tracking-tighter">Emergency</span>
                                        <div class="flex-grow border-t border-blue-200"></div>
                                    </li>

                                    <li>
                                        <a href="<?php echo htmlspecialchars($baseS); ?>admin/logout.php"
                                            class="flex items-center px-4 py-2.5 rounded-xl hover:bg-red-50 text-slate-600 hover:text-red-600 transition-all group/item">
                                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center mr-3 group-hover/item:bg-red-200 transition-colors">
                                                <svg class="w-4 h-4 text-red-600 group-hover/item:text-red-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-widest">LOG OUT</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="px-5 py-4 bg-red-50 border-b border-red-200">
                                <p class="text-xs font-black text-red-800 uppercase tracking-wider">Not Logged In</p>
                                <p class="text-[10px] text-red-600 font-medium mt-0.5">Please login to access admin features</p>
                            </div>

                            <div class="p-2 bg-white">
                                <ul class="space-y-1">
                                    <li>
                                        <a href="<?php echo htmlspecialchars($baseS); ?>login.php?type=admin" class="flex items-center px-4 py-2.5 rounded-xl hover:bg-blue-50 text-slate-600 hover:text-blue-700 transition-all group/item">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-3 group-hover/item:bg-blue-200 transition-colors">
                                                <svg class="w-4 h-4 text-blue-600 group-hover/item:text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                            </div>
                                            <span class="text-[10px] font-bold uppercase tracking-widest">LOGIN</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="px-5 py-2 bg-blue-50 flex justify-between items-center border-t border-blue-200">
                            <span class="text-[8px] font-mono text-green-600 uppercase">System: Online</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <?php msms_render_csrf_auto_form_script(); ?>
