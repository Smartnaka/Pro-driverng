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

// Fetch all bookings for this user with driver details
$bookings_sql = "
    SELECT 
        b.*,
        d.first_name as driver_first_name,
        d.last_name as driver_last_name,
        d.phone as driver_phone,
        d.profile_picture as driver_profile_picture
    FROM bookings b
    LEFT JOIN drivers d ON b.driver_id = d.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($bookings_sql);
if ($stmt === false) {
    die("Error preparing bookings query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending_payment':
            return 'bg-warning text-dark';
        case 'pending_driver_response':
            return 'bg-info';
        case 'confirmed':
            return 'bg-success';
        case 'in_progress':
            return 'bg-primary';
        case 'completed':
            return 'bg-secondary';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Function to get status display text
function getStatusDisplayText($status) {
    switch ($status) {
        case 'pending_payment':
            return 'Payment Pending';
        case 'pending_driver_response':
            return 'Waiting for Driver';
        case 'confirmed':
            return 'Confirmed';
        case 'in_progress':
            return 'In Progress';
        case 'completed':
            return 'Completed';
        case 'cancelled':
            return 'Cancelled';
        default:
            return ucfirst(str_replace('_', ' ', $status));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - ProDrivers</title>
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
        <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-th-large"></i> Dashboard
        </a>
        <a href="book-driver.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-plus-circle"></i> Book a Driver
        </a>
        <a href="my-bookings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-blue-700 bg-blue-50">
          <i class="fa fa-calendar-check"></i> My Bookings
        </a>
        <a href="notifications.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 relative">
          <i class="fa fa-bell"></i> Notifications
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
    <header class="w-full bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-10">
      <h1 class="text-2xl font-semibold text-gray-900">My Bookings</h1>
      <!-- Desktop: Show profile picture and name -->
      <div class="items-center gap-4 hidden sm:flex">
        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'images/default-profile.png'); ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover border border-gray-200">
        <span class="font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
      </div>
      <!-- Mobile: Show hamburger menu -->
      <button class="sm:hidden flex items-center text-2xl text-gray-700" id="mobile-menu-btn" aria-label="Open menu">
        <i class="fa fa-bars"></i>
      </button>
    </header>
    <!-- Main Content -->
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Your Bookings</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($bookings as $i => $booking): ?>
          <div class="bg-gray-50 rounded-lg shadow p-5 flex flex-col gap-3">
            <div class="flex items-center gap-3 mb-2">
              <img src="<?php echo htmlspecialchars($booking['driver_profile_picture'] ?? 'images/default-profile.png'); ?>" alt="Driver" class="w-12 h-12 rounded-full object-cover border border-gray-200">
              <div>
                <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']); ?></div>
                <div class="text-xs text-gray-500">Booking #<?php echo $i + 1; ?></div>
              </div>
              <div class="ml-auto">
                <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo getStatusBadgeClass($booking['status']); ?>">
                  <?php echo getStatusDisplayText($booking['status']); ?>
                </span>
              </div>
            </div>
            <div class="text-sm text-gray-700 flex flex-col gap-1">
              <div><span class="font-medium">Pickup:</span> <?php echo htmlspecialchars($booking['pickup_location']); ?></div>
              <div><span class="font-medium">Dropoff:</span> <?php echo htmlspecialchars($booking['dropoff_location']); ?></div>
              <div><span class="font-medium">Date:</span> <?php echo htmlspecialchars($booking['pickup_date']); ?></div>
              <div><span class="font-medium">Time:</span> <?php echo htmlspecialchars($booking['pickup_time']); ?></div>
              <div><span class="font-medium">Amount:</span> ₦<?php echo number_format($booking['amount'], 2); ?></div>
            </div>
            <div class="flex flex-wrap gap-2 mt-2">
              <a href="payment/payment-success.php?reference=<?php echo urlencode($booking['reference']); ?>" class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200 text-xs font-medium flex items-center gap-1"><i class="fa fa-receipt"></i> Receipt</a>
              <?php if (!in_array($booking['status'], ['completed', 'cancelled'])): ?>
              <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 text-xs font-medium flex items-center gap-1"><i class="fa fa-times"></i> Cancel</button>
              <?php endif; ?>
              <button onclick="viewBookingDetails(<?php echo $booking['id']; ?>)" class="bg-gray-100 text-gray-700 px-3 py-1 rounded hover:bg-gray-200 text-xs font-medium flex items-center gap-1"><i class="fa fa-eye"></i> View</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if (empty($bookings)): ?>
        <div class="text-center text-gray-500 py-8">You have no bookings yet.</div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
    <h3 class="text-lg font-semibold mb-2 text-gray-800">Cancel Booking</h3>
    <p class="text-gray-600 mb-4">Are you sure you want to cancel this booking?</p>
    <div class="flex justify-end gap-2">
      <button id="cancelModalNo" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">No</button>
      <button id="cancelModalYes" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Yes, Cancel</button>
    </div>
  </div>
</div>
<!-- Toast Notification -->
<div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-2 items-end"></div>
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
let cancelBookingId = null;
function cancelBooking(bookingId) {
  cancelBookingId = bookingId;
  document.getElementById('cancelModal').classList.remove('hidden');
}
document.getElementById('cancelModalNo').onclick = function() {
  document.getElementById('cancelModal').classList.add('hidden');
  cancelBookingId = null;
};
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `px-4 py-3 rounded shadow text-white text-sm mb-2 animate-fade-in ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
  toast.innerText = message;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('opacity-0');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
document.getElementById('cancelModalYes').onclick = function() {
  if (!cancelBookingId) return;
  fetch('api/cancel_booking.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'booking_id=' + encodeURIComponent(cancelBookingId)
  })
  .then(response => response.json())
  .then(data => {
    document.getElementById('cancelModal').classList.add('hidden');
    cancelBookingId = null;
    if (data.success) {
      showToast('Booking cancelled successfully.', 'success');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(data.message || 'Failed to cancel booking.', 'error');
    }
  })
  .catch(() => {
    document.getElementById('cancelModal').classList.add('hidden');
    cancelBookingId = null;
    showToast('Network error. Please try again.', 'error');
  });
};
function viewBookingDetails(bookingId) {
  // Implement modal or redirect to details page
  alert('Booking details for ID: ' + bookingId);
}
</script>
<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.3s ease;
}
</style>
</body>
</html> 