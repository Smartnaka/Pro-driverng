<?php
/**
 * API Security Middleware
 * Include this file at the top of all API endpoints
 */

session_start();
include_once __DIR__ . '/db.php';
include_once __DIR__ . '/security.php';

header('Content-Type: application/json');

// CORS headers - adjust as needed for your production environment
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// API Security checks
function validateApiRequest() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    // Skip CSRF validation for GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        // Validate CSRF token from header or post data
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
        if (!$token || !validateCSRFToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit();
        }
    }

    return true;
}

// Sanitize all input data
function sanitizeRequestData($data) {
    if (is_array($data)) {
        return array_map('sanitizeRequestData', $data);
    }
    return sanitizeInput($data);
}

// Clean input data
$_POST = isset($_POST) ? sanitizeRequestData($_POST) : [];
$_GET = isset($_GET) ? sanitizeRequestData($_GET) : [];

// Validate request
validateApiRequest();
