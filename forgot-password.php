<?php
session_start();
require 'include/db.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error_message = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $reset_token = bin2hex(random_bytes(32));
            $expires = time() + 3600;

            $stmt = $conn->prepare("INSERT INTO password_resets (email, reset_token, expires) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $email, $reset_token, $expires);
            $stmt->execute();

            $reset_url = " ?token=$reset_token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'israelbabs59@gmail.com';
                $mail->Password = 'uenb rrvr lyrl rzje';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('yourgmail@gmail.com', 'Pro-Drivers');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Reset your password';
                $mail->Body = "Hi {$user['first_name']},<br>Click <a href='$reset_url'>here</a> to reset your password. This link will expire in 1 hour.";
                $mail->AltBody = "Reset your password: $reset_url";

                $mail->send();
                $_SESSION['success_message'] = "Check your email for the password reset link.";
                header("Location: forgot-password.php");
                exit;
            } catch (Exception $e) {
                $error_message = "Mailer error: " . $mail->ErrorInfo;
            }
        } else {
            $error_message = "No user found with that email.";
        }
    }
}
?>

<!-- HTML starts here -->
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h4 class="text-center">Forgot Password</h4>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label>Email Address</label>
        <input type="email" class="form-control" name="email" required>
      </div>
      <button class="btn btn-primary w-100">Send Reset Link</button>
    </form>
  </div>
</body>
</html>
