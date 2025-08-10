<?php
session_start();
include 'include/db.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add secure mailer
require 'vendor/autoload.php';
require_once 'include/SecureMailer.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        $error_message = "Invalid request, please try again.";
    } else {
        // Sanitize inputs
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $phone = htmlspecialchars(trim($_POST['phone']));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate passwords match
        if ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else if (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        }
        // Validate email
        if (!$email && empty($error_message)) {
            $error_message = "Invalid email format.";
        } else if (empty($error_message)) {
            // Email uniqueness check
            $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
            if (!$stmt) {
                die("Database error: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $error_message = "An account with this email already exists!";
                $stmt->close();
            } else {
                $stmt->close();

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    die("Database error: " . $conn->error);
                }

                $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $phone);

                if ($stmt->execute()) {
                    // Send welcome email using secure mailer
                    try {
                        $mailer = new SecureMailer();
                        $mailer->sendWelcomeEmail($email, $first_name);
                    } catch (Exception $e) {
                        error_log("Email Error: " . $e->getMessage());
                    }

                    $_SESSION['success_message'] = "Account created successfully! Please log in.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error_message = "Error: Could not create account.";
                }
                $stmt->close();
            }
        }
    }
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ProDrivers</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { inter: ['Inter', 'sans-serif'] },
            colors: {
              primary: '#0a2a52',
              accent: '#0d6efd',
              light: '#f8fafc',
              dark: '#1e293b',
            },
          },
        },
      }
    </script>
    <style>
      body { font-family: 'Inter', sans-serif; }
      .no-scroll { overflow: hidden; }
</style>
</head>
<body class="bg-light min-h-screen flex items-center justify-center">
  <div class="w-full min-h-screen flex items-center justify-center py-8 px-2">
    <div class="bg-white rounded-2xl shadow-xl flex flex-col md:flex-row w-full max-w-4xl overflow-hidden">
      <!-- Left: Registration Form -->
      <div class="w-full md:w-1/2 flex flex-col justify-center px-8 py-10">
        <div class="flex items-center mb-8">
          <svg class="w-8 h-8 text-accent mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span class="text-2xl font-bold text-primary">ProDrivers</span>
        </div>
        <h1 class="text-3xl md:text-3xl font-extrabold text-primary mb-2">Get Started in Minutes</h1>
        <p class="text-gray-600 mb-6">Create your account and gain access to a network of professional, vetted drivers for your every need.</p>
          <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 text-red-700 border border-red-200 rounded px-4 py-3 mb-4 text-sm">
            <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
          </div>
          <?php endif; ?>
        <form id="registerForm" class="space-y-4" method="post" autocomplete="off" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
          <div class="flex gap-3">
            <div class="w-1/2">
              <label for="signup-first-name" class="block font-semibold mb-1">First Name</label>
              <input type="text" name="first_name" id="signup-first-name" required placeholder="John" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
              </div>
            <div class="w-1/2">
              <label for="signup-last-name" class="block font-semibold mb-1">Last Name</label>
              <input type="text" name="last_name" id="signup-last-name" required placeholder="Doe" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
            </div>
              </div>
          <div class="flex gap-3">
            <div class="w-1/2">
              <label for="signup-email" class="block font-semibold mb-1">Email Address</label>
              <input type="email" name="email" id="signup-email" required placeholder="you@example.com" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>
            <div class="w-1/2">
              <label for="signup-mobile-number" class="block font-semibold mb-1">Mobile Number</label>
              <input type="tel" name="phone" id="signup-mobile-number" required placeholder="(555) 555-5555" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
          </div>
          <div class="flex gap-3">
            <div class="w-1/2">
              <label for="signup-password" class="block font-semibold mb-1">Password</label>
              <div class="relative">
                <input type="password" name="password" id="signup-password" required placeholder="••••••••" minlength="8" class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-accent">
                <button type="button" tabindex="-1" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-accent" onclick="togglePassword('signup-password', 'eyeIcon1')">
                  <span id="eyeIcon1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </span>
                  </button>
              </div>
              <span class="text-xs text-gray-500">Must be at least 8 characters long.</span>
            </div>
            <div class="w-1/2">
              <label for="signup-confirm-password" class="block font-semibold mb-1">Confirm Password</label>
              <div class="relative">
                <input type="password" name="confirm_password" id="signup-confirm-password" required placeholder="••••••••" class="w-full border border-gray-300 rounded px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-accent">
                <button type="button" tabindex="-1" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-accent" onclick="togglePassword('signup-confirm-password', 'eyeIcon2')">
                  <span id="eyeIcon2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </span>
                  </button>
              </div>
            </div>
          </div>
          <div class="flex items-start mb-2">
            <input type="checkbox" name="terms" id="signup-checkbox" required class="mt-1 mr-2">
            <label for="signup-checkbox" class="text-sm text-gray-700">I agree to the <a href="#" class="text-accent font-semibold hover:underline">Terms of Service</a></label>
            </div>
          <button type="submit" name="register" class="w-full bg-primary hover:bg-accent text-white font-semibold rounded py-3 transition-colors">Create My Account</button>
          </form>
        <div class="text-center mt-4 text-sm text-gray-600">
          Already have an account? <a href="login.php" class="text-accent font-semibold hover:underline">Log In</a>
        </div>
      </div>
      <!-- Right: Image & Text -->
      <div class="hidden md:flex w-1/2 relative items-stretch">
        <img src="images/driver7.jpg" alt="Driver" class="object-cover w-full h-full" />
        <div class="absolute inset-0 bg-black bg-opacity-40 flex flex-col justify-end p-8">
          <div class="mb-8">
            <h2 class="text-white text-2xl font-bold mb-2">Your Trusted Partner in Professional Driving</h2>
            <p class="text-white text-base">Safety, reliability, and professionalism at your fingertips.</p>
          </div>
          </div>
        </div>
      </div>
    </div>
    <script>
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        // Password match validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('signup-password');
            const confirmPassword = document.getElementById('signup-confirm-password');
            if (password.value !== confirmPassword.value) {
        confirmPassword.classList.add('border-red-500', 'ring-2', 'ring-red-300');
                e.preventDefault();
            } else {
        confirmPassword.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
            }
        });
        // Password toggle functionality
    function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
      const iconSpan = document.getElementById(iconId);
      if (input && iconSpan) {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        iconSpan.innerHTML = type === 'password'
          ? `<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 12a3 3 0 11-6 0 3 3 0 016 0z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\" /></svg>`
          : `<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95m3.362-2.7A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.197M15 12a3 3 0 11-6 0 3 3 0 016 0z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M3 3l18 18\" /></svg>`;
            }
        }
    </script>
</body>
</html>
