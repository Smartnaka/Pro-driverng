<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Site Settings
        $site_name = $_POST['site_name'] ?? '';
        $site_email = $_POST['site_email'] ?? '';
        $support_phone = $_POST['support_phone'] ?? '';
        $commission_rate = $_POST['commission_rate'] ?? 0;
        
        // Email Settings
        $smtp_host = $_POST['smtp_host'] ?? '';
        $smtp_user = $_POST['smtp_user'] ?? '';
        $smtp_port = $_POST['smtp_port'] ?? '';
        
        // Only update SMTP password if provided
        $smtp_password = $_POST['smtp_password'] ?? '';
        
        // Update site settings
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES 
                ('site_name', ?),
                ('site_email', ?),
                ('support_phone', ?),
                ('commission_rate', ?),
                ('smtp_host', ?),
                ('smtp_user', ?),
                ('smtp_port', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssdsss', 
            $site_name, 
            $site_email, 
            $support_phone,
            $commission_rate,
            $smtp_host,
            $smtp_user,
            $smtp_port
        );
        
        if ($stmt->execute()) {
            // Update SMTP password if provided
            if (!empty($smtp_password)) {
                $pwd_sql = "INSERT INTO settings (setting_key, setting_value) 
                           VALUES ('smtp_password', ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                $pwd_stmt = $conn->prepare($pwd_sql);
                $pwd_stmt->bind_param('s', $smtp_password);
                $pwd_stmt->execute();
            }
            
            $_SESSION['status_message'] = "Settings updated successfully.";
            $_SESSION['status_type'] = "success";
        } else {
            throw new Exception("Failed to update settings.");
        }
    } catch (Exception $e) {
        $_SESSION['status_message'] = "Error updating settings: " . $e->getMessage();
        $_SESSION['status_type'] = "danger";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch current settings
$settings = [];
$sql = "SELECT setting_key, setting_value FROM settings";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Dashboard</title>
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
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .settings-section {
            margin-bottom: 2rem;
        }
        .settings-section h2 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #2d3748;
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
                <h1 class="h3 mb-0">System Settings</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" class="settings-form">
                        <div class="settings-section">
                            <h2><i class="bi bi-gear-fill me-2"></i>Site Settings</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control" 
                                           value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Email</label>
                                    <input type="email" name="site_email" class="form-control" 
                                           value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Support Phone</label>
                                    <input type="tel" name="support_phone" class="form-control" 
                                           value="<?= htmlspecialchars($settings['support_phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Commission Rate (%)</label>
                                    <input type="number" name="commission_rate" class="form-control" 
                                           value="<?= htmlspecialchars($settings['commission_rate'] ?? '10') ?>" 
                                           min="0" max="100" step="0.1">
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h2><i class="bi bi-envelope-fill me-2"></i>Email Settings</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" name="smtp_host" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Port</label>
                                    <input type="number" name="smtp_port" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" name="smtp_user" class="form-control" 
                                           value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" name="smtp_password" class="form-control" 
                                           placeholder="Leave blank to keep current password">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 