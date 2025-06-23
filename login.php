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

        .submit-btn:disabled {
            background: #a0c3f7;
            cursor: not-allowed;
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
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none; /* Hidden by default */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="page-header">
                <h2>Customer Login</h2>
                <p>Access your Pro-Drivers account</p>
            </div>
            
            <div id="error-message" class="error-message"></div>

            <form id="login-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="input-group">
                    <label for="email" class="input-group-text"><i class="fas fa-envelope"></i> &nbsp;Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label for="password" class="input-group-text"><i class="fas fa-lock"></i> &nbsp;Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" id="submit-btn" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i>&nbsp; Login
                </button>
            </form>

            <div class="helper-links">
                <a href="forgot-password.php">Forgot password?</a> | 
                <a href="register.php">Create an account</a>
            </div>
        </div>
    </div>

    <script src="assets/javascript/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const submitBtn = document.getElementById('submit-btn');
            const errorMessageDiv = document.getElementById('error-message');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>&nbsp; Logging in...';
                errorMessageDiv.style.display = 'none';

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
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i>&nbsp; Login';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMessageDiv.textContent = 'An unexpected error occurred. Please try again.';
                    errorMessageDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i>&nbsp; Login';
                });
            });
        });
    </script>
</body>
</html>