<?php
if (!defined('BASE_URL')) { require_once dirname(__DIR__, 2) . '/auth/path_config_loader.php'; }
$ict_public = (rtrim(BASE_URL, '/') === '' ? '' : rtrim(BASE_URL, '/')) . '/ictOffice/public';
header("Location: " . $ict_public . "/?page=dashboard");
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative min-h-screen flex flex-col items-center justify-start bg-cover bg-no-repeat" 
      style="background-image: url('<?php echo htmlspecialchars($ict_public); ?>/images/bg2.jpg');">

  <!-- Overlay to dim the background -->
  <div class="absolute inset-0 bg-black/60 z-0"></div>

  <!-- Top Navigation -->
  <nav class="w-full bg-blue-600 bg-opacity-80 shadow-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <!-- Logo -->
        <div class="flex-shrink-0">
          <img class="h-10 w-10 object-contain" src="<?php echo htmlspecialchars($ict_public); ?>/images/school_logo.png" alt="Logo">
        </div>
        <!-- Links -->
        <div class="hidden md:flex space-x-8">
          <a href="/" class="text-white hover:text-blue-300 font-medium transition">Home</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Centered Login Card -->
  <div class="flex-grow flex items-center justify-center mt-20 w-full z-10 relative">
    <div class="bg-white/90 backdrop-blur-md border border-white/20 shadow-2xl rounded-xl w-full max-w-md p-8">
      
      <!-- Logo -->
      <div class="flex justify-center mb-4">
        <img 
          src="<?php echo htmlspecialchars($ict_public); ?>/images/school_logo.png" 
          alt="Logo" 
          class="h-16 w-16 object-contain"
        />
      </div>

      <h2 class="text-3xl font-bold text-center text-blue-600 mb-6">
        ICT Office Login
      </h2>

      <form class="space-y-5" id="loginForm">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input
            name="username"
            type="text"
            id="username"
            placeholder="Username"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input
            name="password"
            type="password"
            id="password"
            placeholder="••••••••"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center space-x-2">
            <input type="checkbox" class="text-blue-600 rounded" />
            <span>Remember me</span>
          </label>
          <a href="#" class="text-blue-600 hover:underline">Forgot password?</a>
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition"
        >
          Login
        </button>
      </form>
    </div>
  </div>

  <!-- Alert container -->
  <div id="alertContainer" class="fixed top-5 right-5 flex flex-col gap-2 z-50"></div>

<script>
function showAlert(message, type = 'success', duration = 3000) {
  const container = document.getElementById('alertContainer');
  const alert = document.createElement('div');
  const isSuccess = type === 'success';

  alert.setAttribute('role', 'alert');

  alert.innerHTML = `
    <div class="${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white font-bold rounded-t px-4 py-2">
      ${isSuccess ? 'Success' : 'Error'}
    </div>
    <div class="border border-t-0 rounded-b px-4 py-3 ${isSuccess ? 'border-green-400 bg-green-100 text-green-700' : 'border-red-400 bg-red-100 text-red-700'}">
      <p>${message}</p>
    </div>
  `;

  container.appendChild(alert);

  setTimeout(() => {
    alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
    setTimeout(() => alert.remove(), 500);
  }, duration);
}

const loginForm = document.getElementById('loginForm');

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(loginForm);
  const itemData = Object.fromEntries(formData);

  fetch((window.ICT_API || <?php echo json_encode((rtrim(BASE_URL,'/')==='' ? '' : rtrim(BASE_URL,'/')).'/ictOffice/api'); ?>) + '/login', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(itemData)
  })
  .then(res => res.json())
  .then(data=>{
    if(data.success){
      window.location.href = (window.ICT_PUBLIC || <?php echo json_encode($ict_public); ?>) + '/?page=dashboard'
    } else {
      showAlert(data.error || 'Error login unsuccessful', 'error');
    }
  })
  .catch(err=>{
    console.error(err);
    showAlert('Error login unsuccessful', 'error');
  });
});
</script>

</body>
</html>
