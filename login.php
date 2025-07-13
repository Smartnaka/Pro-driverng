<?php
session_start();

// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'include/db.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set basic security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");
header("Content-Security-Policy: default-src 'self' http: https: data: 'unsafe-inline' 'unsafe-eval'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - ProDrivers</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
  <!-- Navbar -->
  <nav class="flex flex-col sm:flex-row items-center justify-between px-4 sm:px-8 py-5 bg-white border-b gap-3 sm:gap-0">
    <div class="flex items-center gap-2">
      <span class="block w-7 h-7 bg-blue-900 rounded-sm"></span>
      <span class="text-xl font-bold text-blue-900">ProDrivers</span>
    </div>
    <!-- Hamburger for mobile -->
    <div class="sm:hidden ml-auto">
      <button id="mobile-menu-btn" aria-label="Open menu" class="p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-900">
        <svg id="hamburger-icon" class="h-6 w-6 text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg id="close-icon" class="h-6 w-6 text-blue-900 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <!-- Desktop menu -->
    <div id="desktop-menu" class="hidden sm:flex flex-col sm:flex-row items-center gap-2 sm:gap-6 w-full sm:w-auto">
      <a href="driver/register.php" class="text-gray-700 hover:text-blue-900 transition text-sm sm:text-base">Become a Driver</a>
      <a href="help.php" class="text-gray-700 hover:text-blue-900 transition text-sm sm:text-base">Help</a>
      <a href="register.php" class="px-4 py-2 border border-blue-900 rounded-md text-blue-900 font-semibold hover:bg-blue-900 hover:text-white transition text-sm sm:text-base w-full sm:w-auto text-center">Sign Up</a>
    </div>
    <!-- Mobile menu -->
    <div id="mobile-menu" class="sm:hidden fixed inset-0 z-40 bg-black bg-opacity-40 transition-opacity duration-200 hidden">
      <div class="absolute top-0 right-0 h-full w-3/4 max-w-xs bg-white shadow-lg flex flex-col justify-between animate-none transform translate-x-full transition-transform duration-300" id="mobile-menu-panel">
        <div>
          <div class="flex justify-end p-4">
            <button id="close-menu-btn" aria-label="Close menu" class="p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-900">
              <svg class="h-6 w-6 text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <nav class="flex flex-col gap-6 items-start px-6 mt-8">
            <a href="driver/register.php" class="text-gray-700 hover:text-blue-900 transition text-base">Become a Driver</a>
            <a href="help.php" class="text-gray-700 hover:text-blue-900 transition text-base">Help</a>
            <a href="register.php" class="px-4 py-2 border border-blue-900 rounded-md text-blue-900 font-semibold hover:bg-blue-900 hover:text-white transition text-base text-center w-full">Sign Up</a>
          </nav>
        </div>
        <div class="h-8"></div>
      </div>
    </div>
  </nav>
  <!-- Login Form -->
  <main class="flex-1 flex items-center justify-center px-2 sm:px-0">
    <form class="bg-white rounded-xl shadow-md p-4 sm:p-8 w-full max-w-md" method="POST" action="api/login_handler.php" id="login-form" autocomplete="on">
      <h1 class="text-xl sm:text-2xl font-bold mb-2 text-center">Login to your account</h1>
      <p class="text-gray-500 text-center mb-6 text-sm sm:text-base">Welcome back! Please enter your details.</p>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
      <div id="error-message" class="bg-red-100 text-red-700 rounded px-3 py-2 mb-4 text-sm text-center hidden"></div>
      <div class="mb-4">
        <label for="email" class="block text-gray-700 font-medium mb-1 text-sm sm:text-base">Email</label>
        <input type="email" id="email" name="email" required placeholder="you@example.com" class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm sm:text-base" />
      </div>
      <div class="mb-4">
        <div class="flex justify-between items-center mb-1">
          <label for="password" class="text-gray-700 font-medium text-sm sm:text-base">Password</label>
          <a href="forgot-password.php" class="text-blue-900 text-xs sm:text-sm hover:underline">Forgot Password?</a>
        </div>
        <input type="password" id="password" name="password" required class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-900 text-sm sm:text-base" />
      </div>
      <button type="submit" id="submit-btn" class="w-full bg-blue-900 text-white font-semibold py-2 rounded-md mt-2 hover:bg-blue-800 transition text-sm sm:text-base">Login</button>
      <p class="text-center text-gray-600 mt-6 text-sm sm:text-base">Don't have an account? <a href="register.php" class="text-blue-900 font-semibold hover:underline">Sign Up</a></p>
    </form>
  </main>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('login-form');
      const submitBtn = document.getElementById('submit-btn');
      const errorMessageDiv = document.getElementById('error-message');
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';
        errorMessageDiv.style.display = 'none';
        errorMessageDiv.classList.add('hidden');
        const formData = new FormData(form);
        fetch('api/login_handler.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = data.redirect_url;
          } else {
            errorMessageDiv.textContent = data.message;
            errorMessageDiv.style.display = 'block';
            errorMessageDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login';
          }
        })
        .catch(error => {
          errorMessageDiv.textContent = 'An unexpected error occurred. Please try again.';
          errorMessageDiv.style.display = 'block';
          errorMessageDiv.classList.remove('hidden');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Login';
        });
      });
    });

    // Hamburger menu logic
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const desktopMenu = document.getElementById('desktop-menu');
    const hamburgerIcon = document.getElementById('hamburger-icon');
    const closeIcon = document.getElementById('close-icon');
    const mobileMenuPanel = document.getElementById('mobile-menu-panel');
    const closeMenuBtn = document.getElementById('close-menu-btn');

    function openMenu() {
      mobileMenu.classList.remove('hidden');
      setTimeout(() => {
        mobileMenu.classList.add('opacity-100');
        mobileMenuPanel.classList.remove('translate-x-full');
        mobileMenuPanel.classList.add('translate-x-0');
      }, 10);
      hamburgerIcon.classList.add('hidden');
      closeIcon.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }
    function closeMenu() {
      mobileMenu.classList.remove('opacity-100');
      mobileMenuPanel.classList.remove('translate-x-0');
      mobileMenuPanel.classList.add('translate-x-full');
      setTimeout(() => {
        mobileMenu.classList.add('hidden');
      }, 200);
      hamburgerIcon.classList.remove('hidden');
      closeIcon.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
    if (mobileMenuBtn) {
      mobileMenuBtn.addEventListener('click', function() {
        if (mobileMenu.classList.contains('hidden')) {
          openMenu();
        } else {
          closeMenu();
        }
      });
    }
    if (closeMenuBtn) {
      closeMenuBtn.addEventListener('click', closeMenu);
    }
    if (mobileMenu) {
      mobileMenu.addEventListener('click', function(e) {
        if (e.target === mobileMenu) closeMenu();
      });
    }
    window.addEventListener('resize', function() {
      if (window.innerWidth >= 640) {
        closeMenu();
      }
    });
  </script>
</body>
</html>