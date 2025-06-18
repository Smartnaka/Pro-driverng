<?php
session_start();
include '../include/db.php';
include 'auth_check.php';

// Check if driver ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-drivers.php");
    exit();
}

$driver_id = $_GET['id'];

// Fetch driver details
$sql = "SELECT * FROM drivers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    header("Location: manage-drivers.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone']);
    $exp_years = filter_var($_POST['exp_years'], FILTER_VALIDATE_INT);
    $education = trim($_POST['education']);
    $address = trim($_POST['address']);
    $resident = trim($_POST['resident']);
    $license_number = trim($_POST['license_number']);
    $about_me = trim($_POST['about_me']);
    $drive = trim($_POST['drive']);
    $speak = trim($_POST['speak']);
    $nin = trim($_POST['nin']);
    $dob = trim($_POST['dob']);
    $bank_name = trim($_POST['bank_name']);
    $acc_num = trim($_POST['acc_num']);
    $acc_name = trim($_POST['acc_name']);
    $skills = trim($_POST['skills']);
    $status = trim($_POST['status']);

    // Handle file uploads
    $profile_picture = $driver['profile_picture'];
    $license_image = $driver['license_image'];
    $vehicle_papers_path = $driver['vehicle_papers_path'];

    // Profile Picture Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profile-picture/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $filename)) {
            $profile_picture = 'uploads/profile-picture/' . $filename;
        }
    }

    // License Image Upload
    if (isset($_FILES['license_image']) && $_FILES['license_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/documents/licenses/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['license_image']['name']);
        if (move_uploaded_file($_FILES['license_image']['tmp_name'], $upload_dir . $filename)) {
            $license_image = 'uploads/documents/licenses/' . $filename;
        }
    }

    // Vehicle Papers Upload
    if (isset($_FILES['vehicle_papers']) && $_FILES['vehicle_papers']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/documents/vehicle_papers/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['vehicle_papers']['name']);
        if (move_uploaded_file($_FILES['vehicle_papers']['tmp_name'], $upload_dir . $filename)) {
            $vehicle_papers_path = 'uploads/documents/vehicle_papers/' . $filename;
        }
    }

    // Update database
    $sql = "UPDATE drivers SET 
            first_name = ?, 
            last_name = ?, 
            email = ?, 
            phone = ?, 
            exp_years = ?, 
            education = ?, 
            address = ?, 
            resident = ?,
            license_number = ?, 
            about_me = ?, 
            drive = ?,
            speak = ?, 
            nin = ?, 
            dob = ?, 
            bank_name = ?, 
            acc_num = ?,
            acc_name = ?, 
            skills = ?, 
            status = ?, 
            profile_picture = ?,
            license_image = ?, 
            vehicle_papers_path = ?
            WHERE id = ?";
            
    // Count parameters to make sure they match (23 total: 22 SET values + 1 WHERE clause)
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }

    // Create the type string that matches exactly with our parameters
    $types = str_repeat('s', 22) . 'i'; // 22 strings + 1 integer for id
    
    try {
        $stmt->bind_param($types, 
            $first_name,      // 1
            $last_name,       // 2
            $email,          // 3
            $phone,          // 4
            $exp_years,      // 5
            $education,      // 6
            $address,        // 7
            $resident,       // 8
            $license_number, // 9
            $about_me,       // 10
            $drive,          // 11
            $speak,          // 12
            $nin,            // 13
            $dob,            // 14
            $bank_name,      // 15
            $acc_num,        // 16
            $acc_name,       // 17
            $skills,         // 18
            $status,         // 19
            $profile_picture,// 20
            $license_image,  // 21
            $vehicle_papers_path, // 22
            $driver_id       // 23 (WHERE clause)
        );
    } catch (Exception $e) {
        die('Error binding parameters: ' . $e->getMessage());
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Driver information updated successfully.";
        header("Location: manage-drivers.php");
        exit();
    } else {
        $error_message = "Error updating driver information: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            position: relative;
        }
        .main-content {
            margin-left: 280px; /* Same as sidebar width */
            padding: 2rem;
            min-height: 100vh;
            background: #f8f9fa;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        .form-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .current-image {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
        /* Alert Styling */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        /* Wrapper for proper sidebar integration */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit Driver Information</h2>
                    <a href="manage-drivers.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Back to Drivers
                    </a>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-person"></i> Basic Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required-field">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($driver['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required-field">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($driver['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required-field">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($driver['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required-field">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($driver['phone']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($driver['dob'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIN</label>
                                <input type="text" name="nin" class="form-control" value="<?= htmlspecialchars($driver['nin'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-geo-alt"></i> Location Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($driver['address'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Residential Address</label>
                                <input type="text" name="resident" class="form-control" value="<?= htmlspecialchars($driver['resident'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-briefcase"></i> Professional Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required-field">Experience (Years)</label>
                                <input type="number" name="exp_years" class="form-control" value="<?= htmlspecialchars($driver['exp_years']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required-field">Education Level</label>
                                <select name="education" class="form-select" required>
                                    <option value="">Select Education Level</option>
                                    <option value="Secondary" <?= ($driver['education'] === 'Secondary') ? 'selected' : '' ?>>Secondary</option>
                                    <option value="Tertiary" <?= ($driver['education'] === 'Tertiary') ? 'selected' : '' ?>>Tertiary</option>
                                    <option value="Uneducated" <?= ($driver['education'] === 'Uneducated') ? 'selected' : '' ?>>Uneducated</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">License Number</label>
                                <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($driver['license_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vehicle Types</label>
                                <select name="drive" class="form-select">
                                    <option value="">Select Vehicle Types</option>
                                    <?php
                                    $driveOptions = [
                                        'Car, Bus',
                                        'Car, Bus, Coaster',
                                        'Car, Bus, Coaster, Motorcycle/Tricycle'
                                    ];
                                    foreach ($driveOptions as $option):
                                    ?>
                                        <option value="<?= $option ?>" <?= ($driver['drive'] ?? '') === $option ? 'selected' : '' ?>>
                                            <?= $option ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Languages Spoken</label>
                                <input type="text" name="speak" class="form-control" value="<?= htmlspecialchars($driver['speak'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional Skills</label>
                                <input type="text" name="skills" class="form-control" value="<?= htmlspecialchars($driver['skills'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">About Driver</label>
                                <textarea name="about_me" class="form-control" rows="3"><?= htmlspecialchars($driver['about_me'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-file-earmark-text"></i> Documents
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_picture" class="form-control" accept="image/*">
                                <?php if(!empty($driver['profile_picture'])): ?>
                                    <img src="../<?= htmlspecialchars($driver['profile_picture']) ?>" class="current-image" alt="Current Profile Picture">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Driver's License</label>
                                <input type="file" name="license_image" class="form-control" accept="image/*,.pdf">
                                <?php if(!empty($driver['license_image'])): ?>
                                    <div class="mt-2">
                                        <a href="../<?= htmlspecialchars($driver['license_image']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            View Current License
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Vehicle Papers</label>
                                <input type="file" name="vehicle_papers" class="form-control" accept="image/*,.pdf">
                                <?php if(!empty($driver['vehicle_papers_path'])): ?>
                                    <div class="mt-2">
                                        <a href="../<?= htmlspecialchars($driver['vehicle_papers_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            View Current Papers
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Information -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-bank"></i> Bank Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($driver['bank_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Name</label>
                                <input type="text" name="acc_name" class="form-control" value="<?= htmlspecialchars($driver['acc_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="acc_num" class="form-control" value="<?= htmlspecialchars($driver['acc_num'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-toggle2-on"></i> Account Status
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required-field">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" <?= ($driver['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= ($driver['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                                    <option value="rejected" <?= ($driver['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                    <option value="blocked" <?= ($driver['status'] === 'blocked') ? 'selected' : '' ?>>Blocked</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 