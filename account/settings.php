<?php
$app_root = dirname(__DIR__);
if (!defined('BASE_URL')) {
    require_once $app_root . '/auth/path_config_loader.php';
}
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/security.php';
require_once $app_root . '/auth/admin_auth.php';
require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_helper.php';

$base = rtrim(BASE_URL, '/');
$baseS = $base === '' ? '/' : $base . '/';

if (!isAdminLoggedIn() && !isStudentLoggedIn()) {
    header("Location: " . $baseS . "login.php");
    exit();
}

$isAdmin = isAdminLoggedIn();
$isStudent = isStudentLoggedIn();
$userInfo = $isAdmin ? AdminAuth::getAdminInfo() : getStudentInfo();
$userName = $isAdmin ? ($userInfo['name'] ?? 'Admin') : ($userInfo['name'] ?? 'Student');
$userType = $isAdmin ? 'Admin' : 'Student';

$success = '';
$error = '';
$activeTab = $_GET['tab'] ?? 'profile';

if ($isAdmin) {
    require_once $app_root . '/auth/check_admin_access.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!msms_verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please refresh and try again.';
    } elseif ($isAdmin) {
        $adminAuth = new AdminAuth();
        $adminId = $userInfo['id'];
        
        if (isset($_POST['update_profile'])) {
            
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (empty($fullName)) {
                $error = 'Full name is required';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Valid email is required';
            } else {
                $result = $adminAuth->updateProfile($adminId, $fullName, $email);
                if ($result['success']) {
                    $success = $result['message'];
                    
                    $userInfo = AdminAuth::getAdminInfo();
                    $userName = $userInfo['name'];
                } else {
                    $error = $result['message'];
                }
            }
        } elseif (isset($_POST['change_password'])) {
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'All password fields are required';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New password and confirmation do not match';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters long';
            } else {
                $result = $adminAuth->changePassword($adminId, $currentPassword, $newPassword);
                if ($result['success']) {
                    $success = $result['message'];
                    $activeTab = 'profile'; 
                } else {
                    $error = $result['message'];
                    $activeTab = 'password'; 
                }
            }
        }
    }
}

if ($isAdmin):
    require_once $app_root . '/admin/header_unified.php';
