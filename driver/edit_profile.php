<?php
session_start();
include '../include/db.php';

// Display success message if exists
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token. Please try again.');
    }
}

$id = $_SESSION['driver_id'];
$query = "SELECT * FROM drivers WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$driver = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$success = "";
$error = "";

$fullname = $driver['first_name'] . ' ' . $driver['last_name'];
$profile_picture = $driver['profile_picture'] ?? '../images/default-profile.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $address = trim($_POST['address'] ?? '');
    $experience = filter_var($_POST['experience'] ?? '', FILTER_VALIDATE_INT);
    $license_number = trim($_POST['license_number'] ?? '');
    $about_me = trim($_POST['about_me'] ?? '');
    $resident = trim($_POST['resident'] ?? '');
    $family = trim($_POST['family'] ?? '');
    $education_level = trim($_POST['education_level'] ?? '');
    $drive = trim($_POST['drive'] ?? '');
    $speak = trim($_POST['speak'] ?? '');
    $nin = trim($_POST['nin'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $bank_name = trim($_POST['bank_name'] ?? '');
    $acc_num = trim($_POST['acc_num'] ?? '');
    $acc_name = trim($_POST['acc_name'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($address)) $errors[] = "Location is required";
    if (!$experience || $experience < 1 || $experience > 20) $errors[] = "Experience must be between 1 and 20 years";
    if (empty($license_number)) $errors[] = "License number is required";
    if (empty($about_me)) $errors[] = "About me is required";
    if (empty($resident)) $errors[] = "Residential address is required";
    if (empty($drive)) $errors[] = "Vehicle types is required";
    if (empty($speak)) $errors[] = "Languages spoken is required";
    if (!empty($nin) && !preg_match('/^\d{11}$/', $nin)) $errors[] = "NIN must be 11 digits";
    if (!empty($dob)) {
        $dob_timestamp = strtotime($dob);
        $min_age = strtotime('-65 years');
        $max_age = strtotime('-18 years');
        if ($dob_timestamp > $max_age || $dob_timestamp < $min_age) {
            $errors[] = "Age must be between 18 and 65 years";
        }
    }
    if (!empty($acc_num) && !preg_match('/^\d{10}$/', $acc_num)) $errors[] = "Account number must be 10 digits";
    if (empty($bank_name)) $errors[] = "Bank name is required";
    if (empty($acc_name)) $errors[] = "Account name is required";

    // If there are no errors, proceed with database update
    if (empty($errors)) {
        try {
            // Verify database connection
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }

            // Simplified update query
            $sql = "UPDATE drivers SET 
                address = ?, 
                experience = ?, 
                license_number = ?, 
                about_me = ?, 
                resident = ?, 
                family = ?, 
                education_level = ?, 
                drive = ?, 
                speak = ?, 
                nin = ?, 
                dob = ?, 
                bank_name = ?, 
                acc_num = ?, 
                acc_name = ?, 
                skills = ?
                WHERE id = ?";

            $params = [
                $address, 
                $experience, 
                $license_number, 
                $about_me,
                $resident, 
                $family, 
                $education_level, 
                $drive, 
                $speak, 
                $nin, 
                $dob,
                $bank_name, 
                $acc_num, 
                $acc_name, 
                $skills,
                $id
            ];
            $types = "sisssssssssssssi";

            // Debug information
            error_log("Updating driver profile with data:");
            error_log("Driver ID: " . $id);
            error_log("SQL: " . $sql);
            error_log("Parameters: " . print_r($params, true));

            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
            }

            if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
                throw new Exception("Failed to bind parameters: " . mysqli_stmt_error($stmt));
            }

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
            }

            $affected_rows = mysqli_stmt_affected_rows($stmt);
            error_log("Affected rows: " . $affected_rows);

            mysqli_stmt_close($stmt);

            if ($affected_rows > 0) {
                // Refresh the driver data
                $query = "SELECT * FROM drivers WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $driver = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);

                echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your profile has been updated successfully',
                        icon: 'success',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                </script>";
            } else {
                throw new Exception("No changes were made to the profile. Please try again.");
            }

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: '" . addslashes($e->getMessage()) . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Validation Error!',
                html: '" . addslashes(implode("<br>", $errors)) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Array of locations
$locations = [
    "Agege", "Aguda", "Ajah", "Ajegunle", "Ajeromi-Ifelodun", "Akerele", "Akoka", "Alaba", "Alagomeji",
    "Alausa", "Alimosho", "Amuwo Odofin", "Anthony Village", "Apapa", "Badagry", "Bariga", "Coker",
    "Dolphin Estate", "Dopemu", "Ebute Metta", "Epe", "Eti-Osa", "Festac Town", "Gbagada", "Ifako - Ijaiye",
    "Ijesha", "Ijora", "Ikeja", "Ikorodu", "Ikoyi", "Ilasamaja", "Ilupeju", "Iwaya", "Iyana", "Ipaja",
    "Jibowu", "Ketu", "Kosofe", "Ladipo", "Lagos Island", "Lagos Mainland", "Lawanson", "Lekki", "Marina",
    "Maryland", "Masha", "Maza Maza", "Mende", "Mile 2", "Mushin", "Obalende", "Obanikoro", "Ogba", "Ogudu",
    "Ojo", "Ojodu", "Ojodu Berger", "Ojota", "Ojuelegba", "Olodi", "Onigbongbo", "Onipanu", "Oniru", "Opebi",
    "Oregun", "Oshodi - Isolo", "Palmgrove", "Papa Ajao", "Sabo", "Satellite Town", "Shomolu", "Surulere",
    "Takwa Bay", "Tinubu Square", "Victoria Garden", "City", "Victoria", "Island", "Yaba"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            overflow-x: hidden;
        }

        .content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: white;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hamburger-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e50;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hamburger-btn:focus {
            outline: none;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }

        /* Sidebar modifications */
        #sidebar {
            transition: transform 0.3s ease;
            z-index: 1050;
        }

        @media (max-width: 768px) {
            .mobile-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 4.5rem;
            }

            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.active {
                transform: translateX(0);
            }

            .mobile-overlay.active {
                display: block;
            }
        }

        .form-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.625rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
        }

        .form-control:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #2c3e50;
            border: none;
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #34495e;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-section-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-floating > .form-control:disabled {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<!-- Mobile Navigation -->
<nav class="mobile-nav d-md-none">
    <button class="hamburger-btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
        <span class="d-none d-sm-inline">Menu</span>
    </button>
    <span class="fw-bold">Edit Profile</span>
    <div style="width: 2rem;"><!-- Empty div for flex spacing --></div>
