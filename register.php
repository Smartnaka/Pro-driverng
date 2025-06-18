<?php
session_start();
include 'include/db.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
            padding: 2rem 0;
        }

        .main-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin: 2rem auto;
            max-width: 800px;
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
            min-width: 140px;
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
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: #1557b0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.2);
        }

        .form-check {
            margin: 1.5rem 0;
        }

        .form-check-input:checked {
            background-color: #1a73e8;
            border-color: #1a73e8;
        }

        .helper-links {
            text-align: center;
            margin-top: 1.5rem;
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

        @media (max-width: 768px) {
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
                <h2>Create Account</h2>
                <p class="text-muted">Join Pro-Drivers today</p>
            </div>

            <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>

            <form id="registerForm" method="post" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i> First Name
                            </span>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i> Last Name
                            </span>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i> Email
                            </span>
                            <input type="email" id="email" name="email" class="form-control" 
                                   required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i> Phone
                            </span>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i> Password
                            </span>
                            <input type="password" id="password" name="password" class="form-control" 
                                   required minlength="8">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i> Confirm
                            </span>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="form-control" required minlength="8">
                        </div>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" name="register" class="submit-btn">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>

                <div class="helper-links">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
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
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const terms = document.getElementById('terms');
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

            // Confirm password
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
            } else {
                confirmPassword.classList.remove('is-invalid');
            }

            // Terms checkbox
            if (!terms.checked) {
                terms.classList.add('is-invalid');
                isValid = false;
            } else {
                terms.classList.remove('is-invalid');
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
