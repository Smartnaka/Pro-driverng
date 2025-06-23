<?php
session_start();
include '../include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get driver details
$driver_id = isset($_GET['driver_id']) ? $_GET['driver_id'] : null;
$amount = isset($_GET['amount']) ? $_GET['amount'] : 5000; // Default amount if not specified

if ($driver_id) {
    $sql = "SELECT * FROM drivers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $driver = $result->fetch_assoc();
    if (!$driver) {
        echo '<div style="margin:2rem; color:red; font-weight:bold;">Invalid driver selected. <a href="book-driver.php">Go back</a></div>';
        exit();
    }
} else {
    echo '<div style="margin:2rem; color:red; font-weight:bold;">No driver selected. <a href="book-driver.php">Go back</a></div>';
    exit();
}

// Generate a unique reference for this transaction
$reference = 'PD_' . time() . '_' . uniqid();

// Store all booking details in session for later use
if (isset($_SESSION['pending_booking'])) {
    $_SESSION['booking_details'] = array_merge(
        $_SESSION['pending_booking'],
        [
            'amount' => $amount,
            'reference' => $reference
        ]
    );
} else {
    // fallback for direct access
    $_SESSION['booking_details'] = [
        'driver_id' => $driver_id,
        'amount' => $amount,
        'reference' => $reference
    ];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 280px;
            padding: 1.5rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.15);
        }

        .page-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .payment-section {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #2563eb;
        }

        .payment-summary {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            color: #64748b;
        }

        .summary-item.total {
            border-top: 1px solid #e2e8f0;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            font-weight: 600;
            color: #1e293b;
        }

        .pay-button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .pay-button:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
        }

        .pay-button i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .payment-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="content">
        <div class="page-header">
            <h3 class="mb-0">Complete Payment</h3>
            <p class="mb-0 opacity-75">Secure payment powered by Paystack</p>
        </div>

        <div class="payment-section">
            <h4 class="section-title">
                <i class="bi bi-credit-card"></i>
                Payment Details
            </h4>

            <div class="payment-summary">
                <div class="summary-item">
                    <span>Driver Name:</span>
                    <span><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></span>
                </div>
                <div class="summary-item">
                    <span>Vehicle Type:</span>
                    <span><?= htmlspecialchars($driver['drive']) ?></span>
                </div>
                <div class="summary-item">
                    <span>Amount:</span>
                    <span>₦<?= number_format($amount, 2) ?></span>
                </div>
                <div class="summary-item total">
                    <span>Total Amount:</span>
                    <span>₦<?= number_format($amount, 2) ?></span>
                </div>
            </div>

            <button onclick="payWithPaystack()" class="pay-button">
                <i class="bi bi-credit-card"></i>
                Pay Now
            </button>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function payWithPaystack() {
            const handler = PaystackPop.setup({
                key: 'pk_test_9da1212b6c99a9b813dc323aa680e01bfcc8e52d', // Replace with your public key
                email: '<?= htmlspecialchars($user['email']) ?>',
                amount: <?= $amount * 100 ?>, // Convert to kobo
                currency: 'NGN',
                ref: '<?= $reference ?>',
                callback: function(response) {
                    // Make an AJAX call to your server with the reference to verify the transaction
                    window.location.href = 'verify-payment.php?reference=' + response.reference;
                },
                onClose: function() {
                    alert('Transaction was not completed, window closed.');
                }
            });
            handler.openIframe();
        }
    </script>
</body>
</html> 