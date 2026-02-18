<div id="systemPrompt" class="fixed inset-0 bg-[#020617]/90 backdrop-blur-xl hidden items-center justify-center z-[200] p-4">
    <div class="bg-[#0f172a] border border-red-500/30 rounded-2xl shadow-[0_0_80px_rgba(239,68,68,0.15)] w-full max-w-md overflow-hidden relative">
        <div class="h-1.5 w-full bg-gradient-to-r from-transparent via-red-600 to-transparent"></div>
        <div class="p-8 text-center">
            <div class="relative w-20 h-20 mx-auto mb-6">
                <div class="absolute inset-0 bg-red-600/20 rounded-full animate-ping"></div>
                <div class="relative w-full h-full bg-slate-900 border border-red-500/40 rounded-full flex items-center justify-center shadow-[0_0_20px_rgba(239,68,68,0.3)]">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <h3 id="promptTitle" class="text-white font-black uppercase italic tracking-tighter text-2xl mb-2">Protocol_Override</h3>
            <p id="promptMessage" class="text-slate-400 text-[10px] font-mono leading-relaxed uppercase tracking-[0.15em] px-4">
                Warning: Signal interruption detected. Authorizing manual session termination.
            </p>
        </div>
        <div class="grid grid-cols-2 border-t border-slate-800/50 bg-slate-950/30">
            <button id="promptCancel" class="p-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-white hover:bg-slate-800/50 transition-all border-r border-slate-800/50">Abort_Action</button>
            <button id="promptConfirm" class="p-5 text-[10px] font-black uppercase tracking-[0.2em] text-red-500 hover:text-white hover:bg-red-600 transition-all">Confirm_Execute</button>
        </div>
    </div>
</div>

<?php
if (!defined('BASE_URL')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
$baseS = (rtrim(BASE_URL, '/') === '' ? '/' : rtrim(BASE_URL, '/') . '/');
include __DIR__ . '/loading_overlay.php';
?>
<script>
document.querySelectorAll('.logout-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var url = this.getAttribute('href');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Log Out?',
                text: 'Are you sure you want to log out?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Log out'
            }).then(function(r) {
                if (r.isConfirmed) window.location.href = url;
            });
        } else { window.location.href = url; }
    });
});
</script>
<script src="<?php echo htmlspecialchars($baseS); ?>assets/js/lab-loader.js"></script>
<script src="<?php echo htmlspecialchars($baseS); ?>assets/js/global-alert.js"></script>
<script src="<?php echo htmlspecialchars($baseS); ?>assets/js/global-prompt.js"></script>
<link rel="stylesheet" href="<?php echo htmlspecialchars($baseS); ?>assets/style.css">

</body>

</html>
