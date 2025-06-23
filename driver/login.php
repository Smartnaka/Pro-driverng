<?php
define('SECURE_ACCESS', true);
require_once '../include/db.php';
require_once '../include/config.php';

// Start session with basic settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$error_message = '';
$email = '';

// Check for error message in session and assign to variable
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the session error
}

// Function to log activity
function logActivity($conn, $user_id, $action) {
    try {
        // Check if activity_log table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'activity_log'");
        if ($table_check->num_rows === 0) {
            // Create activity_log table if it doesn't exist
            $create_table_sql = "CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES drivers(id) ON DELETE CASCADE
            )";
            $conn->query($create_table_sql);
        }

        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $stmt->bind_param("isss", $user_id, $action, $ip, $user_agent);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        $error_message = "Invalid request, please try again.";
    } else {
        // Sanitize and validate inputs
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        // Validate email
        if (!$email) {
            $error_message = "Invalid email format.";
        } else {
            try {
                // Check if email exists
                $stmt = $conn->prepare("SELECT id, password, status FROM drivers WHERE email = ?");
                if (!$stmt) {
                    throw new Exception($conn->error);
                }

                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    
                    // Verify password and check account status
                    if (password_verify($password, $row['password'])) {
                        if ($row['status'] === 'rejected') {
                            $error_message = "Your account has been rejected. Please contact support.";
                            logActivity($conn, $row['id'], 'login_rejected');
                        } else {
                            // Successful login - allow both 'pending' and 'approved' status
                            session_regenerate_id(true);
                            $_SESSION['driver_id'] = $row['id'];
                            $_SESSION['last_activity'] = time();
                            $_SESSION['status'] = $row['status'];
                            
                            // Log successful login
                            logActivity($conn, $row['id'], 'login_success');
                            
                            // Redirect to dashboard
                            header("Location: dashboard.php");
                            exit();
                        }
                    } else {
                        $error_message = "Invalid email or password.";
                        if (isset($row['id'])) {
                            logActivity($conn, $row['id'], 'login_failed');
                        }
                    }
                } else {
                    $error_message = "Invalid email or password.";
                }
                $stmt->close();
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = "An error occurred. Please try again later.";
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
    <title>Driver Login - Pro-Drivers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.cdnfonts.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/driver-theme.css">
    <style>
        @font-face {
            font-family: 'Euclid Circular B';
            src: url('https://fonts.cdnfonts.com/s/17397/EuclidCircularB-Bold.woff') format('woff');
            font-weight: 700 900;
            font-style: normal;
            font-display: swap;
        }
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('../images/login.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8vw;
        }
        /* Bolt-inspired left-content styles */
        .left-content {
            flex: 1 1 0%;
            max-width: 532px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 0 0 0 3vw; /* shift more to the left */
            min-height: 100vh;
        }
        .left-content h1 {
            font-family: 'Euclid Circular B';
            font-weight: 900;
            font-size: 48px;
            line-height: 1.05;
            letter-spacing: -2px;
            color: #fff;
            margin: 0 0 24px 0;
            padding: 0;
            background: none;
            width: 100%;
            max-width: 532px;
        }
        @media (min-width: 768px) {
            .left-content h1 {
                font-size: 80px !important;
            }
        }
        .left-content .subtitle {
            font-family: 'Euclid Circular B';
            font-weight: 500;
            font-size: 1.35rem;
            color: #fff;
            line-height: 1.4;
            margin-bottom: 0;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            max-width: 400px;
            width: 100%;
            margin-left: auto;
        }
        .login-title {
            font-weight: 600;
            color: #222;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .login-subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #222;
        }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e1e5ea;
            padding: 0.9rem 1rem;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 0 2px rgba(0, 51, 102, 0.15);
        }
        .btn-bolt {
            background: #003366;
            color: #fff;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.9rem 0;
            margin-top: 1rem;
            transition: background 0.2s;
        }
        .btn-bolt:hover {
            background: #00509e;
        }
        .error-message {
            background: #fee;
            color: #c00;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #fcc;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .helper-links {
            margin-top: 1.5rem;
            text-align: center;
        }
        .helper-links a {
            color: #003366;
            text-decoration: none;
            font-weight: 500;
            margin: 0 0.5rem;
        }
        .helper-links a:hover {
            text-decoration: underline;
            color: #00509e;
        }
        @media (max-width: 991.98px) {
            .login-container {
                justify-content: center;
                padding: 0 2rem;
                flex-direction: column;
            }
            .left-content {
                align-items: center;
                text-align: center;
                padding: 3rem 1rem 1.5rem 1rem;
                min-height: unset;
            }
            .left-content h1 {
                font-size: 2.2rem;
            }
            .login-card {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid login-container">
        <!-- Left Content Section -->
        <div class="left-content">
            <h1 style="font-family: 'Euclid Circular B'; font-weight: 900; font-size: 48px; line-height: 1.05; letter-spacing: -2px; color: #fff; margin: 0 0 24px 0; padding: 0; background: none; width: 100%; max-width: 532px;">
                Make money driving with ProDriver
            </h1>
            <div class="subtitle" style="font-family: 'Euclid Circular B'; font-weight: 500; font-size: 1.35rem; color: #fff; line-height: 1.4; margin-bottom: 0;">
                Become a ProDriver, set your schedule and earn money by driving!
            </div>
        </div>
        
        <!-- Login Form Section -->
        <div class="login-card">
            <div class="login-title">Sign in to your driver account</div>
            <div class="login-subtitle">Drive and earn with ProDriver</div>
            <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>
            <form id="loginForm" method="post" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" style="padding-right: 2.5rem;">
                        <span id="togglePassword" style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); cursor: pointer; color: #003366; font-size: 1.1rem;">
                            <i class="fa fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn btn-bolt w-100">Sign In</button>
                <div class="helper-links mt-3">
                    <a href="forgot-password.php">Forgot password?</a> |
                    <a href="register.php">Create account</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../assets/javascript/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let isValid = true;
            if (!email.value.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
            }
            if (password.value.length < 8) {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
            }
            if (!isValid) {
                e.preventDefault();
            }
        });
        // Auto-hide alerts after 4 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert, .error-message');
            alerts.forEach(alert => {
                alert.classList.add('fade');
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500);
            });
        }, 4000);
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (togglePassword && passwordInput && eyeIcon) {
          togglePassword.addEventListener('click', function () {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            eyeIcon.className = type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
          });
        }
    </script>
</body>
</html>