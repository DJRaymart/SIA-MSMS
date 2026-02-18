<?php

$logbook_title       = $logbook_title ?? 'Logbook';
require_once dirname(__DIR__) . '/auth/security.php';
$logbook_home        = (defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/';
$logbook_back_url    = $logbook_back_url ?? $logbook_home;
$logbook_back_label  = $logbook_back_label ?? 'Return to Home Page';
$logbook_input_name  = $logbook_input_name ?? 'student_id';
$logbook_form_action = $logbook_form_action ?? '';
$logbook_submit_label= $logbook_submit_label ?? 'Log Attendance';
$logbook_alert       = $logbook_alert ?? null;
$logbook_search_url  = $logbook_search_url ?? null;
$logbook_accent      = $logbook_accent ?? 'blue';
$logbook_admin_bypass = $logbook_admin_bypass ?? false;

if (!empty($logbook_standalone)) {
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($logbook_title ?? 'Logbook'); ?> - MSMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen"><?php
}

$accent_map = [
    'blue'    => ['from' => 'from-blue-600', 'to' => 'to-indigo-500', 'ring' => 'focus:ring-blue-500/5', 'border' => 'focus:border-blue-500', 'btn' => 'hover:bg-blue-600', 'shadow' => 'hover:shadow-blue-500/40', 'dot' => 'bg-blue-500', 'text' => 'text-blue-600'],
    'teal'    => ['from' => 'from-teal-600', 'to' => 'to-cyan-500', 'ring' => 'focus:ring-teal-500/5', 'border' => 'focus:border-teal-500', 'btn' => 'hover:bg-teal-600', 'shadow' => 'hover:shadow-teal-500/40', 'dot' => 'bg-teal-500', 'text' => 'text-teal-600'],
    'cyan'    => ['from' => 'from-cyan-600', 'to' => 'to-blue-500', 'ring' => 'focus:ring-cyan-500/5', 'border' => 'focus:border-cyan-500', 'btn' => 'hover:bg-cyan-600', 'shadow' => 'hover:shadow-cyan-500/40', 'dot' => 'bg-cyan-500', 'text' => 'text-cyan-600'],
    'emerald' => ['from' => 'from-emerald-600', 'to' => 'to-teal-500', 'ring' => 'focus:ring-emerald-500/5', 'border' => 'focus:border-emerald-500', 'btn' => 'hover:bg-emerald-600', 'shadow' => 'hover:shadow-emerald-500/40', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
    'pink'    => ['from' => 'from-pink-600', 'to' => 'to-rose-500', 'ring' => 'focus:ring-pink-500/5', 'border' => 'focus:border-pink-500', 'btn' => 'hover:bg-pink-600', 'shadow' => 'hover:shadow-pink-500/40', 'dot' => 'bg-pink-500', 'text' => 'text-pink-600'],
];
$a = $accent_map[$logbook_accent] ?? $accent_map['blue'];

$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$baseS = ($base === '' || $base === '/') ? '/' : $base . '/';
$lookup_url = ($base !== '' ? $base . '/' : '/') . 'api/lookup_student.php';
?>
<div class="logbook-unified min-h-screen flex flex-col items-center justify-center py-12 px-4">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200"></div>
    <div class="absolute inset-0 -z-10 opacity-30 bg-[radial-gradient(at_30%_20%,rgba(59,130,246,0.4)_0,transparent_50%)]"></div>
    
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-4">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?php echo $a['dot']; ?> opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 <?php echo $a['dot']; ?>"></span>
                </span>
                <span class="text-[10px] font-black <?php echo $a['text']; ?> uppercase tracking-[0.4em]">MSMS Online</span>
            </div>
            <h1 class="text-5xl md:text-6xl font-black tracking-tighter text-slate-900 leading-none">
                <?php echo htmlspecialchars($logbook_title); ?> <span class="<?php echo $a['text']; ?> italic">Logbook</span>
            </h1>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-3">Scan ID or RFID</p>
        </div>

        <div class="relative p-[2px] bg-gradient-to-r <?php echo $a['from']; ?> <?php echo $a['to']; ?> rounded-[2rem] shadow-2xl">
            <div class="bg-white/95 backdrop-blur-xl rounded-[1.95rem] p-8 md:p-10 relative overflow-hidden">
                <div class="absolute -top-16 -right-16 w-32 h-32 bg-slate-200/50 blur-2xl rounded-full"></div>

                <?php if ($logbook_alert): ?>
                    <div class="mb-6 p-4 rounded-xl <?php echo $logbook_alert['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                        <?php echo htmlspecialchars($logbook_alert['message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($logbook_form_action); ?>" class="space-y-6 relative z-10" id="logbook_form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase font-bold text-slate-600 tracking-widest">Student ID or RFID</label>
                        <div class="relative">
                            <input type="text" 
                                name="<?php echo htmlspecialchars($logbook_input_name); ?>" 
                                id="logbook_input" 
                                autocomplete="off" autofocus
                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-6 py-5 text-lg font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-2 <?php echo $a['border']; ?> focus:ring-4 <?php echo $a['ring']; ?> outline-none transition-all"
                                placeholder="Scan or type ID / RFID">
                            <?php if ($logbook_search_url): ?>
                            <div id="logbook_suggestions" class="absolute top-full left-0 w-full bg-white mt-2 rounded-xl shadow-xl border border-slate-200 hidden z-50 max-h-52 overflow-y-auto divide-y divide-slate-100"></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$logbook_admin_bypass): ?>
                    <button type="button" id="logbook_check_btn" class="w-full bg-slate-200 text-slate-700 font-bold py-4 rounded-xl transition-all flex justify-center items-center gap-2 hover:bg-slate-300 active:scale-[0.98]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Check
                    </button>
                    <?php endif; ?>

                    <div id="logbook_preview" class="hidden p-4 rounded-xl bg-slate-50 border-2 border-slate-200">
                        <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-3">Student verified</p>
                        <div class="space-y-1 text-sm">
                            <p><span class="font-semibold text-slate-600">Name:</span> <span id="preview_name" class="font-bold text-slate-900"></span></p>
                            <p><span class="font-semibold text-slate-600">Student ID:</span> <span id="preview_id" class="font-mono text-slate-800"></span></p>
                            <p><span class="font-semibold text-slate-600">Grade & Section:</span> <span id="preview_grade" class="text-slate-800"></span></p>
                        </div>
                        <p id="preview_error" class="hidden mt-2 text-sm font-semibold text-red-600"></p>
                    </div>

                    <button type="submit" id="logbook_submit_btn" disabled class="w-full <?php echo $a['btn']; ?> bg-slate-900 text-white font-bold py-5 rounded-xl transition-all flex justify-center items-center gap-3 <?php echo $a['shadow']; ?> active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l6 6 6-6" />
                        </svg>
                        <?php echo htmlspecialchars($logbook_submit_label); ?>
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <a href="<?php echo htmlspecialchars($baseS . 'auth/clear_exit.php?redirect=' . urlencode($logbook_back_url)); ?>" class="flex justify-center items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        <?php echo htmlspecialchars($logbook_back_label); ?>
                    </a>
                </div>
            </div>
        </div>

        <p class="mt-4 text-center text-[10px] font-mono text-slate-400 tracking-wider"><?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</div>

<script>
(function() {
    const lookupUrl = '<?php echo addslashes($lookup_url); ?>';
    const input = document.getElementById('logbook_input');
    const preview = document.getElementById('logbook_preview');
    const submitBtn = document.getElementById('logbook_submit_btn');
    const adminBypass = <?php echo $logbook_admin_bypass ? 'true' : 'false'; ?>;

    function doLookup() {
        const id = input.value.trim();
        if (!id) {
            preview.classList.add('hidden');
            submitBtn.disabled = true;
            return;
        }
        fetch(lookupUrl + '?id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(data => {
                preview.classList.remove('hidden');
                document.getElementById('preview_error').classList.add('hidden');
                if (data.found) {
                    document.getElementById('preview_name').textContent = data.name || data.fullname || '';
                    document.getElementById('preview_id').textContent = data.student_id || '';
                    document.getElementById('preview_grade').textContent = data.grade_section || (data.grade && data.section ? data.grade + ' - ' + data.section : '');
                    preview.classList.remove('border-red-300', 'bg-red-50');
                    preview.classList.add('border-emerald-200', 'bg-emerald-50/50');
                    submitBtn.disabled = false;
                } else {
                    document.getElementById('preview_name').textContent = '-';
                    document.getElementById('preview_id').textContent = '-';
                    document.getElementById('preview_grade').textContent = '-';
                    const errEl = document.getElementById('preview_error');
                    errEl.textContent = data.message || 'Student not found or account not yet approved.';
                    errEl.classList.remove('hidden');
                    preview.classList.remove('border-emerald-200', 'bg-emerald-50/50');
                    preview.classList.add('border-red-300', 'bg-red-50');
                    submitBtn.disabled = true;
                }
            })
            .catch(() => {
                preview.classList.remove('hidden');
                document.getElementById('preview_error').textContent = 'Unable to verify. Please try again.';
                document.getElementById('preview_error').classList.remove('hidden');
                preview.classList.add('border-red-300', 'bg-red-50');
                submitBtn.disabled = true;
            });
    }

    if (!adminBypass) {
        document.getElementById('logbook_check_btn').addEventListener('click', doLookup);
    }
    input.addEventListener('input', function() {
        if (adminBypass) {
            submitBtn.disabled = !input.value.trim();
        } else {
            preview.classList.add('hidden');
            submitBtn.disabled = true;
        }
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (!submitBtn.disabled) {
                document.getElementById('logbook_form').submit();
            } else if (!adminBypass) {
                doLookup();
            }
        }
    });
})();
</script>
<?php if ($logbook_search_url): ?>
<script>
(function() {
    const input = document.getElementById('logbook_input');
    const suggestions = document.getElementById('logbook_suggestions');
    const searchUrl = '<?php echo addslashes($logbook_search_url); ?>';
    
    input.addEventListener('keyup', function() {
        const v = this.value.trim();
        if (v.length < 1) { suggestions.classList.add('hidden'); return; }
        fetch(searchUrl + '?q=' + encodeURIComponent(v))
            .then(r => r.text())
            .then(html => {
                if (html.trim()) { suggestions.innerHTML = html; suggestions.classList.remove('hidden'); }
                else { suggestions.classList.add('hidden'); }
            });
    });
    
    suggestions.addEventListener('mousedown', function(e) {
        const el = e.target.closest('.select-student');
        if (el) {
            e.preventDefault();
            try {
                const d = JSON.parse(el.getAttribute('data-student'));
                input.value = d.id || d.student_id;
                suggestions.classList.add('hidden');
                input.focus();
            } catch (_) {}
        }
    });
    
    document.addEventListener('mousedown', function(e) {
        if (!suggestions.contains(e.target) && e.target !== input) suggestions.classList.add('hidden');
    });
})();
</script>
<?php endif; ?>

<?php if (!empty($logbook_standalone)): ?>
</body>
</html>
<?php endif; ?>
