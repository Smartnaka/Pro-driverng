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
    <title>Notifications - Pro-Drivers</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
:root {
    --primary-color: #003366;
    --primary-dark: #1557b0;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #06b6d4;
    --light-bg: #f8fafc;
    --card-bg: #ffffff;
    --border-color: #e2e8f0;
    --text-primary: #003366;
    --text-secondary: #64748b;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}
        body {
            background: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }
        .notifications-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 1rem;
            padding: 2.5rem 2rem 2rem 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .notifications-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url('data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><circle cx=\'20\' cy=\'20\' r=\'20\' fill=\'rgba(255,255,255,0.05)\'/></svg>') repeat;
            opacity: 0.1;
        }
        .notifications-icon {
            font-size: 3rem;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            padding: 1rem;
            box-shadow: var(--shadow-md);
            z-index: 1;
        }
        .notifications-title {
            font-size: 2rem;
            font-weight: 600;
            z-index: 1;
        }
        .info-card {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        .notification-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 0;
            border-bottom: 1px solid var(--light-bg);
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-top: 0.25rem;
        }
        .notification-icon.info { background-color: #cfe2ff; color: #0d6efd; }
        .notification-icon.warning { background-color: #fff3cd; color: #ffc107; }
        .notification-icon.success { background-color: #d1e7dd; color: #198754; }
        .notification-icon.error { background-color: #f8d7da; color: #dc3545; }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .notification-message {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        .notification-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .notification-unread {
            background: #f8f9ff;
            border-left: 4px solid #0d6efd;
            border-radius: 0.5rem;
        }
        .mark-read-btn {
            color: var(--primary-color);
            font-size: 0.95rem;
            background: none;
            border: none;
            padding: 0;
            margin-left: 0.5rem;
            cursor: pointer;
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .notifications-header {
                padding: 2rem 1rem 1.5rem 1rem;
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            .notifications-icon {
                font-size: 2.2rem;
                padding: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="notifications-header">
            <span class="notifications-icon"><i class="fas fa-bell"></i></span>
            <span class="notifications-title">Notifications</span>
        </div>

        <div class="info-card">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                    <h5>No notifications yet</h5>
                    <p class="text-muted">We'll notify you when there's something new.</p>
                </div>
            <?php else: ?>
                <ul class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                        <li class="notification-item <?php echo !$notification['is_read'] ? 'notification-unread' : ''; ?>">
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
                            <div class="notification-content">
                                <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                <div class="d-flex align-items-center">
                                    <span class="notification-time">
                                        <?php echo date('M j, g:i a', strtotime($notification['created_at'])); ?>
                                    </span>
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" class="d-inline ms-2">
                                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                            <button type="submit" name="mark_read" class="mark-read-btn">Mark as read</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>