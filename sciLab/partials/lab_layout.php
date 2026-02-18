<div class="fixed inset-0 -z-30 pointer-events-none overflow-hidden">
    <div class="absolute inset-0 bg-[#020617]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,#1e293b_0%,#020617_100%)]"></div>
    
    <div class="absolute -top-16 -left-16 w-80 h-80 bg-yellow-50 rounded-full blur-[4px] opacity-90 shadow-[0_0_120px_50px_rgba(253,224,71,0.4)]"></div>
    <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-orange-500/15 blur-[100px] rounded-full animate-pulse"></div>
    
    <div class="absolute -bottom-16 -right-16 w-64 h-64 bg-slate-300 rounded-full opacity-10 blur-[2px] shadow-[inset_-25px_15px_30px_rgba(0,0,0,0.7),0_0_60px_rgba(148,163,184,0.2)]"></div>
    <div class="absolute -bottom-24 -right-24 w-[500px] h-[500px] bg-blue-500/10 blur-[100px] rounded-full"></div>

    <div class="absolute inset-0 opacity-30 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] animate-pulse"></div>
    <div class="absolute inset-0 opacity-[0.05]" 
         style="background-image: linear-gradient(#3b82f6 1px, transparent 1px), linear-gradient(90deg, #3b82f6 1px, transparent 1px); background-size: 100px 100px;">
    </div>
    
    <div class="absolute top-[10%] right-[10%] w-[50%] h-[50%] bg-blue-600/5 blur-[180px] rounded-full"></div>
    <div class="absolute bottom-[10%] left-[10%] w-[50%] h-[50%] bg-purple-600/5 blur-[180px] rounded-full"></div>
</div>

<div class="fixed inset-0 w-full h-full pointer-events-none -z-20 overflow-hidden">
    <img src="./assets/images/Hubble-Space.png"
         class="absolute w-96 opacity-10 blur-[6px] rounded-full" 
         style="top:25%; left:20%; animation: float 45s ease-in-out infinite;">

    <img src="./assets/images/earth.svg" 
         class="absolute w-64 opacity-25 blur-[1px]" 
         style="top:-2%; right:-2%; animation: float 32s ease-in-out infinite; filter: drop-shadow(0 0 40px rgba(59,130,246,0.4));">

    <img src="./assets/images/astronaut.png" 
         class="absolute w-40 opacity-30" 
         style="top:45%; left:8%; animation: float 22s ease-in-out infinite; filter: drop-shadow(0 10px 20px rgba(255,255,255,0.1));">

    <img src="./assets/images/saturn.png"
         class="absolute w-56 opacity-15 blur-[2px]"
         style="top:60%; right:15%; animation: float 28s ease-in-out infinite reverse;">

    <img src="./assets/images/dna.svg"
         class="absolute w-24 opacity-40 invert brightness-200 drop-shadow-[0_0_20px_rgba(59,130,246,0.6)]" 
         style="top:35%; left:75%; animation: float 14s ease-in-out infinite;">

    <img src="./assets/images/atom.png"
         class="absolute w-20 opacity-40 invert brightness-150" 
         style="top:15%; left:30%; animation: float 12s ease-in-out infinite reverse;">
    
    <img src="./assets/images/molecule.png"
         class="absolute w-28 opacity-30 invert"
         style="bottom:15%; right:40%; animation: float 18s ease-in-out infinite;">

    <img src="./assets/images/space-shuttle.png"
         class="absolute w-20 opacity-20 invert" 
         style="bottom:30%; left:40%; animation: float 20s ease-in-out infinite;">

    <div class="absolute top-[35%] left-[15%] w-2 h-2 bg-white rounded-full animate-ping opacity-20"></div>
    <div class="absolute bottom-[40%] right-[30%] w-1.5 h-1.5 bg-blue-400 rounded-full animate-ping opacity-40" style="animation-delay: 2s;"></div>
    <div class="absolute top-[60%] left-[50%] w-1 h-1 bg-yellow-200 rounded-full animate-ping opacity-30" style="animation-delay: 4s;"></div>
</div>

<div class="fixed inset-0 pointer-events-none z-50">
    <div class="absolute inset-0 bg-[radial-gradient(circle,transparent_40%,rgba(2,6,23,0.3)_100%)]"></div>
    <div class="w-full h-[2px] bg-blue-500/20 shadow-[0_0_20px_rgba(59,130,246,0.4)] animate-scanline"></div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        33% { transform: translate(5px, -35px) rotate(2deg); }
        66% { transform: translate(-5px, 20px) rotate(-2deg); }
    }

    .animate-scanline {
        position: absolute;
        animation: scanline 12s linear infinite;
        opacity: 0.6;
    }

    @keyframes scanline {
        0% { top: -10%; }
        100% { top: 110%; }
    }

    .invert {
        filter: invert(1);
    }
    
    /* Optimize performance for many floating objects */
    img, .animate-pulse {
        will-change: transform, opacity;
        backface-visibility: hidden;
    }
</style>