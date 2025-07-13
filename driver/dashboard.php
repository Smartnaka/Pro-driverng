<?php
session_start();
include '../include/db.php';

// Redirect if driver is not logged in
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];

// Fetch driver details
$sql = "SELECT * FROM drivers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing driver query: " . $conn->error);
}
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// Check if notifications table exists
$table_check = $conn->query("SHOW TABLES LIKE 'driver_notifications'");
$notifications_table_exists = $table_check->num_rows > 0;

// Create notifications table if it doesn't exist
if (!$notifications_table_exists) {
    $create_table_sql = "CREATE TABLE driver_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'warning', 'success', 'error') NOT NULL DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
    )";
    $conn->query($create_table_sql);
}

// Fetch real statistics
$stats = [];

// Total trips (completed bookings)
$trips_sql = "SELECT COUNT(*) as count FROM bookings WHERE driver_id = ? AND status = 'completed'";
$stmt = $conn->prepare($trips_sql);
if ($stmt) {
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $stats['total_trips'] = $stmt->get_result()->fetch_assoc()['count'];
}

// Active bookings
$active_sql = "SELECT COUNT(*) as count FROM bookings WHERE driver_id = ? AND status IN ('confirmed', 'in_progress')";
$stmt = $conn->prepare($active_sql);
if ($stmt) {
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $stats['active_bookings'] = $stmt->get_result()->fetch_assoc()['count'];
}

// Total earnings (sum of completed bookings)
$earnings_sql = "SELECT COALESCE(SUM(amount), 0) as total FROM bookings WHERE driver_id = ? AND status = 'completed'";
$stmt = $conn->prepare($earnings_sql);
if ($stmt) {
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $stats['total_earnings'] = $stmt->get_result()->fetch_assoc()['total'];
}

// Average rating (placeholder for now)
$stats['average_rating'] = 4.8;

// Recent bookings
$recent_bookings_sql = "SELECT b.*, 
    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
    c.phone as customer_phone
    FROM bookings b 
    LEFT JOIN customers c ON b.user_id = c.id 
    WHERE b.driver_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 5";
$stmt = $conn->prepare($recent_bookings_sql);
if ($stmt) {
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $recent_bookings = $stmt->get_result();
}

// Fetch unread notifications count
$unread_notifications = 0;
if ($notifications_table_exists) {
    $notifications_sql = "SELECT COUNT(*) as count FROM driver_notifications WHERE driver_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($notifications_sql);
    if ($stmt) {
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $unread_notifications = $stmt->get_result()->fetch_assoc()['count'];
    }
}

// Determine profile picture path
$picPath = !empty($driver['profile_picture']) ? '../' . $driver['profile_picture'] : "../images/default-avatar.png";
$cacheBuster = file_exists($picPath) ? "?v=" . filemtime($picPath) : "";

