<?php
session_start();
include '../include/db.php';
include '../include/BookingService.php';
include '../config.php'; // Include config to get environment variables

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if reference is provided
if (!isset($_GET['reference'])) {
    header("Location: ../book-driver.php");
    exit();
}

$reference = $_GET['reference'];

$bookingService = new BookingService($conn);

// Verify the transaction using environment variable
$paystackSecretKey = defined('PAYSTACK_SECRET_KEY') ? PAYSTACK_SECRET_KEY : 'sk_test_0ca80ae7e863b608623399886ceb90cd29951246'; // Use environment variable with fallback
$verifyResult = $bookingService->verifyPayment($reference, $paystackSecretKey);
if (!$verifyResult['status']) {
    error_log("Paystack verification error for reference $reference: " . $verifyResult['error']);
    $_SESSION['payment_error'] = "We couldn't verify your payment due to a network issue. If you have been charged, please contact support with your payment reference. Thank you.";
    header("Location: payment-error.php");
    exit();
}
$tranx = $verifyResult['data'];
if ('success' !== $tranx->data->status) {
    error_log("Payment not successful for reference $reference. Tranx data: " . print_r($tranx, true));
    $_SESSION['payment_error'] = "Your payment was not successful. Please try again or contact support if you have been charged.";
    header("Location: payment-error.php");
    exit();
}

if ('success' == $tranx->data->status) {
    // Payment successful (confirmed from backend)
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
        error_log("Booking failed for user_id {$_SESSION['user_id']} due to missing fields: " . implode(', ', $missing_fields));
        $_SESSION['payment_error'] = "Some required booking information is missing. Please try booking again or contact support if the problem persists.";
        header("Location: payment-error.php");
        exit();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        error_log("Booking failed: user session not found for reference {$booking['reference']}");
        $_SESSION['payment_error'] = "Your session has expired. Please log in and try again.";
        header("Location: payment-error.php");
        exit();
    }

    // Prevent duplicate bookings by checking for existing reference
    if ($bookingService->isDuplicateBooking($booking['reference'])) {
        error_log("Duplicate booking attempt for reference: {$booking['reference']} by user_id: {$_SESSION['user_id']}");
        $_SESSION['payment_error'] = "This booking has already been processed. If you believe you have been charged but did not receive a booking confirmation, please contact our support team with your payment reference.";
        header("Location: payment-success.php?reference=" . urlencode($booking['reference'])); // Always redirect with reference
        exit();
    }
    
    // Fetch user email, first name, and last name for notification and DB
    $user_id = $_SESSION['user_id'];
    $user_email = '';
    $user_first_name = '';
    $user_last_name = '';
    $user_sql = "SELECT email, first_name, last_name FROM customers WHERE id = ? LIMIT 1";
    $user_stmt = $conn->prepare($user_sql);
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_stmt->bind_result($user_email, $user_first_name, $user_last_name);
        $user_stmt->fetch();
        $user_stmt->close();
    }
    
    $user_info = [
        'email' => $user_email,
        'first_name' => $user_first_name,
        'last_name' => $user_last_name
    ];
    
    // Create booking with transaction support
    $createResult = $bookingService->createBooking($booking, $_SESSION['user_id'], $user_info);
    if ($createResult['success']) {
        // Send notification email with error handling
        try {
            include_once '../include/SecureMailer.php';
            $mailer = new SecureMailer();
            $emailResult = $mailer->sendBookingConfirmationEmail($user_email, $user_first_name);
            
            if (!$emailResult) {
                error_log("Failed to send booking confirmation email to: $user_email for booking reference: {$booking['reference']}");
                // Don't fail the booking if email fails, just log it
            }
        } catch (Exception $e) {
            error_log("Exception sending booking confirmation email: " . $e->getMessage());
            // Don't fail the booking if email fails, just log it
        }
        
        // Redirect to payment-success page with reference in URL
        unset($_SESSION['booking_details']);
        unset($_SESSION['pending_booking']);
        header("Location: payment-success.php?reference=" . urlencode($booking['reference']));
        exit();
    } else {
        error_log("Booking insert failed for reference {$booking['reference']}: " . $createResult['error']);
        $_SESSION['payment_error'] = "We couldn't complete your booking due to a technical issue. If you have been charged, please contact support with your payment reference.";
        header("Location: payment-error.php");
        exit();
    }
} else {
    // Payment failed
    error_log("Payment not successful for reference $reference. Tranx data: " . print_r($tranx, true));
    $_SESSION['payment_error'] = "Your payment was not successful. Please try again or contact support if you have been charged.";
    header("Location: payment-error.php");
    exit();
}
?>
<?php include '../partials/sidebar.php'; ?> 