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
      <div class="mb-8 p-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="flex flex-col">
            <label for="location" class="mb-1 text-sm font-medium text-gray-700">Location</label>
            <input id="location" type="text" name="location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>" placeholder="Location" class="rounded-xl border px-4 py-2 w-full" />
          </div>
          <div class="flex flex-col">
            <label for="vehicle_type" class="mb-1 text-sm font-medium text-gray-700">Vehicle Type</label>
            <select id="vehicle_type" name="vehicle_type" class="rounded-xl border px-4 py-2 w-full">
              <option value="">Vehicle Type</option>
              <option value="Manual, Long Distance" <?= (($_GET['vehicle_type'] ?? '') == 'Manual, Long Distance') ? 'selected' : '' ?>>Manual, Long Distance</option>
              <option value="Car, Bus" <?= (($_GET['vehicle_type'] ?? '') == 'Car, Bus') ? 'selected' : '' ?>>Car, Bus</option>
              <option value="Car, Bus, Coaster" <?= (($_GET['vehicle_type'] ?? '') == 'Car, Bus, Coaster') ? 'selected' : '' ?>>Car, Bus, Coaster</option>
              <option value="Car, Bus, Coaster, Motorcycle/Tricycle" <?= (($_GET['vehicle_type'] ?? '') == 'Car, Bus, Coaster, Motorcycle/Tricycle') ? 'selected' : '' ?>>Car, Bus, Coaster, Motorcycle/Tricycle</option>
            </select>
          </div>
          <div class="flex flex-col">
            <label for="experience" class="mb-1 text-sm font-medium text-gray-700">Min Experience (years)</label>
            <select id="experience" name="experience" class="rounded-xl border px-4 py-2 w-full">
              <option value="">Min Experience (years)</option>
              <?php for ($i = 1; $i <= 20; $i++): ?>
                <option value="<?= $i ?>" <?= (($_GET['experience'] ?? '') == $i) ? 'selected' : '' ?>><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="invisible md:visible mb-1 text-sm font-medium">&nbsp;</label>
            <button type="submit" class="bg-blue-900 text-white rounded-xl px-6 py-2 w-full md:w-auto md:self-end">Search</button>
          </div>
        </form>
      </div>
      <!-- Section Title -->
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Available Drivers</h2>
      <!-- Drivers Grid -->
      <div id="driver-grid-container">
        <?php include 'partials/driver-grid.php'; ?>
      </div>
      <!-- Pagination Controls -->
      <?php
        // Calculate total drivers for pagination
        $count_sql = "SELECT COUNT(*) as total FROM drivers";
        $count_where = [];
        $count_params = [];
        $count_types = "";
        if (isset($_GET['location']) && !empty($_GET['location'])) {
          $count_where[] = "address LIKE ?";
          $count_params[] = "%" . $_GET['location'] . "%";
          $count_types .= "s";
        }
        if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
          $count_where[] = "drive LIKE ?";
          $count_params[] = "%" . $_GET['vehicle_type'] . "%";
          $count_types .= "s";
        }
        if (isset($_GET['experience']) && !empty($_GET['experience'])) {
          $count_where[] = "experience >= ?";
          $count_params[] = $_GET['experience'];
          $count_types .= "i";
        }
        if (!empty($count_where)) {
          $count_sql .= " WHERE " . implode(" AND ", $count_where);
        }
        $count_stmt = $conn->prepare($count_sql);
        if (!empty($count_params)) {
          $count_stmt->bind_param($count_types, ...$count_params);
        }
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_drivers = $count_result->fetch_assoc()['total'] ?? 0;
        $per_page = 8;
        $total_pages = ceil($total_drivers / $per_page);
        $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
      ?>
      <div class="flex justify-center items-center gap-2 mb-8" id="pagination-controls">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <button type="button" class="w-8 h-8 rounded <?= $i == $current_page ? 'bg-blue-900 text-white font-bold' : 'bg-gray-200 text-gray-700' ?> pagination-btn" data-page="<?= $i ?>"><?= $i ?></button>
        <?php endfor; ?>
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