</nav>

<!-- Mobile Overlay -->
<div class="mobile-overlay" onclick="toggleSidebar()"></div>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="form-container">
        <h4 class="section-title">
            <i class="bi bi-person-circle me-2"></i>
            Edit Driver Profile
        </h4>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <!-- Personal Information -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-person"></i>
                    Personal Information
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($driver['first_name']) ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($driver['last_name']) ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($driver['email']) ?>" disabled>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-geo-alt"></i>
                    Location Details
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <select name="address" class="form-select" required>
                            <option value="">--Select Location--</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc) ?>" <?= ($driver['address'] === $loc) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Residential Address</label>
                        <input type="text" name="resident" class="form-control" value="<?= htmlspecialchars($driver['resident'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-briefcase"></i>
                    Professional Information
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Driving Experience (Years)</label>
                        <input class="form-control" name="experience" type="number" value="<?= htmlspecialchars($driver['experience'] ?? '') ?>" min="1" max="20" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Driver's License Number</label>
                        <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($driver['license_number'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vehicle Types</label>
                        <select name="drive" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php
                            $driveOptions = [
                              'Car, Bus',
                              'Car, Bus, Coaster',
                              'Car, Bus, Coaster, Motorcycle/Tricycle'
                            ];
                            foreach ($driveOptions as $drive):
                            ?>
                              <option value="<?= $drive ?>" <?= ($driver['drive'] ?? '') === $drive ? 'selected' : '' ?>><?= $drive ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-info-circle"></i>
                    Additional Information
                </div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">About Me</label>
                        <textarea name="about_me" class="form-control" rows="3" required><?= htmlspecialchars($driver['about_me'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Languages Spoken</label>
                        <input type="text" name="speak" class="form-control" value="<?= htmlspecialchars($driver['speak'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Skills</label>
                        <input class="form-control" name="skills" type="text" value="<?= htmlspecialchars($driver['skills'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Personal Documents -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-file-earmark-text"></i>
                    Personal Documents
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">NIN</label>
                        <input type="text" name="nin" class="form-control" value="<?= htmlspecialchars($driver['nin'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input class="form-control" name="dob" type="date" value="<?= htmlspecialchars($driver['dob'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-bank"></i>
                    Bank Information
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Bank Name</label>
                        <input class="form-control" name="bank_name" type="text" value="<?= htmlspecialchars($driver['bank_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Number</label>
                        <input class="form-control" name="acc_num" type="text" value="<?= htmlspecialchars($driver['acc_num'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Name</label>
                        <input class="form-control" name="acc_name" type="text" value="<?= htmlspecialchars($driver['acc_name'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" name="update-acct" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add SweetAlert2 JS before your custom script -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.querySelector('.mobile-overlay').classList.toggle('active');
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
            document.querySelector('.mobile-overlay').classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Show success message with SweetAlert2
    window.addEventListener('DOMContentLoaded', () => {
        if (window.location.search.includes('updated=1')) {
            Swal.fire({
                title: 'Success!',
                text: 'Your profile has been updated successfully',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            window.history.replaceState({}, document.title, "edit_profile.php");
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const formFields = {
            address: { required: true },
            experience: { required: true, min: 1, max: 20 },
            license_number: { required: true },
            about_me: { required: true },
            resident: { required: true },
            drive: { required: true },
            speak: { required: true },
            nin: { pattern: /^\d{11}$/, required: false },
            dob: { 
                required: false,
                validate: function(value) {
                    if (!value) return true;
                    const date = new Date(value);
                    const now = new Date();
                    const age = now.getFullYear() - date.getFullYear();
                    return age >= 18 && age <= 65;
                }
            },
            acc_num: { pattern: /^\d{10}$/, required: true },
            bank_name: { required: true },
            acc_name: { required: true }
        };

        // Real-time validation
        Object.keys(formFields).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            field.addEventListener('input', function() {
                validateField(field, formFields[fieldName]);
            });

            field.addEventListener('blur', function() {
                validateField(field, formFields[fieldName]);
            });
        });

        function validateField(field, rules) {
            const value = field.value.trim();
            let isValid = true;
            let errorMessage = '';

            // Required validation
            if (rules.required && !value) {
                isValid = false;
                errorMessage = `${field.previousElementSibling.textContent} is required`;
            }
            // Pattern validation
            else if (rules.pattern && value && !rules.pattern.test(value)) {
                isValid = false;
                errorMessage = `${field.previousElementSibling.textContent} is invalid`;
            }
            // Min/Max validation
            else if (rules.min !== undefined && value && (parseInt(value) < rules.min || parseInt(value) > rules.max)) {
                isValid = false;
                errorMessage = `${field.previousElementSibling.textContent} must be between ${rules.min} and ${rules.max}`;
            }
            // Custom validation
            else if (rules.validate && !rules.validate(value)) {
                isValid = false;
                errorMessage = `${field.previousElementSibling.textContent} is invalid`;
            }

            // Update UI
            field.classList.toggle('is-invalid', !isValid);
            field.classList.toggle('is-valid', isValid && value);

            // Update or create feedback div
            let feedback = field.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.parentNode.insertBefore(feedback, field.nextSibling);
            }
            feedback.textContent = errorMessage;

            return isValid;
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            let isValid = true;
            Object.keys(formFields).forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field && !validateField(field, formFields[fieldName])) {
                    isValid = false;
                }
            });

            if (!isValid) {
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';

            // Prepare form data
            const formData = new FormData(form);

            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Check if the response contains success message
                if (html.includes('success')) {
                    // Parse the HTML response to get updated values
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    // Update form fields with new values
                    Object.keys(formFields).forEach(fieldName => {
                        const newField = tempDiv.querySelector(`[name="${fieldName}"]`);
                        const currentField = form.querySelector(`[name="${fieldName}"]`);
                        if (newField && currentField) {
                            // For select elements
                            if (currentField.tagName === 'SELECT') {
                                const newValue = newField.value;
                                Array.from(currentField.options).forEach(option => {
                                    option.selected = option.value === newValue;
                                });
                            } else {
                                // For other input types
                                currentField.value = newField.value;
                            }
                        }
                    });

                    // Show success message using SweetAlert2
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your profile has been updated successfully',
                        icon: 'success',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    // Extract error message if present
                    const errorMatch = html.match(/<div class="alert alert-danger">(.*?)<\/div>/);
                    if (errorMatch) {
                        Swal.fire({
                            title: 'Error!',
                            html: errorMatch[1],
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        throw new Error('Unknown error occurred');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while saving your changes. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    });
</script>
</body>
</html>