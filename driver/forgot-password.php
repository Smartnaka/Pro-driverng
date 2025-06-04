<?php
session_start();
require '../include/db.php';
require_once '../include/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';
// Load environment variables
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error_message = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM drivers WHERE email = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Generate a reset token
            $token = bin2hex(random_bytes(50));
            $stmt = $conn->prepare("UPDATE drivers SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();

            // Send reset email using PHPMailer
            $reset_link = "http://localhost/New%20Prodriverng/driver/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = $_ENV['SMTP_HOST']; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_USERNAME']; // Replace with your email
                $mail->Password = $_ENV['SMTP_PASSWORD']; // Replace with your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $_ENV['SMTP_PORT'];

                // Recipients
                $mail->setFrom('no-reply@prodriverng.com', 'Pro-Drivers');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "Click the link below to reset your password: <br><a href='$reset_link'>$reset_link</a>";

                $mail->send();
                $success_message = "A password reset link has been sent to your email.";
            } catch (Exception $e) {
                $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "No account found with that email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Pro-Drivers</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Forgot Password</h2>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"> <?php echo htmlspecialchars($error_message); ?> </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"> <?php echo htmlspecialchars($success_message); ?> </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
        </div>
    </div>
</div>
<script src="../javascript/jquery.min.js"></script>
<script src="../javascript/bootstrap.min.js"></script>
</body>
</html>