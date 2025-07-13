<?php
session_start();
include '../include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if reference is provided
if (!isset($_GET['reference'])) {
    header("Location: ../book-driver.php");
    exit();
}

$reference = $_GET['reference'];

// Verify the transaction
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer sk_test_0ca80ae7e863b608623399886ceb90cd29951246", // Replace with your secret key
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    // Handle error
    $_SESSION['payment_error'] = "Error verifying payment: " . $err;
    header("Location: payment-error.php");
    exit();
}

$tranx = json_decode($response);

if (!$tranx->status) {
    // Handle error
    $_SESSION['payment_error'] = "Payment verification failed: " . $tranx->message;
    header("Location: payment-error.php");
    exit();
}

if ('success' == $tranx->data->status) {
    // Payment successful
    // Check if booking details exist in session
    if (!isset($_SESSION['booking_details']) || empty($_SESSION['booking_details'])) {
        $_SESSION['payment_error'] = "Booking details not found. Please try booking again.";
        header("Location: payment-error.php");
        exit();
    }
    
    // Get booking details from session
    $booking = $_SESSION['booking_details'];
    
    // Debug: Log booking details (remove in production)
    error_log("Booking details: " . print_r($booking, true));
    
    // Validate required fields
    $required_fields = [
        'driver_id', 'pickup_location', 'dropoff_location', 'pickup_date', 
        'pickup_time', 'duration_days', 'vehicle_type', 'trip_purpose', 'amount', 'reference'
    ];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($booking[$field]) || empty($booking[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $_SESSION['payment_error'] = "Missing required booking information: " . implode(', ', $missing_fields) . ". Please try booking again.";
        header("Location: payment-error.php");
        exit();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['payment_error'] = "User session not found. Please login and try again.";
        header("Location: payment-error.php");
        exit();
    }
    
    // Prepare the SQL statement with proper error handling
    $sql = "INSERT INTO bookings (
        user_id, driver_id, pickup_location, dropoff_location, pickup_date, pickup_time, duration_days, vehicle_type, trip_purpose, additional_notes, status, amount, reference, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['payment_error'] = "Database error: " . $conn->error;
        header("Location: payment-error.php");
        exit();
    }
    
    $additional_notes = isset($booking['additional_notes']) ? $booking['additional_notes'] : '';
    
    $stmt->bind_param(
        "iisssissssds",
        $_SESSION['user_id'],
        $booking['driver_id'],
        $booking['pickup_location'],
        $booking['dropoff_location'],
        $booking['pickup_date'],
        $booking['pickup_time'],
        $booking['duration_days'],
        $booking['vehicle_type'],
        $booking['trip_purpose'],
        $additional_notes,
        $booking['amount'],
        $booking['reference']
    );
    
    if ($stmt->execute()) {
        unset($_SESSION['booking_details']);
        unset($_SESSION['pending_booking']);
        header("Location: payment-success.php");
        exit();
    } else {
        $_SESSION['payment_error'] = "Error saving booking: " . $stmt->error;
        header("Location: payment-error.php");
        exit();
    }
} else {
    // Payment failed
    $_SESSION['payment_error'] = "Payment was not successful";
    header("Location: payment-error.php");
    exit();
}
?>
<?php include '../partials/sidebar.php'; ?> 