<?php
session_start();
require '../include/db.php';
require_once '../include/config.php';

// Update PHPMailer imports with correct namespace and path
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $exp_years = htmlspecialchars(trim($_POST['exp_years']));
    $education = htmlspecialchars(trim($_POST['education']));
    $photo_path = ''; // Set after image upload

    // Validate email
    if (!$email) {
        $error_message = "Invalid email format.";
    } else {
        // Email uniqueness check
        $stmt = $conn->prepare("SELECT id FROM drivers WHERE email = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $error_message = "An account with this email already exists!";
            $stmt->close();
        } else {
            $stmt->close();

            // Upload photo if file is submitted
            if (isset($_FILES['photo_passport']) && $_FILES['photo_passport']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/profile-picture/'; // Update path to match the correct location
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        $error_message = "Failed to create upload directory.";
                    }
                }
                
                // Check if directory is writable
                if (!is_writable($upload_dir)) {
                    $error_message = "Upload directory is not writable.";
                }
                
                if (empty($error_message)) {
                    $filename = time() . '_' . basename($_FILES['photo_passport']['name']);
                    $photo_path = $upload_dir . $filename;

                    if (!move_uploaded_file($_FILES['photo_passport']['tmp_name'], $photo_path)) {
                        $error_message = "Failed to upload photo. Error: " . error_get_last()['message'];
                    } else {
                        // Adjust the path to be relative for database storage
                        $photo_path = 'uploads/profile-picture/' . $filename;
                    }
                }
            }

            if (empty($error_message)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into database using profile_picture field
                $stmt = $conn->prepare("INSERT INTO drivers (first_name, last_name, email, password, phone, exp_years, education, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    die("Database error: " . $conn->error);
                }

                $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $hashed_password, $phone, $exp_years, $education, $photo_path);

                if ($stmt->execute()) {
                    $stmt->close();

                    // Send welcome email
                    require_once __DIR__ . '/send_welcome_email.php';
                    send_welcome_email($email, $first_name);

                    $_SESSION['success_message'] = "Driver account created successfully! Please log in.";
                    header("Location: ../driver/login.php");
                    exit();
                } else {
                    $error_message = "Error: Could not register driver.";
                    $stmt->close();
                }
            }
        }
    }

    // Display any errors
    if (!empty($error_message)) {

      $_SESSION['error_message'] = $error_message;
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
      

    }
}







if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']);
}




?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register - Pro-Drivers</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="../assets/css/style.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
	<link rel="stylesheet" href="../assets/css/driver-theme.css">
	<style>
		body, html {
			height: 100%;
			margin: 0;
			font-family: 'Inter', sans-serif;
			background: #fff;
			color: #333;
		}
		.register-container {
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.register-card {
			background: none;
			border-radius: 0;
			box-shadow: none;
			padding: 2.5rem 2.5rem 2rem 2.5rem;
			max-width: 500px;
			width: 100%;
			margin: 0;
		}

		.page-header {
			text-align: center;
			margin-bottom: 2.5rem;
			position: relative;
			padding-bottom: 1rem;
		}

		.page-header h2 {
			color: #003366;
			font-weight: 600;
			font-size: 2rem;
			margin-bottom: 0.5rem;
		}

		.page-header:after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);
			width: 80px;
			height: 3px;
			background: #003366;
			border-radius: 2px;
		}

		.input-group {
			margin-bottom: 1.5rem;
			border: 1px solid #e1e5ea;
			border-radius: 8px;
			overflow: hidden;
			transition: all 0.3s ease;
		}

		.input-group:focus-within {
			border-color: #003366;
			box-shadow: 0 0 0 2px rgba(0, 51, 102, 0.15);
		}

		.input-group-text {
			background: #f8f9fa;
			border: none;
			color: #003366;
			font-weight: 500;
			min-width: 160px;
			padding: 0.8rem 1rem;
		}

		.form-control {
			border: 1.5px solid #e1e5ea;
			border-radius: 10px;
			padding: 0.8rem 1rem;
			font-size: 0.95rem;
			transition: border-color 0.2s;
		}

		.form-control:focus {
			border-color: #003366;
			box-shadow: 0 0 0 2px rgba(0, 51, 102, 0.15);
		}

		select.form-control {
			cursor: pointer;
		}

		.submit-btn {
			background: #003366;
			color: white;
			border: none;
			padding: 1rem 2rem;
			font-size: 1rem;
			font-weight: 500;
			border-radius: 8px;
			width: 100%;
			transition: all 0.3s ease;
		}

		.submit-btn:hover {
			background: #00509e;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(0, 51, 102, 0.2);
		}

		.form-check {
			margin: 1.5rem 0;
		}

		.form-check-input:checked {
			background-color: #003366;
			border-color: #003366;
		}

		.form-check-label a {
			color: #003366;
			text-decoration: none;
			font-weight: 500;
		}

		.form-check-label a:hover {
			text-decoration: underline;
			color: #00509e;
		}

		.login-link {
			color: #003366;
			text-decoration: none;
			font-weight: 500;
		}

		.login-link:hover {
			text-decoration: underline;
			color: #00509e;
		}

		.alert {
			border-radius: 8px;
			margin-bottom: 2rem;
		}

		@media (max-width: 768px) {
			.register-card {
				padding: 1.5rem;
				margin: 1rem;
			}
			.input-group-text {
				min-width: auto;
			}
			.page-header h2 {
				font-size: 1.75rem;
			}
		}

		.file-upload {
			position: relative;
		}

		.file-upload .form-control {
			padding-left: 1rem;
		}

		.input-group i {
			font-size: 1.1rem;
			width: 20px;
			text-align: center;
			margin-right: 8px;
		}
	</style>
