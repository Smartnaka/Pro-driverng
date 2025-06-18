<?php
session_start();
require_once 'include/db.php';
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if there's a pending booking
if (!isset($_SESSION['pending_booking'])) {
    header("Location: book-driver.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = $_SESSION['pending_booking']['amount'];

// Function to initialize payment
function initializePayment($email, $amount) {
    $url = "https://api.paystack.co/transaction/initialize";
    $fields = [
        'email' => $email,
        'amount' => $amount * 100, // Convert to kobo
        'callback_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/verify-payment.php',
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'booking_data' => json_encode($_SESSION['pending_booking'])
        ]
    ];

    $headers = [
        'Authorization: Bearer ' . $_ENV['PAYSTACK_SECRET_KEY'],
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Handle payment initialization
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $response = initializePayment($email, $amount);
    
    if ($response['status']) {
        echo json_encode([
            'status' => 'success',
            'authorization_url' => $response['data']['authorization_url']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => $response['message']
        ]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .card-title {
            color: #1e293b;
            font-weight: 600;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">Complete Your Payment</h3>
                        <div id="payment-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (NGN)</label>
                                <input type="number" class="form-control" id="amount" value="<?= $amount ?>" readonly>
                            </div>
                            <button class="btn btn-primary w-100" onclick="payWithPaystack()">
                                Pay Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function payWithPaystack() {
            const email = document.getElementById('email').value;
            
            if (!email) {
                alert('Please enter your email address');
                return;
            }

            // Initialize payment
            fetch('payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${email}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.authorization_url;
                } else {
                    alert(data.message || 'Payment initialization failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your payment');
            });
        }
    </script>
</body>
</html> 