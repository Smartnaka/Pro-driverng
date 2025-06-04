<?php if (!isset($driver)) exit(); ?>

<?php
  $current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Sidebar</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .sidebar {
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            color: #1e293b;
            padding: 1.5rem;
            height: 100vh;
            border-right: 1px solid rgba(0,0,0,0.08);
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1040;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-brand {
            padding: 0.5rem 1rem;
            margin-bottom: 2rem;
        }

        .profile-section {
            background: linear-gradient(135deg, #e9f2fb 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .profile-pic {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 2px 12px rgba(13, 110, 253, 0.15);
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .profile-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .profile-email {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
        }

        .nav-item {
            color: #1e293b;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.375rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-item i {
            font-size: 1.25rem;
            margin-right: 0.75rem;
            color: #64748b;
            transition: color 0.2s ease;
        }

        .nav-item:hover {
            background-color: #f1f5f9;
            color: #0d6efd;
        }

        .nav-item:hover i {
            color: #0d6efd;
        }

        .nav-item.active {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: #ffffff;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .nav-item.active i {
            color: #ffffff;
        }

        .nav-badge {
            position: absolute;
            right: 1rem;
            background: #ef4444;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-weight: 500;
        }

        .nav-divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(0,0,0,0.04) 0%, rgba(0,0,0,0.06) 50%, rgba(0,0,0,0.04) 100%);
            margin: 1rem 0;
        }

        .nav-item.danger {
            color: #dc3545;
        }

        .nav-item.danger i {
            color: #dc3545;
        }

        .nav-item.danger:hover {
            background-color: #dc354510;
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    
<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <?php if (!empty($driver['profile_picture'])): ?>
            <img src="<?= htmlspecialchars('../' . $driver['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
        <?php else: ?>
            <img src="../assets/images/default-profile.jpg" class="profile-pic" alt="Default Profile Picture">
        <?php endif; ?>
        <h6 class="profile-name"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h6>
        <p class="profile-email"><?= htmlspecialchars($driver['email']) ?></p>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Main Menu</div>
        <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i>Dashboard
        </a>
        
        <a href="profile.php" class="nav-item <?= $current_page === 'profile.php' ? 'active' : '' ?>">
            <i class="bi bi-person"></i>My Profile
        </a>
        
        <a href="documents.php" class="nav-item <?= $current_page === 'documents.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>Documents
        </a>
        
        <a href="notifications.php" class="nav-item <?= $current_page === 'notifications.php' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i>Notifications
            <?php
            $table_check = $conn->query("SHOW TABLES LIKE 'driver_notifications'");
            $notifications_table_exists = $table_check->num_rows > 0;

            if ($notifications_table_exists) {
                $unread_sql = "SELECT COUNT(*) as count FROM driver_notifications WHERE driver_id = ? AND is_read = FALSE";
                $stmt = $conn->prepare($unread_sql);
                if ($stmt) {
                    $stmt->bind_param("i", $driver['id']);
                    $stmt->execute();
                    $unread_count = $stmt->get_result()->fetch_assoc()['count'];
                    if ($unread_count > 0):
                    ?>
                    <span class="nav-badge"><?= $unread_count ?></span>
                    <?php 
                    endif;
                }
            }
            ?>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Settings & Support</div>
        <a href="settings.php" class="nav-item <?= $current_page === 'settings.php' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>Settings
        </a>
        
        <a href="support.php" class="nav-item <?= $current_page === 'support.php' ? 'active' : '' ?>">
            <i class="bi bi-question-circle"></i>Support
        </a>
    </div>

    <div class="nav-divider"></div>
    
    <a href="logout.php" class="nav-item danger">
        <i class="bi bi-box-arrow-right"></i>Logout
    </a>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/jquery.min.js"></script>
</body>
</html>