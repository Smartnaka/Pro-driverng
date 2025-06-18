<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle booking status updates
if (isset($_POST['booking_id']) && isset($_POST['action'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    $driver_id = $_POST['driver_id'] ?? null;
    
    $status = match($action) {
        'assign' => 'assigned',
        'start' => 'in_progress',
        'complete' => 'completed',
        'cancel' => 'cancelled',
        default => 'pending'
    };
    
    $sql = "UPDATE bookings SET status = ?, driver_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $driver_id, $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Booking status updated successfully.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error updating booking status.";
        $_SESSION['status_type'] = "danger";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all bookings with customer and driver details
$sql = "SELECT b.*, 
        CONCAT(c.first_name, ' ', c.last_name) as customer_name,
        c.phone as customer_phone,
        c.email as customer_email,
        CONCAT(d.first_name, ' ', d.last_name) as driver_name,
        d.phone as driver_phone,
        d.email as driver_email,
        d.profile_picture as driver_photo
        FROM bookings b
        LEFT JOIN customers c ON b.user_id = c.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        ORDER BY b.pickup_date DESC, b.pickup_time DESC";
$result = $conn->query($sql);

// Fetch available drivers
$drivers_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name, profile_picture, phone, email, exp_years 
                FROM drivers 
                WHERE status = 'approved'";
$drivers = $conn->query($drivers_sql);
$available_drivers = [];
while ($driver = $drivers->fetch_assoc()) {
    $available_drivers[] = $driver;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Bookings - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        .booking-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .booking-card:hover {
            transform: translateY(-2px);
        }
        .driver-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .driver-photo-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 1.2rem;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-badge {
            text-transform: capitalize;
            font-size: 0.875rem;
            padding: 0.4rem 0.8rem;
        }
        .trip-info {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .contact-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            animation: fadeOut 5s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php if (isset($_SESSION['status_message'])): ?>
            <div class="alert alert-<?= $_SESSION['status_type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['status_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['status_message']);
            unset($_SESSION['status_type']);
            ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Driver Bookings</h2>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="filterBookings('all')">All</button>
                <button class="btn btn-outline-warning" onclick="filterBookings('pending')">Pending</button>
                <button class="btn btn-outline-info" onclick="filterBookings('assigned')">Assigned</button>
                <button class="btn btn-outline-primary" onclick="filterBookings('in_progress')">In Progress</button>
                <button class="btn btn-outline-success" onclick="filterBookings('completed')">Completed</button>
            </div>
        </div>

        <div class="row" id="bookingsContainer">
            <?php while($booking = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4 booking-item" data-status="<?= htmlspecialchars($booking['status']) ?>">
                    <div class="card booking-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    Booking #<?= htmlspecialchars($booking['id']) ?>
                                </h5>
                                <?php
                                $status_class = match($booking['status']) {
                                    'pending' => 'warning',
                                    'assigned' => 'info',
                                    'in_progress' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $status_class ?> status-badge">
                                    <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                </span>
                            </div>

                            <div class="trip-info">
                                <div class="d-flex justify-content-between mb-3">
                                    <div>
                                        <p class="mb-1">
                                            <i class="bi bi-calendar-event"></i>
                                            <strong><?= date('M d, Y', strtotime($booking['pickup_date'])) ?></strong>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-clock"></i>
                                            <?= date('g:i A', strtotime($booking['pickup_time'])) ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-1">
                                            <i class="bi bi-hourglass-split"></i>
                                            <strong><?= htmlspecialchars($booking['duration_days']) ?> day(s)</strong>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-car-front"></i>
                                            <?= htmlspecialchars($booking['vehicle_type']) ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <strong>From:</strong> <?= htmlspecialchars($booking['pickup_location']) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-geo-alt-fill text-success"></i>
                                    <strong>To:</strong> <?= htmlspecialchars($booking['dropoff_location']) ?>
                                </p>
                            </div>

                            <div class="customer-info mb-3">
                                <h6 class="border-bottom pb-2">Customer Details</h6>
                                <p class="mb-1">
                                    <i class="bi bi-person"></i>
                                    <strong><?= htmlspecialchars($booking['customer_name']) ?></strong>
                                </p>
                                <div class="contact-info">
                                    <p class="mb-1">
                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($booking['customer_phone']) ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($booking['customer_email']) ?>
                                    </p>
                                </div>
                            </div>

                            <?php if($booking['driver_id'] && $booking['driver_name']): ?>
                                <div class="driver-info mb-3">
                                    <h6 class="border-bottom pb-2">Assigned Driver</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <?php if(!empty($booking['driver_photo'])): ?>
                                            <img src="../<?= htmlspecialchars($booking['driver_photo']) ?>" 
                                                 alt="<?= htmlspecialchars($booking['driver_name']) ?>" 
                                                 class="driver-photo me-2">
                                        <?php else: ?>
                                            <div class="driver-photo-placeholder me-2">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($booking['driver_name']) ?></strong>
                                    </div>
                                    <div class="contact-info">
                                        <p class="mb-1">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($booking['driver_phone']) ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($booking['driver_email']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($booking['additional_notes'])): ?>
                                <div class="notes-info mb-3">
                                    <h6 class="border-bottom pb-2">Additional Notes</h6>
                                    <p class="mb-0 small">
                                        <?= nl2br(htmlspecialchars($booking['additional_notes'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#bookingModal<?= $booking['id'] ?>">
                                    Manage Booking
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Management Modal -->
                <div class="modal fade" id="bookingModal<?= $booking['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Manage Booking #<?= $booking['id'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    
                                    <?php if($booking['status'] === 'pending'): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Assign Driver</label>
                                            <select name="driver_id" class="form-select" required>
                                                <option value="">Select Driver</option>
                                                <?php foreach($available_drivers as $driver): ?>
                                                    <option value="<?= $driver['id'] ?>">
                                                        <?= htmlspecialchars($driver['name']) ?> 
                                                        (<?= htmlspecialchars($driver['exp_years']) ?> years exp.)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="action" value="assign">
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label">Update Status</label>
                                            <select name="action" class="form-select" required>
                                                <?php if($booking['status'] === 'assigned'): ?>
                                                    <option value="start">Start Trip</option>
                                                <?php endif; ?>
                                                <?php if($booking['status'] === 'in_progress'): ?>
                                                    <option value="complete">Complete Trip</option>
                                                <?php endif; ?>
                                                <?php if($booking['status'] !== 'completed' && $booking['status'] !== 'cancelled'): ?>
                                                    <option value="cancel">Cancel Booking</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update Booking</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterBookings(status) {
            const bookings = document.querySelectorAll('.booking-item');
            bookings.forEach(booking => {
                if (status === 'all' || booking.dataset.status === status) {
                    booking.style.display = 'block';
                } else {
                    booking.style.display = 'none';
                }
            });
            
            // Update active filter button
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase().includes(status)) {
                    btn.classList.add('active');
                }
            });
        }
    </script>
</body>
</html> 