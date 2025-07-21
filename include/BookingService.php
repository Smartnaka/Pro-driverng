<?php
class BookingService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Verify payment with Paystack
     */
    public function verifyPayment($reference, $paystackSecretKey) {
        $url = "https://api.paystack.co/transaction/verify/" . urlencode($reference);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystackSecretKey",
            "Cache-Control: no-cache"
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return [
                'status' => false,
                'error' => $err,
                'data' => null
            ];
        }
        $result = json_decode($response);
        if (!$result || !$result->status) {
            return [
                'status' => false,
                'error' => $result && isset($result->message) ? $result->message : 'Unknown error',
                'data' => $result
            ];
        }
        return [
            'status' => true,
            'data' => $result
        ];
    }

    /**
     * Check for duplicate booking by reference
     */
    public function isDuplicateBooking($reference) {
        $sql = "SELECT id FROM bookings WHERE reference = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }

    /**
     * Create a new booking
     */
    public function createBooking($booking, $user_id, $user_info) {
        $sql = "INSERT INTO bookings (
            user_id, driver_id, pickup_location, dropoff_location, pickup_date, pickup_time, duration_days, vehicle_type, trip_purpose, additional_notes, status, amount, reference, user_email, user_first_name, user_last_name, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'error' => $this->conn->error
            ];
        }
        $additional_notes = isset($booking['additional_notes']) ? $booking['additional_notes'] : '';
        $stmt->bind_param(
            "iisssissssdssss",
            $user_id,
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
            $booking['reference'],
            $user_info['email'],
            $user_info['first_name'],
            $user_info['last_name']
        );
        $result = $stmt->execute();
        if ($result) {
            $booking_id = $stmt->insert_id ? $stmt->insert_id : $this->conn->insert_id;
            $stmt->close();
            return [
                'success' => true,
                'booking_id' => $booking_id
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'success' => false,
                'error' => $error
            ];
        }
    }
} 