<?php
session_start();
include 'include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the query to check if the user exists
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start the session and store user information
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            // Redirect user to the dashboard (or wherever you want)
            header("Location: dashboard-customer.php");
            exit();
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "No account found with this email!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pro-Drivers Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
    }

    .login-box {
      max-width: 420px;
      margin: 80px auto;
      padding: 40px 30px;
      background-color: white;
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    .logo {
      display: block;
      margin: 0 auto 30px;
      width: 120px;
      height: auto;
    }

    .form-control {
      border-radius: 12px;
    }

    .btn-primary {
      border-radius: 12px;
      background-color: #0052cc;
      border-color: #0052cc;
    }

    .btn-primary:hover {
      background-color: #003e99;
      border-color: #003e99;
    }

    .text-small {
      font-size: 0.9rem;
    }

    .signup-link {
      margin-top: 20px;
      text-align: center;
    }

    .signup-link a {
      color: #0052cc;
      text-decoration: none;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }

    .alert {
      color: red;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="login-box">
      <img src="images/sm_logo.png" alt="Logo" class="logo">
      <h4 class="text-center mb-4">Sign in to your account</h4>

      <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="e.g. user@example.com" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label text-small" for="rememberMe">Remember me</label>
          </div>
          <a href="#" class="text-small">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100">LoginA</button>
      </form>

      <div class="signup-link">
        <p class="mt-3 mb-0">Don't have an account? <a href="register.php">Create an account</a></p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>