?>
<div class="max-w-4xl mx-auto px-6">
    <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6 border-b border-slate-700">
            <h1 class="text-2xl font-black text-white mb-1">Account Settings</h1>
            <p class="text-slate-400 text-sm">Manage your account information and security</p>
        </div>

        <!-- Content -->
        <div class="px-8 py-6">
            <!-- User Info Card -->
            <div class="bg-slate-50 rounded-xl p-6 mb-6 border-2 border-slate-200">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900"><?php echo htmlspecialchars($userName); ?></h2>
                        <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide"><?php echo $userType; ?></p>
                        <?php if ($isAdmin): ?>
                        <p class="text-xs text-slate-500 mt-1">Username: <?php echo htmlspecialchars($userInfo['username'] ?? 'N/A'); ?></p>
                        <?php else: ?>
                        <p class="text-xs text-slate-500 mt-1">Student ID: <?php echo htmlspecialchars($userInfo['student_id'] ?? 'N/A'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                    <p class="text-sm text-green-800 font-semibold"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-sm text-red-800 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
            <!-- Tabs -->
            <div class="flex border-b border-slate-200 mb-6">
                <button onclick="switchTab('profile')" id="profileTab" class="px-6 py-3 font-bold text-sm uppercase tracking-wide transition-all duration-300 <?php echo $activeTab === 'profile' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500 hover:text-blue-600'; ?>">
                    Profile Information
                </button>
                <button onclick="switchTab('password')" id="passwordTab" class="px-6 py-3 font-bold text-sm uppercase tracking-wide transition-all duration-300 <?php echo $activeTab === 'password' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500 hover:text-blue-600'; ?>">
                    Change Password
                </button>
            </div>

            <!-- Profile Tab -->
            <div id="profileSection" class="<?php echo $activeTab === 'profile' ? '' : 'hidden'; ?>">
                <h3 class="text-lg font-bold text-slate-900 mb-4 uppercase tracking-wide">Profile Information</h3>
                
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($userInfo['name'] ?? ''); ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($userInfo['email'] ?? ''); ?>" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($userInfo['username'] ?? 'N/A'); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border-2 border-slate-200 rounded-xl text-slate-600 cursor-not-allowed">
                        <p class="text-xs text-slate-500 mt-1">Username cannot be changed</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Role</label>
                        <input type="text" value="<?php echo htmlspecialchars($userInfo['role'] ?? 'Admin'); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border-2 border-slate-200 rounded-xl text-slate-600 cursor-not-allowed">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Tab -->
            <div id="passwordSection" class="<?php echo $activeTab === 'password' ? '' : 'hidden'; ?>">
                <h3 class="text-lg font-bold text-slate-900 mb-4 uppercase tracking-wide">Change Password</h3>
                
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Current Password</label>
                        <input type="password" name="current_password" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">New Password</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                        <p class="text-xs text-slate-500 mt-1">Password must be at least 6 characters long</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none transition-all">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <!-- Student View (Read-only) -->
            <div>
                <h3 class="text-lg font-bold text-slate-900 mb-4 uppercase tracking-wide">Account Information</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($userName); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Student ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($userInfo['student_id'] ?? 'N/A'); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Grade & Section</label>
                        <input type="text" value="<?php echo htmlspecialchars(($userInfo['grade'] ?? '') . ' - ' . ($userInfo['section'] ?? '')); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="pt-6 mt-6 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo htmlspecialchars($baseS); ?>admin/dashboard.php" class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl transition-all text-center">
                        ← Back to Dashboard
                    </a>
                    <a href="<?php echo htmlspecialchars($baseS); ?>admin/logout.php" class="logout-btn flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all text-center">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    // Update tab buttons
    document.getElementById('profileTab').classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
    document.getElementById('profileTab').classList.add('text-slate-500');
    document.getElementById('passwordTab').classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
    document.getElementById('passwordTab').classList.add('text-slate-500');
    
    if (tab === 'profile') {
        document.getElementById('profileTab').classList.remove('text-slate-500');
        document.getElementById('profileTab').classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
        document.getElementById('profileSection').classList.remove('hidden');
        document.getElementById('passwordSection').classList.add('hidden');
    } else {
        document.getElementById('passwordTab').classList.remove('text-slate-500');
        document.getElementById('passwordTab').classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
        document.getElementById('passwordSection').classList.remove('hidden');
        document.getElementById('profileSection').classList.add('hidden');
    }
}
</script>

<?php
    require_once $app_root . '/admin/footer_unified.php';
else:
    include $app_root . '/partials/header.php';
?>
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
            <h1 class="text-3xl font-black text-white mb-2">Account Settings</h1>
            <p class="text-blue-100 text-sm">Manage your account information</p>
        </div>

        <!-- Content -->
        <div class="px-8 py-6">
            <!-- User Info Card -->
            <div class="bg-slate-50 rounded-xl p-6 mb-6 border border-slate-200">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900"><?php echo htmlspecialchars($userName); ?></h2>
                        <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide"><?php echo $userType; ?></p>
                        <p class="text-xs text-slate-500 mt-1">Student ID: <?php echo htmlspecialchars($userInfo['student_id'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                    <p class="text-sm text-green-800 font-semibold"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <p class="text-sm text-red-800 font-semibold"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <!-- Student View (Read-only) -->
            <div>
                <h3 class="text-lg font-bold text-slate-900 mb-4 uppercase tracking-wide">Account Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($userName); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Student ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($userInfo['student_id'] ?? 'N/A'); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Grade & Section</label>
                        <input type="text" value="<?php echo htmlspecialchars(($userInfo['grade'] ?? '') . ' - ' . ($userInfo['section'] ?? '')); ?>" disabled class="w-full px-4 py-3 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-6 mt-6 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo htmlspecialchars($base ? $base . '/' : '/'); ?>" class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-lg transition-all text-center">
                        Back to Portal
                    </a>
                    <a href="<?php echo htmlspecialchars($baseS); ?>student/logout.php" class="logout-btn flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-all text-center">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include $app_root . '/partials/footer.php'; ?>
<?php endif; ?>
