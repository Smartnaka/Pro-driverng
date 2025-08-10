<?php
session_start();
include 'include/db.php';
include 'include/security.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// Pagination settings
$page = isset($_GET['page']) ? (int)filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Count total bookings for pagination
$count_sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_bookings = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_bookings / $per_page);

// Validate page number
$page = max(1, min($page, $total_pages));

// Fetch paginated bookings for this user with driver details
$bookings_sql = "
    SELECT 
        b.*,
        b.cancellation_time,
        b.cancellation_fee,
        b.refund_amount,
        d.first_name as driver_first_name,
        d.last_name as driver_last_name,
        d.phone as driver_phone,
        d.profile_picture as driver_profile_picture
    FROM bookings b
    LEFT JOIN drivers d ON b.driver_id = d.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($bookings_sql);
if ($stmt === false) {
    die("Error preparing bookings query: " . $conn->error);
}
$stmt->bind_param("iii", $user_id, $per_page, $offset);
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
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link crossorigin="" href="https://fonts.gstatic.com/" rel="preconnect"/>
    <link as="style" href="https://fonts.googleapis.com/css2?display=swap&amp;family=Manrope%3Awght%40400%3B500%3B700%3B800&amp;family=Noto+Sans%3Awght%40400%3B500%3B700%3B900" onload="this.rel='stylesheet'" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <title>My Bookings - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[var(--background-color)] text-[var(--text-primary)]">
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
      <div class="flex items-center gap-3">
        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'images/default-avatar.png'); ?>" alt="Profile Picture" class="w-10 h-10 rounded-full object-cover border border-gray-200">
        <div>
          <p class="text-sm font-semibold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
          <a href="profile.php" class="text-xs text-gray-500 hover:text-blue-600">View Profile</a>
        </div>
      </div>
    </div>
  </aside>
  <div class="flex-1 flex flex-col">
    <header class="w-full bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-10">
      <h1 class="text-2xl font-semibold text-gray-900">My Bookings</h1>
      <div class="flex items-center gap-4">
        <a href="book-driver.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
          <i class="fa fa-plus"></i> New Booking
        </a>
      </div>
    </header>
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <div class="mb-6">
          <div class="relative bg-white rounded-lg shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fa fa-search text-gray-400"></i>
            </div>
            <input class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="Search by driver name, date, or booking reference..." 
                   type="text"/>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider">Booking Ref.</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider">Driver</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider">Pickup/Drop-off</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[var(--text-secondary)] uppercase tracking-wider text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php foreach ($bookings as $i => $booking): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]">#PD<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[var(--text-primary)]">
                    <div class="flex items-center gap-3">
                      <img src="<?php echo htmlspecialchars($booking['driver_profile_picture'] ?? 'images/default-avatar.png'); ?>" 
                           alt="Driver" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                      <?php echo htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']); ?>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">
                    <?php echo date('M j, Y', strtotime($booking['pickup_date'])); ?><br>
                    <span class="text-xs"><?php echo date('g:i A', strtotime($booking['pickup_time'])); ?></span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-secondary)]">
                    <?php echo htmlspecialchars($booking['pickup_location']); ?> to<br>
                    <?php echo htmlspecialchars($booking['dropoff_location']); ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="relative has-tooltip">
                      <?php
                        $statusClasses = [
                            'pending_payment' => 'bg-yellow-100 text-yellow-800',
                            'pending_driver_response' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-green-100 text-green-800',
                            'in_progress' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-blue-100 text-blue-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        $statusClass = $statusClasses[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                      ?>
                      <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full <?php echo $statusClass; ?>">
                        <?php echo getStatusDisplayText($booking['status']); ?>
                      </span>
                      <div class="tooltip bottom-full mb-2 w-max max-w-xs p-3 bg-white text-[var(--text-secondary)] text-sm rounded-lg shadow-soft-lg border border-gray-200">
                        <?php if ($booking['status'] === 'cancelled'): ?>
                          <?php if (isset($booking['cancellation_time']) && $booking['cancellation_time']): ?>
                            Cancelled on <?php echo date('M j, Y g:i A', strtotime($booking['cancellation_time'])); ?>
                          <?php else: ?>
                            Cancelled
                          <?php endif; ?>
                          <?php if (isset($booking['cancellation_fee']) && $booking['cancellation_fee'] > 0): ?>
                            <br>Cancellation Fee: ₦<?php echo number_format($booking['cancellation_fee'], 2); ?>
                          <?php endif; ?>
                          <?php if (isset($booking['refund_amount']) && $booking['refund_amount'] > 0): ?>
                            <br>Refund Amount: ₦<?php echo number_format($booking['refund_amount'], 2); ?>
                          <?php endif; ?>
                        <?php else: ?>
                          <?php echo getStatusDisplayText($booking['status']); ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-2">
                    <a href="javascript:void(0)" onclick="viewBookingDetails(<?php echo $booking['id']; ?>)" 
                       class="text-[var(--primary-color)] hover:underline">View</a>
                    <?php if (!in_array($booking['status'], ['completed', 'cancelled'])): ?>
                    <a href="javascript:void(0)" 
                       onclick="cancelBooking(<?php echo $booking['id']; ?>, '<?php echo $booking['pickup_date']; ?>', '<?php echo $booking['pickup_time']; ?>')" 
                       class="text-red-600 hover:underline">Cancel</a>
                    <?php endif; ?>
                    <?php if ($booking['status'] === 'completed' || $booking['status'] === 'cancelled'): ?>
                    <button class="button_primary px-3 py-1 text-xs">Rebook</button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if (empty($bookings)): ?>
          <div class="text-center text-gray-500 py-8">You have no bookings yet.</div>
          <?php else: ?>
          <!-- Pagination -->
          <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to 
                <span class="font-medium"><?php echo min($offset + $per_page, $total_bookings); ?></span> of 
                <span class="font-medium"><?php echo $total_bookings; ?></span> bookings
              </div>
              <div class="flex gap-2">
                <?php if ($page > 1): ?>
                  <a href="?page=<?php echo $page - 1; ?>" 
                     class="px-3 py-1 rounded-md bg-white border border-gray-300 text-sm hover:bg-gray-50">
                    Previous
                  </a>
                <?php endif; ?>
                <?php if ($page < $total_pages): ?>
                  <a href="?page=<?php echo $page + 1; ?>" 
                     class="px-3 py-1 rounded-md bg-white border border-gray-300 text-sm hover:bg-gray-50">
                    Next
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</div>
    </main>
  </div>
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-lg shadow-soft-lg p-6 w-full max-w-sm">
    <h3 class="typography_h2 mb-4">Cancel Booking</h3>
    <div class="mb-6">
      <div class="text-[var(--text-secondary)] mb-4">Are you sure you want to cancel this booking?</div>
      <div class="bg-[var(--secondary-color)] p-4 rounded-lg">
        <p class="font-semibold mb-2">Cancellation Policy:</p>
        <ul class="space-y-2 text-sm text-[var(--text-secondary)]">
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-green-600">check_circle</span>
            More than 24 hours: 10% fee
          </li>
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-yellow-600">warning</span>
            2-24 hours: 50% fee
          </li>
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-red-600">error</span>
            Less than 2 hours: 100% fee
          </li>
        </ul>
        <div class="mt-4 pt-4 border-t border-gray-200">
          <p id="cancellationFeeEstimate" class="font-medium text-red-600"></p>
          <p id="refundEstimate" class="text-green-600 mt-1"></p>
        </div>
      </div>
    </div>
    <div class="flex justify-end gap-3">
      <button id="cancelModalNo" class="button_secondary">No, Keep Booking</button>
      <button id="cancelModalYes" class="button_primary bg-red-600 hover:bg-red-700 flex items-center gap-2">
        <span id="cancelModalYesText">Yes, Cancel</span>
        <svg id="cancelModalSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </button>
    </div>
  </div>
</div>

<!-- Toast Notifications -->
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
let cancelling = false;

function calculateHoursUntilPickup(pickupDate, pickupTime) {
  const pickup = new Date(pickupDate + ' ' + pickupTime);
  const now = new Date();
  return (pickup - now) / (1000 * 60 * 60);
}

function cancelBooking(bookingId, pickupDate, pickupTime) {
  if (cancelling) return;
  
  cancelBookingId = bookingId;
  const hoursUntil = calculateHoursUntilPickup(pickupDate, pickupTime);
  const feeEstimateEl = document.getElementById('cancellationFeeEstimate');
  const refundEstimateEl = document.getElementById('refundEstimate');
  
  let feePercentage = 0;
  if (hoursUntil < 2) {
    feePercentage = 100;
  } else if (hoursUntil < 24) {
    feePercentage = 50;
  } else {
    feePercentage = 10;
  }
  
  if (hoursUntil < 0) {
    feeEstimateEl.textContent = 'This booking is in the past and cannot be cancelled.';
    refundEstimateEl.textContent = '';
    document.getElementById('cancelModalYes').disabled = true;
    document.getElementById('cancelModalYes').classList.add('opacity-50');
  } else {
    feeEstimateEl.textContent = `Estimated cancellation fee: ${feePercentage}% of booking amount`;
    if (feePercentage < 100) {
      refundEstimateEl.textContent = `You will receive a ${100 - feePercentage}% refund`;
    } else {
      refundEstimateEl.textContent = 'No refund will be issued';
    }
    document.getElementById('cancelModalYes').disabled = false;
    document.getElementById('cancelModalYes').classList.remove('opacity-50');
  }
  
  document.getElementById('cancelModal').classList.remove('hidden');
}

document.getElementById('cancelModalNo').onclick = function() {
  if (cancelling) return;
  document.getElementById('cancelModal').classList.add('hidden');
  cancelBookingId = null;
};

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `px-4 py-3 rounded-lg shadow-soft-lg text-white text-sm mb-2 flex items-center gap-2 ${
    type === 'success' ? 'bg-green-600' : 'bg-red-600'
  }`;
  const icon = document.createElement('span');
  icon.className = 'material-symbols-outlined text-lg';
  icon.textContent = type === 'success' ? 'check_circle' : 'error';
  toast.appendChild(icon);
  const text = document.createElement('span');
  text.textContent = message;
  toast.appendChild(text);
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

document.getElementById('cancelModalYes').onclick = function() {
  if (!cancelBookingId || cancelling) return;
  cancelling = true;
  document.getElementById('cancelModalYes').disabled = true;
  document.getElementById('cancelModalNo').disabled = true;
  document.getElementById('cancelModalSpinner').classList.remove('hidden');
  
  fetch('api/cancel_booking.php', {
    method: 'POST',
    headers: { 
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
    },
    body: 'booking_id=' + encodeURIComponent(cancelBookingId) + '&csrf_token=' + encodeURIComponent('<?php echo $_SESSION['csrf_token']; ?>')
  })
  .then(response => response.json())
  .then(data => {
    cancelling = false;
    document.getElementById('cancelModalYes').disabled = false;
    document.getElementById('cancelModalNo').disabled = false;
    document.getElementById('cancelModalSpinner').classList.add('hidden');
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
    cancelling = false;
    document.getElementById('cancelModalYes').disabled = false;
    document.getElementById('cancelModalNo').disabled = false;
    document.getElementById('cancelModalSpinner').classList.add('hidden');
    document.getElementById('cancelModal').classList.add('hidden');
    cancelBookingId = null;
    showToast('Network error. Please try again.', 'error');
  });
};

function viewBookingDetails(bookingId) {
  window.location.href = `view-booking.php?id=${bookingId}&token=<?php echo $_SESSION['csrf_token']; ?>`;
}
</script>

</body>
</html>
</body>
</html> 