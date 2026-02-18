<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modern Homepage</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen w-screen">
  <!-- Background Image -->
  <div class="relative h-screen w-screen bg-cover bg-center" style="background-image: url('/inventory_app/public/images/bg_school.jpg');">
    <!-- Overlay for better readability -->
    <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center items-center text-center px-4">
      
      <!-- Heading -->
      <h1 class="text-white text-4xl md:text-5xl font-bold mb-4">
        Welcome to the ICT Office
      </h1>
      
      <!-- Subheading -->
      <p class="text-white text-lg md:text-2xl mb-8 max-w-2xl">
        Manage inventory and monitor entrance logs efficiently with our modern system.
      </p>
      
      <!-- Buttons -->
      <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
        <a href="/inventory_app/public/?page=logbook" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
          View Logbook
        </a>
        <a href="/inventory_app/public/?page=login" class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-300">
          Login
        </a>
      </div>

    </div>
  </div>
</body>
</html>
