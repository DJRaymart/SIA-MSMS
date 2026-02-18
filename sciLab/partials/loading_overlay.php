<div id="initOverlay" class="fixed inset-0 z-[100] bg-[#020617] hidden flex flex-col items-center justify-center overflow-hidden">

    <div class="fixed inset-0 -z-30 pointer-events-none">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_#0f172a_0%,_#020617_100%)]"></div>
        <div class="absolute inset-0 opacity-30 mix-blend-screen animate-nebula-float bg-[radial-gradient(ellipse_at_top_left,_rgba(59,130,246,0.3)_0%,transparent_70%),radial-gradient(ellipse_at_bottom_right,_rgba(79,70,229,0.3)_0%,transparent_70%)]"></div>
        <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>

        <div class="absolute top-8 left-8 border-t-2 border-l-2 border-blue-500/30 w-12 h-12 md:w-20 md:h-20"></div>
        <div class="absolute top-8 right-8 border-t-2 border-r-2 border-blue-500/30 w-12 h-12 md:w-20 md:h-20"></div>
        <div class="absolute bottom-8 left-8 border-b-2 border-l-2 border-blue-500/30 w-12 h-12 md:w-20 md:h-20"></div>
        <div class="absolute bottom-8 right-8 border-b-2 border-r-2 border-blue-500/30 w-12 h-12 md:w-20 md:h-20"></div>
    </div>

    <div class="relative z-10 flex flex-col items-center justify-center w-full max-w-xl px-8 text-center">

        <div class="relative mb-16 flex items-center justify-center">
            <div class="absolute w-52 h-52 md:w-64 md:h-64 border-2 border-blue-400/10 rounded-full"></div>
            <div class="absolute w-52 h-52 md:w-64 md:h-64 border-t-2 border-blue-500/40 rounded-full animate-spin-linear-slow"></div>
            <div class="absolute w-36 h-36 md:w-48 md:h-48 border border-dashed border-blue-400/30 rounded-full animate-spin-linear-reverse"></div>

            <div class="relative flex items-center justify-center">
                <div class="absolute w-28 h-28 md:w-32 md:h-32 border-t-2 border-blue-400/60 border-r-transparent rounded-full animate-spin-fast"></div>

                <div class="relative flex items-center justify-center w-24 h-24 scale-75 md:scale-100">
                    <div class="absolute inset-0 bg-blue-500/40 blur-3xl rounded-full animate-pulse scale-150"></div>
                    
                    <div class="relative w-6 h-6 bg-white rounded-full shadow-[0_0_20px_rgba(255,255,255,0.8),0_0_40px_rgba(59,130,246,0.6)] z-30 animate-atom-play"></div>
                    
                    <div class="absolute w-24 h-24 border border-blue-400/40 rounded-full rotate-[60deg] animate-orbit-a">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-cyan-300 rounded-full shadow-[0_0_10px_#67e8f9]"></div>
                    </div>
                    <div class="absolute w-24 h-24 border border-blue-400/40 rounded-full rotate-[-60deg] animate-orbit-b">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-blue-300 rounded-full shadow-[0_0_10px_#93c5fd]"></div>
                    </div>
                    <div class="absolute w-24 h-24 border border-blue-400/40 rounded-full rotate-[180deg] animate-orbit-c">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-indigo-300 rounded-full shadow-[0_0_10px_#a5b4fc]"></div>
                    </div>

                    <div class="absolute inset-0 bg-white/30 blur-xl rounded-full scale-50 animate-ping z-10"></div>
                </div>
            </div>
        </div>

        <div class="w-full flex flex-col items-center space-y-8">
            <div class="flex items-center justify-center gap-4 w-full">
                <div class="flex-grow h-[1px] bg-gradient-to-r from-transparent via-blue-500/50 to-blue-500/50"></div>
                <h2 id="statusText" class="text-blue-400 font-black tracking-[0.6em] uppercase text-[10px] md:text-[12px] whitespace-nowrap px-4 mt-12">
                    System Boot Sequence
                </h2>
                <div class="flex-grow h-[1px] bg-gradient-to-l from-transparent via-blue-500/50 to-blue-500/50"></div>
            </div>

            <div class="w-full max-w-md mx-auto relative px-2">
                <div class="w-full h-3 bg-slate-900/80 rounded-full overflow-hidden p-[2px] border border-blue-500/30 shadow-[0_0_15px_rgba(59,130,246,0.1)]">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-blue-600 via-cyan-400 to-blue-600 bg-[size:200%_auto] animate-gradient-x rounded-full transition-all duration-700 w-0 relative">
                        <div class="absolute inset-0 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,0.4),transparent)] animate-shimmer"></div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center relative py-4">
                <div class="relative flex items-center justify-center">
                    <span id="percentNum" class="text-8xl md:text-9xl font-black text-white tabular-nums tracking-tighter drop-shadow-[0_0_20px_rgba(255,255,255,0.2)] leading-none">0</span>
                    <span class="ml-2 text-2xl md:text-3xl font-black text-blue-500 italic align-top mt-[-2rem]"> %</span>
                </div>

                <div class="mt-6 inline-flex items-center gap-3 px-6 py-2 border-y border-blue-500/20 bg-blue-500/5">
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></div>
                    <span id="dataFragment" class="font-mono text-[10px] md:text-[12px] text-blue-300 uppercase tracking-[0.4em]">establishing_secure_link...</span>
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></div>
                </div>
            </div>
        </div>

        <p class="mt-20 text-[9px] md:text-[11px] text-blue-400/40 font-bold tracking-[1.2em] uppercase leading-none">
            HC MINTAL LAB
        </p>
    </div>
</div>

<style>
    @keyframes gradient-x {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }
    .animate-gradient-x { animation: gradient-x 3s linear infinite; }

    @keyframes nebulaFloat {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.15); opacity: 0.5; }
    }

    /* Nucleus Pulse and Vibration */
    @keyframes atomPlay {
        0%, 100% { transform: scale(1); filter: brightness(100%); }
        50% { transform: scale(1.2); filter: brightness(150%); }
    }

    /* 3D-Like Orbit Animations */
    @keyframes orbitA {
        from { transform: rotateX(65deg) rotateY(0deg); }
        to { transform: rotateX(65deg) rotateY(360deg); }
    }
    @keyframes orbitB {
        from { transform: rotateX(-65deg) rotateY(0deg); }
        to { transform: rotateX(-65deg) rotateY(360deg); }
    }
    @keyframes orbitC {
        from { transform: rotateX(0deg) rotateY(0deg); }
        to { transform: rotateX(0deg) rotateY(360deg); }
    }

    .animate-atom-play { animation: atomPlay 2s ease-in-out infinite; }
    .animate-orbit-a { animation: orbitA 3s linear infinite; transform-style: preserve-3d; }
    .animate-orbit-b { animation: orbitB 2.5s linear infinite; transform-style: preserve-3d; }
    .animate-orbit-c { animation: orbitC 4s linear infinite; transform-style: preserve-3d; }

    .animate-nebula-float { animation: nebulaFloat 15s ease-in-out infinite; }
    .animate-spin-fast { animation: spin-linear 0.8s linear infinite; }
    .animate-spin-linear-slow { animation: spin-linear 20s linear infinite; }
    .animate-shimmer { animation: shimmer 2s linear infinite; }

    @keyframes spin-linear {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
</style>