<?php

$verify_title      = $verify_title ?? 'Verify';
require_once dirname(__DIR__) . '/auth/security.php';
$verify_subtitle   = $verify_subtitle ?? 'Enter your Student ID or RFID to verify your account.';
$verify_back_url   = $verify_back_url ?? ((defined('BASE_URL') && rtrim(BASE_URL, '/') !== '') ? rtrim(BASE_URL, '/') . '/' : '/');
$verify_back_label = $verify_back_label ?? 'Return to Home Page';
$verify_accent     = $verify_accent ?? 'blue';
$verify_error      = $verify_error ?? null;
$verify_standalone  = $verify_standalone ?? false;

$accent_map = [
    'blue'    => ['from' => 'from-blue-600', 'to' => 'to-indigo-500', 'ring' => 'focus:ring-blue-500/5', 'border' => 'focus:border-blue-500', 'btn' => 'hover:bg-blue-600', 'shadow' => 'hover:shadow-blue-500/40', 'dot' => 'bg-blue-500', 'text' => 'text-blue-600'],
    'teal'    => ['from' => 'from-teal-600', 'to' => 'to-cyan-500', 'ring' => 'focus:ring-teal-500/5', 'border' => 'focus:border-teal-500', 'btn' => 'hover:bg-teal-600', 'shadow' => 'hover:shadow-teal-500/40', 'dot' => 'bg-teal-500', 'text' => 'text-teal-600'],
    'pink'    => ['from' => 'from-pink-600', 'to' => 'to-rose-500', 'ring' => 'focus:ring-pink-500/5', 'border' => 'focus:border-pink-500', 'btn' => 'hover:bg-pink-600', 'shadow' => 'hover:shadow-pink-500/40', 'dot' => 'bg-pink-500', 'text' => 'text-pink-600'],
];
$a = $accent_map[$verify_accent] ?? $accent_map['blue'];

if ($verify_standalone) {
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($verify_title); ?> - MSMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen"><?php
}
?>
<div class="verify-unified min-h-screen flex flex-col items-center justify-center py-12 px-4">
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
                <?php echo htmlspecialchars($verify_title); ?> <span class="<?php echo $a['text']; ?> italic">Verify</span>
            </h1>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-3"><?php echo htmlspecialchars($verify_subtitle); ?></p>
        </div>

        <div class="relative p-[2px] bg-gradient-to-r <?php echo $a['from']; ?> <?php echo $a['to']; ?> rounded-[2rem] shadow-2xl">
            <div class="bg-white/95 backdrop-blur-xl rounded-[1.95rem] p-8 md:p-10 relative overflow-hidden">
                <div class="absolute -top-16 -right-16 w-32 h-32 bg-slate-200/50 blur-2xl rounded-full"></div>

                <form method="POST" class="space-y-6 relative z-10">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                    <input type="hidden" name="action" value="verify_student">
                    <div class="space-y-2">
                        <label class="text-[10px] uppercase font-bold text-slate-600 tracking-widest">Student ID or RFID</label>
                        <input type="text" name="student_id" required autofocus
                            class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-6 py-5 text-lg font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-2 <?php echo $a['border']; ?> focus:ring-4 <?php echo $a['ring']; ?> outline-none transition-all"
                            placeholder="Scan or type ID / RFID">
                    </div>
                    <?php if ($verify_error): ?>
                    <p class="text-sm font-semibold text-red-600"><?php echo htmlspecialchars($verify_error); ?></p>
                    <?php endif; ?>
                    <div class="flex gap-4">
                        <a href="<?php echo htmlspecialchars($verify_back_url); ?>" class="flex-1 bg-slate-200 text-slate-700 font-bold py-5 rounded-xl transition-all flex justify-center items-center gap-2 hover:bg-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            <?php echo htmlspecialchars($verify_back_label); ?>
                        </a>
                        <button type="submit" class="flex-1 <?php echo $a['btn']; ?> bg-slate-900 text-white font-bold py-5 rounded-xl transition-all flex justify-center items-center gap-2 <?php echo $a['shadow']; ?> active:scale-[0.98]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Check
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="mt-4 text-center text-[10px] font-mono text-slate-400 tracking-wider"><?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</div>
<?php if ($verify_standalone): ?>
</body>
</html>
<?php endif; ?>
