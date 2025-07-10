<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Zus Coffee</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1d4ed8',
            primaryHover: '#1e40af',
          }
        }
      }
    }
  </script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 font-sans">

  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-sm text-center">
    <img src="logo-welcome.png" alt="Zus Coffee Logo" class="w-48 mx-auto mb-4 transition hover:scale-105" />
    
    <p class="text-sm text-gray-500 mb-1 tracking-wide">ZUS SUPPLY CHAIN MANAGEMENT</p>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Login</h2>

    <!-- Show error message if set -->
    <?php if (isset($_SESSION['login_error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm">
        <?= $_SESSION['login_error'] ?>
      </div>
      <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <form action="ZusSupplyManagement.php" method="post" class="space-y-4">
      <input 
        type="text" 
        name="username" 
        placeholder="Username" 
        required 
        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
      >
      <input 
        type="password" 
        name="userpass" 
        placeholder="Password" 
        required 
        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
      >
      <button 
        type="submit" 
        class="w-full bg-primary hover:bg-primaryHover text-white font-semibold py-2 rounded transition"
      >
        Login
      </button>
    </form>
  </div>

  <footer class="absolute bottom-6 text-sm text-gray-500 text-center w-full">
    &copy; 2025 Zus Coffee. All rights reserved.
  </footer>
</body>
</html>
