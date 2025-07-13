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
        $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
        
        $update_sql = "UPDATE drivers SET 
            email_notifications = ?,
            sms_notifications = ?,
            push_notifications = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iiii", $email_notifications, $sms_notifications, $push_notifications, $driver_id);
        
        if ($stmt->execute()) {
            $success = "Notification preferences updated successfully!";
        } else {
            $error = "Failed to update notification preferences.";
        }
    }

    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $phone = $_POST['phone'];
        $emergency_contact = $_POST['emergency_contact'];
        
        $update_sql = "UPDATE drivers SET 
            phone = ?,
            emergency_contact = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssi", $phone, $emergency_contact, $driver_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh driver data
            $driver['phone'] = $phone;
            $driver['emergency_contact'] = $emergency_contact;
        } else {
            $error = "Failed to update profile.";
        }
    }

    // Handle account deletion request
    if (isset($_POST['delete_account'])) {
        if (password_verify($_POST['delete_confirm_password'], $driver['password'])) {
            // Add logic here to handle account deletion
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
      <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
 <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #003366;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: var(--gray-700);
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            background: var(--gray-50);
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-subtitle {
            color: var(--gray-500);
            font-size: 1rem;
            margin: 0;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .settings-card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .settings-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .settings-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .settings-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .settings-title i {
            font-size: 1.25rem;
            color: var(--primary-color);
        }

        .settings-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-text {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 2px solid transparent;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            line-height: 1;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background: transparent;
            color: var(--gray-600);
            border-color: var(--gray-300);
        }

        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        /* Custom Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--gray-300);
            transition: .3s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: var(--shadow-sm);
        }

        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        .notification-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .notification-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 500;
            color: var(--gray-900);
            margin: 0 0 0.25rem 0;
        }

        .notification-desc {
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin: 0;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }

        .delete-section {
            border: 2px solid #fee2e2;
            background: #fef2f2;
        }

        .delete-section .settings-header {
            background: #fee2e2;
        }

        .delete-section .settings-title {
            color: var(--danger-color);
        }

        .warning-text {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 1rem;
            color: #92400e;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .warning-text i {
            color: var(--warning-color);
            margin-top: 0.125rem;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .settings-body {
                padding: 1.5rem;
            }
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-meter {
            height: 4px;
            background: var(--gray-200);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { width: 25%; background: var(--danger-color); }
        .strength-fair { width: 50%; background: var(--warning-color); }
        .strength-good { width: 75%; background: #3b82f6; }
        .strength-strong { width: 100%; background: var(--success-color); }

        .input-group {
            position: relative;
        }

        .input-group .form-control {
            padding-right: 3rem;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0;
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--gray-600);
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-container">
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="bi bi-gear-fill"></i>
                Account Settings
            </h1>
            <p class="page-subtitle">Manage your account preferences, security settings, and notifications</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- Profile Information -->
            <div class="settings-card">
                <div class="settings-header">
                    <h2 class="settings-title">
                        <i class="bi bi-person-circle"></i>
                        Profile Information
                    </h2>
                </div>
                <div class="settings-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($driver['name'] ?? '') ?>" readonly>
                            <div class="form-text">Contact support to change your name</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($driver['email'] ?? '') ?>" readonly>
                            <div class="form-text">Contact support to change your email</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($driver['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Emergency Contact</label>
                            <input type="tel" name="emergency_contact" class="form-control" value="<?= htmlspecialchars($driver['emergency_contact'] ?? '') ?>">
                            <div class="form-text">This contact will be notified in case of emergencies</div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="settings-card">
                <div class="settings-header">
                    <h2 class="settings-title">
                        <i class="bi bi-shield-lock"></i>
                        Security Settings
                    </h2>
                </div>
                <div class="settings-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="current_password" class="form-control" id="currentPassword" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('currentPassword')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" class="form-control" id="newPassword" required oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="toggle-password" onclick="togglePassword('newPassword')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="form-text" id="strengthText">Password must be at least 8 characters long</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" class="form-control" id="confirmPassword" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary">
                            <i class="bi bi-key"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="settings-card">
                <div class="settings-header">
                    <h2 class="settings-title">
                        <i class="bi bi-bell"></i>
                        Notification Preferences
                    </h2>
                </div>
                <div class="settings-body">
                    <form method="POST">
                        <div class="notification-item">
                            <div class="notification-content">
                                <h4 class="notification-title">Email Notifications</h4>
                                <p class="notification-desc">Receive trip updates, earnings reports, and important announcements via email</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_notifications" <?= ($driver['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-content">
                                <h4 class="notification-title">SMS Notifications</h4>
                                <p class="notification-desc">Get ride requests and urgent updates via text message</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="sms_notifications" <?= ($driver['sms_notifications'] ?? 1) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-content">
                                <h4 class="notification-title">Push Notifications</h4>
                                <p class="notification-desc">Receive real-time alerts through the mobile app</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="push_notifications" <?= ($driver['push_notifications'] ?? 1) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <button type="submit" name="update_notifications" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Save Preferences
                        </button>
                    </form>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="settings-card delete-section">
                <div class="settings-header">
                    <h2 class="settings-title">
                        <i class="bi bi-exclamation-triangle"></i>
                        Delete Account
                    </h2>
                </div>
                <div class="settings-body">
                    <div class="warning-text">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>
                            <strong>Warning:</strong> This action cannot be undone. All your data, including trip history, earnings, and account information will be permanently deleted.
                        </div>
                    </div>
                    <form method="POST" onsubmit="return confirmDelete()">
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" name="delete_confirm_password" class="form-control" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('delete_confirm_password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" name="delete_account" class="btn btn-danger">
                            <i class="bi bi-trash"></i>
                            Delete My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/javascript/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    function checkPasswordStrength(password) {
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        let text = '';
        let className = '';
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        switch(strength) {
            case 0:
            case 1:
                text = 'Very weak password';
                className = 'strength-weak';
                break;
            case 2:
                text = 'Weak password';
                className = 'strength-weak';
                break;
            case 3:
                text = 'Fair password';
                className = 'strength-fair';
                break;
            case 4:
                text = 'Good password';
                className = 'strength-good';
                break;
            case 5:
                text = 'Strong password';
                className = 'strength-strong';
                break;
        }
        
        strengthFill.className = 'strength-fill ' + className;
        strengthText.textContent = text;
    }

    function confirmDelete() {
        return confirm('⚠️ FINAL WARNING ⚠️\n\nAre you absolutely sure you want to delete your account?\n\nThis will:\n• Permanently delete all your data\n• Remove your trip history\n• Delete your earnings records\n• Deactivate your driver profile\n\nThis action CANNOT be undone!\n\nClick OK only if you\'re completely sure.');
    }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    }

    // Auto-hide alerts after 7 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 7000);
    });

    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>

</body>
</html>