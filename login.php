<?php
session_start();
include 'include/db.php';

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
                // Check if user exists
                $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM customers WHERE email = ?");
                if (!$stmt) {
                    throw new Exception($conn->error);
                }

                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Start the session and store user information
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['last_activity'] = time();

                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error_message = "Invalid email or password.";
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
    <title>Login - Pro-Drivers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            color: #333;
            display: flex;
            align-items: center;
        }

        .main-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin: 2rem auto;
            max-width: 500px;
            width: 100%;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .page-header h2 {
            color: #1a73e8;
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .page-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #1a73e8;
            border-radius: 2px;
        }

        .input-group {
            margin-bottom: 1.5rem;
            border: 1px solid #e1e5ea;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            border-color: #1a73e8;
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
        }

        .input-group-text {
            background: #f8f9fa;
            border: none;
            color: #1a73e8;
            font-weight: 500;
            min-width: 120px;
            padding: 0.8rem 1rem;
        }

        .form-control {
            border: none;
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: none;
        }

        .submit-btn {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .submit-btn:hover {
            background: #1557b0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.2);
        }

        .helper-links {
            text-align: center;
        }

        .helper-links a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
        }

        .helper-links a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #fee;
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
        
        .error-message i {
            color: #c00;
            font-size: 1.1rem;
        }

        @media (max-width: 576px) {
            .main-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .page-header h2 {
                font-size: 1.75rem;
            }

            .input-group-text {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="page-header">
                <h2>Welcome Back</h2>
                <p class="text-muted">Sign in to your account</p>
            </div>

            <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>

            <form id="loginForm" method="post" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i> Email
                    </span>
                    <input type="email" id="email" name="email" class="form-control" 
                           required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                           autocomplete="off">
                </div>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i> Password
                    </span>
                    <input type="password" id="password" name="password" class="form-control" 
                           required minlength="8"
                           autocomplete="off">
                </div>

                <button type="submit" name="login" class="submit-btn">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>

                <div class="helper-links">
                    <p>
                        <a href="forgot-password.php">
                            <i class="fas fa-key me-1"></i> Forgot your password?
                        </a>
                    </p>
                    <p>
                        Don't have an account? 
                        <a href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Create one now
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let isValid = true;

            // Email validation
            if (!email.value.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            // Password validation
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

        // Prevent XSS in form inputs
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[<>]/g, '');
            });
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
    </script>
</body>
</html>