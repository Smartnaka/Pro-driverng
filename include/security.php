<?php
/**
 * Security helper functions
 */

// Validate CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
    return true;
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate date format
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validate time format
function validateTime($time, $format = 'H:i:s') {
    $d = DateTime::createFromFormat($format, $time);
    return $d && $d->format($format) === $time;
}

// Validate booking ID
function validateBookingId($id) {
    return is_numeric($id) && $id > 0;
}

// Check if user has permission to access booking
function userCanAccessBooking($conn, $booking_id, $user_id) {
    $stmt = $conn->prepare("SELECT 1 FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
