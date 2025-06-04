<?php
session_start();
require 'include/db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];

    if (!$email) {
        $error_message = "Invalid email format.";
    } else {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "An account with this email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into DB
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                // Send welcome email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'israelbabs59@gmail.com';
                    $mail->Password = 'uenb rrvr lyrl rzje'; // Use an App Password
                    $mail->SMTPSecure = 'PHPMailer::ENCRYPTION_STARTTLS;';
                    $mail->Port = 587;

                    $mail->setFrom('no-reply@prodrivers.com', 'Pro-Drivers');
                    $mail->addAddress($email, $first_name);

                    $mail->isHTML(true);
                    $mail->Subject = '🎉 Registration Successful - Welcome to Pro-Drivers!';
                    $mail->Body = "
                        <h2>Hello {$first_name},</h2>
                        <p>Welcome to <strong>Pro-Drivers</strong>! Your registration was successful.</p>
                        <p>You can now log in and start using our services.</p>
                        <br><hr>
                        <p style='font-size: 12px; color: #777;'>This email was sent to you because you signed up at Pro-Drivers. If you didn’t register, you can ignore this email.</p>
                    ";
                    $mail->AltBody = "Hello {$first_name},\n\nWelcome to Pro-Drivers! Your registration was successful.\n\nThis email was sent because you registered at Pro-Drivers.";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("PHPMailer Error: " . $mail->ErrorInfo);
                }

                $_SESSION['success_message'] = "Account created successfully! Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Error: Could not register user. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pro-Drivers Register</title>
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background-color: #f0f2f5; }
    .register-box {
      max-width: 420px;
      margin: 80px auto;
      padding: 40px 30px;
      background-color: white;
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    .logo { display: block; margin: 0 auto 30px; width: 120px; }
    .form-control { border-radius: 12px; }
    .btn-primary {
      border-radius: 12px;
      background-color: #0052cc;
      border-color: #0052cc;
    }
    .btn-primary:hover {
      background-color: #003e99;
      border-color: #003e99;
    }
    .signup-link { text-align: center; margin-top: 20px; }
    .signup-link a { color: #0052cc; text-decoration: none; }
    .signup-link a:hover { text-decoration: underline; }
    .alert { text-align: center; }
    .input-group-text { cursor: pointer; }
  </style>
</head>
<body>
  <div class="container">
    <div class="register-box">
      <img src="images/sm_logo.png" alt="Logo" class="logo" />
      <h4 class="text-center mb-4">Create your account</h4>

      <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
      <?php endif; ?>

      <form action="register.php" method="POST">
        <div class="mb-3">
          <label for="first_name" class="form-label">First Name</label>
          <input type="text" class="form-control" id="first_name" name="first_name" required />
        </div>
        <div class="mb-3">
          <label for="last_name" class="form-label">Last Name</label>
          <input type="text" class="form-control" id="last_name" name="last_name" required />
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" name="email" required />
        </div>
        <div class="mb-3">
          <label for="phone" class="form-label">Mobile Number</label>
          <input type="tel" class="form-control" id="phone" name="phone" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" />
            <span class="input-group-text" onclick="togglePassword()">
              <i id="toggleIcon" class="bi bi-eye"></i>
            </span>
          </div>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required />
          <label class="form-check-label" for="agreeTerms">
            I agree to the <a href="terms/signup" target="_blank">Terms of Service</a>
          </label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Create Account</button>
      </form>

      <div class="signup-link">
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById("password");
      const icon = document.getElementById("toggleIcon");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
