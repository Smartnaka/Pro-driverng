<?php
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Secure Mailer Class
 * Handles email sending with secure credential management
 */
// Include configuration file
require_once __DIR__ . '/../config.php';

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
        $this->config = [
            'SMTP_HOST' => MAIL_HOST,
            'SMTP_USERNAME' => MAIL_USERNAME,
            'SMTP_PASSWORD' => MAIL_PASSWORD,
            'SMTP_PORT' => MAIL_PORT,
            'SMTP_ENCRYPTION' => MAIL_ENCRYPTION
        ];
        
        // Validate configuration
        foreach ($this->config as $key => $value) {
            if (empty($value)) {
                throw new Exception("Missing required SMTP configuration: {$key}");
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
            error_log("PHPMailer Error sending welcome email to {$email}: " . $this->mailer->ErrorInfo);
            error_log("Exception details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send welcome email to driver
     */
    public function sendDriverWelcomeEmail($email, $firstName) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email, $firstName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '🚗 Welcome to Pro-Drivers Driver Network!';
            $this->mailer->Body = "
                <h2>Hello {$firstName},</h2>
                <p>Welcome to <strong>Pro-Drivers</strong>! Your driver account has been created successfully.</p>
                <p>You can now log in and start receiving booking requests from customers.</p>
                <br><hr>
                <p style='font-size: 12px; color: #777;'>This email was sent to you because you registered as a driver with Pro-Drivers. If you didn't register, you can ignore this email.</p>
            ";
            $this->mailer->AltBody = "Hello {$firstName},\n\nWelcome to Pro-Drivers! Your driver account has been created successfully.";
            
            $this->mailer->send();
            error_log("Driver welcome email sent successfully to: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error sending driver welcome email to {$email}: " . $this->mailer->ErrorInfo);
            error_log("Exception details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $firstName, $resetUrl) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email, $firstName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '🔐 Password Reset Request - Pro-Drivers';
            $this->mailer->Body = "
                <h2>Hello {$firstName},</h2>
                <p>You have requested to reset your password for your Pro-Drivers account.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='{$resetUrl}' style='background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Reset Password</a></p>
                <p>If you didn't request this, you can safely ignore this email.</p>
                <br><hr>
                <p style='font-size: 12px; color: #777;'>This link will expire in 1 hour for security reasons.</p>
            ";
            $this->mailer->AltBody = "Hello {$firstName},\n\nYou have requested to reset your password. Click this link: {$resetUrl}";
            
            $this->mailer->send();
            error_log("Password reset email sent successfully to: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error sending password reset email to {$email}: " . $this->mailer->ErrorInfo);
            error_log("Exception details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send contact form email
     */
    public function sendContactFormEmail($name, $email, $message) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($this->config['SMTP_USERNAME'], 'Pro-Drivers Support');
            $this->mailer->addReplyTo($email, $name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '📧 New Contact Form Submission - Pro-Drivers';
            $this->mailer->Body = "
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ";
            $this->mailer->AltBody = "New contact form submission from {$name} ({$email}):\n\n{$message}";
            
            $this->mailer->send();
            error_log("Contact form email sent successfully from: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error sending contact form email from {$email}: " . $this->mailer->ErrorInfo);
            error_log("Exception details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmationEmail($email, $firstName) {
        try {
            $this->mailer->setFrom($this->config['SMTP_USERNAME'], 'PRODRIVERS');
            $this->mailer->addAddress($email, $firstName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '✅ Booking Confirmed - Pro-Drivers';
            $this->mailer->Body = "
                <h2>Hello {$firstName},</h2>
                <p>Your booking has been confirmed! 🎉</p>
                <p>We have received your payment and your driver will be in touch with you shortly.</p>
                <p>You can view your booking details in your dashboard.</p>
                <br><hr>
                <p style='font-size: 12px; color: #777;'>Thank you for choosing Pro-Drivers for your transportation needs.</p>
            ";
            $this->mailer->AltBody = "Hello {$firstName},\n\nYour booking has been confirmed! We have received your payment and your driver will be in touch with you shortly.";
            
            $this->mailer->send();
            error_log("Booking confirmation email sent successfully to: " . $email);
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error sending booking confirmation email to {$email}: " . $this->mailer->ErrorInfo);
            error_log("Exception details: " . $e->getMessage());
            return false;
        }
    }
}
?> 