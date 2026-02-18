<?php
require_once __DIR__ . '/auth/session_init.php';
require_once __DIR__ . '/auth/security.php';
require_once __DIR__ . '/auth/admin_auth.php';
require_once __DIR__ . '/auth/student_auth.php';
$base = rtrim(BASE_URL, '/');
$baseS = $base === '' ? '/' : $base . '/';
$baseSlash = $base . '/';

$normalizeStudentRedirect = function ($url) use ($baseSlash) {
    if (empty($url)) return $url;
    if (strpos($url, 'sciLab/portal.php') !== false) {
        return preg_replace('#sciLab/portal\.php(\?.*)?$#', 'sciLab/logs/log_book.php', $url);
    }
    return $url;
};

if (AdminAuth::isLoggedIn()) {
    $redirect = msms_safe_redirect($_GET['redirect'] ?? '', $baseSlash . '?admin_logged_in=1');
    header("Location: " . $redirect);
    exit();
}

if (StudentAuth::isLoggedIn()) {
    $redirect = msms_safe_redirect($_GET['redirect'] ?? $_SESSION['student_redirect_after_login'] ?? '', $baseSlash);
    unset($_SESSION['student_redirect_after_login']);
    $redirect = $normalizeStudentRedirect($redirect);
    header("Location: " . $redirect);
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$logged_out = $_GET['logged_out'] ?? '';
$login_type = $_GET['type'] ?? 'student'; 
$redirect_url = msms_safe_redirect($_GET['redirect'] ?? $_POST['redirect'] ?? '', '');

if (isset($_GET['clear_student_step'])) {
    unset($_SESSION['student_login_step'], $_SESSION['student_id_pending'], $_SESSION['student_name_pending'], $_SESSION['student_first_time']);
}
$student_step = $_SESSION['student_login_step'] ?? 1;
$student_id_pending = $_SESSION['student_id_pending'] ?? '';
$student_name_pending = $_SESSION['student_name_pending'] ?? '';
$student_first_time = $_SESSION['student_first_time'] ?? false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please refresh the page and try again.';
    }

    $login_type = $_POST['login_type'] ?? 'student';
    $redirect_url = $_POST['redirect'] ?? '';
    
    if ($error === '' && $login_type === 'admin') {
        unset($_SESSION['student_login_step'], $_SESSION['student_id_pending'], $_SESSION['student_name_pending'], $_SESSION['student_first_time']);
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $adminAuth = new AdminAuth();
            $result = $adminAuth->login($username, $password);
            
            if ($result['success']) {
                $redirect = msms_safe_redirect($redirect_url, $baseSlash . '?admin_logged_in=1');
                header("Location: " . $redirect);
                exit();
            } else {
                $error = $result['message'];
            }
        }
    } elseif ($error === '') {
        $studentAuth = new StudentAuth();
        
        $student_step_post = (int) ($_POST['student_step'] ?? 1);
        
        if ($student_step_post === 1) {
            
            $student_id = trim($_POST['student_id'] ?? '');
            
            if (empty($student_id)) {
                $error = 'Please enter your Student ID';
            } else {
                $student = $studentAuth->checkStudentExists($student_id);
                if (!$student) {
                    $error = 'Invalid Student ID';
                } elseif (($student['account_status'] ?? 'approved') === 'pending') {
                    $error = 'Your account is pending approval. An admin must verify you before you can log in.';
                } elseif (($student['account_status'] ?? 'approved') === 'rejected') {
                    $error = 'Your registration was not approved. Contact the school for assistance.';
                } else {
                    $_SESSION['student_login_step'] = 2;
                    $_SESSION['student_id_pending'] = $student['student_id'];
                    $_SESSION['student_name_pending'] = $student['name'];
                    $_SESSION['student_first_time'] = empty($student['password']);
                    $student_step = 2;
                    $student_id_pending = $student['student_id'];
                    $student_name_pending = $student['name'];
                    $student_first_time = $_SESSION['student_first_time'];
                }
            }
        } else {
            
            $student_id = trim($_POST['student_id'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if ($student_id !== ($_SESSION['student_id_pending'] ?? '')) {
                $error = 'Session expired. Please enter your Student ID again.';
                unset($_SESSION['student_login_step'], $_SESSION['student_id_pending'], $_SESSION['student_name_pending'], $_SESSION['student_first_time']);
                $student_step = 1;
            } elseif (empty($password)) {
                $error = $student_first_time ? 'Please create your password' : 'Please enter your password';
                $student_step = 2;
                $student_id_pending = $_SESSION['student_id_pending'] ?? '';
                $student_name_pending = $_SESSION['student_name_pending'] ?? '';
                $student_first_time = $_SESSION['student_first_time'] ?? false;
            } else {
                if ($_SESSION['student_first_time'] ?? false) {
                    $password_confirm = $_POST['password_confirm'] ?? '';
                    if ($password !== $password_confirm) {
                        $error = 'Passwords do not match';
                        $student_step = 2;
                        $student_id_pending = $_SESSION['student_id_pending'] ?? '';
                        $student_name_pending = $_SESSION['student_name_pending'] ?? '';
                        $student_first_time = true;
                    } else {
                        $result = $studentAuth->firstTimeLogin($student_id, $password);
                        if ($result['success']) {
                            unset($_SESSION['student_login_step'], $_SESSION['student_id_pending'], $_SESSION['student_name_pending'], $_SESSION['student_first_time']);
                            $redirect = msms_safe_redirect($redirect_url ?: $_SESSION['student_redirect_after_login'] ?? '', $baseSlash);
                            unset($_SESSION['student_redirect_after_login']);
                            $redirect = $normalizeStudentRedirect($redirect);
                            header("Location: " . $redirect);
                            exit();
                        } else {
                            $error = $result['message'];
                            $student_step = 2;
                            $student_id_pending = $_SESSION['student_id_pending'] ?? '';
                            $student_name_pending = $_SESSION['student_name_pending'] ?? '';
                            $student_first_time = true;
                        }
                    }
                } else {
                    $result = $studentAuth->login($student_id, $password);
                    unset($_SESSION['student_login_step'], $_SESSION['student_id_pending'], $_SESSION['student_name_pending'], $_SESSION['student_first_time']);
                    if ($result['success']) {
                        $redirect = msms_safe_redirect($redirect_url ?: $_SESSION['student_redirect_after_login'] ?? '', $baseSlash);
                        unset($_SESSION['student_redirect_after_login']);
                        $redirect = $normalizeStudentRedirect($redirect);
                        header("Location: " . $redirect);
                        exit();
                    } else {
                        $error = $result['message'];
                        $student_step = 2;
                        $student_id_pending = $_SESSION['student_id_pending'] ?? $student_id;
                        $student_name_pending = $_SESSION['student_name_pending'] ?? '';
                        $student_first_time = false;
                        $_SESSION['student_login_step'] = 2;
                        $_SESSION['student_id_pending'] = $student_id_pending;
                        $_SESSION['student_name_pending'] = $student_name_pending;
                        $_SESSION['student_first_time'] = false;
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MSMS</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($baseS); ?>assets/images/32x32.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        .tab-active {
            background: linear-gradient(to right, #2563eb, #3b82f6);
            color: white;
        }
        .tab-inactive {
            background: #f1f5f9;
            color: #64748b;
        }
    </style>
</head>
<body class="relative min-h-screen flex items-center justify-center p-4 overflow-hidden">
    <!-- Background -->
    <div class="fixed inset-0 -z-30 pointer-events-none overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat blur-md" style="background-image: url('<?php echo htmlspecialchars($baseS); ?>assets/images/bg_school.png'); transform: scale(1.1);"></div>
    </div>
    
    <div class="w-full max-w-md fade-in relative z-10">
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-white/20">
            <!-- Logo and Title -->
            <div class="bg-gradient-to-r from-blue-600/90 to-blue-700/90 backdrop-blur-sm px-8 py-6 text-center border-b border-white/10">
                <div class="inline-block bg-white/20 p-4 rounded-full mb-4 backdrop-blur-sm">
                    <img src="<?php echo htmlspecialchars($baseS); ?>assets/images/school_logo.png" alt="Logo" class="w-16 h-16 object-contain">
                </div>
                <h1 class="text-3xl font-black text-white mb-2 drop-shadow-lg">MSMS Login</h1>
                <p class="text-blue-100 text-sm drop-shadow-md">Holy Cross of Mintal, Inc.</p>
            </div>

            <!-- Tabs (Student first) -->
            <div class="flex border-b border-slate-200/50 bg-slate-50/50">
                <button 
                    type="button"
                    id="studentTab"
                    onclick="switchTab('student')"
                    class="flex-1 py-4 px-6 font-bold text-sm uppercase tracking-wide transition-all duration-300 tab-<?php echo $login_type === 'student' ? 'active' : 'inactive'; ?>"
                >
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Student
                </button>
                <button 
                    type="button"
                    id="adminTab"
                    onclick="switchTab('admin')"
                    class="flex-1 py-4 px-6 font-bold text-sm uppercase tracking-wide transition-all duration-300 tab-<?php echo $login_type === 'admin' ? 'active' : 'inactive'; ?>"
                >
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Admin
                </button>
            </div>

            <!-- Form Container -->
            <div class="px-8 py-6 bg-white/50">
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <p class="text-sm text-red-800 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if ($success): ?>
                    <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <p class="text-sm text-green-800 font-semibold"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                    <?php if (stripos($success, 'logged out') !== false): ?>
                    <script>if(typeof Swal!=='undefined')Swal.fire({title:'Logged Out',text:'<?php echo addslashes(htmlspecialchars($success)); ?>',icon:'success',timer:2500,timerProgressBar:true});</script>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Logged Out Message -->
                <?php if ($logged_out): ?>
                    <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                        <p class="text-sm text-blue-800 font-semibold">You have been logged out successfully.</p>
                    </div>
                    <script>if(typeof Swal!=='undefined')Swal.fire({title:'Logged Out',text:'You have been logged out successfully.',icon:'success',timer:2500,timerProgressBar:true});</script>
                <?php endif; ?>

                <!-- Admin Login Form -->
                <form method="POST" action="" id="adminForm" class="space-y-5 <?php echo $login_type === 'admin' ? '' : 'hidden'; ?>">
                    <input type="hidden" name="login_type" value="admin">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                    <?php if ($redirect_url): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_url); ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label for="username" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                            Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            autofocus
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Enter your username"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Enter your password"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-black uppercase tracking-wide py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
                    >
                        Admin Login
                    </button>
                </form>

                <!-- Student Login Form (first) -->
                <form method="POST" action="" id="studentForm" class="space-y-5 <?php echo $login_type === 'student' ? '' : 'hidden'; ?>">
                    <input type="hidden" name="login_type" value="student">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(msms_csrf_token()); ?>">
                    <input type="hidden" name="student_step" value="<?php echo $student_step; ?>">
                    <?php if ($redirect_url): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_url); ?>">
                    <?php endif; ?>
                    
                    <?php if ($student_step === 1): ?>
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
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-lg font-semibold"
                            placeholder="Enter your Student ID"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white font-black uppercase tracking-wide py-3 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
                    >
                        Continue
                    </button>

                    <p class="text-center text-sm text-slate-600">
                        Don't have an account? 
                        <a href="<?php echo htmlspecialchars($baseSlash); ?>register.php" class="text-green-600 hover:text-green-800 font-semibold">Register here</a>
                    </p>
                    <?php else: ?>
                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id_pending); ?>">
                    <div class="p-3 bg-slate-100 rounded-xl mb-4">
                        <p class="text-xs text-slate-500 uppercase tracking-wide font-bold">Student ID</p>
                        <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($student_id_pending); ?></p>
                        <p class="text-sm text-slate-600"><?php echo htmlspecialchars($student_name_pending); ?></p>
                    </div>
                    
                    <div>
                        <label for="student_password" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                            <?php echo $student_first_time ? 'Create Password' : 'Password'; ?>
                        </label>
                        <input 
                            type="password" 
                            id="student_password" 
                            name="password" 
                            required 
                            autofocus
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="<?php echo $student_first_time ? 'Enter your new password' : 'Enter your password'; ?>"
                        >
                    </div>

                    <?php if ($student_first_time): ?>
                    <div>
                        <label for="student_password_confirm" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">
                            Confirm Password
                        </label>
                        <input 
                            type="password" 
                            id="student_password_confirm" 
                            name="password_confirm" 
                            required
                            class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                            placeholder="Confirm your password"
                        >
                    </div>
                    <?php endif; ?>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white font-black uppercase tracking-wide py-3 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
                    >
                        <?php echo $student_first_time ? 'Set Password & Login' : 'Student Login'; ?>
                    </button>

                    <p class="text-center">
                        <a href="<?php echo htmlspecialchars($baseSlash); ?>login.php?type=student&clear_student_step=1" class="text-sm text-slate-600 hover:text-green-600 font-medium">Use different Student ID</a>
                    </p>
                    <?php endif; ?>
                </form>

                <!-- Back to Portal -->
                <div class="mt-6 text-center">
                    <a href="<?php echo htmlspecialchars($baseSlash); ?>" class="text-sm text-slate-600 hover:text-blue-600 font-medium">
                        ← Back to Portal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(type) {
            // Update tabs
            const adminTab = document.getElementById('adminTab');
            const studentTab = document.getElementById('studentTab');
            const adminForm = document.getElementById('adminForm');
            const studentForm = document.getElementById('studentForm');
            
            if (type === 'admin') {
                adminTab.classList.remove('tab-inactive');
                adminTab.classList.add('tab-active');
                studentTab.classList.remove('tab-active');
                studentTab.classList.add('tab-inactive');
                adminForm.classList.remove('hidden');
                studentForm.classList.add('hidden');
                document.getElementById('username').focus();
            } else {
                studentTab.classList.remove('tab-inactive');
                studentTab.classList.add('tab-active');
                adminTab.classList.remove('tab-active');
                adminTab.classList.add('tab-inactive');
                studentForm.classList.remove('hidden');
                adminForm.classList.add('hidden');
                var pw = document.getElementById('student_password');
                var sid = document.getElementById('student_id');
                if (pw) pw.focus();
                else if (sid) sid.focus();
            }
        }
        
        // Set initial focus
        <?php if ($login_type === 'student'): ?>
        <?php if ($student_step === 2): ?>
        document.getElementById('student_password') && document.getElementById('student_password').focus();
        <?php else: ?>
        document.getElementById('student_id') && document.getElementById('student_id').focus();
        <?php endif; ?>
        <?php else: ?>
        document.getElementById('username').focus();
        <?php endif; ?>
    </script>
</body>
</html>
