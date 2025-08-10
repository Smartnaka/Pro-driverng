<?php
// Prevent direct access
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__));

// Check if config is already loaded
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
    
    // Load environment variables from .env file if it exists
    if (file_exists(ABSPATH . '/.env')) {
        $lines = file(ABSPATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }

    // Only define constants if they haven't been defined yet
    if (!defined('PAYSTACK_SECRET_KEY')) {
        define('PAYSTACK_SECRET_KEY', $_ENV['PAYSTACK_SECRET_KEY'] ?? getenv('PAYSTACK_SECRET_KEY') ?? 'sk_test_0ca80ae7e863b608623399886ceb90cd29951246');
    }
    if (!defined('PAYSTACK_PUBLIC_KEY')) {
        define('PAYSTACK_PUBLIC_KEY', $_ENV['PAYSTACK_PUBLIC_KEY'] ?? getenv('PAYSTACK_PUBLIC_KEY') ?? 'pk_test_9da1212b6c99a9b813dc323aa680e01bfcc8e52d');
    }

    // Database Configuration
    if (!defined('DB_HOST')) {
        define('DB_HOST', 'localhost');
    }
    if (!defined('DB_NAME')) {
        define('DB_NAME', 'prodrivers');
    }
    if (!defined('DB_USER')) {
        define('DB_USER', 'root');
    }
    if (!defined('DB_PASS')) {
        define('DB_PASS', '');
    }

    // Email Configuration
    if (!defined('MAIL_HOST')) {
        define('MAIL_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.gmail.com');
    }
    if (!defined('MAIL_PORT')) {
        define('MAIL_PORT', $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? '587');
    }
    if (!defined('MAIL_USERNAME')) {
        define('MAIL_USERNAME', $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?? '');
    }
    if (!defined('MAIL_PASSWORD')) {
        define('MAIL_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?? '');
    }
    if (!defined('MAIL_ENCRYPTION')) {
        define('MAIL_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?? 'tls');
    }
}
?> 