<?php
session_start();
include 'include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details with error handling
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing user query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error executing user query: " . $stmt->error);
}
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch available drivers with error handling
$drivers_sql = "SELECT id, first_name, last_name, drive, speak, skills, profile_picture, address, experience 
                FROM drivers";
$drivers_result = $conn->query($drivers_sql);
if ($drivers_result === false) {
    die("Error fetching drivers: " . $conn->error);
}

// Debug information
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Session Info:\n";
    print_r($_SESSION);
    echo "\nUser Info:\n";
    print_r($user);
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Driver - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
        }

        .overlay.active {
            display: block;
        }

        .content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .booking-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .driver-card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .driver-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd, #0099ff);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .driver-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.15);
            transform: translateY(-4px);
        }

        .driver-card:hover::before {
            opacity: 1;
        }

        .driver-card.selected {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }

        .driver-card.selected::before {
            opacity: 1;
        }

        .driver-avatar {
            width: 100px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
            transition: transform 0.3s ease;
            background-color: #f8f9fa;
        }

        .driver-card:hover .driver-avatar {
            transform: scale(1.02);
        }

        .driver-profile {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .driver-info {
            flex: 1;
            padding-top: 0.5rem;
        }

        .driver-info h5 {
            margin: 0 0 0.5rem 0;
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .driver-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .driver-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .driver-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #64748b;
            font-size: 0.95rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .detail-item:hover {
            background-color: #f8f9fa;
        }

        .detail-item i {
            font-size: 1.1rem;
            color: #0d6efd;
        }

        .driver-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #e9f2ff;
            color: #0d6efd;
            margin-right: 0.5rem;
        }

        .driver-badge i {
            margin-right: 0.35rem;
            font-size: 0.9rem;
        }

        .driver-status {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #dcfce7;
            color: #16a34a;
        }

        .form-floating > label {
            color: #64748b;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
        }

        .mobile-nav {
            display: none;
            padding: 1rem;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .hamburger-btn {
            border: none;
            background: none;
            padding: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .select-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            gap: 0.5rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .select-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .select-btn.selected {
            background: #198754;
        }

        .select-btn i {
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }

            .mobile-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .booking-section {
                padding: 1rem;
            }

            .driver-details {
                grid-template-columns: 1fr;
            }
        }

        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .selected-driver-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .selected-driver-avatar {
            width: 60px;
            height: 72px;
            border-radius: 8px;
            object-fit: cover;
        }

        .selected-driver-details h5 {
            margin: 0;
            color: #1e293b;
        }

        .selected-driver-details p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'partials/sidebar.php'; ?>
    
    <!-- Overlay for mobile sidebar -->
    <div class="overlay" onclick="toggleSidebar()"></div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <button class="hamburger-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
            <span class="d-none d-sm-inline">Menu</span>
        </button>
        <span class="fw-bold">Book a Driver</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="page-header">
            <h3 class="mb-0">Book a Driver</h3>
            <p class="mb-0 opacity-75">Find and book professional drivers for your journey</p>
        </div>

        <form method="POST" id="bookingForm">
            <!-- Available Drivers Section -->
            <div class="booking-section">
                <h4 class="section-title">
                    <i class="bi bi-person-check"></i>
                    Available Drivers
                </h4>
                
                <?php if ($drivers_result && $drivers_result->num_rows > 0): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php while($driver = $drivers_result->fetch_assoc()): ?>
                            <div class="col">
                                <div class="driver-card">
                                    <div class="driver-status">
                                        <i class="bi bi-circle-fill me-1"></i>Available
                                    </div>
                                    <div class="driver-profile">
                                        <img src="<?= !empty($driver['profile_picture']) ? htmlspecialchars($driver['profile_picture']) : 'images/default-profile.png' ?>" 
                                             class="driver-avatar" alt="Driver Photo">
                                        <div class="driver-info">
                                            <h5><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h5>
                                            <div class="driver-meta">
                                                <span class="driver-meta-item">
                                                    <i class="bi bi-clock-history"></i>
                                                    Fast Response
                                                </span>
                                                <span class="driver-meta-item">
                                                    <i class="bi bi-shield-check"></i>
                                                    Verified
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="driver-details">
                                        <div class="detail-item">
                                            <i class="bi bi-car-front-fill"></i>
                                            <span><?= htmlspecialchars($driver['drive'] ?? 'All Vehicles') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            <span><?= htmlspecialchars($driver['address'] ?? 'Lagos, Nigeria') ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="bi bi-clock-history"></i>
                                            <span><?= htmlspecialchars($driver['experience'] ?? '0') ?> yrs experience</span>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <span class="driver-badge">
                                            <i class="bi bi-award-fill"></i>
                                            Experienced Driver
                                        </span>
                                        <span class="driver-badge">
                                            <i class="bi bi-person-check-fill"></i>
                                            Licensed
                                        </span>
                                    </div>
                                    <form action="booking.php" method="GET" style="width: 100%;">
                                        <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
                                        <button type="submit" class="select-btn">
                                            <i class="bi bi-check-circle"></i>
                                            <span class="btn-text">Select Driver</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No drivers are currently available. Please try again later.
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectAndRedirect(driverId) {
            // Redirect directly to booking page with driver ID
            window.location.href = 'booking.php?driver_id=' + driverId;
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
        }
    </script>
</body>
</html> 