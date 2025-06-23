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
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            background: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 1rem;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="20" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
            opacity: 0.1;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: var(--shadow-md);
            object-fit: cover;
            margin-bottom: 1.5rem;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .profile-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            font-size: 0.9rem;
        }

        .info-card {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .info-card-title {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-bg);
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 500;
        }

        .badge-verified {
            background: #d1e7dd;
            color: #0f5132;
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-pending {
            background: #fff3cd;
            color: #664d03;
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .edit-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s ease;
            text-decoration: none;
        }

        .edit-button:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .profile-header {
                padding: 2rem 1rem;
                text-align: center;
            }

            .profile-picture {
                width: 120px;
                height: 120px;
            }

            .edit-button {
                position: relative;
                top: auto;
                right: auto;
                margin-top: 1rem;
                justify-content: center;
            }
        }
    </style>
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
            <div class="profile-name"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></div>
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