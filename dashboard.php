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

// --- Real-time booking stats ---
// Total Bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Active Bookings (pending, accepted)
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status IN ('pending', 'accepted')");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Cancelled Bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'cancelled'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cancelled_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Completed Bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Upcoming Booking (next future booking)
$stmt = $conn->prepare("SELECT b.*, d.first_name as driver_first_name, d.last_name as driver_last_name, d.profile_picture as driver_profile_picture, d.phone as driver_phone FROM bookings b LEFT JOIN drivers d ON b.driver_id = d.id WHERE b.user_id = ? AND b.status IN ('pending', 'accepted') AND CONCAT(b.pickup_date, ' ', b.pickup_time) >= NOW() ORDER BY b.pickup_date ASC, b.pickup_time ASC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_booking = $stmt->get_result()->fetch_assoc();

// Recent Bookings (last 3)
$stmt = $conn->prepare("SELECT b.*, d.first_name as driver_first_name, d.last_name as driver_last_name FROM bookings b LEFT JOIN drivers d ON b.driver_id = d.id WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 3");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside id="sidebar" class="w-64 bg-white border-r flex flex-col justify-between py-6 px-4 hidden md:flex">
    <div>
      <div class="flex items-center gap-2 mb-10 px-2">
        <span class="fa fa-car text-blue-700 text-2xl"></span>
        <span class="font-bold text-xl text-blue-700">ProDrivers</span>
      </div>
      <nav class="flex flex-col gap-1">
        <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-blue-700 bg-blue-50">
          <i class="fa fa-th-large"></i> Dashboard
        </a>
        <a href="book-driver.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-plus-circle"></i> Book a Driver
        </a>
        <a href="my-bookings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-calendar-check"></i> My Bookings
        </a>
        <a href="notifications.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 relative">
          <i class="fa fa-bell"></i> Notifications
          <?php if ($unread_notifications > 0): ?>
            <span class="absolute right-4 top-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5"><?php echo $unread_notifications; ?></span>
          <?php endif; ?>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-user"></i> My Profile
        </a>
        <a href="settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-cog"></i> Settings
        </a>
        <a href="support.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-question-circle"></i> Support
        </a>
        <a href="logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 mt-2">
          <i class="fa fa-sign-out-alt"></i> Logout
        </a>
    </nav>
    </div>
    <div class="px-2 mt-8">
      <a href="support.php" class="flex items-center gap-2 text-gray-400 hover:text-blue-600 text-sm">
        <i class="fa fa-question-circle"></i> Support
      </a>
    </div>
  </aside>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="w-full bg-white border-b px-6 py-4 flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
      <!-- Desktop: Show profile picture and name -->
      <div class="items-center gap-4 hidden sm:flex">
        <img src="<?php echo htmlspecialchars($picPath . $cacheBuster); ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover border border-gray-200">
        <span class="font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
      </div>
      <!-- Mobile: Show hamburger menu -->
      <button class="sm:hidden flex items-center text-2xl text-gray-700" id="mobile-menu-btn" aria-label="Open menu">
        <i class="fa fa-bars"></i>
      </button>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
      <!-- Account Progress Bar -->
      <?php
        $progress_steps = [
          'Profile Picture' => [!empty($user['profile_picture']) && $user['profile_picture'] !== 'images/default-profile.png', 'profile.php'],
          'First Name' => [!empty($user['first_name']), 'profile.php'],
          'Last Name' => [!empty($user['last_name']), 'profile.php'],
          'Email' => [!empty($user['email']), 'settings.php'],
          'Phone' => [!empty($user['phone']), 'settings.php'],
          'Address' => [!empty($user['address']), 'profile.php'],
          'State' => [!empty($user['state']), 'profile.php'],
          'Occupation' => [!empty($user['occupation']), 'profile.php'],
          'ID Type' => [!empty($user['id_type']), 'profile.php'],
          'ID Uploaded' => [!empty($user['upload_id']), 'profile.php'],
        ];
        $completed = array_sum(array_map(fn($v) => $v[0], $progress_steps));
        $total = count($progress_steps);
        $percent = round(($completed / $total) * 100);
      ?>
      <div class="bg-white rounded-xl p-6 mb-8 shadow flex flex-col gap-4">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-semibold text-gray-800">Account Progress</h2>
          <span class="text-sm font-medium text-gray-500"><?php echo $percent; ?>% Complete</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
          <div class="bg-blue-500 h-3 rounded-full transition-all duration-300" style="width: <?php echo $percent; ?>%"></div>
        </div>
        <ul class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
          <?php foreach (
            $progress_steps as $label => [$done, $link]
          ): ?>
            <li class="flex items-center gap-2 text-sm <?php echo $done ? 'text-green-600' : 'text-gray-500'; ?>">
              <?php if ($done): ?>
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <?php else: ?>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 12l2 2 4-4"/></svg>
              <?php endif; ?>
              <span><?php echo htmlspecialchars($label); ?></span>
              <?php if (!$done): ?>
                <a href="<?php echo htmlspecialchars($link); ?>" class="ml-2 text-xs text-blue-600 hover:underline font-medium">Complete now</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 flex flex-col items-start shadow">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl font-bold text-gray-900"><?php echo $total_bookings; ?></span>
            <span class="ml-2 bg-blue-100 text-blue-600 rounded-full p-2"><i class="fa fa-archive"></i></span>
          </div>
          <div class="text-gray-500 text-sm font-medium">Total Bookings</div>
        </div>
        <div class="bg-white rounded-xl p-6 flex flex-col items-start shadow">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl font-bold text-gray-900"><?php echo $active_bookings; ?></span>
            <span class="ml-2 bg-green-100 text-green-600 rounded-full p-2"><i class="fa fa-hourglass-half"></i></span>
          </div>
          <div class="text-gray-500 text-sm font-medium">Active Bookings</div>
        </div>
        <div class="bg-white rounded-xl p-6 flex flex-col items-start shadow">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl font-bold text-gray-900"><?php echo $cancelled_bookings; ?></span>
            <span class="ml-2 bg-red-100 text-red-600 rounded-full p-2"><i class="fa fa-times-circle"></i></span>
          </div>
          <div class="text-gray-500 text-sm font-medium">Cancelled Bookings</div>
        </div>
        <div class="bg-white rounded-xl p-6 flex flex-col items-start shadow">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl font-bold text-gray-900"><?php echo $completed_bookings; ?></span>
            <span class="ml-2 bg-indigo-100 text-indigo-600 rounded-full p-2"><i class="fa fa-check-circle"></i></span>
          </div>
          <div class="text-gray-500 text-sm font-medium">Completed Bookings</div>
        </div>
      </div>
      <!-- Assigned Driver Widget -->
      <?php
      // If $upcoming_booking has a driver, use it. Otherwise, fetch the most recent booking with a driver assigned and status in ('in_progress', 'accepted', 'pending')
      $assigned_driver = null;
      if (!empty($upcoming_booking) && !empty($upcoming_booking['driver_first_name'])) {
        $assigned_driver = $upcoming_booking;
      } else {
        $stmt = $conn->prepare("SELECT b.*, d.first_name as driver_first_name, d.last_name as driver_last_name, d.profile_picture as driver_profile_picture, d.phone as driver_phone FROM bookings b LEFT JOIN drivers d ON b.driver_id = d.id WHERE b.user_id = ? AND b.driver_id IS NOT NULL AND b.status IN ('in_progress', 'accepted', 'pending') ORDER BY b.pickup_date DESC, b.pickup_time DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!empty($row['driver_first_name'])) {
          $assigned_driver = $row;
        }
      }
      ?>
      <div class="bg-white rounded-xl p-6 mb-8 shadow flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-800 mb-2">Assigned Driver</h2>
          <?php if (!empty($assigned_driver['driver_first_name'])): ?>
            <div class="text-gray-700 font-medium mb-1">
              <?php echo htmlspecialchars($assigned_driver['driver_first_name'] . ' ' . $assigned_driver['driver_last_name']); ?>
            </div>
            <div class="text-gray-500 mb-4">
              Phone: <a href="tel:<?php echo htmlspecialchars($assigned_driver['driver_phone']); ?>" class="text-blue-700 hover:underline"><?php echo htmlspecialchars($assigned_driver['driver_phone']); ?></a>
            </div>
            <a href="tel:<?php echo htmlspecialchars($assigned_driver['driver_phone']); ?>" class="inline-block bg-green-600 text-white px-5 py-2 rounded-lg font-semibold shadow hover:bg-green-700 transition"><i class="fa fa-phone mr-2"></i>Contact Driver</a>
          <?php else: ?>
            <div class="text-gray-500">No driver is currently assigned to your bookings.</div>
          <?php endif; ?>
        </div>
        <div class="flex-shrink-0">
          <?php if (!empty($assigned_driver['driver_profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars($assigned_driver['driver_profile_picture']); ?>" alt="Driver" class="w-40 h-32 rounded-lg object-cover bg-gray-100">
          <?php else: ?>
            <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=driver" alt="Driver" class="w-40 h-32 rounded-lg object-cover bg-gray-100">
          <?php endif; ?>
        </div>
      </div>
      <!-- Upcoming Booking Card -->
      <?php if ($upcoming_booking): ?>
      <div class="bg-white rounded-xl p-6 mb-8 shadow flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-800 mb-2">Upcoming Booking</h2>
          <div class="text-gray-700 font-medium mb-1">
            Driver: <?php echo htmlspecialchars($upcoming_booking['driver_first_name'] . ' ' . $upcoming_booking['driver_last_name']); ?>
          </div>
          <div class="text-gray-500 mb-4">
            Date: <?php echo date('F j, Y', strtotime($upcoming_booking['pickup_date'])); ?> | Time: <?php echo date('g:i A', strtotime($upcoming_booking['pickup_time'])); ?>
          </div>
          <a href="my-bookings.php#booking-<?php echo $upcoming_booking['id']; ?>" class="inline-block bg-blue-900 text-white px-5 py-2 rounded-lg font-semibold shadow hover:bg-blue-800 transition">View Booking</a>
        </div>
        <div class="flex-shrink-0">
          <?php if (!empty($upcoming_booking['driver_profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars($upcoming_booking['driver_profile_picture']); ?>" alt="Driver" class="w-40 h-32 rounded-lg object-cover bg-gray-100">
          <?php else: ?>
            <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=driver" alt="Driver" class="w-40 h-32 rounded-lg object-cover bg-gray-100">
          <?php endif; ?>
        </div>
      </div>
      <!-- Next Driver Contact Widget -->
      <?php if (!empty($upcoming_booking['driver_first_name']) && !empty($upcoming_booking['driver_phone'])): ?>
      <div class="bg-white rounded-xl p-6 mb-8 shadow flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-800 mb-2">Next Driver Contact</h2>
          <div class="text-gray-700 font-medium mb-1">
            <?php echo htmlspecialchars($upcoming_booking['driver_first_name'] . ' ' . $upcoming_booking['driver_last_name']); ?>
          </div>
          <div class="text-gray-500 mb-4">
            Phone: <a href="tel:<?php echo htmlspecialchars($upcoming_booking['driver_phone']); ?>" class="text-blue-700 hover:underline"><?php echo htmlspecialchars($upcoming_booking['driver_phone']); ?></a>
          </div>
          <a href="tel:<?php echo htmlspecialchars($upcoming_booking['driver_phone']); ?>" class="inline-block bg-green-600 text-white px-5 py-2 rounded-lg font-semibold shadow hover:bg-green-700 transition"><i class="fa fa-phone mr-2"></i>Call Driver</a>
        </div>
      </div>
      <?php endif; ?>
      <?php else: ?>
      <div class="bg-white rounded-xl p-6 mb-8 shadow flex items-center justify-center text-gray-500">
        <span class="text-lg">No upcoming bookings found.</span>
      </div>
      <?php endif; ?>

      <!-- Recent Bookings Widget -->
      <div class="bg-white rounded-xl p-6 mt-8 shadow">
        <h2 class="text-lg font-semibold mb-4 text-gray-800">Recent Bookings</h2>
        <?php if (!empty($recent_bookings)): ?>
          <ul class="divide-y divide-gray-100">
            <?php foreach ($recent_bookings as $booking): ?>
              <li class="py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                  <div class="font-medium text-gray-700">
                    <?php echo htmlspecialchars($booking['pickup_location']); ?> → <?php echo htmlspecialchars($booking['dropoff_location']); ?>
                  </div>
                  <div class="text-gray-500 text-sm">
                    <?php echo date('M j, Y', strtotime($booking['pickup_date'])); ?> | <?php echo date('g:i A', strtotime($booking['pickup_time'])); ?>
                  </div>
                  <div class="text-xs mt-1">
                    Status: <span class="inline-block px-2 py-0.5 rounded bg-gray-100 text-gray-700"><?php echo ucfirst($booking['status']); ?></span>
                  </div>
                </div>
                <div>
                  <a href="my-bookings.php#booking-<?php echo $booking['id']; ?>" class="text-blue-700 hover:underline font-semibold">View</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-gray-500">No recent bookings found.</div>
        <?php endif; ?>
            </div>
    </main>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var btn = document.getElementById('mobile-menu-btn');
  var sidebar = document.getElementById('sidebar');
  if (btn && sidebar) {
    btn.addEventListener('click', function() {
      sidebar.classList.toggle('hidden');
      sidebar.classList.toggle('fixed');
      sidebar.classList.toggle('z-50');
      sidebar.classList.toggle('top-0');
      sidebar.classList.toggle('left-0');
      sidebar.classList.toggle('h-full');
      sidebar.classList.toggle('shadow-lg');
      sidebar.classList.toggle('animate-slideIn');
    });
  }
});
</script>
</body>
</html>
