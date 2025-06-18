<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle verification status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_id'])) {
    $driver_id = $_POST['driver_id'];
    $is_verified = $_POST['is_verified'];
    $notes = $_POST['notes'];
    $verified_by = $_SESSION['admin_id'];

    // Simple update to drivers table
    $sql = "UPDATE drivers SET 
            is_verified = ?,
            verified_at = CURRENT_TIMESTAMP,
            verification_notes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $is_verified, $notes, $driver_id);
    
    if ($stmt->execute()) {
        $_SESSION['status_message'] = "Driver verification status updated successfully.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_message'] = "Error updating verification status.";
        $_SESSION['status_type'] = "danger";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all drivers with basic info
$sql = "SELECT 
    d.id,
    d.first_name,
    d.last_name,
    d.email,
    d.phone,
    COALESCE(d.is_verified, 0) as is_verified,
    d.verification_notes,
    d.verified_at,
    COUNT(b.id) as total_trips
    FROM drivers d
    LEFT JOIN bookings b ON d.id = b.driver_id AND b.status = 'completed'
    GROUP BY d.id, d.first_name, d.last_name, d.email, d.phone, d.is_verified, d.verification_notes, d.verified_at
    ORDER BY d.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Verification - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f6fa;
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
            background: #f5f6fa;
        }
        .table th { 
            background: #f8f9fa;
            font-weight: 500;
        }
        .verified { 
            color: #198754;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .unverified { 
            color: #dc3545;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .table td {
            vertical-align: middle;
            font-weight: normal;
        }
        .driver-name {
            font-weight: normal;
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
                <div class="alert alert-<?= $_SESSION['status_type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['status_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['status_message'], $_SESSION['status_type']); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Driver Verification</h1>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Contact</th>
                                    <th>Trips</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($driver = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="driver-name">
                                                <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($driver['email']) ?>
                                                </span><br>
                                                <span class="text-muted">
                                                    <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($driver['phone']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="bi bi-car-front-fill me-1"></i><?= $driver['total_trips'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?= $driver['is_verified'] ? 'verified' : 'unverified' ?>">
                                                    <i class="bi <?= $driver['is_verified'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                                                    <?= $driver['is_verified'] ? 'Verified' : 'Unverified' ?>
                                                </span>
                                                <?php if($driver['verified_at']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        <?= date('M j, Y', strtotime($driver['verified_at'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($driver['verification_notes'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                                                    <input type="hidden" name="is_verified" 
                                                           value="<?= $driver['is_verified'] ? '0' : '1' ?>">
                                                    <input type="text" name="notes" 
                                                           class="form-control form-control-sm" 
                                                           placeholder="Add note" 
                                                           style="width: 150px;"
                                                           value="<?= htmlspecialchars($driver['verification_notes'] ?? '') ?>">
                                                    <button type="submit" 
                                                            class="btn btn-sm <?= $driver['is_verified'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                                        <?= $driver['is_verified'] ? 'Unverify' : 'Verify' ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">No drivers found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 