<?php
session_start();
include 'include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details with error handling
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing user query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error executing user query: " . $stmt->error);
}
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch available drivers with error handling
$where_conditions = [];
$params = [];
$types = "";

// Search filters
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_conditions[] = "address LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
    $where_conditions[] = "drive LIKE ?";
    $params[] = "%" . $_GET['vehicle_type'] . "%";
    $types .= "s";
}

if (isset($_GET['experience']) && !empty($_GET['experience'])) {
    $where_conditions[] = "experience >= ?";
    $params[] = $_GET['experience'];
    $types .= "i";
}

// Build the query
$drivers_sql = "SELECT id, first_name, last_name, drive, speak, skills, profile_picture, address, experience 
                FROM drivers";

if (!empty($where_conditions)) {
    $drivers_sql .= " WHERE " . implode(" AND ", $where_conditions);
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'experience';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$drivers_sql .= " ORDER BY $sort $order";

$stmt = $conn->prepare($drivers_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$drivers_result = $stmt->get_result();

// Debug information
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Session Info:\n";
    print_r($_SESSION);
    echo "\nUser Info:\n";
    print_r($user);
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Driver - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<div class="flex min-h-screen">
\
  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r flex flex-col justify-between py-6 px-4 hidden md:flex">
    <div>
      <div class="flex items-center gap-2 mb-10 px-2">
        <span class="fa fa-car text-blue-700 text-2xl"></span>
        <span class="font-bold text-xl text-blue-700">ProDrivers</span>
      </div>
      <nav class="flex flex-col gap-1">
        <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-blue-700 bg-blue-50">
          <i class="fa fa-th-large"></i> Dashboard
        </a>
        <a href="book-driver.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 bg-blue-50 font-medium">
          <i class="fa fa-plus-circle"></i> Book a Driver
        </a>
        <a href="my-bookings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
          <i class="fa fa-calendar-check"></i> My Bookings
        </a>
        <a href="notifications.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 relative">
          <i class="fa fa-bell"></i> Notifications
          <?php if (isset($unread_notifications) && $unread_notifications > 0): ?>
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
    <header class="w-full bg-white border-b px-8 py-4 flex items-center justify-between sticky top-0 z-10">
      <h1 class="text-2xl font-semibold text-gray-900">Book a Driver</h1>
      <div class="flex items-center gap-4">
        <img src="<?php echo htmlspecialchars(!empty($user['profile_picture']) ? $user['profile_picture'] : 'images/default-profile.png'); ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover border border-gray-200">
        <span class="font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
        <i class="fa fa-chevron-down text-gray-400"></i>
      </div>
    </header>
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
      <!-- Large Search Bar -->
      <form method="GET" class="mb-8">
        <input type="text" name="location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>" placeholder="Search by location, date, time, experience, rating" class="w-full rounded-2xl border border-gray-200 bg-white px-6 py-4 text-base text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
      </form>
      <!-- Section Title -->
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Available Drivers</h2>
      <!-- Drivers Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 mb-8">
        <?php if ($drivers_result && $drivers_result->num_rows > 0): ?>
          <?php while($driver = $drivers_result->fetch_assoc()): ?>
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 flex flex-col items-center">
              <img src="<?= !empty($driver['profile_picture']) ? htmlspecialchars($driver['profile_picture']) : 'images/default-profile.png' ?>" class="w-28 h-28 rounded-xl object-cover border border-gray-200 bg-gray-100 mb-4" alt="Driver Photo">
              <div class="text-center w-full">
                <h4 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h4>
                <div class="text-gray-600 text-sm mb-1"><?= htmlspecialchars($driver['experience'] ?? '0') ?> years experience</div>
                <div class="text-gray-500 text-sm mb-1"><?= htmlspecialchars($driver['address'] ?? 'Lagos, Nigeria') ?></div>
                <div class="text-green-600 text-xs mb-2">Available Now</div>
                <!-- Star Rating Placeholder -->
                <div class="flex items-center justify-center gap-1 mb-4">
                  <i class="fa fa-star text-yellow-400"></i>
                  <i class="fa fa-star text-yellow-400"></i>
                  <i class="fa fa-star text-yellow-400"></i>
                  <i class="fa fa-star text-yellow-400"></i>
                  <i class="fa fa-star-half-alt text-yellow-400"></i>
                  <span class="ml-2 text-gray-500 text-xs">4.8</span>
                </div>
                <form action="payment/payment.php" method="GET" class="w-full">
                  <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
                  <input type="hidden" name="amount" value="5000"> <!-- Replace with actual amount calculation -->
                  <button type="submit" class="w-full rounded-lg bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2.5 text-base shadow-sm transition">Book Now</button>
                </form>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-span-full flex flex-col items-center justify-center text-gray-500 py-12">
            <i class="fa fa-info-circle text-3xl mb-2"></i>
            <span class="text-lg">No drivers match your search criteria. Please try different filters.</span>
          </div>
        <?php endif; ?>
      </div>
      <!-- Pagination Placeholder -->
      <div class="flex justify-center items-center gap-2 mb-8">
        <button class="w-8 h-8 rounded bg-blue-900 text-white font-bold">1</button>
        <button class="w-8 h-8 rounded bg-gray-200 text-gray-700">2</button>
        <button class="w-8 h-8 rounded bg-gray-200 text-gray-700">3</button>
        <button class="w-8 h-8 rounded bg-gray-200 text-gray-700">4</button>
        <button class="w-8 h-8 rounded bg-gray-200 text-gray-700">5</button>
      </div>
    </main>
  </div>
</div>
<script>
  function updateSort(value) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('sort', value);
    urlParams.set('order', 'DESC');
    window.location.search = urlParams.toString();
  }
</script>
</body>
</html> 