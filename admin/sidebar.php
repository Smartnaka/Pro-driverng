<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    z-index: 1000;
    overflow-y: auto;
}

/* Main Content Styles */
.main-content {
    margin-left: 280px; /* Same as sidebar width */
    padding: 20px;
    min-height: 100vh;
    background: #f8f9fa;
    transition: margin-left 0.3s ease;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
    padding: .75rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar .nav-link:hover {
    color: #0d6efd;
    background: rgba(13, 110, 253, .05);
}

.sidebar .nav-link.active {
    color: #0d6efd;
    background: rgba(13, 110, 253, .1);
}

.sidebar .nav-link i {
    font-size: 1.1rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .sidebar-toggle {
        display: block;
    }
}

/* Hide scrollbar for cleaner look */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 3px;
}
</style>

<!-- Mobile Toggle Button -->
<button class="btn btn-primary sidebar-toggle d-md-none position-fixed" 
        style="top: 10px; left: 10px; z-index: 1001;"
        onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<div class="sidebar">
    <div class="p-3 mb-3">
        <img src="../assets/img/logo.png" alt="Logo" class="img-fluid" style="max-height: 50px;">
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>
        
        <!-- Bookings Section -->
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'manage-bookings.php' ? 'active' : '' ?>" href="manage-bookings.php">
                <i class="bi bi-calendar-check"></i>
                Manage Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'driver-bookings.php' ? 'active' : '' ?>" href="driver-bookings.php">
                <i class="bi bi-car-front"></i>
                Driver Operations
            </a>
        </li>
        
        <!-- Users Section -->
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'manage-users.php' ? 'active' : '' ?>" href="manage-users.php">
                <i class="bi bi-people"></i>
                Manage Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'manage-drivers.php' ? 'active' : '' ?>" href="manage-drivers.php">
                <i class="bi bi-person-badge"></i>
                Manage Drivers
            </a>
        </li>
        
        <!-- Driver Verification -->
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'driver-verification.php' ? 'active' : '' ?>" href="driver-verification.php">
                <i class="bi bi-patch-check"></i>
                Driver Verification
            </a>
        </li>
        
        <!-- Settings -->
        <li class="nav-item">
            <a class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                <i class="bi bi-gear"></i>
                Settings
            </a>
        </li>
        
        <!-- Logout -->
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>
    </ul>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
}
</script> 