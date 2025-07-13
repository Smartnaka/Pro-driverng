<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['driver_id'];

// Fetch driver details
$sql = "SELECT * FROM drivers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing driver query: " . $conn->error);
}
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// Get profile picture path
$profile_picture = $driver['profile_picture'] ?? '../images/default-avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pro-Drivers</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/profile.css">
</head>
<body>
    <!-- Include Shared Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-header">
            <a href="edit_profile.php" class="edit-button">
                <i class="fas fa-edit"></i>
                Edit Profile
            </a>
            <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="profile-picture">
            <div class="profile-name text-white"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></div>
            <div class="profile-status">
                <?php if ($driver['is_verified']): ?>
                    <span class="badge-verified">✓ Verified Driver</span>
                <?php else: ?>
                    <span class="badge-pending">⏳ Pending Verification</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="info-card">
                    <h5 class="info-card-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </h5>
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?= htmlspecialchars($driver['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?= htmlspecialchars($driver['phone']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?= htmlspecialchars($driver['date_of_birth'] ?? 'Not provided') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?= htmlspecialchars($driver['address'] ?? 'Not provided') ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <h5 class="info-card-title">
                        <i class="fas fa-id-card"></i>
                        Driver Information
                    </h5>
                    <div class="info-item">
                        <div class="info-label">License Number</div>
                        <div class="info-value"><?= htmlspecialchars($driver['license_number'] ?? 'Not provided') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">License Expiry Date</div>
                        <div class="info-value"><?= htmlspecialchars($driver['license_expiry'] ?? 'Not provided') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Years of Experience</div>
                        <div class="info-value"><?= htmlspecialchars($driver['exp_years'] ?? '0') ?> years</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Vehicle Type</div>
                        <div class="info-value"><?= htmlspecialchars($driver['vehicle_type'] ?? 'Not specified') ?></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="info-card">
                    <h5 class="info-card-title">
                        <i class="fas fa-shield-alt"></i>
                        Account Status
                    </h5>
                    <div class="info-item">
                        <div class="info-label">Verification Status</div>
                        <div class="info-value">
                            <?php if ($driver['is_verified']): ?>
                                <span class="badge-verified">✓ Verified</span>
                            <?php else: ?>
                                <span class="badge-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Online Status</div>
                        <div class="info-value">
                            <?php if ($driver['is_online']): ?>
                                <span class="badge-verified">🟢 Online</span>
                            <?php else: ?>
                                <span class="badge-pending">🔴 Offline</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?= date('M d, Y', strtotime($driver['created_at'])) ?></div>
                    </div>
                </div>

                <div class="info-card">
                    <h5 class="info-card-title">
                        <i class="fas fa-cog"></i>
                        Quick Actions
                    </h5>
                    <div class="d-grid gap-2">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                        <a href="documents.php" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-2"></i>Manage Documents
                        </a>
                        <a href="settings.php" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i>Account Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>