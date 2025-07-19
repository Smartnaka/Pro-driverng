<?php
session_start();
include '../include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user details for sidebar/header
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$picPath = !empty($user['profile_picture']) ? $user['profile_picture'] : "../images/default-profile.png";
$cacheBuster = file_exists($picPath) ? "?v=" . filemtime($picPath) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="flex min-h-screen">
  <!-- Sidebar (copied from dashboard.php, paths adjusted) -->
  <aside id="sidebar" class="w-64 bg-white border-r flex flex-col justify-between py-6 px-4 hidden md:flex">
    <div>
      <div class="flex items-center gap-2 mb-10 px-2">
        <span class="fa fa-car text-blue-700 text-2xl"></span>
        <span class="font-bold text-xl text-blue-700">ProDrivers</span>
      </div>
      <nav class="flex flex-col gap-1">
        <a href="../dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-th-large"></i> Dashboard
        </a>
        <a href="../book-driver.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-plus-circle"></i> Book a Driver
        </a>
        <a href="../my-bookings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-calendar-check"></i> My Bookings
        </a>
        <a href="../notifications.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 relative">
          <i class="fa fa-bell"></i> Notifications
        </a>
        <a href="../profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-user"></i> My Profile
        </a>
        <a href="../settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-cog"></i> Settings
        </a>
        <a href="../support.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-question-circle"></i> Support
        </a>
        <a href="../logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 mt-2">
          <i class="fa fa-sign-out-alt"></i> Logout
        </a>
      </nav>
    </div>
    <div class="px-2 mt-8">
      <a href="../support.php" class="flex items-center gap-2 text-gray-400 hover:text-blue-600 text-sm">
        <i class="fa fa-question-circle"></i> Support
      </a>
    </div>
  </aside>
  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="w-full bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-10">
      <h1 class="text-2xl font-semibold text-gray-900">Payment Successful</h1>
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
    <main class="flex-1 w-full max-w-2xl mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow p-8 mb-8 text-center">
        <div class="flex flex-col items-center justify-center mb-6">
          <div class="w-20 h-20 flex items-center justify-center rounded-full bg-green-100 mb-4">
            <i class="fa fa-check text-4xl text-green-600"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
          <p class="text-gray-600 mb-4">Your booking has been confirmed and the driver has been notified.<br>You can view your booking details in your dashboard.</p>
        </div>
        <?php if (isset($_SESSION['last_booking_reference'])): ?>
        <div class="mb-4">
          <div class="text-gray-700 text-lg font-semibold">Booking Reference:</div>
          <div class="text-blue-900 text-xl font-mono font-bold mb-2"><?php echo htmlspecialchars($_SESSION['last_booking_reference']); ?></div>
          <?php if (isset($_SESSION['last_booking_id'])): ?>
          <div class="text-gray-500 text-sm">Booking ID: <?php echo htmlspecialchars($_SESSION['last_booking_id']); ?></div>
          <?php endif; ?>
          <div class="text-gray-600 text-sm mt-2">Please keep this reference for your records. If you need support, quote this reference.</div>
        </div>
        <?php 
          unset($_SESSION['last_booking_reference']);
          unset($_SESSION['last_booking_id']);
        ?>
        <?php endif; ?>
        <div class="mb-4">
          <span class="text-gray-700">Need help? Contact <a href="mailto:support@example.com" class="text-blue-700 underline">support@example.com</a></span>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
          <a href="../dashboard.php" class="w-full sm:w-auto bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 px-6 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg">
            <i class="fa fa-th-large"></i> Go to Dashboard
          </a>
          <a href="../book-driver.php" class="w-full sm:w-auto bg-white border border-blue-900 text-blue-900 font-semibold py-3 px-6 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg hover:bg-blue-50">
            <i class="fa fa-plus-circle"></i> Book Another Driver
                </a>
            </div>
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