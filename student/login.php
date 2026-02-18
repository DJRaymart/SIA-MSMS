<?php
$app_root = dirname(__DIR__);
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/security.php';
require_once $app_root . '/auth/student_auth.php';
$baseSlash = (rtrim(BASE_URL, '/') === '' ? '' : rtrim(BASE_URL, '/')) . '/';
if ($baseSlash === '/') $baseSlash = '/';

if (StudentAuth::isLoggedIn()) {
    $redirect = msms_safe_redirect($_SESSION['student_redirect_after_login'] ?? '', $baseSlash);
    unset($_SESSION['student_redirect_after_login']);
    header("Location: " . $redirect);
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please refresh the page and try again.';
    }

    $student_id = trim($_POST['student_id'] ?? '');
    
    if ($error === '' && empty($student_id)) {
        $error = 'Please enter your Student ID';
    } elseif ($error === '') {
        $auth = new StudentAuth();
        $result = $auth->login($student_id);
        
        if ($result['success']) {
            $redirect = $_SESSION['student_redirect_after_login'] ?? '/';
            unset($_SESSION['student_redirect_after_login']);
            header("Location: " . $redirect);
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
    <title>Student Login - MSMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md fade-in">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-blue-100">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6 text-center">
                <div class="inline-block w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-white uppercase tracking-wide">Student Login</h1>
                <p class="text-blue-100 text-sm mt-2">Access student features</p>
            </div>

            <!-- Form -->
            <div class="px-8 py-6">
                <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-sm text-red-800 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-500 rounded-lg">
                    <p class="text-sm text-green-800 font-semibold"><?php echo htmlspecialchars($success); ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                    <div>
                        <label for="student_id" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                            Student ID
                        </label>
                        <input 
                            type="text" 
                            id="student_id" 
                            name="student_id" 
                            required 
                            autofocus
                            class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-lg font-semibold"
                            placeholder="Enter your Student ID"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-black uppercase tracking-wide py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
                    >
                        Login
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-200">
                    <a href="<?php echo htmlspecialchars($baseSlash); ?>" class="block text-center text-sm text-blue-600 hover:text-blue-800 font-semibold">
                        ← Back to Main Portal
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-slate-500 mt-6">
            Need admin access? <a href="<?php echo htmlspecialchars((rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/').'/') . 'login.php?type=admin'); ?>" class="text-blue-600 hover:text-blue-800 font-semibold">Admin Login</a>
        </p>
    </div>
</body>
</html>
