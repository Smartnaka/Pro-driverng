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

// Get error message from session
$error_message = $_SESSION['payment_error'] ?? 'An error occurred during payment processing.';
unset($_SESSION['payment_error']); // Clear the error message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Error - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.15);
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

        .error-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .error-icon i {
            font-size: 2.5rem;
            color: #dc2626;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #dc2626;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.2);
        }

        .btn-outline {
            background: transparent;
            color: #dc2626;
            border: 1px solid #dc2626;
        }

        .btn-outline:hover {
            background: #fef2f2;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .error-section {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
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
            <h3 class="mb-0">Payment Error</h3>
            <p class="mb-0 opacity-75">We couldn't process your payment</p>
        </div>

        <div class="error-section">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Payment Failed</h2>
            <p class="error-message">
                <?= htmlspecialchars($error_message) ?>
            </p>
            <div class="action-buttons">
                <a href="payment.php" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i>
                    Try Again
                </a>
                <a href="book-driver.php" class="btn btn-outline">
                    <i class="bi bi-arrow-left"></i>
                    Back to Booking
                </a>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 