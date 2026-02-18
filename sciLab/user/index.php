<?php
if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
include (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/sciLab/user/header.php';
?>
<?php $userName = "Guest User"; ?>

<style>
    .no-scrollbar::-webkit-scrollbar { width: 0; }
    .no-scrollbar { scrollbar-width: none; }
</style>
<link rel="stylesheet" href="/sciLab/assets/style.css">
<div class="relative h-screen w-full overflow-hidden flex flex-col">

    <?php include "../partials/lab_layout.php"; ?>

    <!-- SCROLL SNAP CONTAINER -->
    <section
        class="relative flex-1 overflow-y-scroll no-scrollbar scroll-smooth
               snap-y snap-mandatory
               bg-gradient-to-br from-[#020617] via-[#020617] to-[#020617]
               text-white">

        <!-- ================= PAGE 1 : WELCOME TERMINAL ================= -->
        <div class="snap-start min-h-screen flex items-center justify-center px-6 relative">

            <!-- Glow blobs -->
            <div class="absolute -top-32 -right-32 h-96 w-96 bg-blue-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 h-96 w-96 bg-indigo-500/20 rounded-full blur-3xl"></div>

            <div class="relative z-10 text-center max-w-4xl space-y-6">

                <div class="inline-flex items-center gap-3 px-6 py-2.5 rounded-2xl bg-black/70 backdrop-blur-md text-white border-2 border-white/30 text-sm font-black uppercase tracking-[0.3em] shadow-lg drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Digital Attendance Terminal
                </div>

                <h1 class="text-6xl md:text-8xl font-black tracking-tight leading-none">
                    Welcome Back,<br>
                    <span class="text-blue-500 drop-shadow-[0_0_30px_rgba(59,130,246,0.6)]">
                        <?php echo htmlspecialchars($userName); ?>
                    </span>
                </h1>

                <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto">
                    Access your laboratory attendance, booking requests,
                    and session history inside the Science Laboratory System.
                </p>

                <span class="block text-xs uppercase tracking-[0.3em] text-slate-400 mt-10">
                    Scroll to continue ↓
                </span>
            </div>
        </div>

        <!-- ================= PAGE 2 : BOOKING HISTORY ================= -->
        <div class="snap-start min-h-screen flex items-center justify-center px-6">

            <div class="w-full max-w-6xl p-1 rounded-[2.5rem]
                        bg-gradient-to-br from-emerald-400 via-teal-500 to-blue-600">

                <div class="rounded-[2.4rem] bg-[#020617]/90 backdrop-blur-xl
                            border border-white/10 p-10">

                    <h2 class="text-3xl md:text-4xl font-black text-emerald-400 mb-2">
                        Booking Request History
                    </h2>
                    <p class="text-slate-400 mb-8">
                        All laboratory reservation requests you submitted
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 text-xs uppercase text-slate-400">
                                    <th class="py-4 px-4">Date</th>
                                    <th class="py-4 px-4">Laboratory</th>
                                    <th class="py-4 px-4">Time</th>
                                    <th class="py-4 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                    <td class="py-4 px-4 font-semibold">Jan 28, 2026</td>
                                    <td class="py-4 px-4">Physics Lab</td>
                                    <td class="py-4 px-4">9:00 – 11:00</td>
                                    <td class="py-4 px-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                                     bg-yellow-500/20 text-yellow-400">
                                            Pending
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <!-- ================= PAGE 3 : ATTENDANCE LOGS ================= -->
        <div class="snap-start min-h-screen flex items-center justify-center px-6">

            <div class="w-full max-w-6xl p-1 rounded-[2.5rem]
                        bg-gradient-to-br from-blue-400 via-indigo-500 to-purple-600">

                <div class="rounded-[2.4rem] bg-[#020617]/90 backdrop-blur-xl
                            border border-white/10 p-10">

                    <h2 class="text-3xl md:text-4xl font-black text-blue-400 mb-2">
                        Attendance Logs
                    </h2>
                    <p class="text-slate-400 mb-8">
                        Your recorded laboratory sessions
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 text-xs uppercase text-slate-400">
                                    <th class="py-4 px-4">Date</th>
                                    <th class="py-4 px-4">Laboratory</th>
                                    <th class="py-4 px-4">Time In</th>
                                    <th class="py-4 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                    <td class="py-4 px-4 font-semibold">Jan 30, 2026</td>
                                    <td class="py-4 px-4">Chemistry Lab</td>
                                    <td class="py-4 px-4">8:05 AM</td>
                                    <td class="py-4 px-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                                     bg-emerald-500/20 text-emerald-400">
                                            Present
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </section>
</div>

