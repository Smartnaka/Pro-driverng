<?php
session_start();
include '../include/db.php';

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

// Initialize notifications array
$notifications = [];

// Fetch notifications if table exists
if ($notifications_table_exists) {
    $notifications_sql = "SELECT * FROM driver_notifications WHERE driver_id = ? ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($notifications_sql);
    if ($stmt) {
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Mark notification as read
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $update_sql = "UPDATE driver_notifications SET is_read = TRUE WHERE id = ? AND driver_id = ?";
    $stmt = $conn->prepare($update_sql);
    if ($stmt) {
        $stmt->bind_param("ii", $notification_id, $driver_id);
        $stmt->execute();
        header("Location: notifications.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Driver Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .notification-card {
            border: none;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .notification-card:hover {
            transform: translateY(-2px);
        }
        .notification-unread {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9ff;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 0.5rem;
        }
        .notification-time {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .notification-message {
            color: #495057;
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        .notification-icon.info { background-color: #cfe2ff; color: #0d6efd; }
        .notification-icon.warning { background-color: #fff3cd; color: #ffc107; }
        .notification-icon.success { background-color: #d1e7dd; color: #198754; }
        .notification-icon.error { background-color: #f8d7da; color: #dc3545; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Mobile Navbar -->
    <nav class="navbar navbar-light bg-white d-md-none border-bottom">
        <div class="container-fluid">
            <button class="btn btn-outline-primary" onclick="toggleSidebar()">☰ Menu</button>
            <span class="navbar-brand mb-0">Notifications</span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Notifications</h4>
            <?php if (count($notifications) > 0): ?>
            <button class="btn btn-outline-primary btn-sm">
                Mark All as Read
            </button>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                <h5>No notifications yet</h5>
                <p class="text-muted">We'll notify you when there's something new.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="card notification-card <?php echo !$notification['is_read'] ? 'notification-unread' : ''; ?>">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="notification-icon <?php echo $notification['type']; ?>">
                                <?php
                                $icon = match($notification['type']) {
                                    'info' => 'fa-info',
                                    'warning' => 'fa-exclamation',
                                    'success' => 'fa-check',
                                    'error' => 'fa-times',
                                    default => 'fa-bell'
                                };
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="notification-header">
                                    <h6 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                    <span class="notification-time">
                                        <?php echo date('M j, g:i a', strtotime($notification['created_at'])); ?>
                                    </span>
                                </div>
                                <p class="notification-message mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <?php if (!$notification['is_read']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <button type="submit" name="mark_read" class="btn btn-sm btn-link p-0">Mark as read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>
</html> 