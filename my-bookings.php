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
            return 'badge bg-warning text-dark';
        case 'pending_driver_response':
            return 'badge bg-info';
        case 'confirmed':
            return 'badge bg-success';
        case 'in_progress':
            return 'badge bg-primary';
        case 'completed':
            return 'badge bg-secondary';
        case 'cancelled':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
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
    <title>My Bookings - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .page-header h3 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e5e7eb;
        }

        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .driver-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .driver-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 3px solid #e5e7eb;
        }

        .driver-details h6 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
        }

        .driver-details p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .driver-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .driver-rating .bi-star-fill {
            color: #fbbf24;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 500;
            color: #1e293b;
        }

        .booking-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
        }

        .overlay.active {
            display: block;
        }

        .mobile-nav {
            display: none;
            padding: 1rem;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .mobile-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
        }

        .hamburger-btn {
            border: none;
            background: none;
            padding: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .hamburger-btn:hover {
            color: #0d6efd;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .amount-display {
            font-size: 1.25rem;
            font-weight: 600;
            color: #059669;
        }

        .reference-code {
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .toast-notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        .toast-notification.success {
            background-color: #28a745;
        }
        .toast-notification.error {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast-notification" class="toast-notification"></div>

    <!-- Include Sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <button class="hamburger-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
            <span class="d-none d-sm-inline">Menu</span>
        </button>
        <span class="fw-bold">My Bookings</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h3>My Bookings</h3>
            <p class="mb-0">Track all your driver bookings and their current status</p>
        </div>

        <?php if (empty($bookings)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>No bookings yet</h5>
                <p>You haven't made any bookings yet. Start by booking a driver for your journey.</p>
                <a href="book-driver.php" class="btn btn-primary">
                    <i class="bi bi-car-front me-2"></i>Book a Driver
                </a>
            </div>
        <?php else: ?>
            <!-- Bookings List -->
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card" id="booking-<?= $booking['id'] ?>">
                    <!-- Driver Information -->
                    <div class="driver-info">
                        <?php if (!empty($booking['driver_profile_picture'])): ?>
                            <img src="<?= htmlspecialchars($booking['driver_profile_picture']) ?>" 
                                 alt="Driver Profile" class="driver-avatar">
                        <?php else: ?>
                            <img src="images/default-profile.png" alt="Default Profile" class="driver-avatar">
                        <?php endif; ?>
                        
                        <div class="driver-details">
                            <h6>
                                <?php if ($booking['driver_first_name']): ?>
                                    <?= htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Driver not assigned yet</span>
                                <?php endif; ?>
                            </h6>
                            <?php if ($booking['driver_phone']): ?>
                                <p><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($booking['driver_phone']) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ms-auto">
                            <span class="status-badge <?= getStatusBadgeClass($booking['status']) ?>">
                                <?= getStatusDisplayText($booking['status']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="booking-details">
                        <div class="detail-item">
                            <span class="detail-label">Pickup Location</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['pickup_location']) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Destination</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['dropoff_location']) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Date & Time</span>
                            <span class="detail-value">
                                <?= date('M j, Y', strtotime($booking['pickup_date'])) ?>
                                <br>
                                <small class="text-muted"><?= date('g:i A', strtotime($booking['pickup_time'])) ?></small>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Amount</span>
                            <span class="amount-display">₦<?= number_format($booking['amount'], 2) ?></span>
                        </div>
                        
                        <?php if (!empty($booking['payment_reference'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Payment Reference</span>
                            <span class="reference-code"><?= htmlspecialchars($booking['payment_reference']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-item">
                            <span class="detail-label">Booked On</span>
                            <span class="detail-value"><?= date('M j, Y g:i A', strtotime($booking['created_at'])) ?></span>
                        </div>
                    </div>

                    <!-- Booking Actions -->
                    <div class="booking-actions">
                        <?php if ($booking['status'] === 'pending_payment'): ?>
                            <a href="payment/payment.php?booking_id=<?= $booking['id'] ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-credit-card me-1"></i>Complete Payment
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'confirmed' || $booking['status'] === 'in_progress'): ?>
                            <button class="btn btn-success btn-sm" onclick="contactDriver('<?= $booking['driver_phone'] ?>')">
                                <i class="bi bi-telephone me-1"></i>Contact Driver
                            </button>
                        <?php endif; ?>
                        
                        <?php if (!in_array($booking['status'], ['completed', 'cancelled'])): ?>
                            <button class="btn btn-danger btn-sm" onclick="cancelBooking(<?= $booking['id'] ?>)">
                                <i class="bi bi-x-circle me-1"></i>Cancel Booking
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-secondary btn-sm" onclick="viewBookingDetails(<?= $booking['id'] ?>)">
                            <i class="bi bi-eye me-1"></i>View Details
                        </button>
                    </div>
                </div>
                <!-- Booking Details Modal -->
                <div class="modal fade" id="bookingDetailsModal<?= $booking['id'] ?>" tabindex="-1" aria-labelledby="bookingDetailsLabel<?= $booking['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="bookingDetailsLabel<?= $booking['id'] ?>">Booking Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item"><strong>Pickup Location:</strong> <?= htmlspecialchars($booking['pickup_location']) ?></li>
                          <li class="list-group-item"><strong>Destination:</strong> <?= htmlspecialchars($booking['dropoff_location']) ?></li>
                          <li class="list-group-item"><strong>Date:</strong> <?= date('M j, Y', strtotime($booking['pickup_date'])) ?></li>
                          <li class="list-group-item"><strong>Time:</strong> <?= date('g:i A', strtotime($booking['pickup_time'])) ?></li>
                          <li class="list-group-item"><strong>Amount:</strong> ₦<?= number_format($booking['amount'], 2) ?></li>
                          <li class="list-group-item"><strong>Status:</strong> <?= getStatusDisplayText($booking['status']) ?></li>
                          <li class="list-group-item"><strong>Booked On:</strong> <?= date('M j, Y g:i A', strtotime($booking['created_at'])) ?></li>
                          <?php if (!empty($booking['payment_reference'])): ?>
                          <li class="list-group-item"><strong>Payment Reference:</strong> <?= htmlspecialchars($booking['payment_reference']) ?></li>
                          <?php endif; ?>
                          <?php if (!empty($booking['driver_first_name'])): ?>
                          <li class="list-group-item"><strong>Driver:</strong> <?= htmlspecialchars($booking['driver_first_name'] . ' ' . $booking['driver_last_name']) ?></li>
                          <li class="list-group-item"><strong>Driver Phone:</strong> <?= htmlspecialchars($booking['driver_phone']) ?></li>
                          <?php endif; ?>
                        </ul>
                        <?php if (!empty($booking['additional_notes'])): ?>
                        <div class="mt-3">
                          <strong>Additional Notes:</strong>
                          <div><?= nl2br(htmlspecialchars($booking['additional_notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script src="assets/javascript/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/javascript/jquery.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            toast.textContent = message;
            toast.className = 'toast-notification ' + type;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
            document.body.style.overflow = document.getElementById('sidebar').classList.contains('active') ? 'hidden' : '';
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !hamburgerBtn.contains(event.target)) {
                toggleSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('overlay').classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        function contactDriver(phone) {
            if (phone) {
                window.open(`tel:${phone}`, '_self');
            } else {
                alert('Driver phone number not available');
            }
        }

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                fetch('api/cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `booking_id=${bookingId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Update the UI
                        const bookingCard = document.querySelector(`#booking-${bookingId}`);
                        if (bookingCard) {
                            // Update status badge
                            const statusBadge = bookingCard.querySelector('.status-badge');
                            statusBadge.textContent = 'Cancelled';
                            statusBadge.className = 'status-badge badge bg-danger';
                            
                            // Remove action buttons
                            const actions = bookingCard.querySelector('.booking-actions');
                            actions.innerHTML = '<button class="btn btn-outline-secondary btn-sm" onclick="viewBookingDetails(' + bookingId + ')"><i class="bi bi-eye me-1"></i>View Details</button>';
                        }
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An unexpected error occurred.', 'error');
                });
            }
        }

        function viewBookingDetails(bookingId) {
            var modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal' + bookingId));
            modal.show();
        }
    </script>
</body>
</html> 