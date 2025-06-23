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

$update_message = '';
$update_type = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE customers SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $update_message = 'Password changed successfully!';
                        $update_type = 'success';
                    } else {
                        $update_message = 'Error changing password. Please try again.';
                        $update_type = 'error';
                    }
                } else {
                    $update_message = 'New password must be at least 6 characters long.';
                    $update_type = 'error';
                }
            } else {
                $update_message = 'New passwords do not match.';
                $update_type = 'error';
            }
        } else {
            $update_message = 'Current password is incorrect.';
            $update_type = 'error';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Pro-Drivers</title>
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

        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .settings-section {
            margin-bottom: 2rem;
        }

        .settings-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
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

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .btn-danger:hover {
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
            
            .settings-card {
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

        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-info h6 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
        }

        .setting-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .form-switch {
            padding-left: 2.5rem;
        }

        .form-check-input {
            width: 3rem;
            height: 1.5rem;
            margin-left: -2.5rem;
        }

        .danger-zone {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 1.5rem;
        }

        .danger-zone .section-title {
            color: #dc3545;
            border-bottom-color: #fecaca;
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
        <span class="fw-bold">Settings</span>
        <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h3>Settings</h3>
            <p class="mb-0">Manage your account settings and preferences</p>
        </div>

        <!-- Settings Cards -->
        <div class="settings-card">
            <?php if (!empty($update_message)): ?>
                <div class="alert alert-<?= $update_type === 'success' ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($update_message) ?>
                </div>
            <?php endif; ?>

            <!-- Account Information -->
            <div class="settings-section">
                <h5 class="section-title">
                    <i class="bi bi-person-circle me-2"></i>Account Information
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h6>Name</h6>
                                <p><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h6>Email</h6>
                                <p><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h6>Phone</h6>
                                <p><?= htmlspecialchars($user['phone']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h6>Member Since</h6>
                                <p><?= date('M j, Y', strtotime($user['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="settings-section">
                <h5 class="section-title">
                    <i class="bi bi-shield-lock me-2"></i>Change Password
                </h5>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notification Preferences -->
            <div class="settings-section">
                <h5 class="section-title">
                    <i class="bi bi-bell me-2"></i>Notification Preferences
                </h5>
                <div class="setting-item">
                    <div class="setting-info">
                        <h6>Email Notifications</h6>
                        <p>Receive notifications about your bookings via email</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                        <label class="form-check-label" for="emailNotifications"></label>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-info">
                        <h6>SMS Notifications</h6>
                        <p>Receive booking updates via SMS</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="smsNotifications" checked>
                        <label class="form-check-label" for="smsNotifications"></label>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-info">
                        <h6>Marketing Communications</h6>
                        <p>Receive promotional offers and updates</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="marketingNotifications">
                        <label class="form-check-label" for="marketingNotifications"></label>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="settings-section">
                <h5 class="section-title">
                    <i class="bi bi-eye me-2"></i>Privacy Settings
                </h5>
                <div class="setting-item">
                    <div class="setting-info">
                        <h6>Profile Visibility</h6>
                        <p>Allow drivers to see your profile information</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="profileVisibility" checked>
                        <label class="form-check-label" for="profileVisibility"></label>
                    </div>
                </div>
                <div class="setting-item">
                    <div class="setting-info">
                        <h6>Location Sharing</h6>
                        <p>Share your location with drivers for better service</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="locationSharing" checked>
                        <label class="form-check-label" for="locationSharing"></label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="settings-card danger-zone">
            <h5 class="section-title">
                <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
            </h5>
            <div class="setting-item">
                <div class="setting-info">
                    <h6>Delete Account</h6>
                    <p>Permanently delete your account and all associated data</p>
                </div>
                <button class="btn btn-danger" onclick="confirmDeleteAccount()">
                    <i class="bi bi-trash me-1"></i>Delete Account
                </button>
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

        function confirmDeleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone and will permanently remove all your data.')) {
                if (confirm('This is your final warning. Are you absolutely sure you want to delete your account?')) {
                    // Here you would typically redirect to a delete account page or make an AJAX call
                    alert('Account deletion feature will be implemented soon.');
                }
            }
        }

        // Save notification preferences
        document.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Here you would typically save the preference via AJAX
                console.log('Preference changed:', this.id, this.checked);
            });
        });
    </script>
</body>
</html> 