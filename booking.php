<?php
session_start();
include 'include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    $pickup_location = $_POST['pickup_location'];
    $dropoff_location = $_POST['dropoff_location'];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    $duration_days = $_POST['duration_days'];
    $vehicle_type = $_POST['vehicle_type'];
    $trip_purpose = $_POST['trip_purpose'];
    $additional_notes = $_POST['additional_notes'];

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

    // Redirect to payment page
    header("Location: payment.php?amount=" . $amount);
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
  <aside class="w-64 bg-white border-r flex flex-col justify-between py-6 px-4 hidden md:flex">
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
      <div class="flex items-center gap-4">
        <img src="<?php echo htmlspecialchars(!empty($user['profile_picture']) ? $user['profile_picture'] : 'images/default-profile.png'); ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover border border-gray-200">
        <span class="font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
      </div>
    </header>
    <main class="flex-1 w-full max-w-3xl mx-auto px-4 py-8">
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
            <button type="submit" name="book_driver" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg shadow transition flex items-center justify-center gap-2 text-lg">
              <i class="fa fa-check-circle"></i> Confirm Booking
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
<script>
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
</script>
</body>
</html> 