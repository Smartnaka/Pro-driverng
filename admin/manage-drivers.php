<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle driver status updates
if (isset($_POST['driver_id']) && isset($_POST['action'])) {
    $driver_id = $_POST['driver_id'];
    $action = $_POST['action'];
    
    switch($action) {
        case 'approve':
            $status = 'approved';
            break;
        case 'reject':
            $status = 'rejected';
            break;
        case 'block':
            $status = 'blocked';
            break;
        case 'activate':
            $status = 'approved';
            break;
        case 'set_pending':
            $status = 'pending';
            break;
        default:
            $status = 'pending';
    }
    
    // Debug output
    error_log("Action: " . $action . ", Status: " . $status);
    
    $sql = "UPDATE drivers SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $driver_id);
    if ($stmt->execute()) {
        error_log("Status updated successfully");
        $_SESSION['status_message'] = "Driver status updated successfully.";
    } else {
        error_log("Error updating status: " . $stmt->error);
        $_SESSION['status_message'] = "Error updating driver status.";
    }
    
    // Redirect after form submission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all drivers
$sql = "SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM drivers ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            position: relative;
        }
        /* Wrapper styles */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        .main-content {
            flex: 1;
            margin-left: 280px; /* Same as sidebar width */
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            background: #f8f9fa;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        .status-badge {
            text-transform: capitalize;
        }
        .driver-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .driver-photo-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 1.5rem;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .experience-badge {
            background: #e8f0fe;
            color: #1a73e8;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }
        .table-responsive {
            overflow-x: auto;
            min-height: 400px;
        }
        .table th {
            white-space: nowrap;
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .table td {
            vertical-align: middle;
            min-width: 100px;
        }
        .table td.actions-column {
            min-width: 200px;
            white-space: nowrap;
        }
        .btn-group {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
        }
        .btn-group .form-select {
            width: auto;
            min-width: 150px;
        }
        .btn-group .btn {
            flex-shrink: 0;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .form-select-sm {
            padding: 0.25rem 2rem 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-outline-primary:hover {
            color: #fff;
        }
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
        .table td.email-column {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table td.name-column {
            min-width: 150px;
            white-space: nowrap;
        }
        .table td.phone-column {
            min-width: 120px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="wrapper">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
            <!-- Status Message -->
            <?php if (isset($_SESSION['status_message'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php 
                    echo htmlspecialchars($_SESSION['status_message']);
                    unset($_SESSION['status_message']); 
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

        <h2 class="mb-4">Manage Drivers</h2>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                        <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Experience</th>
                                <th>Education</th>
                                <th>Status</th>
                                    <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($driver = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($driver['profile_picture'])): ?>
                                            <img src="../<?= htmlspecialchars($driver['profile_picture']) ?>" 
                                                 alt="<?= htmlspecialchars($driver['full_name']) ?>" 
                                                 class="driver-photo">
                                        <?php else: ?>
                                            <div class="driver-photo-placeholder">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                        <td class="name-column"><?= htmlspecialchars($driver['full_name']) ?></td>
                                        <td class="email-column"><?= htmlspecialchars($driver['email']) ?></td>
                                        <td class="phone-column"><?= htmlspecialchars($driver['phone']) ?></td>
                                    <td>
                                        <span class="experience-badge">
                                            <i class="bi bi-clock-history me-1"></i>
                                            <?= htmlspecialchars($driver['exp_years']) ?> years
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($driver['education']) ?></td>
                                    <td>
                                        <?php 
                                            $current_status = $driver['status'] ?? 'pending';
                                            $status_class = match($current_status) {
                                                'approved' => 'success',
                                                'pending' => 'warning',
                                                'rejected' => 'danger',
                                                'blocked' => 'secondary',
                                                default => 'info'
                                            };
                                        ?>
                                        <span class="badge status-badge bg-<?= $status_class ?>">
                                            <?= htmlspecialchars(ucfirst($current_status)) ?>
                                        </span>
                                    </td>
                                        <td class="actions-column text-end">
                                        <div class="btn-group">
                                            <form method="POST" class="d-inline me-1">
                                                <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                                                <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="">Update Status</option>
                                                    <?php if(($driver['status'] ?? 'pending') === 'pending'): ?>
                                                        <option value="approve">Approve Driver</option>
                                                        <option value="reject">Reject Driver</option>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(($driver['status'] ?? 'pending') === 'approved'): ?>
                                                        <option value="block">Block Driver</option>
                                                        <option value="set_pending">Set as Pending</option>
                                                    <?php endif; ?>

                                                    <?php if(($driver['status'] ?? 'pending') === 'blocked'): ?>
                                                        <option value="activate">Activate Driver</option>
                                                        <option value="set_pending">Set as Pending</option>
                                                    <?php endif; ?>

                                                    <?php if(($driver['status'] ?? 'pending') === 'rejected'): ?>
                                                        <option value="set_pending">Set as Pending</option>
                                                    <?php endif; ?>
                                                </select>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#viewDriverModal<?= $driver['id'] ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="edit-driver.php?id=<?= $driver['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>

                                        <!-- Driver Details Modal -->
                                        <div class="modal fade" id="viewDriverModal<?= $driver['id'] ?>" tabindex="-1" 
                                             aria-labelledby="viewDriverModalLabel<?= $driver['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewDriverModalLabel<?= $driver['id'] ?>">
                                                            Driver Details
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                                <!-- Profile Picture Section -->
                                                            <div class="col-md-4 text-center mb-3">
                                                                <?php if(!empty($driver['profile_picture'])): ?>
                                                                    <img src="../<?= htmlspecialchars($driver['profile_picture']) ?>" 
                                                                         alt="<?= htmlspecialchars($driver['full_name']) ?>" 
                                                                         class="img-fluid rounded" style="max-width: 200px;">
                                                                <?php else: ?>
                                                                    <div class="driver-photo-placeholder" style="width: 200px; height: 200px; margin: 0 auto;">
                                                                        <i class="bi bi-person" style="font-size: 4rem;"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                    <div class="mt-2">
                                                                    <span class="badge status-badge bg-<?php 
                                                                        echo match($driver['status'] ?? 'pending') {
                                                                            'approved' => 'success',
                                                                            'pending' => 'warning',
                                                                            'rejected' => 'danger',
                                                                            'blocked' => 'secondary',
                                                                            default => 'info'
                                                                        };
                                                                    ?>">
                                                                            <?= htmlspecialchars(ucfirst($driver['status'] ?? 'pending')) ?>
                                                                    </span>
                                                                    </div>
                                                                </div>

                                                                <!-- Basic Information -->
                                                                <div class="col-md-8">
                                                                    <h4 class="border-bottom pb-2"><?= htmlspecialchars($driver['full_name']) ?></h4>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p><strong><i class="bi bi-envelope"></i> Email:</strong><br>
                                                                            <?= htmlspecialchars($driver['email']) ?></p>
                                                                            
                                                                            <p><strong><i class="bi bi-telephone"></i> Phone:</strong><br>
                                                                            <?= htmlspecialchars($driver['phone']) ?></p>

                                                                            <p><strong><i class="bi bi-calendar"></i> Date of Birth:</strong><br>
                                                                            <?= !empty($driver['dob']) ? htmlspecialchars(date('F j, Y', strtotime($driver['dob']))) : 'Not provided' ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p><strong><i class="bi bi-geo-alt"></i> Location:</strong><br>
                                                                            <?= htmlspecialchars($driver['address'] ?? 'Not provided') ?></p>

                                                                            <p><strong><i class="bi bi-house"></i> Residential Address:</strong><br>
                                                                            <?= htmlspecialchars($driver['resident'] ?? 'Not provided') ?></p>

                                                                            <p><strong><i class="bi bi-person-vcard"></i> NIN:</strong><br>
                                                                            <?= htmlspecialchars($driver['nin'] ?? 'Not provided') ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Professional Information -->
                                                            <div class="mt-4">
                                                                <h5 class="border-bottom pb-2"><i class="bi bi-briefcase"></i> Professional Information</h5>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <p><strong>Experience:</strong><br>
                                                                        <?= htmlspecialchars($driver['exp_years']) ?> years</p>

                                                                        <p><strong>Education Level:</strong><br>
                                                                        <?= htmlspecialchars($driver['education_level'] ?? $driver['education']) ?></p>

                                                                        <p><strong>License Number:</strong><br>
                                                                        <?= htmlspecialchars($driver['license_number'] ?? 'Not provided') ?></p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <p><strong>Vehicle Types:</strong><br>
                                                                        <?= htmlspecialchars($driver['drive'] ?? 'Not specified') ?></p>

                                                                        <p><strong>Languages:</strong><br>
                                                                        <?= htmlspecialchars($driver['speak'] ?? 'Not specified') ?></p>

                                                                        <p><strong>Additional Skills:</strong><br>
                                                                        <?= htmlspecialchars($driver['skills'] ?? 'Not specified') ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Documents Section -->
                                                            <div class="mt-4">
                                                                <h5 class="border-bottom pb-2"><i class="bi bi-file-earmark-text"></i> Documents</h5>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="document-card">
                                                                            <p><strong>Driver's License:</strong><br>
                                                                            <?php if(!empty($driver['license_image'])): ?>
                                                                                <a href="../<?= htmlspecialchars($driver['license_image']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="bi bi-eye"></i> View License
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">Not uploaded</span>
                                                                            <?php endif; ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="document-card">
                                                                            <p><strong>Vehicle Papers:</strong><br>
                                                                            <?php if(!empty($driver['vehicle_papers_path'])): ?>
                                                                                <a href="../<?= htmlspecialchars($driver['vehicle_papers_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="bi bi-eye"></i> View Papers
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">Not uploaded</span>
                                                                            <?php endif; ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Bank Information -->
                                                            <div class="mt-4">
                                                                <h5 class="border-bottom pb-2"><i class="bi bi-bank"></i> Bank Information</h5>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <p><strong>Bank Name:</strong><br>
                                                                        <?= htmlspecialchars($driver['bank_name'] ?? 'Not provided') ?></p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <p><strong>Account Name:</strong><br>
                                                                        <?= htmlspecialchars($driver['acc_name'] ?? 'Not provided') ?></p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <p><strong>Account Number:</strong><br>
                                                                        <?= htmlspecialchars($driver['acc_num'] ?? 'Not provided') ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- About Section -->
                                                            <?php if(!empty($driver['about_me'])): ?>
                                                            <div class="mt-4">
                                                                <h5 class="border-bottom pb-2"><i class="bi bi-person-lines-fill"></i> About Driver</h5>
                                                                <p><?= nl2br(htmlspecialchars($driver['about_me'])) ?></p>
                                                            </div>
                                                            <?php endif; ?>

                                                            <!-- Registration Info -->
                                                            <div class="mt-4">
                                                                <h5 class="border-bottom pb-2"><i class="bi bi-clock-history"></i> Registration Information</h5>
                                                                <p><strong>Registered:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($driver['created_at']))) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <a href="edit-driver.php?id=<?= $driver['id'] ?>" class="btn btn-primary">
                                                                <i class="bi bi-pencil"></i> Edit Driver
                                                            </a>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 