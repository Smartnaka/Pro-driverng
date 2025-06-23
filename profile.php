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

// Handle profile update
$update_message = '';
$update_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($email)) {
        $update_message = 'All fields are required.';
        $update_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_message = 'Please enter a valid email address.';
        $update_type = 'error';
    } else {
        // Check if email already exists for another user
        $check_sql = "SELECT id FROM customers WHERE email = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $update_message = 'Email address is already in use by another account.';
            $update_type = 'error';
        } else {
            // Update profile
            $update_sql = "UPDATE customers SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssi", $first_name, $last_name, $phone, $email, $user_id);
            
            if ($update_stmt->execute()) {
                $update_message = 'Profile updated successfully!';
                $update_type = 'success';
                // Refresh user data
                $stmt->execute();
                $user = $result->fetch_assoc();
            } else {
                $update_message = 'Error updating profile. Please try again.';
                $update_type = 'error';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pro-Drivers</title>
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

        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .profile-picture-section {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e5e7eb;
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .upload-btn {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background-color 0.2s ease;
        }

        .upload-btn:hover {
            background: #0b5ed7;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0099ff);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
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
            
            .profile-card {
                padding: 1.5rem;
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

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #0d6efd;
            margin: 0;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <button class="hamburger-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
            <span class="d-none d-sm-inline">Menu</span>
        </button>
        <span class="fw-bold">My Profile</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h3>My Profile</h3>
            <p class="mb-0">Manage your account information and preferences</p>
        </div>

        <!-- Profile Card -->
        <div class="profile-card">
            <?php if (!empty($update_message)): ?>
                <div class="alert alert-<?= $update_type === 'success' ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($update_message) ?>
                </div>
            <?php endif; ?>

            <!-- Profile Picture Section -->
            <div class="profile-picture-section">
                <img src="images/default-avatar.png" alt="Default Avatar" class="profile-picture">
            </div>

            <!-- Profile Form -->
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" 
                              placeholder="Enter your address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Update Profile
                    </button>
                </div>
            </form>

            <!-- Account Statistics -->
            <div class="stats-section">
                <div class="stat-item">
                    <p class="stat-value">
                        <?php
                        // Count total bookings
                        $bookings_sql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
                        $stmt = $conn->prepare($bookings_sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $bookings_count = $stmt->get_result()->fetch_assoc()['count'];
                        echo $bookings_count;
                        ?>
                    </p>
                    <p class="stat-label">Total Bookings</p>
                </div>
                
                <div class="stat-item">
                    <p class="stat-value">
                        <?php
                        // Count completed bookings
                        $completed_sql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'completed'";
                        $stmt = $conn->prepare($completed_sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $completed_count = $stmt->get_result()->fetch_assoc()['count'];
                        echo $completed_count;
                        ?>
                    </p>
                    <p class="stat-label">Completed Trips</p>
                </div>
                
                <div class="stat-item">
                    <p class="stat-value">
                        <?php
                        // Count active bookings
                        $active_sql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status IN ('confirmed', 'in_progress')";
                        $stmt = $conn->prepare($active_sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $active_count = $stmt->get_result()->fetch_assoc()['count'];
                        echo $active_count;
                        ?>
                    </p>
                    <p class="stat-label">Active Bookings</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script>
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
    </script>
</body>
</html> 