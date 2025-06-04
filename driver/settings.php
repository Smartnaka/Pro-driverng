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

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        if (password_verify($current_password, $driver['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE drivers SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("si", $hashed_password, $driver_id);
                    
                    if ($stmt->execute()) {
                        $success = "Password updated successfully!";
                    } else {
                        $error = "Failed to update password.";
                    }
                } else {
                    $error = "New password must be at least 8 characters long.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
    
    // Handle notification preferences
    if (isset($_POST['update_notifications'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        
        $update_sql = "UPDATE drivers SET 
            email_notifications = ?,
            sms_notifications = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iii", $email_notifications, $sms_notifications, $driver_id);
        
        if ($stmt->execute()) {
            $success = "Notification preferences updated successfully!";
        } else {
            $error = "Failed to update notification preferences.";
        }
    }

    // Handle account deletion request
    if (isset($_POST['delete_account'])) {
        if (password_verify($_POST['confirm_password'], $driver['password'])) {
            // Add logic here to handle account deletion
            // You might want to soft delete or archive the account instead of permanent deletion
            $success = "Account deletion request received. Our team will process it within 24 hours.";
        } else {
            $error = "Incorrect password. Account deletion cancelled.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Driver Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .content {
            margin-left: 250px;
            padding: 2rem;
        }
        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .settings-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .settings-body {
            padding: 1.5rem;
        }
        .settings-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-right: 0.5rem;
        }
        .delete-account {
            background: #fee2e2;
            border: 1px solid #fecaca;
        }
        .delete-account .settings-title {
            color: #dc2626;
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

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<nav class="navbar navbar-light bg-white d-md-none border-bottom">
    <div class="container-fluid">
        <button class="btn btn-outline-primary" onclick="toggleSidebar()">☰ Menu</button>
        <span class="navbar-brand mb-0">Settings</span>
    </div>
</nav>

<div class="content">
    <h4 class="mb-4">Account Settings</h4>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Security Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <h5 class="settings-title">
                <i class="bi bi-shield-lock me-2"></i>
                Security Settings
            </h5>
        </div>
        <div class="settings-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                    <div class="form-text">Password must be at least 8 characters long</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-primary">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="settings-card">
        <div class="settings-header">
            <h5 class="settings-title">
                <i class="bi bi-bell me-2"></i>
                Notification Preferences
            </h5>
        </div>
        <div class="settings-body">
            <form method="POST">
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input" id="emailNotifications" 
                           name="email_notifications" <?= ($driver['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input" id="smsNotifications" 
                           name="sms_notifications" <?= ($driver['sms_notifications'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="smsNotifications">SMS Notifications</label>
                </div>
                <button type="submit" name="update_notifications" class="btn btn-primary">
                    Save Preferences
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Account -->
    <div class="settings-card delete-account">
        <div class="settings-header">
            <h5 class="settings-title">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Delete Account
            </h5>
        </div>
        <div class="settings-body">
            <p class="text-danger mb-4">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
            <form method="POST" onsubmit="return confirmDelete()">
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="delete_account" class="btn btn-danger">
                    Delete My Account
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete your account? This action cannot be undone.');
    }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    }

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>

</body>
</html> 