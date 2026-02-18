<?php require_once __DIR__ . '/../auth/path_config_loader.php'; $base = rtrim(BASE_URL, '/'); $baseS = $base === '' ? '/' : $base . '/'; $baseSlash = $base . '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | MSMS</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($baseS); ?>assets/images/32x32.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <div class="inline-block bg-blue-600 p-4 rounded-full mb-4">
                    <img src="<?php echo htmlspecialchars($baseS); ?>assets/images/school_logo.png" alt="Logo" class="w-16 h-16 object-contain">
                </div>
                <h1 class="text-3xl font-black text-slate-900 mb-2">MSMS Admin</h1>
                <p class="text-slate-600 text-sm">Holy Cross of Mintal, Inc.</p>
            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p class="font-semibold"><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p class="font-semibold"><?php echo htmlspecialchars($_GET['success']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="/admin/login_process.php" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
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
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
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
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    Sign In
                </button>
            </form>

            <!-- Back to Portal -->
            <div class="mt-6 text-center">
                <a href="<?php echo htmlspecialchars($baseSlash); ?>" class="text-sm text-slate-600 hover:text-blue-600 font-medium">
                    ← Back to Portal
                </a>
            </div>
        </div>
    </div>
</body>
</html>
