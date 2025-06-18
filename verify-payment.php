<?php
session_start();
require_once 'include/db.php';
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Function to verify Paystack payment
function verifyPayment($reference) {
    $url = "https://api.paystack.co/transaction/verify/" . $reference;
    
    $headers = [
        "Authorization: Bearer " . $_ENV['PAYSTACK_SECRET_KEY'],
        "Cache-Control: no-cache",
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Get the reference from the callback
$reference = $_GET['reference'] ?? null;

if (!$reference) {
    header("Location: payment.php");
    exit();
}

// Verify the payment
$payment = verifyPayment($reference);

if ($payment['status'] && $payment['data']['status'] === 'success') {
    // Get booking data from metadata
    $booking_data = json_decode($payment['data']['metadata']['booking_data'], true);
    
    // Insert booking
    $insert_sql = "INSERT INTO bookings (
        user_id, 
        driver_id, 
        pickup_location, 
        dropoff_location, 
        pickup_date, 
        pickup_time, 
        duration_days,
        vehicle_type, 
        trip_purpose, 
        additional_notes, 
        status,
        payment_reference,
        amount_paid
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param("iissssissssd", 
            $_SESSION['user_id'],
            $booking_data['driver_id'],
            $booking_data['pickup_location'],
            $booking_data['dropoff_location'],
            $booking_data['pickup_date'],
            $booking_data['pickup_time'],
            $booking_data['duration_days'],
            $booking_data['vehicle_type'],
            $booking_data['trip_purpose'],
            $booking_data['additional_notes'],
            $reference,
            $booking_data['amount']
        );
        
        if ($stmt->execute()) {
            // Create notification for driver
            $notification_sql = "INSERT INTO driver_notifications (driver_id, title, message, type) 
                               VALUES (?, 'New Booking Request', 'You have a new booking request. Please check your dashboard.', 'info')";
            $notify_stmt = $conn->prepare($notification_sql);
            if ($notify_stmt) {
                $notify_stmt->bind_param("i", $booking_data['driver_id']);
                $notify_stmt->execute();
            }
            
            // Clear pending booking from session
            unset($_SESSION['pending_booking']);
            
            // Set success message
            $_SESSION['success_message'] = "Payment successful! Your booking has been confirmed.";
            
            // Redirect to success page
            header("Location: payment-success.php");
            exit();
        }
    }
}

// If we get here, something went wrong
$_SESSION['error_message'] = "Payment verification failed. Please contact support.";
header("Location: payment.php");
exit();
?> 