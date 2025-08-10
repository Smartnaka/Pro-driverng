<?php
// Suppress all output and warnings until we're ready to send JSON
error_reporting(E_ERROR | E_PARSE);
ob_start();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection with proper path
$db_path = file_exists('../include/db.php') ? '../include/db.php' : 'include/db.php';
include $db_path;

// Clear any unwanted output and set JSON header
ob_clean();
header('Content-Type: application/json');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Functions for response ---
function send_response($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}

// --- Main Logic ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method.');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    send_response(false, 'Invalid request, please try again.');
}

// Sanitize and validate inputs
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'];

if (!$email) {
    send_response(false, 'Invalid email format.');
}

if (empty($password)) {
    send_response(false, 'Password cannot be empty.');
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM customers WHERE email = ?");
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start the session and store user information
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();

            send_response(true, 'Login successful!', ['redirect_url' => 'dashboard.php']);
        } else {
            send_response(false, 'Invalid email or password.');
        }
    } else {
        send_response(false, 'Invalid email or password.');
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Login API error: " . $e->getMessage());
    send_response(false, 'An error occurred. Please try again later.');
}

$conn->close();
?> 