<?php
session_start();
include '../include/db.php';
header('Content-Type: application/json');

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

// Update booking status to 'cancelled'
// We also verify that the booking belongs to the logged-in user to prevent unauthorized cancellations
$sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement.']);
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