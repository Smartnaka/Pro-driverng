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

// Fetch all notifications for this user
$notifications_sql = "SELECT * FROM customer_notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($notifications_sql);
if ($stmt === false) {
    die("Error preparing notifications query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications_result = $stmt->get_result();
$notifications = $notifications_result->fetch_all(MYSQLI_ASSOC);

// Function to get notification icon
function getNotificationIcon($type) {
    switch ($type) {
        case 'success':
            return 'bi-check-circle-fill text-success';
        case 'warning':
            return 'bi-exclamation-triangle-fill text-warning';
        case 'error':
            return 'bi-x-circle-fill text-danger';
        default:
            return 'bi-info-circle-fill text-info';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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

        .page-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .page-header h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .notification-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid #e5e7eb;
            position: relative;
        }

        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .notification-card.unread {
            border-left-color: #0d6efd;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }

        .notification-card.unread::before {
            content: '';
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 8px;
            height: 8px;
            background: #0d6efd;
            border-radius: 50%;
        }

        .notification-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .notification-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            margin-top: 0.125rem;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
        }

        .notification-message {
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
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
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
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

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
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
        <span class="fw-bold">Notifications</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h3>Notifications</h3>
            <p class="mb-0">Stay updated with your booking status and important updates</p>
        </div>

        <?php if (empty($notifications)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-bell"></i>
                <h5>No notifications yet</h5>
                <p>We'll notify you when there's something new about your bookings.</p>
            </div>
        <?php else: ?>
            <!-- Actions Bar -->
            <div class="actions-bar">
                <div>
                    <span class="text-muted">
                        <?= count($notifications) ?> notification<?= count($notifications) !== 1 ? 's' : '' ?>
                    </span>
                </div>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                        <i class="bi bi-check-all me-1"></i>Mark All as Read
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card <?= !$notification['is_read'] ? 'unread' : '' ?>" 
                     data-notification-id="<?= $notification['id'] ?>">
                    <div class="notification-header">
                        <i class="bi <?= getNotificationIcon($notification['type']) ?> notification-icon"></i>
                        <div class="notification-content">
                            <h6 class="notification-title"><?= htmlspecialchars($notification['title']) ?></h6>
                            <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                            <div class="notification-time">
                                <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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

        // Mark notification as read when clicked
        document.querySelectorAll('.notification-card').forEach(card => {
            card.addEventListener('click', function() {
                const notificationId = this.dataset.notificationId;
                if (this.classList.contains('unread')) {
                    markAsRead(notificationId, this);
                }
            });
        });

        function markAsRead(notificationId, element) {
            // Here you would typically make an AJAX call to mark the notification as read
            fetch('mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.classList.remove('unread');
                    updateNotificationCount();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function markAllAsRead() {
            // Here you would typically make an AJAX call to mark all notifications as read
            fetch('mark-all-notifications-read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-card.unread').forEach(card => {
                        card.classList.remove('unread');
                    });
                    updateNotificationCount();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function updateNotificationCount() {
            // Update the notification count in the sidebar
            const unreadCount = document.querySelectorAll('.notification-card.unread').length;
            const badge = document.querySelector('.nav-badge');
            if (badge) {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount;
                } else {
                    badge.remove();
                }
            }
        }
    </script>
</body>
</html> 