<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Fetch statistics
$stats = [
    'total_customers' => $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'],
    'total_drivers' => $conn->query("SELECT COUNT(*) as count FROM drivers")->fetch_assoc()['count'],
    'total_bookings' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'],
    'pending_bookings' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count']
];

// Fetch recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, c.first_name as customer_name, d.first_name as driver_name 
    FROM bookings b 
    JOIN customers c ON b.user_id = c.id 
    JOIN drivers d ON b.driver_id = d.id 
    ORDER BY b.created_at DESC LIMIT 5
");

// Fetch recent users
$recent_users = $conn->query("
    SELECT * FROM customers 
    ORDER BY created_at DESC LIMIT 5
");

// Fetch recent drivers
$recent_drivers = $conn->query("
    SELECT * FROM drivers 
    ORDER BY created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pro-Drivers</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .content {
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }
        .recent-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }
        .table {
            font-size: 0.875rem;
        }
        .table th {
            font-weight: 600;
            color: #64748b;
        }
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="container-fluid">
            <h2 class="mb-4">Dashboard Overview</h2>

            <!-- Statistics -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value"><?= $stats['total_customers'] ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="stat-value"><?= $stats['total_drivers'] ?></div>
                        <div class="stat-label">Total Drivers</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-value"><?= $stats['pending_bookings'] ?></div>
                        <div class="stat-label">Pending Bookings</div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="recent-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="section-title mb-0">Recent Bookings</h5>
                    <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Driver</th>
                                <th>Pickup</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($booking['driver_name']) ?></td>
                                    <td><?= htmlspecialchars($booking['pickup_location']) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['pickup_date'])) ?></td>
                                    <td>
                                        <span class="status-badge bg-<?= getStatusColor($booking['status']) ?> bg-opacity-10 text-<?= getStatusColor($booking['status']) ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <!-- Recent Customers -->
                <div class="col-md-6">
                    <div class="recent-section">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="section-title mb-0">Recent Customers</h5>
                            <a href="customers.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = $recent_users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Drivers -->
                <div class="col-md-6">
                    <div class="recent-section">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="section-title mb-0">Recent Drivers</h5>
                            <a href="drivers.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Experience</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($driver = $recent_drivers->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></td>
                                            <td><?= htmlspecialchars($driver['address']) ?></td>
                                            <td><?= htmlspecialchars($driver['experience']) ?> yrs</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'accepted':
            return 'success';
        case 'rejected':
            return 'danger';
        case 'completed':
            return 'info';
        case 'cancelled':
            return 'secondary';
        default:
            return 'primary';
    }
}
?> 