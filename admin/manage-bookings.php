<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle booking status updates
if (isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $sql = "UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);
    
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

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_trips,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_trips,
    SUM(CASE WHEN status IN ('rejected', 'cancelled') THEN 1 ELSE 0 END) as cancelled_bookings
    FROM bookings";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Get filter values
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build the query
$sql = "SELECT b.*, 
        CONCAT(d.first_name, ' ', d.last_name) as driver_name,
        d.phone as driver_phone,
        d.email as driver_email,
        CONCAT(c.first_name, ' ', c.last_name) as customer_name,
        c.email as customer_email,
        c.phone as customer_phone
        FROM bookings b 
        LEFT JOIN drivers d ON b.driver_id = d.id
        LEFT JOIN customers c ON b.user_id = c.id
        WHERE 1=1";

if ($status_filter !== 'all') {
    $sql .= " AND b.status = '" . $conn->real_escape_string($status_filter) . "'";
}

if ($date_filter !== 'all') {
    switch($date_filter) {
        case 'today':
            $sql .= " AND DATE(b.pickup_date) = CURDATE()";
            break;
        case 'tomorrow':
            $sql .= " AND DATE(b.pickup_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $sql .= " AND b.pickup_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $sql .= " AND b.pickup_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (
        b.pickup_location LIKE '%$search%' OR 
        b.dropoff_location LIKE '%$search%' OR 
        CONCAT(c.first_name, ' ', c.last_name) LIKE '%$search%' OR
        CONCAT(d.first_name, ' ', d.last_name) LIKE '%$search%' OR
        c.email LIKE '%$search%' OR
        c.phone LIKE '%$search%'
    )";
}

$sql .= " ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management Dashboard - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stats-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            text-transform: capitalize;
            font-size: 0.875rem;
            padding: 0.4rem 0.8rem;
        }
        .location-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table th {
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .search-box {
            max-width: 300px;
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

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Booking Management Dashboard</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <a href="driver-bookings.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-car-front"></i> Driver Operations
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <h2 class="mb-0"><?= number_format($stats['total_bookings']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Pending Bookings</h5>
                        <h2 class="mb-0"><?= number_format($stats['pending_bookings']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Trips</h5>
                        <h2 class="mb-0"><?= number_format($stats['active_trips']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Completed Trips</h5>
                        <h2 class="mb-0"><?= number_format($stats['completed_trips']) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Filter</label>
                        <select name="date" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $date_filter === 'all' ? 'selected' : '' ?>>All Dates</option>
                            <option value="today" <?= $date_filter === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="tomorrow" <?= $date_filter === 'tomorrow' ? 'selected' : '' ?>>Tomorrow</option>
                            <option value="week" <?= $date_filter === 'week' ? 'selected' : '' ?>>Next 7 Days</option>
                            <option value="month" <?= $date_filter === 'month' ? 'selected' : '' ?>>Next 30 Days</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search locations, names, contact..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="?status=all&date=all" class="btn btn-outline-secondary">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Driver</th>
                                <th>Pickup</th>
                                <th>Dropoff</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($booking['customer_name']) ?></strong><br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($booking['customer_phone']) ?><br>
                                            <?= htmlspecialchars($booking['customer_email']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if($booking['driver_name']): ?>
                                            <strong><?= htmlspecialchars($booking['driver_name']) ?></strong><br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($booking['driver_phone']) ?><br>
                                                <?= htmlspecialchars($booking['driver_email']) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="location-cell" title="<?= htmlspecialchars($booking['pickup_location']) ?>">
                                        <?= htmlspecialchars($booking['pickup_location']) ?>
                                    </td>
                                    <td class="location-cell" title="<?= htmlspecialchars($booking['dropoff_location']) ?>">
                                        <?= htmlspecialchars($booking['dropoff_location']) ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($booking['pickup_date'])) ?><br>
                                        <i class="bi bi-clock"></i> <?= date('g:i A', strtotime($booking['pickup_time'])) ?><br>
                                        <small class="text-muted">
                                            <i class="bi bi-hourglass-split"></i> <?= htmlspecialchars($booking['duration_days']) ?> day(s)
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = match($booking['status']) {
                                                'pending' => 'warning',
                                                'accepted' => 'primary',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'rejected', 'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?= $status_class ?> status-badge">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#bookingModal<?= $booking['id'] ?>">
                                                Manage
                                            </button>
                                        </div>
                                    </td>
                                </tr>

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
                                                        <label class="form-label">Update Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>
                                                                Pending
                                                            </option>
                                                            <option value="accepted" <?= $booking['status'] == 'accepted' ? 'selected' : '' ?>>
                                                                Accepted
                                                            </option>
                                                            <option value="in_progress" <?= $booking['status'] == 'in_progress' ? 'selected' : '' ?>>
                                                                In Progress
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
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 