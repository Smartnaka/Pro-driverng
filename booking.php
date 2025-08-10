<?php
session_start();
include 'include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Debug information
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "GET Parameters:\n";
    print_r($_GET);
    echo "\nSession Info:\n";
    print_r($_SESSION);
    echo "</pre>";
}

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user details
$user_sql = "SELECT * FROM customers WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
if ($user_stmt === false) {
    die("Error preparing user query: " . $conn->error);
}
$user_stmt->bind_param("i", $user_id);
if (!$user_stmt->execute()) {
    die("Error executing user query: " . $user_stmt->error);
}
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
if (!$user) {
    die("User not found.");
}

// Get selected driver details
$driver_id = null;
if (isset($_GET['driver_id'])) {
    $driver_id = (int)$_GET['driver_id'];
} else {
    die("No driver ID provided. Please go back and select a driver.");
}

// Fetch driver details with error handling
$sql = "SELECT * FROM drivers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing driver query: " . $conn->error);
}
$stmt->bind_param("i", $driver_id);
if (!$stmt->execute()) {
    die("Error executing driver query: " . $stmt->error);
}
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    die("Driver not found. Please go back and select a valid driver.");
}

// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_driver'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed. Please try again.");
    }
    
    // Validate and sanitize input
    $pickup_location = filter_input(INPUT_POST, 'pickup_location', FILTER_SANITIZE_STRING);
    $dropoff_location = filter_input(INPUT_POST, 'dropoff_location', FILTER_SANITIZE_STRING);
    $pickup_date = filter_input(INPUT_POST, 'pickup_date', FILTER_SANITIZE_STRING);
    $pickup_time = filter_input(INPUT_POST, 'pickup_time', FILTER_SANITIZE_STRING);
    $duration_days = filter_input(INPUT_POST, 'duration_days', FILTER_VALIDATE_INT);
    $vehicle_type = filter_input(INPUT_POST, 'vehicle_type', FILTER_SANITIZE_STRING);
    $trip_purpose = filter_input(INPUT_POST, 'trip_purpose', FILTER_SANITIZE_STRING);
    $additional_notes = filter_input(INPUT_POST, 'additional_notes', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    $errors = [];
    if (empty($pickup_location)) $errors[] = "Pickup location is required";
    if (empty($dropoff_location)) $errors[] = "Dropoff location is required";
    if (empty($pickup_date)) $errors[] = "Pickup date is required";
    if (empty($pickup_time)) $errors[] = "Pickup time is required";
    if ($duration_days < 1 || $duration_days > 30) $errors[] = "Duration must be between 1 and 30 days";
    if (empty($vehicle_type)) $errors[] = "Vehicle type is required";
    if (empty($trip_purpose)) $errors[] = "Trip purpose is required";
    
    // Validate date is not in the past
    if (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Pickup date cannot be in the past";
    }
    
    if (!empty($errors)) {
        $error_message = "Please correct the following errors: " . implode(", ", $errors);
        // You can store this in session and display it on the form
        $_SESSION['booking_error'] = $error_message;
        // Redirect back to form
        header("Location: booking.php?driver_id=" . $driver_id);
        exit();
    }

    // Calculate amount based on duration and vehicle type
    $base_rate = 5000; // Base rate per day
    $amount = $base_rate * $duration_days;

    // Store booking details in session for payment
    $_SESSION['pending_booking'] = [
        'driver_id' => $driver_id,
        'pickup_location' => $pickup_location,
        'dropoff_location' => $dropoff_location,
        'pickup_date' => $pickup_date,
        'pickup_time' => $pickup_time,
        'duration_days' => $duration_days,
        'vehicle_type' => $vehicle_type,
        'trip_purpose' => $trip_purpose,
        'additional_notes' => $additional_notes,
        'amount' => $amount
    ];

    // Debug: Log before redirect
    file_put_contents('debug.log', 'Redirecting to payment at '.date('c').PHP_EOL, FILE_APPEND);

    // Redirect to payment page
    header("Location: payment/payment.php?amount=" . $amount . "&driver_id=" . $driver_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Booking - ProDrivers</title>
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
        <a href="my-bookings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
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
      <h1 class="text-2xl font-semibold text-gray-900">Complete Your Booking</h1>
      <!-- Desktop: Show profile picture and name -->
      <div class="items-center gap-4 hidden sm:flex">
        <img src="<?php echo htmlspecialchars(!empty($user['profile_picture']) ? $user['profile_picture'] : 'images/default-profile.png'); ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover border border-gray-200">
        <span class="font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
      </div>
      <!-- Mobile: Show hamburger menu -->
      <button class="sm:hidden flex items-center text-2xl text-gray-700" id="mobile-menu-btn" aria-label="Open menu">
        <i class="fa fa-bars"></i>
      </button>
    </header>
    <main class="flex-1 w-full max-w-3xl mx-auto px-4 py-8">
      <!-- Error Message Display -->
      <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <?php echo htmlspecialchars($_SESSION['booking_error']); ?>
        </div>
        <?php unset($_SESSION['booking_error']); ?>
      <?php endif; ?>
      
      <!-- Selected Driver Card -->
      <div class="bg-white rounded-xl shadow p-6 mb-8 flex flex-col md:flex-row gap-6 items-center">
        <img src="<?= !empty($driver['profile_picture']) ? htmlspecialchars($driver['profile_picture']) : 'images/default-profile.png' ?>" class="w-28 h-32 rounded-xl object-cover border border-gray-200 bg-gray-100" alt="Driver Photo">
        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-800 mb-1"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h2>
          <div class="text-gray-600 text-sm mb-1 flex items-center gap-2"><i class="fa fa-map-marker-alt text-blue-700"></i> <?= htmlspecialchars($driver['address']) ?></div>
          <div class="text-gray-500 text-sm mb-1 flex items-center gap-2"><i class="fa fa-clock text-green-600"></i> <?= htmlspecialchars($driver['experience']) ?> yrs experience</div>
          <div class="text-gray-500 text-sm mb-1 flex items-center gap-2"><i class="fa fa-car text-indigo-600"></i> <?= htmlspecialchars($driver['drive']) ?></div>
        </div>
      </div>
      <!-- Booking Form Card -->
      <div class="bg-white rounded-xl shadow p-8">
        <form method="POST" id="bookingForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          
          <div>
            <label for="pickup_location" class="block text-sm font-medium text-gray-700 mb-1">Pickup Location</label>
            <input type="text" class="form-input w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="pickup_location" name="pickup_location" placeholder="Enter pickup location" required>
          </div>
          <div>
            <label for="dropoff_location" class="block text-sm font-medium text-gray-700 mb-1">Dropoff Location</label>
            <input type="text" class="form-input w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="dropoff_location" name="dropoff_location" placeholder="Enter dropoff location" required>
          </div>
          <div>
            <label for="pickup_date" class="block text-sm font-medium text-gray-700 mb-1">Pickup Date</label>
            <input type="date" class="form-input w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="pickup_date" name="pickup_date" min="<?= date('Y-m-d') ?>" required>
          </div>
          <div>
            <label for="pickup_time" class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
            <input type="time" class="form-input w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="pickup_time" name="pickup_time" required>
          </div>
          <div>
            <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">Duration (Days)</label>
            <input type="number" class="form-input w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="duration_days" name="duration_days" min="1" max="30" value="1" required>
          </div>
          <div>
            <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-1">Transmission Type</label>
            <select class="form-select w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="vehicle_type" name="vehicle_type" required>
              <option value="">Select transmission type</option>
              <option value="Automatic">Automatic</option>
              <option value="Manual">Manual</option>
              <option value="Both">Both (Automatic & Manual)</option>
            </select>
          </div>
          <div>
            <label for="trip_purpose" class="block text-sm font-medium text-gray-700 mb-1">Trip Purpose</label>
            <select class="form-select w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="trip_purpose" name="trip_purpose" required>
              <option value="">Select trip purpose</option>
              <option value="Business">Business</option>
              <option value="Personal">Personal</option>
              <option value="Airport">Airport Transfer</option>
              <option value="Event">Event</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label for="additional_notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
            <textarea class="form-input w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" id="additional_notes" name="additional_notes" rows="3" placeholder="Enter any additional notes"></textarea>
          </div>
          <div class="md:col-span-2">
            <!-- Price Breakdown -->
            <div id="price-breakdown" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-900 text-base flex flex-col gap-1">
              <span>Base rate: <span id="base-rate">₦5,000</span> per day</span>
              <span>Duration: <span id="duration-display">1</span> day(s)</span>
              <span class="font-semibold">Total: <span id="total-amount">₦5,000</span></span>
            </div>
            <!-- End Price Breakdown -->
            <button type="submit" name="book_driver" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg">
              <i class="fa fa-check-circle"></i> Confirm Booking
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
<div id="loading-overlay" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(255,255,255,0.8);align-items:center;justify-content:center;">
  <div style="text-align:center;">
    <div class="fa fa-spinner fa-spin" style="font-size:3rem;color:#2563eb;"></div>
    <div style="margin-top:1rem;font-size:1.2rem;color:#2563eb;">Redirecting to payment...</div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  console.log('Script loaded');
  // Set minimum time based on selected date
  document.getElementById('pickup_date').addEventListener('change', function() {
    const dateInput = this.value;
    const timeInput = document.getElementById('pickup_time');
    const today = new Date().toISOString().split('T')[0];
    if (dateInput === today) {
      const now = new Date();
      const currentHour = String(now.getHours()).padStart(2, '0');
      const currentMinute = String(now.getMinutes()).padStart(2, '0');
      timeInput.min = `${currentHour}:${currentMinute}`;
    } else {
      timeInput.min = '';
    }
  });

  // Price breakdown logic
  const baseRate = 5000;
  const durationInput = document.getElementById('duration_days');
  const durationDisplay = document.getElementById('duration-display');
  const totalAmount = document.getElementById('total-amount');
  function updatePrice() {
    const days = parseInt(durationInput.value) || 1;
    durationDisplay.textContent = days;
    totalAmount.textContent = `₦${(baseRate * days).toLocaleString()}`;
  }
  durationInput.addEventListener('input', updatePrice);
  updatePrice();

  // Booking summary modal logic
  const bookingForm = document.getElementById('bookingForm');
  // Remove the booking summary modal HTML
  // Remove the modal JavaScript logic, keep only direct form submission
  let pendingSubmit = false;

  console.log('bookingForm:', bookingForm);

  // No modal logic, form submits directly
  // Ensure loading overlay is shown on real submit
  bookingForm.addEventListener('submit', function(e) {
    if (pendingSubmit) {
      document.getElementById('loading-overlay').style.display = 'flex';
    }
  });

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