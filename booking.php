<?php
session_start();
include 'include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "GET Parameters:\n";
    print_r($_GET);
    echo "\nSession Info:\n";
    print_r($_SESSION);
    echo "</pre>";
}

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get selected driver details
$driver_id = null;
if (isset($_GET['driver_id'])) {
    $driver_id = (int)$_GET['driver_id'];
} else {
    die("No driver ID provided. Please go back and select a driver.");
}

// Fetch driver details with error handling
$sql = "SELECT * FROM drivers WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing driver query: " . $conn->error);
}
$stmt->bind_param("i", $driver_id);
if (!$stmt->execute()) {
    die("Error executing driver query: " . $stmt->error);
}
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    die("Driver not found. Please go back and select a valid driver.");
}

// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_driver'])) {
    $pickup_location = $_POST['pickup_location'];
    $dropoff_location = $_POST['dropoff_location'];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    $duration_days = $_POST['duration_days'];
    $vehicle_type = $_POST['vehicle_type'];
    $trip_purpose = $_POST['trip_purpose'];
    $additional_notes = $_POST['additional_notes'];

    // Calculate amount based on duration and vehicle type
    $base_rate = 5000; // Base rate per day
    $amount = $base_rate * $duration_days;

    // Store booking details in session for payment
    $_SESSION['pending_booking'] = [
        'driver_id' => $driver_id,
        'pickup_location' => $pickup_location,
        'dropoff_location' => $dropoff_location,
        'pickup_date' => $pickup_date,
        'pickup_time' => $pickup_time,
        'duration_days' => $duration_days,
        'vehicle_type' => $vehicle_type,
        'trip_purpose' => $trip_purpose,
        'additional_notes' => $additional_notes,
        'amount' => $amount
    ];

    // Redirect to payment page
    header("Location: payment.php?amount=" . $amount);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Booking - Pro-Drivers</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
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

        .booking-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .selected-driver-card {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .driver-avatar {
            width: 100px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .driver-info h5 {
            margin: 0 0 0.5rem 0;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .driver-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            color: #64748b;
        }

        .driver-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .driver-meta-item i {
            color: #0d6efd;
            font-size: 1.1rem;
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
    <?php include 'partials/sidebar.php'; ?>

    <div class="content">
        <div class="page-header">
            <h3 class="mb-0">Complete Your Booking</h3>
            <p class="mb-0 opacity-75">Fill in the details to book your selected driver</p>
        </div>

        <div class="booking-section">
            <h4 class="mb-4">Selected Driver</h4>
            <div class="selected-driver-card">
                <img src="<?= !empty($driver['profile_picture']) ? $driver['profile_picture'] : 'images/default-profile.png' ?>" 
                     class="driver-avatar" alt="Driver Photo">
                <div class="driver-info">
                    <h5><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h5>
                    <div class="driver-meta">
                        <div class="driver-meta-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span><?= htmlspecialchars($driver['address']) ?></span>
                        </div>
                        <div class="driver-meta-item">
                            <i class="bi bi-clock-history"></i>
                            <span><?= htmlspecialchars($driver['experience']) ?> yrs experience</span>
                        </div>
                        <div class="driver-meta-item">
                            <i class="bi bi-car-front-fill"></i>
                            <span><?= htmlspecialchars($driver['drive']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" id="bookingForm" class="mt-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="pickup_location" name="pickup_location" 
                                   placeholder="Enter pickup location" required>
                            <label for="pickup_location">Pickup Location</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" 
                                   placeholder="Enter dropoff location" required>
                            <label for="dropoff_location">Dropoff Location</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                   min="<?= date('Y-m-d') ?>" required>
                            <label for="pickup_date">Pickup Date</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="time" class="form-control" id="pickup_time" name="pickup_time" required>
                            <label for="pickup_time">Pickup Time</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" class="form-control" id="duration_days" name="duration_days" 
                                   min="1" max="30" value="1" required>
                            <label for="duration_days">Duration (Days)</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                <option value="">Select transmission type</option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                                <option value="Both">Both (Automatic & Manual)</option>
                            </select>
                            <label for="vehicle_type">Transmission Type</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" id="trip_purpose" name="trip_purpose" required>
                                <option value="">Select trip purpose</option>
                                <option value="Business">Business</option>
                                <option value="Personal">Personal</option>
                                <option value="Airport">Airport Transfer</option>
                                <option value="Event">Event</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="trip_purpose">Trip Purpose</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-floating">
                            <textarea class="form-control" id="additional_notes" name="additional_notes" 
                                      style="height: 100px" placeholder="Enter any additional notes"></textarea>
                            <label for="additional_notes">Additional Notes</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" name="book_driver" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle me-2"></i>
                            Confirm Booking
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum time based on selected date
        document.getElementById('pickup_date').addEventListener('change', function() {
            const dateInput = this.value;
            const timeInput = document.getElementById('pickup_time');
            const today = new Date().toISOString().split('T')[0];
            
            if (dateInput === today) {
                const now = new Date();
                const currentHour = String(now.getHours()).padStart(2, '0');
                const currentMinute = String(now.getMinutes()).padStart(2, '0');
                timeInput.min = `${currentHour}:${currentMinute}`;
            } else {
                timeInput.min = '';
            }
        });
    </script>
</body>
</html> 