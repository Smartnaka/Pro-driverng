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
$picPath = !empty($driver['profile_picture']) ? '../' . $driver['profile_picture'] : "../images/default-profile.png";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Driver Dashboard</title>

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
    }

    .sidebar {
      background-color: #e9f2fb;
      color: #343a40;
      padding: 1.5rem 1rem;
      height: 100vh;
      border-right: 1px solid #dee2e6;
      position: fixed;
      top: 0;
      left: 0;
      width: 250px;
      z-index: 1040;
    }

    .sidebar a {
      color: #343a40;
      text-decoration: none;
      display: block;
      padding: 0.75rem 1rem;
      border-radius: 0.375rem;
      margin-bottom: 0.5rem;
      transition: background 0.3s, color 0.3s;
    }

    .sidebar a:hover,
    .sidebar a:focus {
      background-color: #cfe2ff;
      color: #0d6efd;
    }

    .profile-pic {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #0d6efd;
      margin-bottom: 0.75rem;
    }

    .card {
      border: none;
      border-radius: 0.75rem;
    }

    .card h5 {
      font-weight: 600;
    }

    .content {
      margin-left: 250px;
      padding: 2rem;
      min-height: 100vh;
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

    .info-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      height: 100%;
    }

    .info-card-header {
      padding: 1.25rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .info-card-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #1e293b;
      margin: 0;
    }

    .info-card-body {
      padding: 1.25rem;
    }

    .info-item {
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .info-item:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .info-label {
      color: #64748b;
      font-size: 0.875rem;
      margin-bottom: 0.25rem;
    }

    .info-value {
      color: #1e293b;
      font-weight: 500;
    }

    .support-card {
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      color: white;
    }

    .support-card .info-card-title {
      color: white;
    }

    .support-link {
      color: white;
      text-decoration: none;
      padding: 0.75rem 1rem;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      display: inline-block;
      transition: background 0.2s ease;
    }

    .support-link:hover {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
        padding: 1rem;
      }

      .welcome-header {
        padding: 1.5rem;
      }

      .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1030;
      }

      .overlay.active {
        display: block;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Overlay for mobile -->
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Mobile Navbar -->
<nav class="navbar navbar-light bg-white d-md-none border-bottom">
  <div class="container-fluid">
    <button class="btn btn-outline-primary" onclick="toggleSidebar()">☰ Menu</button>
    <span class="navbar-brand mb-0">Dashboard</span>
  </div>
</nav>

<!-- Main Content -->
<div class="content">
  <div class="welcome-header">
    <h3>Welcome back, <?= htmlspecialchars($driver['first_name']) ?>! 👋</h3>
    <p class="mb-0 mt-2 opacity-75">Here's your dashboard overview</p>
  </div>

  <div class="stats-container">
    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
        <i class="fas fa-route"></i>
      </div>
      <h4 class="stat-value">0</h4>
      <p class="stat-label">Total Trips</p>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
        <i class="fas fa-star"></i>
      </div>
      <h4 class="stat-value">0.0</h4>
      <p class="stat-label">Average Rating</p>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
        <i class="fas fa-dollar-sign"></i>
      </div>
      <h4 class="stat-value">$0.00</h4>
      <p class="stat-label">Total Earnings</p>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="info-card">
        <div class="info-card-header">
          <h5 class="info-card-title">
            <i class="fas fa-user-circle me-2"></i> Account Information
          </h5>
          <a href="edit_profile.php" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-edit me-1"></i> Edit Profile
          </a>
        </div>
        <div class="info-card-body">
          <div class="info-item">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?= htmlspecialchars($driver['phone']) ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">License Number</div>
            <div class="info-value"><?= htmlspecialchars($driver['license_number'] ?? 'Not Set') ?></div>
          </div>
          <div class="info-item">
            <div class="info-label">Address</div>
            <div class="info-value"><?= htmlspecialchars($driver['address'] ?? 'Not Set') ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="info-card support-card">
        <div class="info-card-header">
          <h5 class="info-card-title">
            <i class="fas fa-headset me-2"></i> Need Help?
          </h5>
        </div>
        <div class="info-card-body">
          <p>Our support team is available 24/7 to assist you with any questions or concerns.</p>
          <a href="support.php" class="support-link">
            <i class="fas fa-arrow-right me-2"></i> Contact Support
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
  }
</script>

</body>
</html>