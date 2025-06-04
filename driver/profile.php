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
$profile_picture = $driver['profile_picture'] ?? '../images/default-profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }

        .content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .profile-header {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            color: white;
            border-radius: 16px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .info-card-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 1rem;
        }

        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: #2c3e50;
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
        }

        .edit-button:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        @media (max-width: 768px) {
            .content {
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

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="profile-header">
        <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture" class="profile-picture">
        <h1 class="profile-name"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h1>
        <div class="profile-status">
            <i class="bi bi-circle-fill <?= $driver['is_online'] ? 'text-success' : 'text-secondary' ?> me-2"></i>
            <?= $driver['is_online'] ? 'Online' : 'Offline' ?>
        </div>
        <a href="edit_profile.php" class="btn edit-button">
            <i class="bi bi-pencil-square"></i>
            Edit Profile
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="info-card">
                <h3 class="info-card-title">
                    <i class="bi bi-person-circle"></i>
                    Personal Information
                </h3>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($driver['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?= htmlspecialchars($driver['phone']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value"><?= htmlspecialchars($driver['dob'] ?? 'Not Set') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">NIN</div>
                    <div class="info-value"><?= htmlspecialchars($driver['nin'] ?? 'Not Set') ?></div>
                </div>
            </div>

            <div class="info-card">
                <h3 class="info-card-title">
                    <i class="bi bi-geo-alt"></i>
                    Location Details
                </h3>
                <div class="info-item">
                    <div class="info-label">Current Location</div>
                    <div class="info-value"><?= htmlspecialchars($driver['address'] ?? 'Not Set') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Residential Address</div>
                    <div class="info-value"><?= htmlspecialchars($driver['resident'] ?? 'Not Set') ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="info-card">
                <h3 class="info-card-title">
                    <i class="bi bi-briefcase"></i>
                    Professional Information
                </h3>
                <div class="info-item">
                    <div class="info-label">Driver's License</div>
                    <div class="info-value">
                        <?= htmlspecialchars($driver['license_number'] ?? 'Not Set') ?>
                        <?php if (!empty($driver['license_number'])): ?>
                            <span class="badge-verified ms-2">Verified</span>
                        <?php else: ?>
                            <span class="badge-pending ms-2">Pending</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Experience</div>
                    <div class="info-value"><?= htmlspecialchars($driver['experience'] ?? '0') ?> years</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Vehicle Types</div>
                    <div class="info-value"><?= htmlspecialchars($driver['drive'] ?? 'Not Set') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Languages</div>
                    <div class="info-value"><?= htmlspecialchars($driver['speak'] ?? 'Not Set') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Skills</div>
                    <div class="info-value"><?= htmlspecialchars($driver['skills'] ?? 'Not Set') ?></div>
                </div>
            </div>

            <div class="info-card">
                <h3 class="info-card-title">
                    <i class="bi bi-bank"></i>
                    Bank Information
                </h3>
                <div class="info-item">
                    <div class="info-label">Bank Name</div>
                    <div class="info-value"><?= htmlspecialchars($driver['bank_name'] ?? 'Not Set') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Account Name</div>
                    <div class="info-value"><?= htmlspecialchars($driver['acc_name'] ?? 'Not Set') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Account Number</div>
                    <div class="info-value"><?= htmlspecialchars($driver['acc_num'] ?? 'Not Set') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handle mobile sidebar toggle if needed
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.querySelector('.mobile-overlay').classList.toggle('active');
        document.body.style.overflow = document.getElementById('sidebar').classList.contains('active') ? 'hidden' : '';
    }
</script>

</body>
</html> 