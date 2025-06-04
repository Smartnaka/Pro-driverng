<?php
session_start();
require './include/db.php';
require_once './include/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './vendor/autoload.php';



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
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                $filename = time() . '_' . basename($_FILES['photo']['name']);
                $photo_path = $upload_dir . $filename;

                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                    $error_message = "Failed to upload photo.";
                }
            }

            if (empty($error_message)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into database
                $stmt = $conn->prepare("INSERT INTO drivers (first_name, last_name, email, password, phone, exp_years, education, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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
                            <p style='font-size: 12px; color: #777;'>This email was sent to you because you signed up as a driver. If you didn’t register, you can ignore this email.</p>
                        ";
                        $mail->AltBody = "Hello {$first_name},\n\nWelcome to Pro-Drivers! Your registration was successful.";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("PHPMailer Error: " . $mail->ErrorInfo);
                    }

                    $_SESSION['success_message'] = "Driver account created successfully! Please log in.";
                    header("Location: login.php");
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
	<link href="./assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="../assets/css/style.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert-dev.js"></script>
	<style>
		body { background-color: #f8f9fa; }
		.button1 {
			background-color: #1C98ED;
			border: none;
			color: white;
			padding: 12px;
			font-size: 17px;
			width: 100%;
			border-radius: 12px;
			cursor: pointer;
		}
		.special-header {
			font-family: 'Poppins', sans-serif;
			font-size: 2.5rem;
			background: linear-gradient(90deg, #007bff, #00c6ff);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
			margin-bottom: 1.5rem;
			border-bottom: 3px solid #007bff;
			display: inline-block;
			padding-bottom: 5px;
			text-align: center;
		}
		.input-group-text {
			background-color: #e9ecef;
			border-right: 0;
			padding-left: 12px;
		}
		.form-control {
			font-size: 16px;
			padding: 10px 12px;
		}
		.input-group i {
			margin-right: 10px;
		}
	</style>
</head>
<body>
<div id="alert-container"></div>


<div class="container mt-5">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<h2 class="special-header">Onboarding Driver</h2>
			<form id="driverForm" method="post" enctype="multipart/form-data">
				<div class="row g-3">
					<div class="col-md-6">
						<div class="input-group">
							<label for="first_name" class="input-group-text"><i class="fas fa-user"></i> First Name</label>
							<input type="text" id="first_name" name="first_name" class="form-control"  required>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="last_name" class="input-group-text"><i class="fas fa-user-tag"></i> Surname</label>
							<input type="text" id="last_name" name="last_name" class="form-control" required>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="email" class="input-group-text"><i class="fas fa-envelope"></i> Email</label>
							<input type="email" id="email" name="email" class="form-control"  required>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="phone" class="input-group-text"><i class="fas fa-phone"></i> Mobile Number</label>
							<input type="tel" id="phone" name="phone" class="form-control" required>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="password" class="input-group-text"><i class="fas fa-lock"></i> Password</label>
							<input type="password" id="password" name="password" class="form-control" required>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="exp_years" class="input-group-text"><i class="fas fa-road"></i> Years of Experience</label>
							<select id="exp_years" name="exp_years" class="form-control" required>
								<option value="">Select Experience</option>
								<?php for ($i = 1; $i <= 20; $i++) echo "<option value='$i'>$i</option>"; ?>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="education" class="input-group-text"><i class="fas fa-graduation-cap"></i> Education Level</label>
							<select id="education" name="education" class="form-control" required>
								<option value="">Select Education</option>
								<option value="Secondary">Secondary</option>
								<option value="Tertiary">Tertiary</option>
								<option value="Uneducated">Uneducated</option>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<label for="photo_passport" class="input-group-text"><i class="fas fa-image"></i> Upload Passport Photo</label>
							<input type="file" id="photo_passport" name="photo_passport" class="form-control" accept="image/*" required>
						</div>
					</div>
					<div class="col-md-12 mt-3">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" required id="terms">
							<label class="form-check-label" for="terms">I agree to the <a href="Terms and Conditions for Drivers.pdf" target="_blank">Terms of Service</a></label>
						</div>
					</div>
					<div class="col-md-12 mt-3">
						<button type="submit" name="signup" class="btn button1">Create Account</button>
					</div>
					<div class="col-md-12 text-center mt-3">
						<p>Already registered? <a href="./driver/login.php">Login</a></p>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>




<script>
document.getElementById('driverForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  fetch('register_driver.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const alertBox = document.getElementById('alert-container');

    alertBox.innerHTML = `
      <div class="alert alert-${data.status} alert-dismissible fade show mt-3" role="alert">
        ${data.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;

    // Auto-hide after 4 seconds
    setTimeout(() => {
      const alert = document.querySelector('.alert');
      if (alert) {
        alert.classList.remove('show');
        alert.classList.add('fade');
        setTimeout(() => alert.remove(), 500);
      }
    }, 4000);

    if (data.status === 'success') {
      form.reset(); // Clear form on success
    }
  })
  .catch(err => {
    console.error('AJAX error:', err);
  });
});
</script>
</body>
</html>
