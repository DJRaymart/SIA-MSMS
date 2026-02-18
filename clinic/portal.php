<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/portal_helper.php';
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_helper.php';
if (isAdminLoggedIn()) {
    header('Location: admin/admin.php');
    exit;
}
include $app_root . '/partials/header.php';
?>

<div class="relative min-h-screen w-full overflow-y-auto overflow-x-hidden flex flex-col justify-start items-center pt-16 md:pt-12 custom-scrollbar">

    <?php include $app_root . '/partials/lab_layout.php'; ?>

    <div class="relative z-10 w-full flex flex-col items-center text-center px-4 pb-24">
        <div class="mb-8 space-y-4 flex flex-col items-center">
            <div class="inline-flex items-center gap-3 px-6 py-2.5 rounded-2xl bg-black/70 backdrop-blur-md text-white border-2 border-white/30 text-sm font-black uppercase tracking-[0.3em] animate-fade-in shadow-lg drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-3.75 3.75V8.443c0-1.096.21-2.16.603-3.108A48.3 48.3 0 0 1 12 3.493c1.015 0 2.02.14 2.997.403a55.378 55.378 0 0 1-.603 3.108ZM18.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443" /></svg>
                Clinic Management Portal
            </div>

            <h1 class="text-7xl md:text-9xl font-black tracking-tighter text-white leading-none animate-fade-in-up drop-shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
                Clinic <br class="md:hidden"> <span class="text-teal-500 drop-shadow-[0_0_25px_rgba(20,184,166,0.6)]">System</span>
            </h1>

            <p class="text-xl md:text-2xl text-slate-300 font-medium max-w-2xl leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s;">
                Record complaints, treatments, and visit logs for students.
            </p>
        </div>

        <div class="flex flex-col items-center gap-10 w-full animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 w-full max-w-3xl">
                <?php $clinicFormHref = 'clinic_form.php'; $clinicLogHref = 'clinic_logbook.php'; ?>
                <a href="<?php echo $clinicFormHref; ?>"
                    class="group relative flex flex-col h-full min-h-[200px] p-1 bg-gradient-to-br from-teal-400 via-cyan-500 to-sky-600 rounded-[2rem] shadow-[0_0_40px_rgba(20,184,166,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-teal-400/50">
                    <div class="w-full flex-1 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2">Clinic Form</span>
                            <span class="text-teal-300 text-xs font-bold uppercase tracking-widest opacity-80">Record complaint & treatment</span>
                        </div>
                        <div class="flex justify-end w-full">
                            <div class="bg-teal-500/20 p-3 rounded-2xl border border-teal-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:translate-x-2" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo $clinicLogHref; ?>"
                    class="group relative flex flex-col h-full min-h-[200px] p-1 bg-gradient-to-br from-cyan-400 via-sky-500 to-blue-600 rounded-[2rem] shadow-[0_0_40px_rgba(6,182,212,0.3)] transition-all duration-500 hover:scale-[1.02] hover:shadow-cyan-400/40">
                    <div class="w-full flex-1 py-8 px-6 rounded-[1.9rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2">Clinic Log Book</span>
                            <span class="text-cyan-300 text-xs font-bold uppercase tracking-widest opacity-80">Log visit time</span>
                        </div>
                        <div class="flex justify-end w-full">
                            <div class="bg-cyan-500/20 p-3 rounded-2xl border border-cyan-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:-translate-y-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <?php
            if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
            require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/admin_helper.php';
            if (isAdminLoggedIn()):
            ?>
            <a href="admin/admin.php"
                class="flex items-center gap-4 px-12 py-5 bg-black/70 backdrop-blur-md text-white border-2 border-white/30 rounded-2xl font-black uppercase tracking-[0.3em] text-sm transition-all duration-300 hover:bg-black/80 hover:border-blue-400/60 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Clinic Admin
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

<?php include $app_root . '/partials/footer.php'; ?>
