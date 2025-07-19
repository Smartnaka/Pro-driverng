<?php
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Secure Mailer Class
 * Handles email sending with secure credential management
 */
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die('DEBUG: .env file not found at: ' . $envFile);
} else {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<pre>DEBUG: .env file found. Contents:\n";
    print_r($lines);
    echo "</pre>";
}

class SecureMailer {
    private $mailer;
    private $config;
    
    public function __construct() {
        // Load environment variables
        $this->loadEnvironmentVariables();
        
        // Initialize PHPMailer
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $this->configureSMTP();
    }
    
    /**
     * Load environment variables from .env file
     */
    private function loadEnvironmentVariables() {
        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found. Please create it with SMTP credentials.');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->config = [];
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue; // Skip comments
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $this->config[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        // Validate required configuration
        $required = ['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_PORT'];
        foreach ($required as $key) {
            if (!isset($this->config[$key])) {
                throw new Exception("Missing required SMTP configuration: $key");
            }
        }
    }
    
    /**
     * Configure SMTP settings
     */
    private function configureSMTP() {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['SMTP_USERNAME'];
        $this->mailer->Password = $this->config['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['SMTP_PORT'];
    }
    
    /**
     * Send welcome email to user
     */
    public function sendWelcomeEmail($email, $firstName) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email, $firstName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '🎉 Welcome to Pro-Drivers!';
            $this->mailer->Body = "
                <h2>Hello {$firstName},</h2>
                <p>Welcome to <strong>Pro-Drivers</strong>! Your account has been created successfully.</p>
                <p>You can now log in and start booking drivers for your transportation needs.</p>
                <br><hr>
                <p style='font-size: 12px; color: #777;'>This email was sent to you because you signed up for a Pro-Drivers account. If you didn't register, you can ignore this email.</p>
            ";
            $this->mailer->AltBody = "Hello {$firstName},\n\nWelcome to Pro-Drivers! Your account has been created successfully.";
            
            $this->mailer->send();
            error_log("Welcome email sent successfully to: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send driver welcome email
     */
    public function sendDriverWelcomeEmail($email, $firstName) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email, $firstName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '🎉 Driver Registration Successful - Welcome to Pro-Drivers!';
            $this->mailer->Body = "
                <h2>Hello {$firstName},</h2>
                <p>Welcome to <strong>Pro-Drivers</strong>! Your registration as a driver was successful.</p>
                <p>You can now log in and start using our platform.</p>
                <br><hr>
                <p style='font-size: 12px; color: #777;'>This email was sent to you because you signed up as a driver. If you didn't register, you can ignore this email.</p>
            ";
            $this->mailer->AltBody = "Hello {$firstName},\n\nWelcome to Pro-Drivers! Your registration was successful.";
            
            $this->mailer->send();
            error_log("Driver welcome email sent successfully to: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $firstName, $resetUrl) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Reset your password';
            $this->mailer->Body = "Hi {$firstName},<br>Click <a href='{$resetUrl}'>here</a> to reset your password. This link will expire in 1 hour.";
            $this->mailer->AltBody = "Reset your password: {$resetUrl}";
            
            $this->mailer->send();
            error_log("Password reset email sent successfully to: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send contact form email
     */
    public function sendContactFormEmail($name, $email, $message) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($this->config['SMTP_USERNAME'], 'Emmanuel');
            
            $this->mailer->Subject = "New Contact Form Submission";
            $this->mailer->Body = "Name: {$name}\nEmail: {$email}\nMessage: {$message}";
            
            $this->mailer->send();
            error_log("Contact form email sent successfully from: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Send booking/payment confirmation email to user
     */
    public function sendBookingConfirmationEmail($email, $firstName) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email, $firstName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '✅ Booking Confirmed - Payment Successful';
            $this->mailer->Body = "<h2>Hello {$firstName},</h2><p>Your payment was successful and your driver booking is now confirmed!</p><p>Thank you for choosing <strong>Pro-Drivers</strong>. We will contact you soon with your driver details.</p><br><hr><p style='font-size: 12px; color: #777;'>This is an automated confirmation for your recent booking and payment on Pro-Drivers.</p>";
            $this->mailer->AltBody = "Hello {$firstName},\n\nYour payment was successful and your driver booking is now confirmed! Thank you for choosing Pro-Drivers.";
            $this->mailer->send();
            error_log("Booking confirmation email sent successfully to: " . $email);
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
?> 