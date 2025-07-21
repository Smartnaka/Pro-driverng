<?php
require_once '../include/db.php';
require_once '../include/WebhookHandler.php';

// Get raw POST body
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

$paystackSecretKey = getenv('PAYSTACK_SECRET_KEY') ?: 'sk_test_0ca80ae7e863b608623399886ceb90cd29951246';
$handler = new WebhookHandler($conn, $paystackSecretKey);

$result = $handler->handlePaystackWebhook($data, $signature);

header('Content-Type: application/json');
echo json_encode($result); 