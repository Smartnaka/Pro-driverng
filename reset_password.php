<?php
session_start();
require 'include/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE reset_token = ? AND expires > ?");
        $now = time();
        $stmt->bind_param("si", $token, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $email = $result->fetch_assoc()['email'];
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed, $email);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            $success = "Password reset successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Invalid or expired token.";
        }
    }
}
?>

<!-- HTML for reset form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h4 class="text-center">Reset Password</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
      <form method="POST" action="">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
          <label>New Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        <div class="mb-3">
          <label>Confirm Password</label>
          <input type="password" class="form-control" name="confirm" required>
        </div>
        <button class="btn btn-primary w-100">Reset Password</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
