<?php
session_start();
include '../include/db.php';
include '../include/security.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel a booking.']);
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

// Validate booking ID
if (!isset($_POST['booking_id']) || !filter_var($_POST['booking_id'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = (int)$_POST['booking_id'];

// Fetch booking and check ownership with additional details
$sql = "SELECT b.*, p.transaction_id, p.amount as paid_amount 
        FROM bookings b 
        LEFT JOIN payments p ON b.id = p.booking_id 
        WHERE b.id = ? AND b.user_id = ? 
        LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement.']);
    exit();
}
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found or does not belong to you.']);
    exit();
}

if (in_array($booking['status'], ['cancelled', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled as it is already ' . $booking['status'] . '.']);
    exit();
}

// Check cancellation time window
$pickup_datetime = strtotime($booking['pickup_date'] . ' ' . $booking['pickup_time']);
$current_time = time();
$hours_until_pickup = ($pickup_datetime - $current_time) / 3600;

// Different rules based on booking status
$allowed_statuses = ['pending_payment', 'pending_driver_response', 'confirmed', 'in_progress'];
if (!in_array($booking['status'], $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled at its current status.']);
    exit();
}

// Calculate cancellation fee and refund amount
$cancellation_fee = 0;
$refund_amount = 0;

if ($booking['status'] !== 'pending_payment' && $booking['paid_amount'] > 0) {
    if ($hours_until_pickup < 2) {
        // Less than 2 hours until pickup - 100% cancellation fee
        $cancellation_fee = $booking['paid_amount'];
        $refund_amount = 0;
    } else if ($hours_until_pickup < 24) {
        // Less than 24 hours until pickup - 50% cancellation fee
        $cancellation_fee = $booking['paid_amount'] * 0.5;
        $refund_amount = $booking['paid_amount'] - $cancellation_fee;
    } else {
        // More than 24 hours until pickup - 10% cancellation fee
        $cancellation_fee = $booking['paid_amount'] * 0.1;
        $refund_amount = $booking['paid_amount'] - $cancellation_fee;
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Update booking status to 'cancelled'
    $cancel_sql = "UPDATE bookings SET 
                   status = 'cancelled',
                   cancellation_time = NOW(),
                   cancellation_fee = ?,
                   refund_amount = ?
                   WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($cancel_sql);
    if (!$stmt) {
        throw new Exception('Could not prepare cancellation statement.');
    }
    $stmt->bind_param("ddii", $cancellation_fee, $refund_amount, $booking_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute cancellation.');
    }
    
    // If there's a driver assigned, notify them
    if (!empty($booking['driver_id'])) {
        $notify_sql = "INSERT INTO notifications (user_id, driver_id, type, message, booking_id) 
                      VALUES (?, ?, 'booking_cancelled', 'Booking has been cancelled by the customer', ?)";
        $notify_stmt = $conn->prepare($notify_sql);
        if ($notify_stmt) {
            $notify_stmt->bind_param("iii", $user_id, $booking['driver_id'], $booking_id);
            $notify_stmt->execute();
            $notify_stmt->close();
        }
    }
    
    // Process refund if applicable
    if ($refund_amount > 0 && !empty($booking['transaction_id'])) {
        // Record refund in refunds table
        $refund_sql = "INSERT INTO refunds (booking_id, amount, transaction_id, status) 
                      VALUES (?, ?, ?, 'pending')";
        $refund_stmt = $conn->prepare($refund_sql);
        if (!$refund_stmt) {
            throw new Exception('Could not prepare refund statement.');
        }
        $refund_stmt->bind_param("ids", $booking_id, $refund_amount, $booking['transaction_id']);
        $refund_stmt->execute();
        $refund_stmt->close();
    }
    
    $conn->commit();
    
    $message = 'Booking cancelled successfully.';
    if ($refund_amount > 0) {
        $message .= ' A refund of ₦' . number_format($refund_amount, 2) . ' will be processed.';
    }
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
$stmt->close();
$conn->close();
?> 