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
$where_conditions = [];
$params = [];
$types = "";

// Search filters
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_conditions[] = "address LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
    $where_conditions[] = "drive LIKE ?";
    $params[] = "%" . $_GET['vehicle_type'] . "%";
    $types .= "s";
}

if (isset($_GET['experience']) && !empty($_GET['experience'])) {
    $where_conditions[] = "experience >= ?";
    $params[] = $_GET['experience'];
    $types .= "i";
}

// Build the query
$drivers_sql = "SELECT id, first_name, last_name, drive, speak, skills, profile_picture, address, experience 
                FROM drivers";

if (!empty($where_conditions)) {
    $drivers_sql .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'experience';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$drivers_sql .= " ORDER BY $sort $order";

$stmt = $conn->prepare($drivers_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$drivers_result = $stmt->get_result();

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

        .booking-section {
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

        .driver-card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.25rem;
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
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .driver-card:hover {
            border-color: #2563eb;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.12);
            transform: translateY(-4px);
        }

        .driver-card:hover::before {
            opacity: 1;
        }

        .driver-avatar {
            width: 90px;
            height: 110px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .driver-card:hover .driver-avatar {
            transform: scale(1.03);
        }

        .driver-profile {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .driver-info {
            flex: 1;
            padding-top: 0.5rem;
        }

        .driver-info h5 {
            margin: 0 0 0.5rem 0;
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .driver-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .driver-meta-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            background: #f8fafc;
            padding: 0.35rem 0.5rem;
            border-radius: 8px;
        }

        .driver-meta-item i {
            color: #2563eb;
        }

        .driver-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
            padding: 0.5rem;
            border-radius: 8px;
            background: #f8fafc;
        }

        .detail-item:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
        }

        .detail-item i {
            font-size: 1.2rem;
            color: #2563eb;
        }

        .driver-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: #eff6ff;
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .driver-badge:hover {
            background-color: #dbeafe;
            transform: translateY(-2px);
        }

        .driver-badge i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .form-floating > label {
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .select-btn {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-top: 1rem;
        }

        .select-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
        }

        .select-btn i {
            font-size: 1.2rem;
        }

        .btn-primary {
            padding: 0.75rem;
            border-radius: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2);
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                padding: 1.25rem;
            }

            .booking-section {
                padding: 1rem;
            }

            .driver-profile {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .driver-meta {
                justify-content: center;
            }
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
            <h3 class="mb-0">Find a Driver</h3>
            <p class="mb-0 opacity-75">Search and book professional drivers for your journey</p>
        </div>

        <!-- Search Filters -->
        <div class="booking-section mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="location" name="location" 
                               placeholder="Enter location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
                        <label for="location"><i class="bi bi-geo-alt"></i> Location</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select class="form-select" id="vehicle_type" name="vehicle_type">
                            <option value="">All Vehicles</option>
                            <option value="Car" <?= (isset($_GET['vehicle_type']) && $_GET['vehicle_type'] == 'Car') ? 'selected' : '' ?>>Car</option>
                            <option value="Van" <?= (isset($_GET['vehicle_type']) && $_GET['vehicle_type'] == 'Van') ? 'selected' : '' ?>>Van</option>
                            <option value="Truck" <?= (isset($_GET['vehicle_type']) && $_GET['vehicle_type'] == 'Truck') ? 'selected' : '' ?>>Truck</option>
                        </select>
                        <label for="vehicle_type"><i class="bi bi-car-front"></i> Vehicle Type</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select class="form-select" id="experience" name="experience">
                            <option value="">Any Experience</option>
                            <option value="1" <?= (isset($_GET['experience']) && $_GET['experience'] == '1') ? 'selected' : '' ?>>1+ years</option>
                            <option value="3" <?= (isset($_GET['experience']) && $_GET['experience'] == '3') ? 'selected' : '' ?>>3+ years</option>
                            <option value="5" <?= (isset($_GET['experience']) && $_GET['experience'] == '5') ? 'selected' : '' ?>>5+ years</option>
                        </select>
                        <label for="experience"><i class="bi bi-clock-history"></i> Experience</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 h-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Sort Options -->
        <div class="booking-section mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="section-title mb-0">
                    <i class="bi bi-person-check"></i>
                    Available Drivers
                </h4>
                <div class="d-flex gap-2">
                    <select class="form-select" onchange="updateSort(this.value)">
                        <option value="rating" <?= $sort == 'rating' ? 'selected' : '' ?>>Sort by Rating</option>
                        <option value="experience" <?= $sort == 'experience' ? 'selected' : '' ?>>Sort by Experience</option>
                        <option value="total_trips" <?= $sort == 'total_trips' ? 'selected' : '' ?>>Sort by Trips</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Drivers List -->
        <div class="booking-section">
            <?php if ($drivers_result && $drivers_result->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php while($driver = $drivers_result->fetch_assoc()): ?>
                        <div class="col">
                            <div class="driver-card">
                                <div class="driver-profile">
                                    <img src="<?= !empty($driver['profile_picture']) ? htmlspecialchars($driver['profile_picture']) : 'images/default-profile.png' ?>" 
                                         class="driver-avatar" alt="Driver Photo">
                                    <div class="driver-info">
                                        <h5><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h5>
                                        <div class="driver-meta">
                                            <span class="driver-meta-item">
                                                <i class="bi bi-clock-history"></i>
                                                <?= htmlspecialchars($driver['experience'] ?? '0') ?> years experience
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
                                        <i class="bi bi-translate"></i>
                                        <span><?= htmlspecialchars($driver['speak'] ?? 'English') ?></span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="driver-badge">
                                        <i class="bi bi-award-fill"></i>
                                        <?= $driver['experience'] >= 5 ? 'Expert Driver' : 'Experienced Driver' ?>
                                    </span>
                                    <span class="driver-badge">
                                        <i class="bi bi-person-check-fill"></i>
                                        Licensed
                                    </span>
                                </div>
                                <form action="payment/payment.php" method="GET" style="width: 100%;">
                                    <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
                                    <input type="hidden" name="amount" value="5000"> <!-- Replace with actual amount calculation -->
                                    <button type="submit" class="select-btn">
                                        <i class="bi bi-check-circle"></i>
                                        <span class="btn-text">Book & Pay Now</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No drivers match your search criteria. Please try different filters.
                </div>
            <?php endif; ?>
        </div>
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

        function updateSort(value) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', value);
            urlParams.set('order', 'DESC');
            window.location.search = urlParams.toString();
        }
    </script>
</body>
</html> 