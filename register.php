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

        // Validate email
        if (!$email) {
            $error_message = "Invalid email format.";
        } else {
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
    <title>Register - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/user-theme.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
  min-height: 100vh; 
  font-family: 'Inter', sans-serif; 
  background: #fff;
  margin: 0;
  color: #333;
}
.signup { 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  min-height: 100vh; 
}
.signup-container { 
  background: none; 
  border-radius: 0; 
  box-shadow: none; 
  padding: 0; 
  max-width: 400px; 
  width: 100%; 
  margin: 0 auto; 
}
.signup-form { 
  margin-top: 0; 
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}
.signup-form-row {
  display: flex;
  gap: 1rem;
  width: 100%;
}
.signup-form-row .signup-label-container {
  flex: 1;
}
.signup-label-container { 
  margin-bottom: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.signup-label-container label {
  font-weight: 600;
  color: #333;
  font-size: 0.95rem;
  margin-bottom: 0.3rem;
}
.signup-form input[type="text"],
.signup-form input[type="email"],
.signup-form input[type="tel"],
.signup-form input[type="password"] {
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  background: #fff;
  transition: border 0.2s;
  width: 100%;
  box-sizing: border-box;
  color: #333;
}
.signup-form input:focus {
  border: 2px solid #003366;
  outline: none;
  background: #fff;
}
.signup-form input::placeholder {
  color: #999;
}
.password-container {
  position: relative;
  width: 100%;
}
.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  font-size: 16px;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  pointer-events: auto;
}
.password-toggle:hover {
  color: #003366;
}
.password-toggle:focus {
  outline: none;
}
.password-container input[type="password"],
.password-container input[type="text"] {
  padding-right: 40px;
}
.signup-label-checkbox { 
  display: flex; 
  align-items: flex-start; 
  gap: 0.5rem; 
  margin: 1rem 0; 
  font-size: 0.9rem;
}
.signup-label-checkbox input[type="checkbox"] {
  margin-top: 0.2rem;
}
.signup-label-checkbox label {
  font-weight: 400;
  margin: 0;
  color: #333;
  line-height: 1.4;
}
.signup-form button { 
  width: 100%; 
  background: #003366; 
  color: #fff; 
  border: none; 
  border-radius: 6px; 
  padding: 0.9rem; 
  font-weight: 600; 
  font-size: 1rem; 
  margin-top: 0.5rem; 
  transition: background 0.2s;
  cursor: pointer;
}
.signup-form button:hover {
  background: #002244;
}
.signup-form button:disabled { 
  background: #ccc; 
  cursor: not-allowed; 
}
.signup-no-account-container { 
  text-align: center; 
  margin-top: 1.5rem; 
  font-size: 0.95rem;
  color: #666;
}
.signup-no-account-container a { 
  color: #003366; 
  text-decoration: none; 
  font-weight: 600; 
}
.signup-no-account-container a:hover { 
  color: #002244; 
  text-decoration: underline; 
}
.error-message { 
  background: #f8d7da; 
  color: #721c24; 
  border: 1px solid #f5c6cb; 
  padding: 1rem; 
  border-radius: 6px; 
  margin-bottom: 1.5rem; 
  display: block; 
  text-align: center; 
  font-size: 0.9rem;
}
.signup-container h2 {
  text-align: center;
  font-weight: 700;
  margin-bottom: 2rem;
  font-size: 1.8rem;
  color: #333;
  letter-spacing: -0.5px;
}
@media (max-width: 600px) { 
  .signup-container { 
    padding: 1rem; 
  }
  .signup-form-row {
    flex-direction: column;
    gap: 1.5rem;
  }
}
    </style>
    <style>
