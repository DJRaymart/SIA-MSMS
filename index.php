<?php 
$sessionPath = file_exists(__DIR__ . '/auth/session_init.php') 
    ? __DIR__ . '/auth/session_init.php'
    : (file_exists(__DIR__ . '/auth/session_init.php') 
        ? __DIR__ . '/auth/session_init.php'
        : null);

if ($sessionPath) {
    require_once $sessionPath;
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

include "partials/header.php"; 
?>

<div class="relative min-h-screen w-full overflow-y-auto overflow-x-hidden flex flex-col justify-start items-center pt-16 md:pt-12 custom-scrollbar">

    <?php include "partials/lab_layout.php"; ?>

    <div class="relative z-10 w-full flex flex-col items-center text-center px-4 pb-24 overflow-x-visible">

        <div class="mb-10 space-y-4 flex flex-col items-center text-center">
            <div class="inline-block px-6 py-2 rounded-full bg-black/70 backdrop-blur-md border border-blue-400/50 text-white text-sm font-black uppercase tracking-[0.3em] animate-fade-in shadow-lg">
                Mintal School Management System
            </div>
            <h1 class="text-7xl md:text-9xl font-black tracking-tighter text-white leading-none animate-fade-in-up drop-shadow-[0_10px_40px_rgba(0,0,0,0.8)]">
                MSMS <br class="md:hidden"> <span class="text-blue-400 drop-shadow-[0_0_30px_rgba(59,130,246,0.8)]">Portal</span>
            </h1>
            <p class="text-xl md:text-2xl text-white/90 font-medium max-w-2xl leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s;">
                Unified management system for Science Laboratory, Library, AVR, Queue, ICT Office, and Clinic operations.
            </p>
        </div>

        <div class="flex flex-col items-center gap-10 w-full animate-fade-in-up" style="animation-delay: 0.2s;">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 w-full max-w-[95rem] px-4 items-stretch">
                
                <a href="sciLab/portal.php"
                    class="group relative flex flex-col h-full min-h-[220px] p-1 bg-gradient-to-br from-blue-400 via-indigo-500 to-purple-600 rounded-[2.5rem] shadow-[0_0_40px_rgba(59,130,246,0.3)] transition-all duration-500 hover:scale-[1.03] hover:shadow-blue-400/50">

                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[2.4rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left w-full mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight break-words">Science Lab</span>
                            <span class="text-blue-300 text-xs md:text-sm font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Inventory & Reservations</span>
                        </div>

                        <div class="flex justify-end w-full">
                            <div class="bg-blue-500/20 p-3 rounded-2xl border border-blue-400/30 backdrop-blur-md">
                            <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:translate-x-2" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232 1.232 3.228 0 4.46s-3.228 1.232-4.46 0L15.3 19.8m-3.3-3.3l-1.402-1.402c-1.232-1.232-1.232-3.228 0-4.46s3.228-1.232 4.46 0L15.3 8.7m-3.3-3.3l-1.402-1.402c-1.232-1.232-1.232-3.228 0-4.46s3.228-1.232 4.46 0L15.3 8.7" />
                            </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="library/portal.php"
                    class="group relative flex flex-col h-full min-h-[220px] p-1 bg-gradient-to-br from-emerald-400 via-teal-500 to-blue-600 rounded-[2.5rem] shadow-[0_0_40px_rgba(16,185,129,0.2)] transition-all duration-500 hover:scale-[1.03] hover:shadow-emerald-400/40">

                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[2.4rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left w-full mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight break-words">Library System</span>
                            <span class="text-emerald-300 text-xs md:text-sm font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Books & Transactions</span>
                        </div>

                        <div class="flex justify-end w-full">
                            <div class="bg-emerald-500/20 p-3 rounded-2xl border border-emerald-400/30 backdrop-blur-md">
                            <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:-translate-y-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="avr/portal.php"
                    class="group relative flex flex-col h-full min-h-[220px] p-1 bg-gradient-to-br from-pink-400 via-rose-500 to-orange-600 rounded-[2.5rem] shadow-[0_0_40px_rgba(236,72,153,0.3)] transition-all duration-500 hover:scale-[1.03] hover:shadow-pink-400/40">

                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[2.4rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left w-full mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight break-words">AVR System</span>
                            <span class="text-pink-300 text-xs md:text-sm font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Audio-Visual Resources</span>
                        </div>

                        <div class="flex justify-end w-full">
                            <div class="bg-pink-500/20 p-3 rounded-2xl border border-pink-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:rotate-12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="queue/portal.php"
                    class="group relative flex flex-col h-full min-h-[220px] p-1 bg-gradient-to-br from-violet-400 via-purple-500 to-fuchsia-600 rounded-[2.5rem] shadow-[0_0_40px_rgba(139,92,246,0.3)] transition-all duration-500 hover:scale-[1.03] hover:shadow-violet-400/40">

                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[2.4rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left w-full mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight break-words">Queue System</span>
                            <span class="text-violet-300 text-xs md:text-sm font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Customer Queuing</span>
                        </div>

                        <div class="flex justify-end w-full">
                            <div class="bg-violet-500/20 p-3 rounded-2xl border border-violet-400/30 backdrop-blur-md">
                            <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                            </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="ictOffice/portal.php"
                    class="group relative flex flex-col h-full min-h-[220px] p-1 bg-gradient-to-br from-cyan-400 via-blue-500 to-indigo-600 rounded-[2.5rem] shadow-[0_0_40px_rgba(34,211,238,0.3)] transition-all duration-500 hover:scale-[1.03] hover:shadow-cyan-400/40">

                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[2.4rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left w-full mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight break-words">ICT Office</span>
                            <span class="text-cyan-300 text-xs md:text-sm font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Logbook & Records</span>
                        </div>

                        <div class="flex justify-end w-full">
                            <div class="bg-cyan-500/20 p-3 rounded-2xl border border-cyan-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:rotate-12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h69.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="clinic/portal.php"
                    class="group relative flex flex-col h-full min-h-[220px] p-1 bg-gradient-to-br from-teal-400 via-cyan-500 to-sky-600 rounded-[2.5rem] shadow-[0_0_40px_rgba(20,184,166,0.3)] transition-all duration-500 hover:scale-[1.03] hover:shadow-teal-400/40">

                    <div class="w-full flex-1 min-h-0 py-8 px-6 rounded-[2.4rem] bg-[#0f172a]/90 backdrop-blur-xl border border-white/10 flex flex-col justify-between overflow-hidden">
                        <div class="flex flex-col items-start text-left w-full mb-4">
                            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-tighter mb-2 leading-tight break-words">Clinic System</span>
                            <span class="text-teal-300 text-xs md:text-sm font-bold uppercase tracking-[0.2em] opacity-80 leading-relaxed">Clinic Form & Logbook</span>
                        </div>

                        <div class="flex justify-end w-full">
                            <div class="bg-teal-500/20 p-3 rounded-2xl border border-teal-400/30 backdrop-blur-md">
                                <svg class="w-7 h-7 text-white transition-transform duration-500 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-3.75 3.75V8.443c0-1.096.21-2.16.603-3.108A48.3 48.3 0 0 1 12 3.493c1.015 0 2.02.14 2.997.403a55.378 55.378 0 0 1-.603 3.108ZM18.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m3.75 3.75V8.443c0-1.096.21-2.16.603-3.108A48.3 48.3 0 0 1 12 3.493c1.015 0 2.02.14 2.997.403a55.378 55.378 0 0 1-.603 3.108Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

            </div>

            <?php
            $adminHelperPath = file_exists(__DIR__ . '/auth/admin_helper.php') 
                ? __DIR__ . '/auth/admin_helper.php'
                : null;

            if ($adminHelperPath) {
                require_once $adminHelperPath;
                $isAdmin = isAdminLoggedIn();
            } else {
                $isAdmin = false;
            }
            ?>
       <?php
       $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
       $baseS = $base === '' ? '/' : $base . '/';
       if ($isAdmin): ?>
       <a href="<?php echo htmlspecialchars($baseS); ?>admin/dashboard.php"
           class="flex items-center gap-4 px-12 py-5 bg-black/70 backdrop-blur-md text-white border-2 border-white/30 rounded-2xl font-black uppercase tracking-[0.3em] text-sm transition-all duration-300 hover:bg-black/80 hover:border-blue-400/60 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
           </svg>
           Admin Dashboard
       </a>
       <?php else: ?>
       <a href="<?php echo htmlspecialchars($baseS); ?>login.php"
           class="flex items-center gap-4 px-12 py-5 bg-black/70 backdrop-blur-md text-white border-2 border-white/30 rounded-2xl font-black uppercase tracking-[0.3em] text-sm transition-all duration-300 hover:bg-black/80 hover:border-blue-400/60 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
           </svg>
           Login
       </a>
       <?php endif; ?>
        </div>
    </div>
</div>

<div class="fixed bottom-12 left-12 text-[12px] font-mono font-black uppercase tracking-[1em] text-white/70 drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)] hidden lg:block origin-left -rotate-90 pointer-events-none">
    Holy Cross of Mintal, Inc.
</div>

<?php include "partials/footer.php"; ?>
