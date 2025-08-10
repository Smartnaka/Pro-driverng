<?php
session_start();
include 'config.php';

// Simple test page to verify payment system
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment System Test - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.paystack.co/v2/inline.js"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Payment System Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Environment Variables Test -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Environment Variables</h2>
                <div class="space-y-2 text-sm">
                    <p><strong>Paystack Public Key:</strong> 
                        <?= defined('PAYSTACK_PUBLIC_KEY') ? '✅ DEFINED' : '❌ NOT DEFINED' ?>
                    </p>
                    <p><strong>Paystack Secret Key:</strong> 
                        <?= defined('PAYSTACK_SECRET_KEY') ? '✅ DEFINED' : '❌ NOT DEFINED' ?>
                    </p>
                    <p><strong>Database Host:</strong> 
                        <?= defined('DB_HOST') ? DB_HOST : '❌ NOT DEFINED' ?>
                    </p>
                    <p><strong>Database Name:</strong> 
                        <?= defined('DB_NAME') ? DB_NAME : '❌ NOT DEFINED' ?>
                    </p>
                    <p><strong>.env file exists:</strong> 
                        <?= file_exists('.env') ? '✅ YES' : '❌ NO' ?>
                    </p>
                </div>
            </div>
            
            <!-- Quick Payment Test -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Payment Test</h2>
                <div class="mb-4">
                    <p><strong>Amount:</strong> ₦5,000</p>
                    <p><strong>Reference:</strong> <?= 'TEST_' . time() ?></p>
                </div>
                <button onclick="testPayment()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg shadow transition">
                    Test Paystack Integration
                </button>
                <div id="payment-result" class="mt-4 p-3 bg-gray-100 rounded text-sm">
                    Click the button above to test payment.
                </div>
            </div>
        </div>
        
        <!-- Test Links -->
        <div class="mt-8 bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Test Links</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="test_env.php" class="block p-4 bg-blue-50 rounded-lg text-blue-900 hover:bg-blue-100 transition">
                    <strong>Environment Test</strong><br>
                    <span class="text-sm">Check environment variables</span>
                </a>
                <a href="payment/simple-payment.php" class="block p-4 bg-green-50 rounded-lg text-green-900 hover:bg-green-100 transition">
                    <strong>Simple Payment</strong><br>
                    <span class="text-sm">Basic payment test</span>
                </a>
                <a href="payment/debug-payment.php" class="block p-4 bg-yellow-50 rounded-lg text-yellow-900 hover:bg-yellow-100 transition">
                    <strong>Debug Payment</strong><br>
                    <span class="text-sm">Detailed payment debug</span>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function testPayment() {
            const paystackKey = '<?= defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : 'pk_test_9da1212b6c99a9b813dc323aa680e01bfcc8e52d' ?>';
            const reference = 'TEST_' + Date.now();
            
            if (!paystackKey) {
                document.getElementById('payment-result').innerHTML = '<p style="color: red;">❌ Error: Paystack key not defined!</p>';
                return;
            }
            
            const handler = PaystackPop.setup({
                key: paystackKey,
                email: 'test@example.com',
                amount: 500000, // ₦5,000 in kobo
                currency: 'NGN',
                ref: reference,
                callback: function(response) {
                    document.getElementById('payment-result').innerHTML = '<p style="color: green;">✅ Payment successful! Reference: ' + response.reference + '</p>';
                    console.log('Payment successful:', response);
                },
                onClose: function() {
                    document.getElementById('payment-result').innerHTML = '<p style="color: orange;">⚠️ Payment window closed.</p>';
                    console.log('Payment window closed');
                }
            });
            
            try {
                handler.openIframe();
                document.getElementById('payment-result').innerHTML = '<p style="color: blue;">🔄 Opening Paystack...</p>';
            } catch (error) {
                document.getElementById('payment-result').innerHTML = '<p style="color: red;">❌ Error: ' + error.message + '</p>';
                console.error('Paystack error:', error);
            }
        }
    </script>
</body>
</html> 