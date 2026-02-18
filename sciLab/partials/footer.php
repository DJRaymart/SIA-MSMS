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
if (!defined('APP_ROOT')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
include (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/sciLab/partials/loading_overlay.php';
?>
<script src="/sciLab/assets/js/lab-loader.js"></script>
<script src="/sciLab/assets/js/global-alert.js"></script>
<script src="/sciLab/assets/js/global-prompt.js"></script>
<link rel="stylesheet" href="/sciLab/assets/style.css">

<script>
    const SESSION_STATUS_KEY = 'scilab_session_lock';
    let hasAlerted = false;

    // Check if the current page is one of the 5 protected admin pages
    function isRestrictedAdminPage() {
        const adminPages = [
            'index.php',
            'inventory.php',
            'inventory_reports.php',
            'log_book_report.php',
            'statistic_report.php'
        ];
        return adminPages.some(page => window.location.pathname.includes('/admin/' + page));
    }

    function isLoginPage() {
        return false; // adminlogin.php has been removed
    }

    async function triggerAutoLockdown() {
        if (hasAlerted || isLoginPage()) return;

        // If it's NOT one of the restricted admin pages (like the user log_book.php), 
        // just redirect silently without showing the prompt.
        if (!isRestrictedAdminPage()) {
            performFinalRedirect();
            return;
        }

        hasAlerted = true;
        const promptElem = document.getElementById('systemPrompt');
        if (promptElem) {
            document.getElementById('promptTitle').innerText = "UPLINK_SEVERED";
            document.getElementById('promptMessage').innerText = "SECURITY ALERT: DEEP SPACE HANDSHAKE LOST. TERMINATING LOCAL SESSION.";
            document.querySelector('.grid-cols-2').classList.replace('grid-cols-2', 'grid-cols-1');
            document.getElementById('promptCancel').style.display = 'none';
            document.getElementById('promptConfirm').innerText = "Acknowledge_Return";
            document.getElementById('promptConfirm').onclick = () => performFinalRedirect();
            promptElem.classList.replace('hidden', 'flex');
        }
        setTimeout(performFinalRedirect, 8000);
    }

    function performFinalRedirect() {
        // Redirect to admin dashboard (login removed)
        const loginUrl = "/sciLab/admin/index.php";
        localStorage.removeItem(SESSION_STATUS_KEY);

        if (typeof triggerLabLoader === "function") {
            triggerLabLoader(loginUrl, "Disconnecting Uplink...");
        } else {
            window.location.href = loginUrl;
        }
    }

    async function syncSession() {
        // Only run the background sync if we are in the admin folder
        if (!window.location.pathname.includes('/admin/') || isLoginPage()) {
            return;
        }

        if (hasAlerted) return;
        try {
            const response = await fetch('/sciLab/auth/check_session.php');
            const data = await response.json();
            if (data.status === 'expired' || localStorage.getItem(SESSION_STATUS_KEY) === 'expired') {
                localStorage.setItem(SESSION_STATUS_KEY, 'expired');
                triggerAutoLockdown();
            }
        } catch (e) {}
    }

    // --- CONFLICT RESOLUTION: GLOBAL LINK HANDLER ---
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', async (e) => {
                const targetUrl = link.getAttribute('href');
                if (!targetUrl || targetUrl.startsWith('#') || targetUrl.includes('javascript:void')) return;

                // 1. If it's a logout link, stop and let handleLogout deal with it
                if (targetUrl.includes('logout.php')) {
                    e.preventDefault();
                    handleLogout(e, link);
                    return;
                }

                // 2. Otherwise, use the standard transition
                e.preventDefault();
                const boldText = link.querySelector('.font-black') || link;
                const pageName = boldText.innerText.trim() || 'Page';

                // Ensure URL is properly formatted (relative paths work fine with window.location.href)
                let finalUrl = targetUrl;
                
                // Try to use the loader, but always fallback to direct navigation
                const currentUrl = window.location.href;
                
                try {
                    if (typeof triggerLabLoader === "function") {
                        triggerLabLoader(finalUrl, `Initialising ${pageName}...`);
                        
                        // Set a timeout fallback in case loader fails to redirect
                        setTimeout(() => {
                            // If still on same page after 2.5 seconds, force redirect
                            if (window.location.href === currentUrl) {
                                console.warn('Loader may have failed, forcing direct navigation to:', finalUrl);
                                window.location.href = finalUrl;
                            }
                        }, 2500);
                    } else {
                        // No loader function, navigate directly
                        console.log('No loader function, navigating directly to:', finalUrl);
                        window.location.href = finalUrl;
                    }
                } catch (error) {
                    console.error('Navigation error:', error);
                    // Fallback: direct navigation
                    window.location.href = finalUrl;
                }
            });
        });

        setInterval(syncSession, 10000);
        syncSession();
        if (isLoginPage()) {
            localStorage.removeItem(SESSION_STATUS_KEY);
            // Check for active session redirect
            fetch('/sciLab/auth/check_session.php').then(r => r.json()).then(data => {
                if (data.status === 'active' && sessionStorage.getItem('logging_in') !== 'true') {
                    triggerLabLoader("/sciLab/admin/index.php", "Re-establishing Connection...");
                }
            }).catch(e => {});
        }
    });

    async function handleLogout(event, element) {
        if (event) event.preventDefault();
        const targetUrl = element ? element.getAttribute('href') : "/sciLab/admin/logout.php";

        let confirmed = true;
        if (typeof showSystemPrompt === "function") {
            confirmed = await showSystemPrompt("TERMINATE_CONNECTION", "Are you sure you want to de-authorize this terminal?");
        } else {
            confirmed = confirm("Terminate session and de-authorize terminal?");
        }

        if (confirmed) {
            localStorage.setItem(SESSION_STATUS_KEY, 'expired');
            if (typeof triggerLabLoader === "function") {
                triggerLabLoader(targetUrl, "Severing Uplink...");
            } else {
                window.location.href = targetUrl;
            }
        }
    }

    window.addEventListener('storage', (e) => {
        if (e.key === SESSION_STATUS_KEY && e.newValue === 'expired') triggerAutoLockdown();
    });
</script>
</body>

</html>