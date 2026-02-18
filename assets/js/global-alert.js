
function showAlert(type, title, message, duration = 8000) {
    const styles = {
        success: { color: "#10b981", border: "border-emerald-500", text: "text-emerald-400", glow: "0 0 20px rgba(16,185,129,0.25)", icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />` },
        danger: { color: "#ef4444", border: "border-red-500", text: "text-red-400", glow: "0 0 20px rgba(239,68,68,0.25)", icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />` },
        warning: { color: "#f59e0b", border: "border-amber-500", text: "text-amber-400", glow: "0 0 20px rgba(245,158,11,0.25)", icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />` },
        info: { color: "#3b82f6", border: "border-blue-500", text: "text-blue-400", glow: "0 0 20px rgba(59,130,246,0.25)", icon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />` }
    };

    const theme = styles[type] || styles.info;

    if (!document.getElementById('terminal-v4-core-styles')) {
        const styleSheet = document.createElement("style");
        styleSheet.id = 'terminal-v4-core-styles';
        styleSheet.innerText = `
            @keyframes shrink-bar { from { width: 100%; } to { width: 0%; } }
            @keyframes pulse-glow { 0%, 100% { box-shadow: ${theme.glow}; } 50% { box-shadow: 0 0 35px ${theme.color}44; } }
            
            @keyframes quantum-materialize {
                0% { opacity: 0; transform: scale(1.1) translateY(-20px); filter: blur(15px) brightness(2); }
                30% { opacity: 1; transform: scale(0.98) translateY(5px); filter: blur(5px) brightness(1.2); }
                100% { opacity: 1; transform: scale(1) translateY(0); filter: blur(0) brightness(1); }
            }

            @keyframes quantum-de-coherence {
                0% { transform: translate(0,0) skew(0deg); clip-path: inset(0 0 0 0); filter: brightness(1) contrast(1); opacity: 1; }
                20% { transform: translate(-15px, 2px) skew(10deg); clip-path: inset(10% 0 60% 0); filter: brightness(2) hue-rotate(90deg); }
                40% { transform: translate(20px, -2px) skew(-20deg); clip-path: inset(50% 0 10% 0); filter: contrast(3) hue-rotate(-90deg); }
                60% { transform: translate(-30px, 5px) scaleY(0.1); clip-path: inset(20% 0 20% 0); filter: brightness(0.5); opacity: 0.7; }
                80% { transform: translate(40px, 0) scaleX(2); filter: blur(10px) brightness(3); opacity: 0.4; }
                100% { transform: translate(100px, 0) scale(0); filter: blur(20px); opacity: 0; }
            }

            .glitch-text-active {
                animation: text-glitch 0.3s steps(2) infinite;
            }

            @keyframes text-glitch {
                0% { text-shadow: 2px 0 #ff00c1, -2px 0 #00fff9; transform: translate(1px, 1px); }
                50% { text-shadow: -2px 0 #ff00c1, 2px 0 #00fff9; transform: translate(-1px, -1px); }
                100% { text-shadow: 2px 0 #ff00c1, -2px 0 #00fff9; transform: translate(0, 0); }
            }
        `;
        document.head.appendChild(styleSheet);
    }

    const alert = document.createElement('div');
    alert.className = `flex items-center p-5 mb-4 rounded-2xl border-2 backdrop-blur-2xl bg-[#0b0f1a]/95 ${theme.border} w-[330px] pointer-events-auto overflow-hidden relative transition-all duration-300`;
    alert.style.animation = "quantum-materialize 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards, pulse-glow 3s infinite ease-in-out";
    alert.setAttribute('role', 'alert');

    alert.innerHTML = `
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none opacity-10" 
             style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,0.05) 3px);"></div>
        
        <div class="absolute left-0 w-1 h-full ${theme.text} bg-current opacity-80 shadow-[0_0_12px_current]"></div>

        <div class="absolute bottom-0 left-0 h-1 ${theme.text} bg-current opacity-70 w-full" 
             style="animation: shrink-bar ${duration}ms linear forwards; box-shadow: 0 -3px 10px current;"></div>
        
        <div class="flex items-center gap-5 relative z-10 w-full content-wrapper">
            <div class="${theme.text} p-3 bg-white/5 rounded-xl border border-white/10 shadow-inner icon-box">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">${theme.icon}</svg>
            </div>
            <div class="flex flex-col flex-1 text-content">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-[8px] font-mono text-slate-400 uppercase tracking-[0.3em] font-black">System_Log</span>
                        <div class="w-1 h-1 rounded-full bg-current ${theme.text} animate-pulse"></div>
                        <span class="text-[8px] font-mono ${theme.text} uppercase tracking-[0.3em] font-black">${type}</span>
                    </div>
                </div>
                <h3 class="alert-title text-white font-black uppercase tracking-tight text-base italic drop-shadow-md leading-none">${title}</h3>
                <p class="text-slate-300 text-xs leading-relaxed mt-1.5 font-semibold antialiased opacity-90">${message}</p>
            </div>
        </div>
    `;

    let container = document.getElementById('alertContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'fixed top-8 right-8 z-[9999] flex flex-col items-end pointer-events-none w-full max-md:right-4';
        document.body.appendChild(container);
    }

    if (container.children.length >= 4) container.lastElementChild.remove();
    container.prepend(alert);

    const removeAlert = () => {

        const title = alert.querySelector('.alert-title');
        if(title) title.classList.add('glitch-text-active');
        
        alert.style.animation = "quantum-de-coherence 0.45s steps(6, end) forwards";
        
        setTimeout(() => {
            alert.remove();
            if (container.children.length === 0) container.remove();
        }, 450);
    };

    let removeTimeout = setTimeout(removeAlert, duration);

    alert.addEventListener('mouseenter', () => {
        clearTimeout(removeTimeout);
        alert.style.animationPlayState = 'paused';
        const bar = alert.querySelector('[style*="animation: shrink-bar"]');
        if (bar) bar.style.animationPlayState = 'paused';
    });

    alert.addEventListener('mouseleave', () => {
        removeTimeout = setTimeout(removeAlert, 2000);
        alert.style.animationPlayState = 'running';
        const bar = alert.querySelector('[style*="animation: shrink-bar"]');
        if (bar) bar.style.animationPlayState = 'running';
    });

    alert.addEventListener('click', removeAlert);
}