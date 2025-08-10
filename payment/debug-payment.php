<?php
session_start();
include '../include/db.php';
include '../config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
echo "<h2>Debug Information</h2>";
echo "<p><strong>Paystack Public Key:</strong> " . (defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : 'NOT DEFINED') . "</p>";
echo "<p><strong>Session user_id:</strong> " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p><strong>GET amount:</strong> " . ($_GET['amount'] ?? 'NOT SET') . "</p>";
echo "<p><strong>GET driver_id:</strong> " . ($_GET['driver_id'] ?? 'NOT SET') . "</p>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>User not logged in!</p>";
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = isset($_GET['amount']) ? $_GET['amount'] : 5000;
$driver_id = isset($_GET['driver_id']) ? $_GET['driver_id'] : null;

echo "<p><strong>Amount:</strong> $amount</p>";
echo "<p><strong>Driver ID:</strong> $driver_id</p>";

// Generate reference
$reference = 'PD_' . time() . '_' . uniqid();
echo "<p><strong>Reference:</strong> $reference</p>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Payment - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.paystack.co/v2/inline.js"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow p-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Debug Payment Test</h2>
        
        <div class="mb-6">
            <p><strong>Amount:</strong> ₦<?= number_format($amount, 2) ?></p>
            <p><strong>Reference:</strong> <?= $reference ?></p>
            <p><strong>Paystack Key:</strong> <?= defined('PAYSTACK_PUBLIC_KEY') ? substr(PAYSTACK_PUBLIC_KEY, 0, 20) . '...' : 'NOT DEFINED' ?></p>
        </div>
        
        <button onclick="payWithPaystack()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg">
            <i class="fa fa-credit-card"></i> Test Paystack
        </button>
        
        <div id="debug-info" class="mt-4 p-4 bg-gray-100 rounded text-sm">
            <p><strong>Debug Info:</strong></p>
            <p>Click the button above to test Paystack integration.</p>
        </div>
    </div>
    
    <script>
        function payWithPaystack() {
            const paystackKey = '<?= defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : '' ?>';
            
            if (!paystackKey) {
                document.getElementById('debug-info').innerHTML = '<p style="color: red;">Error: Paystack public key not defined!</p>';
                return;
            }
            
            console.log('Paystack Key:', paystackKey);
            console.log('Amount:', <?= $amount * 100 ?>);
            console.log('Reference:', '<?= $reference ?>');
            
            const handler = PaystackPop.setup({
                key: paystackKey,
                email: 'test@example.com',
                amount: <?= $amount * 100 ?>, // Convert to kobo
                currency: 'NGN',
                ref: '<?= $reference ?>',
                callback: function(response) {
                    document.getElementById('debug-info').innerHTML = '<p style="color: green;">Payment successful! Reference: ' + response.reference + '</p>';
                    console.log('Payment successful:', response);
                },
                onClose: function() {
                    document.getElementById('debug-info').innerHTML = '<p style="color: orange;">Payment window closed.</p>';
                    console.log('Payment window closed');
                }
            });
            
            try {
                handler.openIframe();
                document.getElementById('debug-info').innerHTML = '<p style="color: blue;">Paystack window opened successfully.</p>';
            } catch (error) {
                document.getElementById('debug-info').innerHTML = '<p style="color: red;">Error opening Paystack: ' + error.message + '</p>';
                console.error('Paystack error:', error);
            }
        }
    </script>
</body>
</html> 