</head>
<body>
<div id="alert-container"></div>


<div class="container-fluid" style="min-height: 100vh; height: 100vh; display: flex; align-items: stretch; justify-content: center; padding: 0;">
    <div class="row w-100" style="min-height: 100vh; height: 100vh; display: flex; align-items: stretch; justify-content: center;">
        <div class="col-lg-5 d-none d-lg-flex flex-column align-items-center justify-content-center" style="background: #003366; min-height: 100vh; height: 100vh;">
            <img src="../images/registerImg.png" alt="Register" style="max-width: 320px; width: 100%; height: auto; display: block; margin: 0 auto;" />
        </div>
        <div class="col-lg-7 col-12 d-flex flex-column justify-content-center" style="background: #fff; min-height: 100vh; height: 100vh; overflow-y: auto;">
            <div style="max-width: 420px; margin: 0 auto; padding: 32px 0 16px 0; width: 100%;">
                <h3 style="font-weight: 700; color: #003366; margin-bottom: 0.5rem;">Onboarding:</h3>
                <div style="font-size: 1.1rem; color: #222; margin-bottom: 2rem; font-weight: 500;">Personal Information</div>
                <form id="driverForm" method="post" enctype="multipart/form-data" style="width: 100%;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" style="font-weight: 500; color: #003366;">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" style="font-weight: 500; color: #003366;">Surname</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" style="font-weight: 500; color: #003366;">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" style="font-weight: 500; color: #003366;">Mobile number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" style="font-weight: 500; color: #003366;">Password</label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" class="form-control" required style="min-height: 38px; padding-right: 2.5rem;">
                                <span id="togglePassword" style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%); cursor: pointer; color: #003366; font-size: 1.1rem;">
                                    <i class="fa fa-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="exp_years" class="form-label" style="font-size: 1rem; font-weight: 500; color: #003366; white-space: nowrap;">Years of experience</label>
                            <select id="exp_years" name="exp_years" class="form-control" required style="min-height: 38px; vertical-align: middle;">
                                <option value="">- Select Experience -</option>
                                <?php for ($i = 1; $i <= 20; $i++) echo "<option value='$i'>$i</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="education" style="font-weight: 500; color: #003366;">Education Level</label>
                            <select id="education" name="education" class="form-control" required>
                                <option value="">- Select Education Level -</option>
                                <option value="Secondary">Secondary</option>
                                <option value="Tertiary">Tertiary</option>
                                <option value="Uneducated">Uneducated</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="photo_passport" style="font-weight: 500; color: #003366;">Upload Your Photo</label>
                            <input type="file" id="photo_passport" name="photo_passport" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" required id="terms">
                        <label class="form-check-label" for="terms">
                            I agree to <a href="Terms and Conditions for Drivers.pdf" target="_blank" style="color: #003366; text-decoration: underline;">Terms of service</a>
                        </label>
                    </div>
                    <button type="submit" name="signup" class="btn w-100" style="background: #003366; color: #fff; font-weight: 600; font-size: 1.1rem; padding: 0.85rem 0; border-radius: 8px;">Create Account</button>
                    <div class="text-center mt-3">
                        <span style="color: #333;">Already have an account? </span><a href="../driver/login.php" style="color: #003366; font-weight: 500; text-decoration: underline;">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<script>
  // Auto-hide alerts after 4 seconds
  setTimeout(function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      alert.classList.add('fade');
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 500); // remove after fade-out
    });
  }, 4000); // time in ms
</script>

<script>
// Password visibility toggle
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const eyeIcon = document.getElementById('eyeIcon');
if (togglePassword && passwordInput && eyeIcon) {
  togglePassword.addEventListener('click', function () {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    eyeIcon.className = type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
  });
}
</script>

<!-- Replace SweetAlert CDN with local if available -->
<script src="../assets/javascript/sweetalert.min.js"></script>
<script src="../assets/javascript/jquery.min.js"></script>



</body>
</html>
