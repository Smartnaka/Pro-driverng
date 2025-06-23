<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function send_welcome_email($email, $first_name) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'israelbabs59@gmail.com';
        $mail->Password = 'uenb rrvr lyrl rzje'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@prodrivers.com', 'Pro-Drivers');
        $mail->addAddress($email, $first_name);

        $mail->isHTML(true);
        $mail->Subject = '🎉 Driver Registration Successful - Welcome to Pro-Drivers!';
        $mail->Body = "
            <h2>Hello {$first_name},</h2>
            <p>Welcome to <strong>Pro-Drivers</strong>! Your registration as a driver was successful.</p>
            <p>You can now log in and start using our platform.</p>
            <br><hr>
            <p style='font-size: 12px; color: #777;'>This email was sent to you because you signed up as a driver. If you didn't register, you can ignore this email.</p>
        ";
        $mail->AltBody = "Hello {$first_name},\n\nWelcome to Pro-Drivers! Your registration was successful.";

        $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
    }
} 