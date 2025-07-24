<?php
session_start();
include '../include/db.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel a booking.']);
    exit();
}

// Check if booking_id is provided
if (!isset($_POST['booking_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = (int)$_POST['booking_id'];

// Fetch booking and check ownership
$sql = "SELECT status FROM bookings WHERE id = ? AND user_id = ? LIMIT 1";
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
    echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled.']);
    exit();
}

// Only allow cancellation for certain statuses
$allowed_statuses = ['pending_payment', 'pending_driver_response', 'confirmed', 'in_progress'];
if (!in_array($booking['status'], $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'This booking cannot be cancelled at its current status.']);
    exit();
}

// Update booking status to 'cancelled'
$sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare update statement.']);
    exit();
}
$stmt->bind_param("ii", $booking_id, $user_id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not cancel booking. It might not belong to you or may not exist.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to execute cancellation.']);
}
$stmt->close();
$conn->close();
?> 