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
include "../partials/header.php"; 
?>

<div class="relative min-h-screen w-full overflow-y-auto overflow-x-hidden flex flex-col justify-start items-center pt-16 md:pt-12 custom-scrollbar">

    <?php include "../partials/lab_layout.php"; ?>

    <div class="relative z-10 w-full flex flex-col items-center text-center px-4 pb-24">
        <div class="mb-8 space-y-4 flex flex-col items-center">
            <div class="inline-flex items-center gap-3 px-6 py-2.5 rounded-2xl bg-black/70 backdrop-blur-md text-white border-2 border-white/30 text-sm font-black uppercase tracking-[0.3em] animate-fade-in shadow-lg drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" /></svg>
                Queue Management Terminal
            </div>

            <h1 class="text-7xl md:text-9xl font-black tracking-tighter text-white leading-none animate-fade-in-up drop-shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
                Queue <br class="md:hidden"> <span class="text-violet-500 drop-shadow-[0_0_25px_rgba(139,92,246,0.6)]">System</span>
            </h1>

            <p class="text-xl md:text-2xl text-slate-300 font-medium max-w-2xl leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s;">
                Efficient customer queuing and counter management system.
            </p>
        </div>

        <div class="flex flex-col items-center gap-10 w-full animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="flex flex-col items-center gap-5 w-full max-w-md">
                <?php $getQueueHref = getButtonHref('counter.php', 'student'); ?>
                <a href="<?php echo $getQueueHref; ?>"
                    class="group relative flex flex-col h-full min-h-[200px] w-full p-1 bg-gradient-to-br from-violet-400 via-purple-500 to-fuchsia-600 rounded-[2rem] shadow-[0_0_40px_rgba(139,92,246,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-violet-400/50">
                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight">Get Queue</span>
                            <span class="text-violet-300 text-xs font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Get your queue number</span>
                        </div>
                        <div class="flex justify-end w-full mt-4">
                            <div class="bg-violet-500/20 p-3 rounded-2xl border border-violet-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:translate-x-2" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" /></svg>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Queue Dashboard
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
