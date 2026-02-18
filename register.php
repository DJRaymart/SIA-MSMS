<?php
require_once __DIR__ . '/auth/session_init.php';
require_once __DIR__ . '/auth/security.php';
require_once __DIR__ . '/auth/admin_auth.php';
require_once __DIR__ . '/auth/student_auth.php';

$base = rtrim(BASE_URL, '/');
$baseS = $base === '' ? '/' : $base . '/';
$baseSlash = $base . '/';

if (AdminAuth::isLoggedIn()) {
    header("Location: " . $baseSlash . "?admin_logged_in=1");
    exit();
}

if (StudentAuth::isLoggedIn()) {
    header("Location: " . $baseSlash);
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please refresh the page and try again.';
    }

    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $grade = (int) ($_POST['grade'] ?? 0);
    $section = trim($_POST['section'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($error === '' && $password !== $password_confirm) {
        $error = 'Passwords do not match';
    } elseif ($error === '' && !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($error === '') {
        $studentAuth = new StudentAuth();
        $result = $studentAuth->register($student_id, $name, $email, $grade, $section, $password);

        if ($result['success']) {
            header("Location: " . $baseSlash . "login.php?type=student&success=" . urlencode($result['message']));
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | MSMS</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($baseS); ?>assets/images/32x32.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .animate-fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .input-modern {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-modern:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 50%, #60a5fa 100%);
            background-size: 200% 200%;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-position: 100% 0;
            transform: translateY(-2px);
            box-shadow: 0 12px 40px -12px rgba(59, 130, 246, 0.5);
        }
        .form-card {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255,255,255,0.8);
        }
        .glass-panel {
            backdrop-filter: blur(20px);
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.85) 100%);
        }
        .form-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .form-scroll::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
            border-radius: 3px;
        }
        .form-scroll::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.4);
            border-radius: 3px;
        }
        .form-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.6);
        }
    </style>
