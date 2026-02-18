<?php 
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/portal_helper.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_helper.php';
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}
$isStudent = function_exists('isStudentLoggedIn') ? isStudentLoggedIn() : false;
include "../partials/header.php"; 
?>

<div class="relative min-h-screen w-full overflow-y-auto overflow-x-hidden flex flex-col justify-start items-center pt-16 md:pt-12 custom-scrollbar">

    <?php include "../partials/lab_layout.php"; ?>

    <div class="relative z-10 w-full flex flex-col items-center text-center px-4 pb-24">
        <div class="mb-8 space-y-4 flex flex-col items-center">
            <div class="inline-flex items-center gap-3 px-6 py-2.5 rounded-2xl bg-black/70 backdrop-blur-md text-white border-2 border-white/30 text-sm font-black uppercase tracking-[0.3em] animate-fade-in shadow-lg drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                Library Management Portal
            </div>

            <h1 class="text-7xl md:text-9xl font-black tracking-tighter text-white leading-none animate-fade-in-up drop-shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
                Library <br class="md:hidden"> <span class="text-emerald-500 drop-shadow-[0_0_25px_rgba(16,185,129,0.6)]">System</span>
            </h1>

            <p class="text-xl md:text-2xl text-slate-300 font-medium max-w-2xl leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s;">
                Comprehensive book management, borrowing, and transaction tracking system.
            </p>
        </div>

        <div class="flex flex-col items-center gap-10 w-full animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 w-full max-w-3xl">
                <?php if (!$isStudent): $transactionsHref = getButtonHref('transactions.php', 'admin'); ?>
                <a href="<?php echo $transactionsHref; ?>"
                    class="group relative flex flex-col h-full min-h-[200px] p-1 bg-gradient-to-br from-blue-400 via-indigo-500 to-purple-600 rounded-[2rem] shadow-[0_0_40px_rgba(59,130,246,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-blue-400/40">
                    <div class="w-full flex-1 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2">Transactions</span>
                            <span class="text-blue-300 text-xs font-bold uppercase tracking-widest opacity-80">Borrow & return books</span>
                        </div>
                        <div class="flex justify-end w-full">
                            <div class="bg-blue-500/20 p-3 rounded-2xl border border-blue-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:-translate-y-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18" /></svg>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
                <?php $logbookHref = 'lib_logbok.php'; ?>
                <a href="<?php echo $logbookHref; ?>"
                    class="group relative flex flex-col h-full min-h-[200px] p-1 bg-gradient-to-br from-amber-400 via-yellow-500 to-orange-600 rounded-[2rem] shadow-[0_0_40px_rgba(251,191,36,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-amber-400/40">
                    <div class="w-full flex-1 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2">Log Book</span>
                            <span class="text-amber-300 text-xs font-bold uppercase tracking-widest opacity-80">Track usage & records</span>
                        </div>
                        <div class="flex justify-end w-full">
                            <div class="bg-amber-500/20 p-3 rounded-2xl border border-amber-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:-translate-y-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <?php
            require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_helper.php';
            if (isAdminLoggedIn()):
            ?>
            <a href="index.php"
                class="flex items-center gap-4 px-12 py-5 bg-black/70 backdrop-blur-md text-white border-2 border-white/30 rounded-2xl font-black uppercase tracking-[0.3em] text-sm transition-all duration-300 hover:bg-black/80 hover:border-blue-400/60 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Library Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<a href="../index.php" class="fixed bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2 px-5 py-3 text-sm font-bold text-white bg-white/20 hover:bg-white/30 backdrop-blur-md border-2 border-white/50 rounded-xl transition-all shadow-lg z-40">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
    Back to Main Portal
</a>

<div class="fixed bottom-12 left-12 text-[12px] font-mono font-black uppercase tracking-[1em] text-white/40 hidden lg:block origin-left -rotate-90 pointer-events-none">
    Holy Cross of Mintal, Inc.
</div>

<?php include "../partials/footer.php"; ?>
