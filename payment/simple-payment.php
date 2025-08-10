<?php
session_start();
include '../config.php';

// Simple test - just check if Paystack key is available
$paystackKey = defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : 'pk_test_9da1212b6c99a9b813dc323aa680e01bfcc8e52d';
$amount = 5000;
$reference = 'PD_' . time() . '_' . uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Payment Test - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.paystack.co/v2/inline.js"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow p-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Simple Payment Test</h2>
        
        <div class="mb-6">
            <p><strong>Amount:</strong> ₦<?= number_format($amount, 2) ?></p>
            <p><strong>Reference:</strong> <?= $reference ?></p>
            <p><strong>Paystack Key:</strong> <?= substr($paystackKey, 0, 20) ?>...</p>
        </div>
        
        <button onclick="payWithPaystack()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg">
            <i class="fa fa-credit-card"></i> Pay Now
        </button>
        
        <div id="result" class="mt-4 p-4 bg-gray-100 rounded text-sm">
            <p>Click the button above to test payment.</p>
        </div>
    </div>
    
    <script>
        function payWithPaystack() {
            const handler = PaystackPop.setup({
                key: '<?= $paystackKey ?>',
                email: 'test@example.com',
                amount: <?= $amount * 100 ?>, // Convert to kobo
                currency: 'NGN',
                ref: '<?= $reference ?>',
                callback: function(response) {
                    document.getElementById('result').innerHTML = '<p style="color: green;">Payment successful! Reference: ' + response.reference + '</p>';
                    console.log('Payment successful:', response);
                },
                onClose: function() {
                    document.getElementById('result').innerHTML = '<p style="color: orange;">Payment window closed.</p>';
                    console.log('Payment window closed');
                }
            });
            
            try {
                handler.openIframe();
                document.getElementById('result').innerHTML = '<p style="color: blue;">Paystack window opened.</p>';
            } catch (error) {
                document.getElementById('result').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                console.error('Paystack error:', error);
            }
        }
    </script>
</body>
</html> 