</head>
<body class="relative min-h-screen flex flex-col items-center p-4 py-8 overflow-y-auto bg-slate-50">
    <!-- Background -->
    <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo htmlspecialchars($baseS); ?>assets/images/bg_school.png'); transform: scale(1.1); filter: blur(12px) saturate(0.8);"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-transparent to-indigo-500/5"></div>
    </div>
    
    <div class="w-full max-w-lg animate-fade-up relative z-10 my-auto flex-shrink-0" style="animation-delay: 0.1s;">
        <div class="form-card glass-panel rounded-3xl overflow-hidden max-h-[calc(100vh-4rem)] flex flex-col">
            <!-- Header -->
            <div class="relative px-8 py-8 text-center overflow-hidden flex-shrink-0">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-600"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.15),transparent_50%)]"></div>
                <div class="relative flex flex-col items-center">
                <div class="animate-float w-20 h-20 bg-white/25 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/30 shadow-xl flex-shrink-0">
                    <img src="<?php echo htmlspecialchars($baseS); ?>assets/images/school_logo.png" alt="Logo" class="w-14 h-14 object-contain mx-auto">
                </div>
                <h1 class="relative mt-5 text-2xl font-extrabold text-white tracking-tight">Create Account</h1>
                <p class="relative mt-1 text-blue-100/90 text-sm font-medium">Student Registration — Holy Cross of Mintal, Inc.</p>
                </div>
            </div>

            <!-- Form -->
            <div class="px-8 py-8 overflow-y-auto flex-1 min-h-0 form-scroll">
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200/80 rounded-2xl flex items-start gap-3 animate-fade-up">
                        <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-rose-800 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label for="student_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Student ID
                            </label>
                            <input 
                                type="text" 
                                id="student_id" 
                                name="student_id" 
                                required 
                                autofocus
                                class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none"
                                placeholder="e.g. 2024-001234"
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Full Name
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                required
                                class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none"
                                placeholder="Your full name"
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <label for="email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none"
                                placeholder="your.email@example.com"
                            >
                        </div>
                        <div>
                            <label for="grade" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Grade Level
                            </label>
                            <select 
                                id="grade" 
                                name="grade" 
                                required
                                class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none cursor-pointer"
                            >
                                <option value="">Select grade</option>
                                <?php for ($g = 1; $g <= 12; $g++): ?>
                                    <option value="<?php echo $g; ?>">Grade <?php echo $g; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="section" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Section
                            </label>
                            <input 
                                type="text" 
                                id="section" 
                                name="section" 
                                required
                                class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none"
                                placeholder="e.g. Rizal"
                            >
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Account Security</p>
                        <div class="space-y-5">
                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Password
                                </label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required
                                    minlength="6"
                                    class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none"
                                    placeholder="Min. 6 characters"
                                    autocomplete="new-password"
                                >
                                <div id="password-strength" class="mt-2 hidden">
                                    <div class="flex gap-1 mb-1">
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="strength-bar-1"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="strength-bar-2"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="strength-bar-3"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="strength-bar-4"></div>
                                    </div>
                                    <p id="strength-label" class="text-xs font-medium"></p>
                                </div>
                            </div>
                            <div>
                                <label for="password_confirm" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Confirm Password
                                </label>
                                <input 
                                    type="password" 
                                    id="password_confirm" 
                                    name="password_confirm" 
                                    required
                                    minlength="6"
                                    class="input-modern w-full px-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none"
                                    placeholder="Re-enter password"
                                    autocomplete="new-password"
                                >
                                <div id="password-match" class="mt-2 hidden">
                                    <div class="flex gap-1 mb-1">
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="match-bar-1"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="match-bar-2"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="match-bar-3"></div>
                                        <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors duration-300" id="match-bar-4"></div>
                                    </div>
                                    <p id="match-label" class="text-xs font-medium"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="btn-register w-full py-4 rounded-xl text-white font-bold text-sm uppercase tracking-wider shadow-lg"
                    >
                        Create Account
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 text-center space-y-3">
                    <a href="<?php echo htmlspecialchars($baseSlash); ?>login.php?type=student" class="block text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        Already have an account? Log in
                    </a>
                    <a href="<?php echo htmlspecialchars($baseSlash); ?>" class="block text-sm text-slate-500 hover:text-slate-700 font-medium transition-colors">
                        ← Back to Portal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var password = document.getElementById('password');
            var passwordConfirm = document.getElementById('password_confirm');
            var strengthWrap = document.getElementById('password-strength');
            var strengthLabel = document.getElementById('strength-label');
            var bars = [1,2,3,4].map(function(i) { return document.getElementById('strength-bar-' + i); });
            var matchWrap = document.getElementById('password-match');
            var matchLabel = document.getElementById('match-label');
            var matchBars = [1,2,3,4].map(function(i) { return document.getElementById('match-bar-' + i); });

            function checkStrength(pw) {
                var score = 0;
                if (!pw.length) return { score: 0, label: '', labelClass: '' };

                if (pw.length >= 6) score++;
                if (pw.length >= 10) score++;
                if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
                if (/\d/.test(pw)) score++;
                if (/[^a-zA-Z0-9]/.test(pw)) score++;

                var levelScore = Math.min(4, Math.max(1, Math.ceil(score * 0.85)));
                var levels = [
                    { label: 'Weak', class: 'text-red-600' },
                    { label: 'Fair', class: 'text-amber-600' },
                    { label: 'Good', class: 'text-blue-600' },
                    { label: 'Strong', class: 'text-green-600' }
                ];
                var level = levels[levelScore - 1];
                return { score: levelScore, label: level.label, labelClass: level.class };
            }

            function updateBars(barsList, score, colors) {
                barsList.forEach(function(bar, i) {
                    bar.className = 'h-1 flex-1 rounded-full transition-colors duration-300 ' + (i < score ? colors[score - 1] : 'bg-slate-200');
                });
            }

            function updateMatch() {
                var pw = password.value;
                var pwConfirm = passwordConfirm.value;
                if (!pwConfirm.length) {
                    matchWrap.classList.add('hidden');
                    return;
                }
                matchWrap.classList.remove('hidden');
                var match = pw === pwConfirm;
                var levelScore = match ? 4 : 1;
                matchLabel.textContent = match ? 'Passwords match' : 'Passwords do not match';
                matchLabel.className = 'text-xs font-medium ' + (match ? 'text-green-600' : 'text-red-600');
                updateBars(matchBars, levelScore, ['bg-red-500', 'bg-amber-500', 'bg-blue-500', 'bg-green-500']);
            }

            password.addEventListener('input', function() {
                var pw = this.value;
                if (!pw.length) {
                    strengthWrap.classList.add('hidden');
                } else {
                    strengthWrap.classList.remove('hidden');
                    var r = checkStrength(pw);
                    strengthLabel.textContent = r.label;
                    strengthLabel.className = 'text-xs font-medium ' + r.labelClass;
                    updateBars(bars, r.score, ['bg-red-500', 'bg-amber-500', 'bg-blue-500', 'bg-green-500']);
                }
                updateMatch();
            });

            passwordConfirm.addEventListener('input', updateMatch);
        })();
    </script>
</body>
</html>
