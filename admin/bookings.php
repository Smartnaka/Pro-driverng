<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $driver_id = $_POST['driver_id'] ?? null;
    
    $sql = "UPDATE bookings SET status = ?, driver_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $driver_id, $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Booking updated successfully.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error updating booking.";
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
        d.email as driver_email
        FROM bookings b
        LEFT JOIN customers c ON b.user_id = c.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        ORDER BY b.pickup_date DESC, b.pickup_time DESC";
$bookings = $conn->query($sql);

// Fetch available drivers for assignment
$drivers_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name 
                FROM drivers 
                WHERE status = 'approved' AND is_verified = 1";
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
    <title>Bookings & Scheduling - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
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
        }
        .booking-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.4rem 0.8rem;
        }
        .calendar-container {
            background: #fff;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
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
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <?php if (isset($_SESSION['status_message'])): ?>
                <div class="alert alert-<?= $_SESSION['status_type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['status_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['status_message']);
                unset($_SESSION['status_type']);
                ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Bookings & Scheduling</h2>
                <div class="btn-group">
                    <button class="btn btn-outline-primary active" data-view="list">
                        <i class="bi bi-list"></i> List View
                    </button>
                    <button class="btn btn-outline-primary" data-view="calendar">
                        <i class="bi bi-calendar"></i> Calendar View
                    </button>
                </div>
            </div>
            
            <!-- List View -->
            <div id="listView">
                <div class="row">
                    <?php while($booking = $bookings->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card booking-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">
                                            Booking #<?= htmlspecialchars($booking['id']) ?>
                                        </h5>
                                        <?php
                                        $status_class = match($booking['status']) {
                                            'pending' => 'warning',
                                            'accepted' => 'success',
                                            'completed' => 'info',
                                            'rejected', 'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $status_class ?> status-badge">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="trip-info">
                                        <p class="mb-2">
                                            <i class="bi bi-geo-alt text-primary"></i>
                                            <strong>From:</strong> <?= htmlspecialchars($booking['pickup_location']) ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-geo-alt-fill text-success"></i>
                                            <strong>To:</strong> <?= htmlspecialchars($booking['dropoff_location']) ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-calendar-event"></i>
                                            <strong>Date:</strong> <?= date('M d, Y', strtotime($booking['pickup_date'])) ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-clock"></i>
                                            <strong>Time:</strong> <?= date('g:i A', strtotime($booking['pickup_time'])) ?>
                                        </p>
                                        <p class="mb-2">
                                            <i class="bi bi-hourglass-split"></i>
                                            <strong>Duration:</strong> <?= htmlspecialchars($booking['duration_days']) ?> day(s)
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-car-front"></i>
                                            <strong>Vehicle:</strong> <?= htmlspecialchars($booking['vehicle_type']) ?>
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
                                            <p class="mb-1">
                                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($booking['customer_email']) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <?php if($booking['driver_id'] && $booking['driver_name']): ?>
                                        <div class="driver-info mb-3">
                                            <h6 class="border-bottom pb-2">Assigned Driver</h6>
                                            <p class="mb-1">
                                                <i class="bi bi-person-badge"></i>
                                                <strong><?= htmlspecialchars($booking['driver_name']) ?></strong>
                                            </p>
                                            <div class="contact-info">
                                                <p class="mb-1">
                                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($booking['driver_phone']) ?>
                                                </p>
                                                <p class="mb-1">
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
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Assign Driver</label>
                                                <select name="driver_id" class="form-select">
                                                    <option value="">Select Driver</option>
                                                    <?php foreach($available_drivers as $driver): ?>
                                                        <option value="<?= $driver['id'] ?>" 
                                                                <?= $booking['driver_id'] == $driver['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($driver['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Update Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>
                                                        Pending
                                                    </option>
                                                    <option value="accepted" <?= $booking['status'] == 'accepted' ? 'selected' : '' ?>>
                                                        Accepted
                                                    </option>
                                                    <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>
                                                        Completed
                                                    </option>
                                                    <option value="rejected" <?= $booking['status'] == 'rejected' ? 'selected' : '' ?>>
                                                        Rejected
                                                    </option>
                                                    <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>
                                                        Cancelled
                                                    </option>
                                                </select>
                                            </div>
                                            
                                            <div class="text-end">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            
            <!-- Calendar View -->
            <div id="calendarView" class="d-none">
                <div class="calendar-container">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View switching
            const viewButtons = document.querySelectorAll('[data-view]');
            const listView = document.getElementById('listView');
            const calendarView = document.getElementById('calendarView');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const view = this.dataset.view;
                    if (view === 'list') {
                        listView.classList.remove('d-none');
                        calendarView.classList.add('d-none');
                    } else {
                        listView.classList.add('d-none');
                        calendarView.classList.remove('d-none');
                        calendar.render(); // Re-render calendar when shown
                    }
                });
            });
            
            // Initialize FullCalendar
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php 
                    mysqli_data_seek($bookings, 0);
                    while($booking = $bookings->fetch_assoc()): 
                        $color = match($booking['status']) {
                            'pending' => '#ffc107',
                            'accepted' => '#28a745',
                            'completed' => '#17a2b8',
                            'rejected', 'cancelled' => '#dc3545',
                            default => '#6c757d'
                        };
                        
                        // Create datetime string from separate date and time fields
                        $start_datetime = date('Y-m-d H:i:s', strtotime($booking['pickup_date'] . ' ' . $booking['pickup_time']));
                        $end_datetime = date('Y-m-d H:i:s', strtotime($booking['pickup_date'] . ' ' . $booking['pickup_time'] . ' +' . $booking['duration_days'] . ' days'));
                    ?>
                    {
                        title: '<?= $booking['customer_name'] ?> - <?= $booking['vehicle_type'] ?>',
                        start: '<?= $start_datetime ?>',
                        end: '<?= $end_datetime ?>',
                        color: '<?= $color ?>',
                        url: '#bookingModal<?= $booking['id'] ?>',
                        extendedProps: {
                            modalId: 'bookingModal<?= $booking['id'] ?>'
                        }
                    },
                    <?php endwhile; ?>
                ],
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    const modalId = info.event.extendedProps.modalId;
                    const modal = new bootstrap.Modal(document.getElementById(modalId));
                    modal.show();
                }
            });
            
            calendar.render();
        });
    </script>
</body>
</html> 