<?php
class WebhookHandler {
    private $conn;
    private $paystackSecretKey;

    public function __construct($conn, $paystackSecretKey) {
        $this->conn = $conn;
        $this->paystackSecretKey = $paystackSecretKey;
    }

    public function handlePaystackWebhook($payload, $signature) {
        // Optionally verify signature here if you store one
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];
        if ($event === 'charge.success') {
            return $this->handlePaymentSuccess($data);
        }
        // Add more event types as needed
        return ['status' => 'ignored'];
    }

    private function handlePaymentSuccess($data) {
        $reference = $data['reference'] ?? null;
        $amount = $data['amount'] ?? null;
        $status = $data['status'] ?? null;
        if (!$reference || !$amount || !$status) {
            return ['error' => 'Missing data'];
        }
        // Update payment/booking status in your DB
        $sql = "UPDATE bookings SET status = 'paid' WHERE reference = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        $stmt->close();
        return ['status' => 'success', 'reference' => $reference];
    }
} 