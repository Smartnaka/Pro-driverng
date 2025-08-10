<?php
session_start();
include '../include/db.php';
include '../config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get driver details
$driver_id = isset($_GET['driver_id']) ? $_GET['driver_id'] : null;
$amount = isset($_GET['amount']) ? $_GET['amount'] : 5000; // Default amount if not specified

if ($driver_id) {
    $sql = "SELECT * FROM drivers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $driver = $result->fetch_assoc();
    if (!$driver) {
        echo '<div style="margin:2rem; color:red; font-weight:bold;">Invalid driver selected. <a href="../book-driver.php">Go back</a></div>';
        exit();
    }
} else {
    echo '<div style="margin:2rem; color:red; font-weight:bold;">No driver selected. <a href="../book-driver.php">Go back</a></div>';
    exit();
}

// Generate a unique reference for this transaction
$reference = 'PD_' . time() . '_' . uniqid();

// Store all booking details in session for later use
if (isset($_SESSION['pending_booking']) && !empty($_SESSION['pending_booking'])) {
    // Validate that all required fields are present
    $required_fields = ['pickup_location', 'dropoff_location', 'pickup_date', 'pickup_time', 'duration_days', 'vehicle_type', 'trip_purpose'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_SESSION['pending_booking'][$field]) || empty($_SESSION['pending_booking'][$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo '<div style="margin:2rem; color:red; font-weight:bold;">Missing required booking information: ' . implode(', ', $missing_fields) . '. <a href="../booking.php?driver_id=' . $driver_id . '">Please complete your booking</a></div>';
        exit();
    }
    
    $_SESSION['booking_details'] = array_merge(
        $_SESSION['pending_booking'],
        [
            'amount' => $amount,
            'reference' => $reference
        ]
    );
} else {
    // Redirect to booking page if no booking details are available
    header("Location: ../booking.php?driver_id=" . $driver_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <style>
      body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="flex min-h-screen">
  <!-- Sidebar (copied from dashboard.php) -->
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
      <h1 class="text-2xl font-semibold text-gray-900">Complete Payment</h1>
      <!-- Desktop: Show profile picture and name -->
      <div class="items-center gap-4 hidden sm:flex">
        <img src="<?php echo htmlspecialchars(!empty($user['profile_picture']) ? $user['profile_picture'] : '../images/default-profile.png'); ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover border border-gray-200">
        <span class="font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
      </div>
      <!-- Mobile: Show hamburger menu -->
      <button class="sm:hidden flex items-center text-2xl text-gray-700" id="mobile-menu-btn" aria-label="Open menu">
        <i class="fa fa-bars"></i>
      </button>
    </header>
    <main class="flex-1 w-full max-w-2xl mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fa fa-credit-card text-blue-700"></i> Payment Details</h2>
        <div class="mb-6">
          <div class="flex justify-between text-gray-600 mb-2"><span>Driver Name:</span><span><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></span></div>
          <div class="flex justify-between text-gray-600 mb-2"><span>Vehicle Type:</span><span><?= htmlspecialchars($driver['drive']) ?></span></div>
          <div class="flex justify-between text-gray-600 mb-2"><span>Amount:</span><span>₦<?= number_format($amount, 2) ?></span></div>
          <div class="flex justify-between font-semibold text-gray-900 border-t pt-3 mt-3"><span>Total Amount:</span><span>₦<?= number_format($amount, 2) ?></span></div>
                </div>
        <button onclick="payWithPaystack()" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg">
          <i class="fa fa-credit-card"></i> Pay Now
            </button>
      </div>
    </main>
        </div>
    </div>
    <script>
        function payWithPaystack() {
            const handler = PaystackPop.setup({
                key: '<?= defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : 'pk_test_9da1212b6c99a9b813dc323aa680e01bfcc8e52d' ?>', // Use environment variable with fallback
                email: '<?= htmlspecialchars($user['email']) ?>',
                amount: <?= $amount * 100 ?>, // Convert to kobo
                currency: 'NGN',
                ref: '<?= $reference ?>',
                callback: function(response) {
                    // Make an AJAX call to your server with the reference to verify the transaction
                    window.location.href = 'verify-payment.php?reference=' + response.reference;
                },
                onClose: function() {
                    alert('Transaction was not completed, window closed.');
                }
            });
            handler.openIframe();
        }
    </script>
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