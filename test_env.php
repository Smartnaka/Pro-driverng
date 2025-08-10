<?php
// Test environment variables loading
include 'config.php';

echo "<h2>Environment Variables Test</h2>";
echo "<p><strong>Paystack Public Key:</strong> " . (defined('PAYSTACK_PUBLIC_KEY') ? 'DEFINED' : 'NOT DEFINED') . "</p>";
echo "<p><strong>Paystack Secret Key:</strong> " . (defined('PAYSTACK_SECRET_KEY') ? 'DEFINED' : 'NOT DEFINED') . "</p>";

if (defined('PAYSTACK_PUBLIC_KEY')) {
    echo "<p><strong>Public Key Value:</strong> " . substr(PAYSTACK_PUBLIC_KEY, 0, 20) . "...</p>";
}

echo "<p><strong>Database Host:</strong> " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "</p>";
echo "<p><strong>Database Name:</strong> " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "</p>";

// Check if .env file exists
echo "<p><strong>.env file exists:</strong> " . (file_exists('.env') ? 'YES' : 'NO') . "</p>";

if (file_exists('.env')) {
    echo "<p><strong>.env file contents:</strong></p>";
    echo "<pre>";
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            echo htmlspecialchars(trim($key)) . " = " . htmlspecialchars(trim($value)) . "\n";
        }
    }
    echo "</pre>";
}
?> 