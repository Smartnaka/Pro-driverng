<?php
session_start();
require_once 'include/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if there's a success message
if (!isset($_SESSION['success_message'])) {
    header("Location: book-driver.php");
    exit();
}

$success_message = $_SESSION['success_message'];
unset($_SESSION['success_message']); // Clear the message
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Pro-Drivers</title>
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
        .success-icon {
            width: 80px;
            height: 80px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .success-icon i {
            font-size: 40px;
            color: #16a34a;
        }
        .card-title {
            color: #1e293b;
            font-weight: 600;
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
                    <div class="card-body p-4 text-center">
                        <div class="success-icon">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <h3 class="card-title mb-3">Payment Successful!</h3>
                        <p class="text-muted mb-4"><?= htmlspecialchars($success_message) ?></p>
                        <div class="d-grid gap-2">
                            <a href="my-bookings.php" class="btn btn-primary">
                                <i class="bi bi-calendar-check me-2"></i>
                                View My Bookings
                            </a>
                            <a href="book-driver.php" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle me-2"></i>
                                Book Another Driver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 