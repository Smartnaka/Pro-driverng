<?php
session_start();
require_once 'include/db.php';
require_once 'include/security.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Validate CSRF token
$token = $_GET['token'] ?? '';
if (!validateCSRFToken($token)) {
    header('Location: my-bookings.php');
    exit();
}

// Get and validate booking ID
$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    header('Location: my-bookings.php');
    exit();
}

// Get booking details with driver info
$sql = "SELECT 
    b.*,
    d.first_name as driver_first_name,
    d.last_name as driver_last_name,
    d.phone as driver_phone,
    d.email as driver_email,
    d.profile_picture as driver_profile_picture
FROM bookings b
LEFT JOIN drivers d ON b.driver_id = d.id
WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: my-bookings.php');
    exit();
}

$stmt->bind_param('ii', $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: my-bookings.php');
    exit();
}

// Get user details for sidebar
$user_sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if notifications table exists and get count
$table_check = $conn->query("SHOW TABLES LIKE 'customer_notifications'");
$notifications_table_exists = $table_check->num_rows > 0;

$unread_notifications = 0;
if ($notifications_table_exists) {
    $notifications_sql = "SELECT COUNT(*) as count FROM customer_notifications WHERE user_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($notifications_sql);
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $unread_notifications = $stmt->get_result()->fetch_assoc()['count'];
    }
}

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

function getStatusBadgeClass($status) {
    $statusClasses = [
        'pending_payment' => 'bg-yellow-100 text-yellow-800',
        'pending_driver_response' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-green-100 text-green-800',
        'in_progress' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'cancelled' => 'bg-red-100 text-red-800'
    ];
    return $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - ProDrivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
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
            <header class="bg-white border-b px-6 py-4">
                <div class="max-w-4xl mx-auto flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Booking Details</h1>
                    <a href="my-bookings.php" class="text-blue-600 hover:text-blue-700">
                        <i class="fa fa-arrow-left mr-2"></i> Back to Bookings
                    </a>
                </div>
            </header>

            <main class="flex-1 px-6 py-8">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <!-- Status Banner -->
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Booking Reference</p>
                                <p class="font-semibold">#PD<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full <?php echo getStatusBadgeClass($booking['status']); ?>">
                                    <?php echo getStatusDisplayText($booking['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Driver Information -->
                                <div class="space-y-4">
                                    <h2 class="text-lg font-semibold">Driver Information</h2>
                                    <div class="flex items-start gap-4">
                                        <img src="<?php echo htmlspecialchars($booking['driver_profile_picture'] ?? 'images/default-avatar.png'); ?>" 
                                             alt="Driver" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                                        <div>
                                            <p class="font-semibold"><?php echo htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']); ?></p>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($booking['driver_phone']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Trip Details -->
                                <div class="space-y-4">
                                    <h2 class="text-lg font-semibold">Trip Details</h2>
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-gray-600">Pickup Date & Time</p>
                                            <p class="font-medium">
                                                <?php echo date('M j, Y', strtotime($booking['pickup_date'])); ?> at 
                                                <?php echo date('g:i A', strtotime($booking['pickup_time'])); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600">Pickup Location</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($booking['pickup_location']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600">Dropoff Location</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($booking['dropoff_location']); ?></p>
                                        </div>
                                        <?php if (isset($booking['duration'])): ?>
                                        <div>
                                            <p class="text-gray-600">Duration</p>
                                            <p class="font-medium"><?php echo htmlspecialchars($booking['duration']); ?> hours</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div class="md:col-span-2 space-y-4">
                                    <h2 class="text-lg font-semibold">Payment Details</h2>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-gray-600">Base Fare</p>
                                                <p class="font-medium">₦<?php echo number_format($booking['base_fare'], 2); ?></p>
                                            </div>
                                            <?php if ($booking['status'] === 'cancelled'): ?>
                                            <div>
                                                <p class="text-gray-600">Cancellation Fee</p>
                                                <p class="font-medium text-red-600">₦<?php echo number_format($booking['cancellation_fee'], 2); ?></p>
                                            </div>
                                            <?php if ($booking['refund_amount'] > 0): ?>
                                            <div>
                                                <p class="text-gray-600">Refund Amount</p>
                                                <p class="font-medium text-green-600">₦<?php echo number_format($booking['refund_amount'], 2); ?></p>
                                            </div>
                                            <?php endif; ?>
                                            <?php else: ?>
                                            <div>
                                                <p class="text-gray-600">Total Amount</p>
                                                <p class="font-medium">₦<?php echo number_format($booking['total_amount'], 2); ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!in_array($booking['status'], ['completed', 'cancelled'])): ?>
                            <!-- Action Buttons -->
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <div class="flex justify-end gap-4">
                                    <button onclick="window.location.href='book-driver.php'" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Book Another Driver
                                    </button>
                                    <button onclick="cancelBooking(<?php echo $booking['id']; ?>, '<?php echo $booking['pickup_date']; ?>', '<?php echo $booking['pickup_time']; ?>')" 
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        Cancel Booking
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
            <h3 class="text-xl font-semibold mb-4">Cancel Booking</h3>
            <div class="mb-6">
                <div class="text-gray-600 mb-4">Are you sure you want to cancel this booking?</div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-semibold mb-2">Cancellation Policy:</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-600"></i>
                            More than 24 hours: 10% fee
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            2-24 hours: 50% fee
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-times-circle text-red-600"></i>
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
                <button id="cancelModalNo" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    No, Keep Booking
                </button>
                <button id="cancelModalYes" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                    <span>Yes, Cancel</span>
                    <svg id="cancelModalSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Re-use the same cancellation logic from my-bookings.php
        <?php include 'assets/javascript/booking-cancellation.js'; ?>
    </script>
</body>
</html>
