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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>
                Audio-Visual Resources Portal
            </div>

            <h1 class="text-7xl md:text-9xl font-black tracking-tighter text-white leading-none animate-fade-in-up drop-shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
                AVR <br class="md:hidden"> <span class="text-pink-500 drop-shadow-[0_0_25px_rgba(236,72,153,0.6)]">System</span>
            </h1>

            <p class="text-xl md:text-2xl text-slate-300 font-medium max-w-2xl leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s;">
                Reservations and attendance tracking for audio-visual resources.
            </p>
        </div>

        <div class="flex flex-col items-center gap-10 w-full animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 w-full max-w-3xl">
                <?php $logbookHref = 'avr_logbook.php'; ?>
                <a href="<?php echo $logbookHref; ?>"
                    class="group relative flex flex-col h-full min-h-[200px] p-1 bg-gradient-to-br from-pink-400 via-rose-500 to-orange-600 rounded-[2rem] shadow-[0_0_40px_rgba(236,72,153,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-pink-400/50">
                    <div class="w-full flex-1 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2">Log Attendance</span>
                            <span class="text-pink-300 text-xs font-bold uppercase tracking-widest opacity-80">Scan ID or RFID</span>
                        </div>
                        <div class="flex justify-end w-full">
                            <div class="bg-pink-500/20 p-3 rounded-2xl border border-pink-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:-translate-y-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="modules/reservation.php"
                    class="group relative flex flex-col h-full min-h-[200px] p-1 bg-gradient-to-br from-orange-400 via-red-500 to-pink-600 rounded-[2rem] shadow-[0_0_40px_rgba(251,146,60,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-orange-400/40">
                    <div class="w-full flex-1 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2">Reservations</span>
                            <span class="text-orange-300 text-xs font-bold uppercase tracking-widest opacity-80">Book AVR rooms</span>
                        </div>
                        <div class="flex justify-end w-full">
                            <div class="bg-orange-500/20 p-3 rounded-2xl border border-orange-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:-translate-y-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
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
                AVR Dashboard
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
