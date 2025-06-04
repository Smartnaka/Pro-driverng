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
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'israelbabs59@gmail.com';
                        $mail->Password = 'uenb rrvr lyrl rzje'; // App password
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('no-reply@prodrivers.com', 'Pro-Drivers');
                        $mail->addAddress($email, $first_name);

                        $mail->isHTML(true);
                        $mail->Subject = '🎉 Driver Registration Successful - Welcome to Pro-Drivers!';
                        $mail->Body = "
                            <h2>Hello {$first_name},</h2>
                            <p>Welcome to <strong>Pro-Drivers</strong>! Your registration as a driver was successful.</p>
                            <p>You can now log in and start using our platform.</p>
                            <br><hr>
                            <p style='font-size: 12px; color: #777;'>This email was sent to you because you signed up as a driver. If you didn't register, you can ignore this email.</p>
                        ";
                        $mail->AltBody = "Hello {$first_name},\n\nWelcome to Pro-Drivers! Your registration was successful.";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("PHPMailer Error: " . $mail->ErrorInfo);
                    }

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
	<style>
		body {
			background: #f0f2f5;
			font-family: 'Inter', sans-serif;
			min-height: 100vh;
			color: #333;
		}

		.main-container {
			background: white;
			border-radius: 16px;
			box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
			padding: 2.5rem;
			margin: 2rem auto;
			max-width: 1000px;
		}

		.page-header {
			text-align: center;
			margin-bottom: 2.5rem;
			position: relative;
			padding-bottom: 1rem;
		}

		.page-header h2 {
			color: #1a73e8;
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
			background: #1a73e8;
			border-radius: 2px;
		}

		.form-section {
			background: #fff;
			border-radius: 12px;
		}

		.input-group {
			margin-bottom: 1.5rem;
			border: 1px solid #e1e5ea;
			border-radius: 8px;
			overflow: hidden;
			transition: all 0.3s ease;
		}

		.input-group:focus-within {
			border-color: #1a73e8;
			box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
		}

		.input-group-text {
			background: #f8f9fa;
			border: none;
			color: #1a73e8;
			font-weight: 500;
			min-width: 160px;
			padding: 0.8rem 1rem;
		}

		.form-control {
			border: none;
			padding: 0.8rem 1rem;
			font-size: 0.95rem;
		}

		.form-control:focus {
			box-shadow: none;
		}

		select.form-control {
			cursor: pointer;
		}

		.submit-btn {
			background: #1a73e8;
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
			background: #1557b0;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(26, 115, 232, 0.2);
		}

		.form-check {
			margin: 1.5rem 0;
		}

		.form-check-input:checked {
			background-color: #1a73e8;
			border-color: #1a73e8;
		}

		.form-check-label a {
			color: #1a73e8;
			text-decoration: none;
			font-weight: 500;
		}

		.form-check-label a:hover {
			text-decoration: underline;
		}

		.login-link {
			color: #1a73e8;
			text-decoration: none;
			font-weight: 500;
		}

		.login-link:hover {
			text-decoration: underline;
		}

		.alert {
			border-radius: 8px;
			margin-bottom: 2rem;
		}

		@media (max-width: 768px) {
			.main-container {
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


<div class="container">
	<div class="main-container">
		<div class="page-header">
			<h2>Join Our Driver Network</h2>
			<p class="text-muted">Create your professional driver account</p>
		</div>

		<form id="driverForm" method="post" enctype="multipart/form-data">
			<div class="row g-4">
				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-user"></i> First Name
						</span>
						<input type="text" id="first_name" name="first_name" class="form-control" required>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-user-tag"></i> Last Name
						</span>
						<input type="text" id="last_name" name="last_name" class="form-control" required>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-envelope"></i> Email
						</span>
						<input type="email" id="email" name="email" class="form-control" required>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-phone"></i> Phone
						</span>
						<input type="tel" id="phone" name="phone" class="form-control" required>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-lock"></i> Password
						</span>
						<input type="password" id="password" name="password" class="form-control" required>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-road"></i> Experience
						</span>
						<select id="exp_years" name="exp_years" class="form-control" required>
							<option value="">Select Years</option>
							<?php for ($i = 1; $i <= 20; $i++) echo "<option value='$i'>$i Years</option>"; ?>
						</select>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group">
						<span class="input-group-text">
							<i class="fas fa-graduation-cap"></i> Education
						</span>
						<select id="education" name="education" class="form-control" required>
							<option value="">Select Level</option>
							<option value="Secondary">Secondary</option>
							<option value="Tertiary">Tertiary</option>
							<option value="Uneducated">Uneducated</option>
						</select>
					</div>
				</div>

				<div class="col-md-6">
					<div class="input-group file-upload">
						<span class="input-group-text">
							<i class="fas fa-image"></i> Photo
						</span>
						<input type="file" id="photo_passport" name="photo_passport" class="form-control" accept="image/*" required>
					</div>
				</div>

				<div class="col-12">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" required id="terms">
						<label class="form-check-label" for="terms">
							I agree to the <a href="Terms and Conditions for Drivers.pdf" target="_blank">Terms of Service</a>
						</label>
					</div>
				</div>

				<div class="col-12">
					<button type="submit" name="signup" class="submit-btn">
						<i class="fas fa-user-plus me-2"></i> Create Account
					</button>
				</div>

				<div class="col-12 text-center mt-4">
					<p class="mb-0">Already have an account? <a href="../driver/login.php" class="login-link">Log in</a></p>
				</div>
			</div>
		</form>
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




<script src="../assets/js/jquery.min.js"></script>
</body>
</html>
