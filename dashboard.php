<?php
session_start();
include 'include/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing user query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if notifications table exists
$table_check = $conn->query("SHOW TABLES LIKE 'customer_notifications'");
$notifications_table_exists = $table_check->num_rows > 0;

// Create notifications table if it doesn't exist
if (!$notifications_table_exists) {
    $create_table_sql = "CREATE TABLE customer_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'warning', 'success', 'error') NOT NULL DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES customers(id) ON DELETE CASCADE
    )";
    $conn->query($create_table_sql);
}

// Fetch unread notifications count
$unread_notifications = 0;
if ($notifications_table_exists) {
    $notifications_sql = "SELECT COUNT(*) as count FROM customer_notifications WHERE user_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($notifications_sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $unread_notifications = $stmt->get_result()->fetch_assoc()['count'];
    }
}

// Set default profile picture path
$picPath = !empty($user['profile_picture']) ? $user['profile_picture'] : "images/default-profile.png";
$cacheBuster = file_exists($picPath) ? "?v=" . filemtime($picPath) : "";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .welcome-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .welcome-header h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #1e293b;
        }

        .stat-label {
            color: #64748b;
            margin: 0;
            font-size: 0.875rem;
        }

        .recent-activity {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
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

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
            .welcome-header {
                padding: 1.5rem;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
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

        @media (max-width: 768px) {
            .mobile-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
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

        .hamburger-btn:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <button class="hamburger-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
            <span class="d-none d-sm-inline">Menu</span>
        </button>
        <span class="fw-bold">Dashboard</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="content">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h3>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! 👋</h3>
            <p class="mb-0">Here's what's happening with your account today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fas fa-car"></i>
                </div>
                <h4 class="stat-value">0</h4>
                <p class="stat-label">Active Bookings</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="stat-value">0</h4>
                <p class="stat-label">Completed Trips</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-star"></i>
                </div>
                <h4 class="stat-value">0</h4>
                <p class="stat-label">Reviews Given</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h5 class="mb-4">Recent Activity</h5>
            <div class="activity-item">
                <div class="activity-icon bg-light text-primary">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="mb-0">Welcome to Pro-Drivers!</p>
                    <small class="text-muted">Get started by booking your first driver</small>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
            document.body.style.overflow = document.getElementById('sidebar').classList.contains('active') ? 'hidden' : '';
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !hamburgerBtn.contains(event.target)) {
                toggleSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    </script>
</body>
</html>