// Update driver status if requested
if (isset($_POST['update_status'])) {
    $status = $_POST['status'] === 'online' ? 1 : 0;
    $update_sql = "UPDATE drivers SET is_online = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    if ($stmt === false) {
        die("Error preparing status update query: " . $conn->error);
    }
    $stmt->bind_param("ii", $status, $driver_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    return match($status) {
        'pending' => 'badge bg-warning text-dark',
        'confirmed' => 'badge bg-info',
        'in_progress' => 'badge bg-primary',
        'completed' => 'badge bg-success',
        'cancelled' => 'badge bg-danger',
        default => 'badge bg-secondary'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Pro-Drivers</title>
    
    <!-- Bootstrap CSS -->
      <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="./assets/css/dashboard.css">
    <style>
       
    </style>
</head>
<body>
    <!-- Include Shared Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header fade-in-up">
            <div class="welcome-text">Welcome back, <?= htmlspecialchars($driver['first_name']) ?>! 👋</div>
            <p class="mb-0 opacity-75">Here's what's happening with your account today</p>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <?= $driver['is_online'] ? 'Online' : 'Offline' ?>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
    
            <div class="stat-card fade-in-up" style="animation-delay: 0.4s;">
                <div class="stat-header">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                        <i class="fas fa-naira-sign"></i>
                    </div>
                </div>
                <div class="stat-value"><i class="fas fa-naira-sign"></i><?= number_format($stats['total_earnings'], 2) ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>


            <div class="stat-card fade-in-up" style="animation-delay: 0.1s;">
                <div class="stat-header">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                        <i class="fas fa-route"></i>
                    </div>
                </div>
                <div class="stat-value"><?= number_format($stats['total_trips']) ?></div>
                <div class="stat-label">Total Trips Completed</div>
            </div>

            <div class="stat-card fade-in-up" style="animation-delay: 0.2s;">
                <div class="stat-header">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-value"><?= number_format($stats['active_bookings']) ?></div>
                <div class="stat-label">Active Bookings</div>
            </div>

            <div class="stat-card fade-in-up" style="animation-delay: 0.3s;">
                <div class="stat-header">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-value"><?= number_format($stats['average_rating'], 1) ?></div>
                <div class="stat-label">Average Rating</div>
            </div>

        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Bookings -->
            <div class="card fade-in-up" style="animation-delay: 0.5s;">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-clock"></i>
                        Recent Bookings
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <div class="booking-item">
                                <div class="booking-avatar">
                                    <?= strtoupper(substr($booking['customer_name'], 0, 1)) ?>
                                </div>
                                <div class="booking-details">
                                    <div class="booking-customer"><?= htmlspecialchars($booking['customer_name']) ?></div>
                                    <div class="booking-info">
                                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                        <?= htmlspecialchars($booking['pickup_location']) ?> →
                                        <?= htmlspecialchars($booking['dropoff_location']) ?>
                                    </div>
                                    <div class="booking-info">
                                        <i class="fas fa-calendar text-secondary me-1"></i>
                                        <?= date('M d, Y', strtotime($booking['pickup_date'])) ?>
                                    </div>
                                </div>
                                <div class="booking-status">
                                    <span class="<?= getStatusBadgeClass($booking['status']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h6 class="text-muted">No bookings yet</h6>
                            <p class="text-muted mb-0">Your recent bookings will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions & Info -->
            <div class="space-y-4">
                <!-- Quick Actions -->
                <div class="card fade-in-up" style="animation-delay: 0.6s;">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="edit_profile.php" class="action-btn">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </a>
                            <a href="documents.php" class="action-btn">
                                <i class="fas fa-upload"></i>
                                Upload Documents
                            </a>
                            <a href="support.php" class="action-btn">
                                <i class="fas fa-headset"></i>
                                Get Support
                            </a>
                            <a href="notifications.php" class="action-btn">
                                <i class="fas fa-bell"></i>
                                View Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="card fade-in-up" style="animation-delay: 0.7s;">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-shield-alt"></i>
                            Account Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="status-dot me-2"></div>
                            <span class="fw-medium">Verification Status</span>
                            <span class="ms-auto badge bg-<?= $driver['is_verified'] ? 'success' : 'warning' ?>">
                                <?= $driver['is_verified'] ? 'Verified' : 'Pending' ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="status-dot me-2"></div>
                            <span class="fw-medium">Online Status</span>
                            <span class="ms-auto badge bg-<?= $driver['is_online'] ? 'success' : 'secondary' ?>">
                                <?= $driver['is_online'] ? 'Online' : 'Offline' ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="status-dot me-2"></div>
                            <span class="fw-medium">Account Type</span>
                            <span class="ms-auto badge bg-primary">Professional Driver</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add loading states
        document.addEventListener('DOMContentLoaded', function() {
            // Remove loading class after page loads
            setTimeout(() => {
                document.body.classList.remove('loading');
            }, 500);
        });

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            // You can add AJAX call here to refresh stats
            console.log('Refreshing stats...');
        }, 30000);
    </script>
</body>
</html>