.signup-split {
  display: flex;
  min-height: 100vh;
}
.signup-split-left {
  flex: 1;
  background: var(--light-bg);
  padding: 0;
  height: 100vh;
  min-width: 0;
  min-height: 0;
  position: relative;
  overflow: hidden;
}
.signup-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  border-radius: 0;
  position: absolute;
  top: 0;
  left: 0;
}
.signup-image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  color: #fff;
  padding: 2rem;
  z-index: 2;
}
.signup-image-overlay h1 {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
  color: #fff;
}
.signup-image-overlay p {
  font-size: 1.2rem;
  font-weight: 400;
  max-width: 400px;
  line-height: 1.6;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
  color: #fff;
}
.signup-split-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
}
@media (max-width: 900px) {
  .signup-split {
    flex-direction: column;
  }
  .signup-split-left, .signup-split-right {
    flex: unset;
    width: 100%;
    min-height: unset;
  }
  .signup-image {
    height: 220px;
    border-radius: 0;
    position: static;
  }
  .signup-split-right {
    padding: 1.5rem;
  }
}
@media (max-width: 600px) { 
  .signup-container { 
    padding: 1rem; 
  }
  .signup-form-row {
    flex-direction: column;
    gap: 1.5rem;
  }
  .signup-split-left {
    display: none;
  }
  .signup-split-right {
    flex: 1;
    width: 100%;
    padding: 2rem 1rem;
  }
}
</style>
</head>
<body>
    <div class="signup-split">
      <div class="signup-split-left">
        <img src="images/driver7.jpg" alt="Driver" class="signup-image" />
        <div class="signup-image-overlay">
          <h1>Book Drivers Today</h1>
          <p>Find reliable and professional drivers for all your transportation needs.</p>
        </div>
      </div>
      <div class="signup-split-right">
        <div class="signup-container">
          <h2>Register</h2>
          <?php if (!empty($error_message)): ?>
          <div class="error-message">
            <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
          </div>
          <?php endif; ?>
          <form id="registerForm" class="signup-form" method="post" autocomplete="off" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="signup-form-row">
              <div class="signup-label-container">
                <label for="signup-first-name">First Name</label>
                <input type="text" name="first_name" id="signup-first-name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
              </div>
              <div class="signup-label-container">
                <label for="signup-last-name">Last Name</label>
                <input type="text" name="last_name" id="signup-last-name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
              </div>
            </div>
            <div class="signup-form-row">
              <div class="signup-label-container">
                <label for="signup-email">Email</label>
                <input type="email" name="email" id="signup-email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
              </div>
              <div class="signup-label-container">
                <label for="signup-mobile-number">Mobile Number</label>
                <input type="tel" name="phone" id="signup-mobile-number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
              </div>
            </div>
            <div class="signup-form-row">
              <div class="signup-label-container">
                <label for="signup-password">Password</label>
                <div class="password-container" style="position: relative;">
                  <input type="password" name="password" id="signup-password" required style="padding-right: 2.5rem;">
                  <span class="password-toggle" id="togglePassword" onclick="togglePassword('signup-password', 'eyeIcon1')" style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); cursor: pointer; color: #003366; font-size: 1.1rem;">
                    <i class="fa fa-eye" id="eyeIcon1"></i>
                  </span>
                </div>
              </div>
              <div class="signup-label-container">
                <label for="signup-confirm-password">Confirm Password</label>
                <div class="password-container" style="position: relative;">
                  <input type="password" name="confirm_password" id="signup-confirm-password" required style="padding-right: 2.5rem;">
                  <span class="password-toggle" id="toggleConfirmPassword" onclick="togglePassword('signup-confirm-password', 'eyeIcon2')" style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); cursor: pointer; color: #003366; font-size: 1.1rem;">
                    <i class="fa fa-eye" id="eyeIcon2"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="signup-label-checkbox">
              <input type="checkbox" name="terms" id="signup-checkbox" required <?php if(isset($_POST['terms'])) echo 'checked'; ?>>
              <label for="signup-checkbox">I agree to <a href="#" style="color: #003366; text-decoration: none;">Terms of service</a></label>
            </div>
            <button type="submit" name="register">Create Account</button>
          </form>
          <div class="signup-no-account-container">
            <p>Already have an account? <a href="login.php">Sign In</a></p>
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
                confirmPassword.classList.add('is-invalid');
                e.preventDefault();
            } else {
                confirmPassword.classList.remove('is-invalid');
            }
        });
        // Password toggle functionality
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
          const icon = document.getElementById(iconId);
          if (input && icon) {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            icon.className = type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
            }
        }
    </script>
</body>
</html>
