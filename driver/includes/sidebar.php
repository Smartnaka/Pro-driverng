<?php
// Ensure driver session is active
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch driver details if not already available
if (!isset($driver)) {
    $driver_id = $_SESSION['driver_id'];
    $sql = "SELECT * FROM drivers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $driver = $stmt->get_result()->fetch_assoc();
    }
}

// Fetch unread notifications count
$unread_notifications = 0;
$table_check = $conn->query("SHOW TABLES LIKE 'driver_notifications'");
$notifications_table_exists = $table_check->num_rows > 0;

if ($notifications_table_exists) {
    $notifications_sql = "SELECT COUNT(*) as count FROM driver_notifications WHERE driver_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($notifications_sql);
    if ($stmt) {
        $stmt->bind_param("i", $driver['id']);
        $stmt->execute();
        $unread_notifications = $stmt->get_result()->fetch_assoc()['count'];
    }
}

// Determine profile picture path
$picPath = !empty($driver['profile_picture']) ? '../' . $driver['profile_picture'] : "../images/default-avatar.png";
$cacheBuster = file_exists($picPath) ? "?v=" . filemtime($picPath) : "";
?>

<!-- Sidebar Styles -->
<style>
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background: var(--card-bg);
        border-right: 1px solid var(--border-color);
        z-index: 1000;
        overflow-y: auto;
        transition: transform 0.3s ease;
    }

    .sidebar-header {
        padding: 2rem 1.5rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .profile-section {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary-color);
        margin-bottom: 1rem;
        box-shadow: var(--shadow-md);
    }

    .profile-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .profile-email {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .nav-menu {
        padding: 0 1rem;
    }

    .nav-item {
        margin-bottom: 0.5rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        position: relative;
    }

    .nav-link:hover {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary-color);
    }

    .nav-link.active {
        background: var(--primary-color);
        color: white;
    }

    .nav-link i {
        width: 20px;
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }

    .nav-badge {
        position: absolute;
        right: 1rem;
        background: var(--danger-color);
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
        font-weight: 500;
    }

    /* Custom Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: var(--light-bg);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-color);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
            box-shadow: var(--shadow-lg);
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="profile-section">
            <img src="<?= htmlspecialchars($picPath . $cacheBuster) ?>" alt="Profile" class="profile-avatar">
            <div class="profile-name"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></div>
            <div class="profile-email"><?= htmlspecialchars($driver['email']) ?></div>
        </div>
    </div>

    <nav class="nav-menu">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="profile.php" class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                Profile
            </a>
        </div>
        <div class="nav-item">
            <a href="documents.php" class="nav-link <?= $current_page === 'documents.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                Documents
            </a>
        </div>
        <div class="nav-item">
            <a href="notifications.php" class="nav-link <?= $current_page === 'notifications.php' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i>
                Notifications
                <?php if ($unread_notifications > 0): ?>
                    <span class="nav-badge"><?= $unread_notifications ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="nav-item">
            <a href="settings.php" class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </div>
        <div class="nav-item">
            <a href="support.php" class="nav-link <?= $current_page === 'support.php' ? 'active' : '' ?>">
                <i class="fas fa-headset"></i>
                Support
            </a>
        </div>
        <div class="nav-item">
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>
</div>

<!-- Mobile Menu Toggle Button -->
<div class="d-md-none position-fixed top-0 start-0 p-3" style="z-index: 1001;">
    <button class="btn btn-primary" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Mobile Overlay -->
<div class="d-md-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" 
     id="sidebarOverlay" 
     onclick="toggleSidebar()" 
     style="z-index: 999; display: none;"></div>

<script>
    // Mobile sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('active');
        
        if (sidebar.classList.contains('active')) {
            overlay.style.display = 'block';
        } else {
            overlay.style.display = 'none';
        }
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !event.target.closest('.btn')) {
                sidebar.classList.remove('active');
                overlay.style.display = 'none';
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        }
    });
</script> 